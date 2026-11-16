<?php

namespace App\Services;

use App\Models\{KycVerification, User};
use Illuminate\Database\Eloquent\Model;

/** Context must come from the authorized database record, never request/file metadata. */
final class IdentityDocumentContext
{
    public static function pathColumn(Model $record, string $type): string
    {
        if (!in_array($type, ['ktp', 'face'], true)
            || !($record instanceof KycVerification || $record instanceof User)) {
            throw new \InvalidArgumentException('Jenis dokumen identitas tidak valid.');
        }

        return $record instanceof KycVerification ? $type.'_image_path' : $type.'_photo_path';
    }

    public static function versionColumn(Model $record, string $type): string
    {
        self::pathColumn($record, $type);
        return $record instanceof KycVerification ? $type.'_encryption_version' : $type.'_photo_encryption_version';
    }

    public static function aad(Model $record, string $type, string $path): string
    {
        self::pathColumn($record, $type);
        $ownerId = $record instanceof KycVerification ? $record->user_id : $record->getKey();
        if (!$record->exists || !$record->getKey() || !$ownerId || $path === '') {
            throw new \InvalidArgumentException('Dokumen belum memiliki konteks tersimpan.');
        }

        // Stable IDs, fixed field order, and per-upload path bind replacements too.
        return json_encode([
            'domain' => 'segidaerah.identity',
            'version' => 2,
            'scope' => $record instanceof KycVerification ? 'kyc' : 'user-verification',
            'record_id' => (string) $record->getKey(),
            'owner_id' => (string) $ownerId,
            'type' => $type,
            'path' => $path,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
