<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IdentityDocumentStorage
{
    public function createDraft(User $owner, array $fields, string $ktpBytes): KycVerification
    {
        if ($owner->fresh()->verification_status === 'verified') {
            throw ValidationException::withMessages(['ktp_image' => 'Identitas akun sudah terverifikasi.']);
        }
        // Allocate the submission ID before constructing AAD. OCR must have succeeded first.
        $kyc = KycVerification::create(array_merge($fields, [
            'user_id' => $owner->id, 'status' => 'draft',
            'ktp_image_path' => null, 'face_image_path' => null,
        ]));
        try {
            $this->save($kyc, ['ktp' => $ktpBytes]);
        } catch (\Throwable $e) {
            // Keep a committed document reference if an after-commit operation failed.
            if (! $kyc->fresh()?->ktp_image_path) {
                $kyc->delete();
            }
            throw $e;
        }

        // Only retire older submissions after the replacement has been safely persisted.
        $oldKycs = KycVerification::where('user_id', $owner->id)
            ->where('id', '<', $kyc->id)->whereIn('status', ['draft', 'pending', 'rejected'])->get();
        foreach ($oldKycs as $old) {
            DB::transaction(function () use ($old) {
                $old = KycVerification::lockForUpdate()->find($old->id);
                if (! $old || ! in_array($old->status, ['draft', 'pending', 'rejected'], true)) {
                    return;
                }
                foreach (['ktp_image_path', 'face_image_path'] as $column) {
                    if ($path = $old->$column) {
                        app(IdentityFileCleanup::class)->enqueue($path);
                        DB::afterCommit(fn () => app(IdentityFileCleanup::class)->attempt($path));
                    }
                }
                AdminNotification::where('type', 'kyc')->where('reference_id', $old->id)->delete();
                $old->delete();
            });
        }

        return $kyc;
    }

    /** Persist file paths + required versions together; keep old files until commit. */
    public function save(Model $record, array $documents, array $attributes = []): void
    {
        $written = [];
        try {
            DB::transaction(function () use ($record, $documents, $attributes, &$written) {
                $locked = $record->newQuery()->lockForUpdate()->findOrFail($record->getKey());
                if ($locked instanceof KycVerification && ! in_array($locked->status, ['draft', 'pending'], true)) {
                    throw ValidationException::withMessages(['kyc_id' => 'Pengajuan ini sudah selesai ditinjau.']);
                }
                $owner = $locked instanceof User ? $locked : User::lockForUpdate()->findOrFail($locked->user_id);
                if ($owner->verification_status === 'verified') {
                    throw ValidationException::withMessages(['kyc_id' => 'Identitas akun sudah terverifikasi.']);
                }
                $submitting = $locked instanceof KycVerification && ($attributes['status'] ?? null) === 'pending';
                if ($submitting && $locked->status !== 'draft') {
                    throw ValidationException::withMessages(['kyc_id' => 'Pengajuan sudah dikirim. Tunggu hasil pemeriksaan admin.']);
                }
                $oldPaths = [];
                $updates = $attributes;
                foreach ($documents as $type => $bytes) {
                    $column = IdentityDocumentContext::pathColumn($locked, $type);
                    $versionColumn = IdentityDocumentContext::versionColumn($locked, $type);
                    $scope = $locked instanceof KycVerification ? 'kyc' : 'verifications';
                    $path = $scope.'/'.$type.'/'.$type.'_'.Str::uuid().'.enc';
                    $ciphertext = FileEncryptionService::encrypt($bytes, IdentityDocumentContext::aad($locked, $type, $path));
                    $written[] = $path;
                    if (! Storage::disk('private')->put($path, $ciphertext)) {
                        throw new \RuntimeException('Berkas identitas gagal disimpan.');
                    }
                    if ($locked->$column) {
                        $oldPaths[] = $locked->$column;
                        app(IdentityFileCleanup::class)->enqueue($locked->$column);
                    }
                    $updates[$column] = $path;
                    $updates[$versionColumn] = 2;
                }
                $locked->forceFill($updates)->save();
                if ($submitting) {
                    $owner->update(['verification_status' => 'pending']);
                    AdminNotification::create([
                        'type' => 'kyc', 'reference_id' => $locked->id, 'region_id' => $owner->region_id,
                        'title' => 'Pengajuan Verifikasi Identitas (KYC)',
                        'message' => 'Warga '.($owner->name ?? 'Warga').' mengajukan verifikasi data e-KTP dan foto wajah.',
                        'is_read' => false,
                    ]);
                    DB::afterCommit(function () use ($owner) {
                        try {
                            NotificationService::notifyKycSubmitted($owner);
                        } catch (\Throwable $e) {
                            \Log::warning('Pengajuan KYC tersimpan; pemberitahuan belum berhasil dikirim.');
                        }
                    });
                }
                DB::afterCommit(function () use ($oldPaths) {
                    foreach ($oldPaths as $oldPath) {
                        app(IdentityFileCleanup::class)->attempt($oldPath);
                    }
                });
            });
        } catch (\Throwable $e) {
            $persistedAttributes = $record->fresh()?->getAttributes() ?? [];
            foreach ($written as $path) {
                // An after-commit failure must never remove a committed document.
                if (in_array($path, $persistedAttributes, true)) {
                    continue;
                }
                app(IdentityFileCleanup::class)->enqueue($path);
                app(IdentityFileCleanup::class)->attempt($path);
            }
            throw $e;
        }
        $record->refresh();
    }
}
