<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentViewService
{
    public function record(Announcement $item, Request $request): void
    {
        $user = $request->user() ?? auth('sanctum')->user();
        if (($user && app(ContentAccess::class)->canManage($user, $item)) || preg_match('/bot|crawler|spider|preview/i', $request->userAgent() ?? '')) return;
        $identity = $user ? 'user:'.$user->id : ($request->hasSession() ? 'session:'.$request->session()->getId() : 'ip:'.$request->ip().'|'.$request->userAgent());
        $fingerprint = hash_hmac('sha256', $identity, config('app.key'));
        DB::transaction(function () use ($item, $fingerprint) {
            $inserted = DB::table('announcement_views')->insertOrIgnore([
                'announcement_id' => $item->id, 'fingerprint' => $fingerprint, 'day' => today()->toDateString(),
            ]);
            if ($inserted) $item->increment('views_count');
        });
    }
}
