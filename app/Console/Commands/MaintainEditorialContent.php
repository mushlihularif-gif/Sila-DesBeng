<?php

namespace App\Console\Commands;

use App\Models\{Announcement, AnnouncementImage};
use App\Services\{EditorialService, NotificationService};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Storage};

class MaintainEditorialContent extends Command
{
    protected $signature = 'content:maintain {--clean-media : Bersihkan foto editorial tanpa referensi setelah satu hari}';
    protected $description = 'Publikasi terjadwal, pengingat peninjauan, serta retensi kiriman 15/3 hari';

    public function handle(EditorialService $editorial): int
    {
        Announcement::whereIn('workflow_state', ['pending','review','rejected','scheduled','published'])->select('id')->chunkById(100, function ($items) use ($editorial) {
            foreach ($items as $entry) DB::transaction(function () use ($entry, $editorial) {
                $item = Announcement::lockForUpdate()->find($entry->id);
                if (!$item || ($item->edit_token && $item->edit_expires_at?->isFuture())) return;
                if (in_array($item->workflow_state,['scheduled','published'],true) && $item->ends_at?->isPast()) {
                    $item->forceFill(['workflow_state'=>'archived','revision'=>$item->revision+1])->save();
                    $editorial->activity($item,null,'archived');
                    return;
                }
                if ($item->workflow_state === 'scheduled' && $item->scheduled_at?->isPast()) {
                    $item->forceFill(['is_active'=>true,'workflow_state'=>'published','published_at'=>$item->published_at ?? now(),'revision'=>$item->revision+1])->save();
                    $editorial->activity($item,null,'published');
                    $editorial->notifyAuthor($item,'Kiriman Anda telah diterbitkan','Kabar yang dijadwalkan sudah dapat dibaca.',true);
                    NotificationService::broadcastAnnouncement($item);
                    return;
                }
                if ($item->workflow_state === 'pending' && !$item->handled_at && $item->submitted_at?->lte(now()->subDay()) && !$item->reminded_at) {
                    $editorial->notifyReviewers($item,'Kiriman menunggu sejak kemarin','Kiriman belum mulai ditangani. Silakan pilih Mulai Tangani saat tersedia.');
                    $item->forceFill(['reminded_at'=>now()])->save();
                }
                $deadline = match (true) {
                    $item->workflow_state === 'pending' && !$item->handled_at && $item->submitted_at !== null => $item->submitted_at->copy()->addDays(15),
                    $item->workflow_state === 'rejected' && $item->rejected_at !== null => $item->rejected_at->copy()->addDays(3),
                    default => null,
                };
                if (!$deadline) return;
                if (!$item->cleanup_notified_at && $deadline->lte(now()->addDay())) {
                    $editorial->notifyAuthor($item,'Pemberitahuan pembersihan kiriman','Kiriman yang tidak diterbitkan akan dibersihkan. Bahan yang sudah dipakai dalam berita terbit tetap dipertahankan.');
                    $item->forceFill(['cleanup_notified_at'=>now()])->save();
                    return;
                }
                // If the scheduler was offline, still give the author time after the first warning.
                if ($deadline->isPast() && $item->cleanup_notified_at?->lte(now()->subHour())) {
                    DB::table('announcement_sources')->where('source_id',$item->id)->update(['source_id'=>null]);
                    $editorial->notifyAuthor($item,'Kiriman sudah dibersihkan','Masa penyimpanan kiriman yang tidak diterbitkan telah berakhir.');
                    // Keep notification history useful after the resource itself has been removed.
                    DB::table('notifications')->where('link',route('content.show',$item))->update(['link'=>route('content.index')]);
                    $item->delete();
                }
            }, 3);
        });
        DB::table('announcement_presence')->where('seen_at','<',now()->subDay())->delete();
        DB::table('announcement_views')->where('day','<',today()->subDays(30)->toDateString())->delete();
        // Delivery details are operational logs; pending/ambiguous attempts are preserved for investigation.
        if (\Illuminate\Support\Facades\Schema::hasTable('notification_deliveries')) {
            DB::table('notification_deliveries')->whereIn('state',['delivered','read','cancelled','failed'])
                ->where('updated_at','<',now()->subDays(90))->delete();
        }
        if ($this->option('clean-media')) {
            foreach (array_merge(Storage::disk('private')->allFiles('editorial'), Storage::disk('private')->allFiles('announcements')) as $path) {
                if (Storage::disk('private')->lastModified($path) > now()->subDay()->timestamp) continue;
                if (AnnouncementImage::where('disk','private')->where('image_path',$path)->exists()
                    || Announcement::where('image_disk','private')->where('image_path',$path)->exists()) continue;
                Storage::disk('private')->delete($path);
            }
        }
        $this->info('Pemeliharaan kabar selesai.');
        return self::SUCCESS;
    }
}
