<?php

namespace App\Services;

use App\Models\{Announcement, AnnouncementImage, Notification, Region, User};
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditorialService
{
    public function __construct(private ContentAccess $access) {}

    public function activity(Announcement $item, ?User $user, string $action, array $details = []): void
    {
        DB::table('announcement_activities')->insert([
            'announcement_id' => $item->id, 'user_id' => $user?->id, 'actor_name' => $user?->name ?? 'Sistem',
            'action' => $action, 'details' => json_encode($details), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function create(User $user, string $category): Announcement
    {
        $manager = $this->access->manager($user);
        abort_unless($manager || ($user->role === 'user' && $user->verification_status === 'verified'), 403, 'Verifikasi akun terlebih dahulu untuk mengirim kabar.');
        abort_unless($user->region_id && Region::find($user->region_id), 403, 'Wilayah akun belum tersedia.');
        abort_unless($category === 'Berita' || $manager, 403);
        return DB::transaction(function () use ($user, $category, $manager) {
            $item = Announcement::create([
                'admin_id' => $user->id, 'region_id' => $user->region_id, 'post_category' => $category,
                'title' => '', 'description' => '', 'type' => $category === 'Berita' ? 'Event' : 'Pengumuman',
                'target_audience_id' => $category === 'Pengumuman' ? $user->region_id : null,
                'target_audience_type' => $category === 'Berita' ? 'all' : 'region',
                'is_active' => false, 'workflow_state' => 'draft', 'is_citizen' => !$manager,
            ]);
            $this->activity($item, $user, 'created');
            return $item;
        });
    }

    public function claim(User $user, int $id): array
    {
        return DB::transaction(function () use ($user, $id) {
            $item = Announcement::lockForUpdate()->findOrFail($id);
            abort_unless($this->access->canWrite($user, $item), 403);
            abort_if(in_array($item->workflow_state, ['rejected', 'merged', 'expired', 'cancelled','archived']), 409, 'Kiriman sudah selesai ditangani.');
            abort_if($item->edit_token && $item->edit_expires_at?->isFuture(), 409, 'Kiriman sedang ditangani pengelola lain. Silakan baca atau pilih kiriman lain.');
            $token = Str::random(64);
            $item->forceFill(['editor_id' => $user->id, 'edit_token' => hash('sha256', $token), 'edit_expires_at' => now()->addSeconds(120)])->save();
            if (in_array($item->workflow_state, ['pending', 'review'])) {
                $item->forceFill(['workflow_state' => 'review', 'handled_at' => $item->handled_at ?? now()])->save();
            }
            $this->activity($item, $user, 'claimed');
            return ['token' => $token, 'revision' => $item->revision];
        }, 3);
    }

    public function locked(User $user, int $id, string $token, int $revision, callable $operation): mixed
    {
        return DB::transaction(function () use ($user, $id, $token, $revision, $operation) {
            $item = Announcement::lockForUpdate()->findOrFail($id);
            abort_unless($this->access->canWrite($user, $item), 403);
            abort_unless($item->editor_id == $user->id && $item->edit_token
                && hash_equals($item->edit_token, hash('sha256', $token)) && $item->edit_expires_at?->isFuture(), 409, 'Hak penanganan sudah berakhir. Tulisan Anda tidak diterapkan; periksa versi terbaru.');
            abort_unless($item->revision === $revision, 409, 'Naskah sudah berubah. Periksa versi terbaru sebelum menyimpan.');
            $result = $operation($item);
            return $result ?? $item->fresh();
        }, 3);
    }

    public function save(Announcement $item, User $user, array $data): void
    {
        if ($item->post_category === 'Pengumuman') {
            abort_unless(in_array((int) ($data['target_audience_id'] ?? 0), $this->access->targets($user), true), 403, 'Sasaran pengumuman berada di luar kewenangan Anda.');
        }
        if (!$this->access->manager($user)) {
            unset($data['scheduled_at'], $data['ends_at']);
        }
        $item->draft_data = $data;
        if (!$item->is_active) {
            $item->fill(collect($data)->except(['scheduled_at', 'ends_at'])->all());
        }
        $item->revision++;
        $item->edit_expires_at = now()->addSeconds(120);
        $item->save();
        $recent = DB::table('announcement_activities')->where('announcement_id', $item->id)->latest('id')->first();
        if ($recent && $recent->action === 'saved' && $recent->user_id == $user->id && $recent->created_at > now()->subMinute()->toDateTimeString()) {
            DB::table('announcement_activities')->where('id', $recent->id)->update(['details'=>json_encode(['revision'=>$item->revision]), 'updated_at'=>now()]);
        } else {
            $this->activity($item, $user, 'saved', ['revision' => $item->revision]);
        }
    }

    public function release(Announcement $item, ?User $user): void
    {
        $item->forceFill(['edit_token' => null, 'editor_id' => null, 'edit_expires_at' => null])->save();
        $this->activity($item, $user, 'released');
    }

    public function submit(Announcement $item, User $user): void
    {
        abort_unless($item->admin_id == $user->id && $item->workflow_state === 'draft', 409);
        $this->validateReady($item);
        $item->fill($item->draft_data ?? []);
        $item->forceFill(['workflow_state' => 'pending', 'submitted_at' => now(), 'is_active' => false, 'revision' => $item->revision + 1])->save();
        $this->release($item, $user);
        $this->activity($item, $user, 'submitted');
        $this->notifyReviewers($item, 'Kiriman baru menunggu peninjauan', 'Kiriman warga/pengelola baru tersedia. Anda dapat mulai menanganinya sekarang.');
    }

    public function validateReady(Announcement $item, bool $publishing = false): void
    {
        $data = $item->draft_data ?? $item->toArray();
        $minimum = $item->contribution_to && !$publishing ? 10 : 40;
        validator($data, [
            'title' => 'required|string|min:5|max:255', 'description' => 'required|string|min:'.$minimum.'|max:20000',
            'location' => 'required|string|max:255', 'event_date' => 'required|date',
        ], ['description.min' => 'Tambahkan penjelasan kegiatan, lokasi, dan rangkaiannya agar kabar mudah dipahami.'])->validate();
        if ($item->contribution_to && !$publishing && !$item->images()->exists()) {
            throw ValidationException::withMessages(['images'=>'Tambahkan foto dokumentasi beserta keterangan singkat.']);
        }
    }

    public function publish(Announcement $item, User $user): void
    {
        abort_unless($this->access->canManage($user, $item), 403);
        $this->validateReady($item, true);
        $data = $item->draft_data ?? [];
        if ($item->post_category === 'Pengumuman') {
            abort_unless(in_array((int) ($data['target_audience_id'] ?? $item->target_audience_id), $this->access->area($user), true), 403, 'Ajukan pengumuman kepada pengelola wilayah sasaran.');
        }
        $wasActive = $item->is_active;
        $item->fill($data);
        abort_if($wasActive && $item->scheduled_at?->isFuture(), 422, 'Perubahan kabar terbit diterapkan sekarang. Jadwal hanya untuk penerbitan pertama.');
        $item->allImages()->where('pending_removal', true)->delete();
        $item->allImages()->update(['is_staged'=>false]);
        DB::table('announcement_sources')->where('announcement_id', $item->id)->whereNotNull('draft_uses_text')
            ->update(['uses_text'=>DB::raw('draft_uses_text'), 'draft_uses_text'=>null]);
        DB::table('announcement_sources')->where('announcement_id', $item->id)->update(['is_staged'=>false]);
        $scheduled = $item->scheduled_at?->isFuture();
        $item->forceFill([
            'workflow_state' => $scheduled ? 'scheduled' : 'published', 'is_active' => !$scheduled,
            'published_at' => $scheduled ? $item->published_at : ($item->published_at ?? now()),
            'publisher_id' => $user->id,
            'draft_data' => null, 'revision' => $item->revision + 1,
        ])->save();
        $this->activity($item, $user, $scheduled ? 'scheduled' : 'published');
        $this->release($item, $user);
        if (!$scheduled) {
            $this->notifyAuthor($item, $wasActive ? 'Kabar diperbarui' : 'Kiriman Anda telah diterbitkan', 'Terima kasih. Kabar sudah dapat dibaca melalui tautan ini.', true);
            // Database notifications are part of the publication transaction. External delivery is queued separately.
            NotificationService::broadcastAnnouncement($item);
        }
    }

    public function reject(Announcement $item, User $user, string $reason): void
    {
        abort_unless($this->access->canManage($user, $item), 403);
        abort_if($item->is_active, 409, 'Gunakan pembatalan untuk publikasi yang sudah terbit.');
        $item->forceFill(['workflow_state' => 'rejected', 'rejected_at' => now(), 'rejection_reason' => $reason, 'revision' => $item->revision + 1])->save();
        $this->release($item, $user);
        $this->activity($item, $user, 'rejected', ['reason' => $reason]);
        $this->notifyAuthor($item, 'Hasil peninjauan kiriman', 'Kiriman belum dapat diterbitkan: '.$reason.'. Kiriman akan dibersihkan setelah tiga hari.');
    }

    public function notifyReviewers(Announcement $item, string $title, string $message): void
    {
        $region = $item->post_category === 'Pengumuman' ? $item->target_audience_id : $item->region_id;
        $line = $region ? Region::garisLayananUntukWarga($region) : [];
        User::whereIn('region_id', $line)->whereIn('role', ['admin_rt', 'admin_rw', 'admin_desa'])
            ->each(function (User $recipient) use ($item, $title, $message) {
                if ($this->access->canManage($recipient, $item)) $this->notify($item, $recipient->id, $title, $message);
            });
    }

    public function notifyAuthor(Announcement $item, string $title, string $message, bool $published = false): void
    {
        $ids = [(int) $item->admin_id, ...DB::table('announcement_sources')->where('announcement_id', $item->id)->where('uses_text',true)->pluck('contributor_id')->all(), ...$item->allImages()->where('pending_removal',false)->pluck('contributor_id')->all()];
        foreach (array_unique(array_filter($ids)) as $id) $this->notify($item, $id, $title, $message, $published);
    }

    public function notify(Announcement $item, int $userId, string $title, string $message, bool $published = false): void
    {
        Notification::create([
            'user_id' => $userId, 'type' => 'kabar_status', 'title' => $title, 'message' => $message,
            'link' => route($published ? 'announcements.show' : 'content.show', $item->id),
            'icon' => 'bx bx-news', 'is_read' => false, 'sent_at' => now(),
        ]);
    }

    public function merge(Announcement $target, User $user, array $sources): void
    {
        abort_unless($target->post_category === 'Berita' && $this->access->canManage($user, $target), 403);
        // Lock source rows in deterministic order; the outer transaction rolls back the entire merge on any conflict.
        $ids = collect($sources)->pluck('id')->map(fn ($id) => (int) $id)->all();
        abort_if(in_array($target->id, $ids, true) || count(array_unique($ids)) !== count($ids), 422);
        $items = Announcement::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        abort_unless($items->count() === count($ids), 404);
        foreach ($sources as $selection) {
            $source = $items[$selection['id']];
            abort_unless($this->access->canManage($user, $source) && $source->post_category === 'Berita', 403);
            $village = fn ($regionId) => Region::whereIn('id', Region::garisLayananUntukWarga($regionId))->whereIn('type',['desa','kelurahan'])->value('id');
            abort_unless($village($source->region_id) && $village($source->region_id) == $village($target->region_id), 403, 'Bahan harus berasal dari desa yang sama.');
            abort_if($source->contribution_to && $source->contribution_to != $target->id, 422, 'Dokumentasi ini ditujukan untuk kabar lain.');
            abort_unless(in_array($source->workflow_state, ['pending', 'review']) && !$source->is_active, 409, 'Sumber sudah ditangani atau diterbitkan.');
            abort_unless($source->revision === (int) ($selection['revision'] ?? -1), 409, 'Bahan sumber sudah berubah. Muat kembali pilihan sebelum menggabungkan.');
            abort_if($source->edit_token && $source->edit_expires_at?->isFuture(), 409, 'Salah satu sumber sedang ditangani. Penggabungan dibatalkan.');
            $images = $source->images()->whereIn('id', $selection['images'] ?? [])->get();
            abort_unless($images->count() === count(array_unique($selection['images'] ?? [])), 422);
            abort_unless(!empty($selection['text']) || $images->isNotEmpty(), 422, 'Pilih informasi atau foto yang digunakan.');
            abort_if($target->allImages()->where('pending_removal',false)->count() + $images->count() > 16, 422, 'Maksimal 16 foto per kabar.');
            DB::table('announcement_sources')->insert([
                'announcement_id' => $target->id, 'source_id' => $source->id,
                'contributor_id' => $source->admin_id, 'contributor_name' => $source->admin?->name ?? 'Warga',
                'uses_text' => (bool) ($selection['text'] ?? false), 'excerpt' => !empty($selection['text']) ? ($source->draft_data['description'] ?? $source->description) : null,
                'is_staged' => $target->is_active,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($images as $image) {
                $target->allImages()->create([
                    'image_path' => $image->image_path, 'disk' => $image->disk,
                    'is_staged' => $target->is_active,
                    'contributor_id' => $image->contributor_id ?? $source->admin_id,
                    'contributor_name' => $image->contributor_name ?? $source->admin?->name ?? 'Warga',
                ]);
            }
            $source->forceFill(['workflow_state' => 'merged', 'merged_into_id' => $target->id, 'handled_at' => now(), 'revision' => $source->revision + 1])->save();
            $this->activity($source, $user, 'merged', ['target_id' => $target->id]);
            $this->notifyAuthor($source, 'Dokumentasi Anda digabungkan', 'Bahan Anda digunakan dalam penyusunan kabar bersama. Kami akan memberi kabar setelah terbit.');
        }
        $target->increment('revision');
        $this->activity($target, $user, 'sources_added', ['source_ids' => $ids]);
    }
}
