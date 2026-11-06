<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\{FonnteService, FirebaseNotificationService, NotificationDeliveryService};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliverNotifications extends Command
{
    protected $signature = 'notifications:deliver {--limit=30}';
    protected $description = 'Kirim antrean pemberitahuan sesuai izin, wilayah, dan pilihan penerima';

    public function handle(NotificationDeliveryService $delivery, FonnteService $fonnte): int
    {
        if (!$delivery->available()) return self::SUCCESS;
        // A crashed worker may already have sent the message. Do not blindly send it again.
        DB::table('notification_deliveries')->where('state','processing')->where('claimed_at','<',now()->subMinutes(10))
            ->update(['state'=>'unknown','error_code'=>'worker_interrupted','updated_at'=>now()]);
        $jobs = DB::table('notification_deliveries')->where('state','pending')->where('available_at','<=',now())
            ->orderByRaw("CASE WHEN category IN ('account','orders','reports','review') THEN 0 ELSE 1 END")
            ->orderBy('id')->limit(max(1,min(100,(int)$this->option('limit'))))->get();
        $handled = [];
        foreach ($jobs as $job) {
            if (in_array($job->id,$handled,true)) continue;
            $user = User::find($job->user_id);
            $mode = $user ? $delivery->mode($user,$job->category,$job->channel) : 'off';
            $group = $mode === 'daily' && $job->channel === 'whatsapp'
                ? $jobs->where('user_id',$job->user_id)->where('category',$job->category)->where('channel','whatsapp')->take(8)
                : collect([$job]);
            $ids = $group->pluck('id')->all(); $handled = [...$handled,...$ids];
            $claim = Str::random(64);
            $claimed = DB::transaction(function () use ($ids,$claim) {
                $rows = DB::table('notification_deliveries')->whereIn('id',$ids)->where('state','pending')->orderBy('id')->lockForUpdate()->get();
                if ($rows->count() !== count($ids)) return false;
                DB::table('notification_deliveries')->whereIn('id',$ids)->update(['state'=>'processing','claim_token'=>$claim,'claimed_at'=>now(),'attempts'=>DB::raw('attempts + 1'),'updated_at'=>now()]);
                return true;
            },3);
            if (!$claimed) continue;
            $current = DB::table('notification_deliveries')->whereIn('id',$ids)->where('claim_token',$claim);
            if ($mode === 'off' || !$user) { $current->update(['state'=>'cancelled','error_code'=>'recipient_opted_out','updated_at'=>now()]); continue; }
            $messages=[]; $validIds=[];
            foreach ($group as $entry) {
                $source=$delivery->source($entry,$user);
                if (!$source) {
                    DB::table('notification_deliveries')->where('id',$entry->id)->where('claim_token',$claim)->update(['state'=>'cancelled','error_code'=>'access_or_content_changed','updated_at'=>now()]);
                    continue;
                }
                $validIds[]=$entry->id; $messages[]=$delivery->message($entry,$source);
            }
            if (!$validIds) continue;
            $update = DB::table('notification_deliveries')->whereIn('id',$validIds)->where('claim_token',$claim);
            try {
                if ($job->channel === 'whatsapp') {
                    $result=$fonnte->sendNotification($user->phone,($mode==='daily' ? "*Ringkasan Harian SegiDaerah*\n\n" : '').implode("\n\n────────\n\n",$messages));
                } else {
                    $urls=$delivery->urls($job);
                    $ok=app(FirebaseNotificationService::class)->sendPushNotification($user->fcm_token,'Pemberitahuan SegiDaerah','Ada pembaruan untuk Anda. Buka untuk melihat detail.',[
                        'notification_kind'=>$job->source_kind,'notification_id'=>(string)$job->source_id,'web_url'=>$urls['web'],'app_url'=>$urls['app'],
                    ]);
                    $result=['status'=>$ok,'code'=>$ok ? null : 'push_failed'];
                }
                if ($result['status']) $update->update(['state'=>'accepted','provider_id'=>$result['provider_id'] ?? null,'error_code'=>null,'updated_at'=>now()]);
                elseif (!empty($result['retryable']) && $job->attempts < 2) $update->update(['state'=>'pending','available_at'=>now()->addMinutes(2 ** ($job->attempts+1)),'error_code'=>$result['code'],'updated_at'=>now()]);
                else $update->update(['state'=>($result['code'] ?? '') === 'unknown' ? 'unknown':'failed','error_code'=>$result['code'] ?? 'send_failed','updated_at'=>now()]);
            } catch (\Throwable $e) {
                $update->update(['state'=>'unknown','error_code'=>'unconfirmed_result','updated_at'=>now()]);
            }
        }
        $this->info('Antrean notifikasi diperiksa: '.count($handled));
        return self::SUCCESS;
    }
}
