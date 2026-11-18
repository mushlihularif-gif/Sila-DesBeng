<?php

namespace App\Services;

use App\Models\KycVerification;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class IdentityReviewService
{
    public function reviewKyc(int $id, User $reviewer, bool $approved, ?string $notes): bool
    {
        $record = DB::transaction(function () use ($id, $reviewer, $approved, $notes) {
            $kyc = KycVerification::lockForUpdate()->findOrFail($id);
            $owner = User::lockForUpdate()->findOrFail($kyc->user_id);
            $this->authorize($reviewer, $owner);
            abort_unless($kyc->status === 'pending' && $owner->verification_status === 'pending', 409,
                'Pengajuan ini sudah berubah atau selesai ditinjau. Muat ulang daftar verifikasi.');
            abort_if(KycVerification::where('user_id', $owner->id)->where('id', '>', $kyc->id)->exists(), 409,
                'Warga telah membuat pengajuan yang lebih baru.');

            if ($approved) {
                $this->requireDocuments($kyc);
                $nik = $kyc->nik_from_ocr;
                if (! is_string($nik) || ! preg_match('/^\d{16}$/D', $nik)) {
                    throw ValidationException::withMessages(['admin_notes' => 'NIK harus berisi 16 angka. Minta warga memperbaiki pengajuannya.']);
                }
                $this->requireUniqueIdentity($owner, $nik);
                $owner->nik = $nik;
                $owner->name = $kyc->name_from_ocr ?: $owner->name;
                $owner->gender = $kyc->gender_from_ocr ?? $owner->gender;
                // Domisili dan struktur RT/RW mengikuti akun. OCR bukan persetujuan pindah domisili.
            }
            $owner->verification_status = $approved ? 'verified' : 'rejected';
            $owner->verified_at = $approved ? now() : null;
            $owner->save();
            $kyc->update([
                'status' => $approved ? 'approved' : 'rejected',
                'admin_notes' => $notes,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'face_scan_data' => null,
            ]);

            return $kyc;
        });

        $this->notify($record->user, $approved, $notes);

        return $this->cleanup($record);
    }

    public function reviewLegacy(int $id, User $reviewer, bool $approved, ?string $notes): bool
    {
        $owner = DB::transaction(function () use ($id, $reviewer, $approved, $notes) {
            $owner = User::lockForUpdate()->findOrFail($id);
            $this->authorize($reviewer, $owner, true);
            abort_unless($owner->verification_status === 'pending', 409,
                'Pengajuan ini sudah berubah atau selesai ditinjau. Muat ulang daftar verifikasi.');
            abort_if(KycVerification::where('user_id', $owner->id)->whereIn('status', ['draft', 'pending'])->exists(), 409,
                'Tinjau pengajuan ini melalui halaman Verifikasi Identitas (KYC).');
            if ($approved) {
                $this->requireDocuments($owner);
                if ($owner->nik) {
                    $this->requireUniqueIdentity($owner, $owner->nik);
                }
            }
            $owner->verification_status = $approved ? 'verified' : 'rejected';
            $owner->verified_at = $approved ? now() : null;
            $owner->ktp_rejection_reason = $approved ? null : $notes;
            $owner->save();

            return $owner;
        });

        $this->notify($owner, $approved, $notes);

        return $this->cleanup($owner);
    }

    private function authorize(User $reviewer, User $owner, bool $legacy = false): void
    {
        abort_unless(in_array($reviewer->role, $legacy ? ['admin_desa', 'super_admin'] : ['admin_desa'], true), 403);
        abort_if($reviewer->status === 'non_aktif', 403);
        if ($legacy && $reviewer->role === 'super_admin') {
            return;
        }
        abort_unless($reviewer->region_id && in_array($owner->region_id,
            array_merge([$reviewer->region_id], Region::getDescendantIds($reviewer->region_id))), 403);
    }

    private function requireUniqueIdentity(User $owner, string $nik): void
    {
        $hash = hash_hmac('sha256', $nik, config('app.key'));
        if (User::where('id', '!=', $owner->id)->where('nik_hash', $hash)->where('verification_status', 'verified')->exists()) {
            throw ValidationException::withMessages(['admin_notes' => 'NIK ini sudah digunakan akun terverifikasi lain. Periksa kepemilikan identitas.']);
        }
    }

    private function requireDocuments(Model $record): void
    {
        foreach (['ktp', 'face'] as $type) {
            $path = $record->{IdentityDocumentContext::pathColumn($record, $type)};
            $version = (int) ($record->{IdentityDocumentContext::versionColumn($record, $type)} ?? 1);
            $valid = false;
            if ($path && ! str_contains($path, '..') && ! str_contains($path, '\\') && ! str_contains($path, "\0")) {
                $disk = Storage::disk('private');
                if ($disk->exists($path)) {
                    $aad = $version === 2 ? IdentityDocumentContext::aad($record, $type, $path) : null;
                    $bytes = FileEncryptionService::decrypt($disk->get($path), $aad, $version);
                    $valid = $bytes && @getimagesizefromstring($bytes) !== false;
                }
            }
            if (! $valid) {
                throw ValidationException::withMessages(['admin_notes' => 'Foto KTP dan wajah harus tersedia dan dapat dibaca sebelum pengajuan disetujui.']);
            }
        }
    }

    private function notify(User $owner, bool $approved, ?string $notes): void
    {
        if ($approved) {
            NotificationService::notifyKycApproved($owner);
        } else {
            NotificationService::notifyKycRejected($owner, $notes);
        }
    }

    /** Delete only after the decision commits; retain failed paths so cleanup can be retried. */
    private function cleanup(Model $record): bool
    {
        $complete = true;
        foreach (['ktp', 'face'] as $type) {
            $column = IdentityDocumentContext::pathColumn($record, $type);
            $path = $record->$column;
            if (! $path) {
                continue;
            }
            try {
                $disk = Storage::disk('private');
                if ($disk->exists($path) && ! $disk->delete($path)) {
                    throw new \RuntimeException('Identity cleanup failed');
                }
                $record->newQuery()->whereKey($record->getKey())->where($column, $path)->update([$column => null]);
            } catch (\Throwable $e) {
                $complete = false;
                Log::warning('Pembersihan dokumen identitas perlu diulang.', [
                    'record_type' => $record->getMorphClass(), 'record_id' => $record->getKey(), 'document_type' => $type,
                ]);
            }
        }

        return $complete;
    }
}
