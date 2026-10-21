<?php

namespace App\Services;

use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class IdentityMediaService
{
    public function authorize(User $owner, bool $legacy = false): void
    {
        $viewer = auth()->user();
        abort_unless($viewer, 401);
        if ((int) $viewer->id === (int) $owner->id) {
            return;
        }
        $roles = $legacy
            ? ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']
            : ['admin_kecamatan', 'admin_desa'];
        abort_unless(in_array($viewer->role, $roles, true), 403);
        if ($legacy && ($viewer->role === 'super_admin' || ($viewer->role === 'admin' && !$viewer->region_id))) {
            return;
        }
        abort_unless($viewer->region_id && $owner->region_id, 403);
        $regions = array_merge([$viewer->region_id], Region::getDescendantIds($viewer->region_id));
        abort_unless(in_array($owner->region_id, $regions), 403);
    }

    public function render(string $path, string $type)
    {
        abort_unless(in_array($type, ['ktp', 'face'], true), 404);
        abort_if(str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '\\'), 404);
        $disk = Storage::disk('private');
        abort_unless($disk->exists($path), 404);
        try {
            $bytes = FileEncryptionService::decrypt($disk->get($path));
            if (!$bytes) {
                throw new \RuntimeException('Invalid encrypted image');
            }
            $manager = \Intervention\Image\ImageManager::gd();
            $image = $manager->read($bytes);
            if ($type === 'ktp') {
                $watermark = $manager->read(public_path('Admin/img/watermarkprivasi/WatermarkPrivasiFIX.png'));
                $watermark->scaleDown(width: max(1, (int) ($image->width() * 0.9)));
                $image->place($watermark, 'center');
            }
            $jpeg = $image->toJpeg(85)->toString();
        } catch (\Throwable $e) {
            // Jangan pernah mengirim dokumen asli saat dekripsi/watermark gagal.
            \Log::warning('Dokumen identitas gagal diproses.', ['exception' => get_class($e)]);
            abort(404, 'Dokumen tidak dapat ditampilkan.');
        }
        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }
}
