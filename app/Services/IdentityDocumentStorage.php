<?php

namespace App\Services;

use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IdentityDocumentStorage
{
    public function createDraft(User $owner, array $fields, string $ktpBytes): KycVerification
    {
        // Allocate the submission ID before constructing AAD. OCR must have succeeded first.
        $kyc = KycVerification::create(array_merge($fields, [
            'user_id' => $owner->id, 'status' => 'draft',
            'ktp_image_path' => null, 'face_image_path' => null,
        ]));
        try {
            $this->save($kyc, ['ktp' => $ktpBytes]);
        } catch (\Throwable $e) {
            $kyc->delete();
            throw $e;
        }

        // Only retire older submissions after the replacement has been safely persisted.
        $oldKycs = KycVerification::where('user_id', $owner->id)
            ->where('id', '<', $kyc->id)->whereIn('status', ['draft', 'pending', 'rejected'])->get();
        foreach ($oldKycs as $old) {
            foreach (['ktp_image_path', 'face_image_path'] as $column) {
                if ($old->$column) Storage::disk('private')->delete($old->$column);
            }
            \App\Models\AdminNotification::where('type', 'kyc')->where('reference_id', $old->id)->delete();
            $old->delete();
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
                if ($locked instanceof KycVerification && !in_array($locked->status, ['draft', 'pending'], true)) {
                    throw ValidationException::withMessages(['kyc_id' => 'Pengajuan ini sudah selesai ditinjau.']);
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
                    if (!Storage::disk('private')->put($path, $ciphertext)) {
                        throw new \RuntimeException('Berkas identitas gagal disimpan.');
                    }
                    if ($locked->$column) $oldPaths[] = $locked->$column;
                    $updates[$column] = $path;
                    $updates[$versionColumn] = 2;
                }
                $locked->forceFill($updates)->save();
                DB::afterCommit(function () use ($oldPaths) {
                    foreach ($oldPaths as $oldPath) {
                        try {
                            if (Storage::disk('private')->delete($oldPath)) continue;
                        } catch (\Throwable $e) {
                            // Committed new files must not be deleted on cleanup failure.
                        }
                        \Log::warning('Berkas identitas pengganti tersimpan; pembersihan berkas lama perlu diulang.');
                    }
                });
            });
        } catch (\Throwable $e) {
            foreach ($written as $path) Storage::disk('private')->delete($path);
            throw $e;
        }
        $record->refresh();
    }
}
