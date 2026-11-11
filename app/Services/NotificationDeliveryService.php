<?php

namespace App\Services;

use App\Models\{AdminNotification, Announcement, Notification, Region, User};
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;

class NotificationDeliveryService
{
    public function available(): bool
    {
        return config('notification_channels.enabled') && Schema::hasTable('notification_deliveries');
    }

    public function category(string $type): string
    {
        return match (true) {
            $type === 'berita' => 'news', $type === 'pengumuman' => 'announcements',
            str_contains($type,'kabar') => 'review',
            preg_match('/kyc|mutasi|role_|kemitraan/', $type) === 1 => 'account',
            preg_match('/produk|product|stok_update/', $type) === 1 => 'market',
            preg_match('/laporan/', $type) === 1 => 'reports',
            preg_match('/order|pesanan|rental|approval|rejection|stock|payment|status_update|status_berubah|cancellation|delivery_proof/', $type) === 1 => 'orders',
            default => 'other',
        };
    }

    public function phoneHash(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');
        if (str_starts_with($digits,'0')) $digits = '62'.substr($digits,1);
        return hash_hmac('sha256',$digits,config('app.key'));
    }

    public function settings(User $user): ?object
    {
        return DB::table('notification_settings')->where('user_id',$user->id)->first();
    }

    public function mode(User $user, string $category, string $channel): string
    {
        $setting = $this->settings($user);
        if ($channel === 'push') return ($setting?->push_enabled ?? true) && $user->fcm_token ? 'instant' : 'off';
        if (!$setting?->consented_at || !$setting->verified_phone_hash || !$user->phone
            || !hash_equals($setting->verified_phone_hash,$this->phoneHash($user->phone))) return 'off';
        $categories = json_decode($setting->categories ?? '{}',true) ?: [];
        return $categories[$category] ?? 'off';
    }

    public function enqueue(Notification $notification): void
    {
        if (!$this->available() || !$notification->user_id || $this->isRetiredMarketNotice($notification)) return;
        $user = User::find($notification->user_id);
        if ($user) $this->queue('user',$notification->id,$user,$this->category($notification->type ?? ''));
    }

    public function enqueueAdmin(AdminNotification $notification): void
    {
        if (!$this->available() || $this->isRetiredMarketNotice($notification)) return;
        User::whereIn('role',['super_admin', 'admin_desa', 'admin_rw', 'admin_rt'])
            ->chunkById(100,function ($users) use ($notification) {
                foreach ($users as $user) {
                    if (AdminNotification::untukPengguna($user)->whereKey($notification->id)->exists())
                        $this->queue('admin',$notification->id,$user,$this->category($notification->type ?? ''));
                }
            });
    }

    private function queue(string $kind, int $id, User $user, string $category): void
    {
        if ($user->status === 'non_aktif' || $category === 'reports') return;
        $contentHash = null;
        if ($kind === 'user') {
            $link = Notification::find($id)?->link;
            if (preg_match('~/kabar-daerah/(\d+)$~',parse_url($link ?? '',PHP_URL_PATH) ?: '',$matches)) {
                $item = Announcement::find($matches[1]);
                if ($item) $contentHash = $this->contentHash($item);
            }
            if ($category === 'market' && ($product = $this->product($link))) {
                $contentHash = $this->productHash($product);
            }
        }
        foreach (['whatsapp','push'] as $channel) {
            $mode = $this->mode($user,$category,$channel);
            if ($mode === 'off') continue;
            DB::table('notification_deliveries')->insertOrIgnore([
                'source_kind'=>$kind,'source_id'=>$id,'user_id'=>$user->id,'category'=>$category,'channel'=>$channel,
                'content_hash'=>$contentHash,'recipient_region_id'=>$user->region_id,
                'state'=>'pending','available_at'=>$mode === 'daily' ? now()->addDay()->startOfDay()->addHours(8) : now(),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    public function source(object $delivery, User $user): Notification|AdminNotification|null
    {
        if ($user->status === 'non_aktif' || $delivery->category === 'reports') return null;
        if (isset($delivery->recipient_region_id) && $delivery->recipient_region_id != $user->region_id) return null;
        if ($delivery->source_kind === 'admin') {
            $notice = $user->isAdmin() ? AdminNotification::untukPengguna($user)->find($delivery->source_id) : null;
            if ($notice && $this->isRetiredMarketNotice($notice)) return null;
            if ($notice && ($type = $this->adminOrderType($notice))) {
                $order = BookingAccess::TYPES[$type][0]::find($notice->reference_id);
                if (!$order || !app(BookingAccess::class)->canView($order, $type, $user)) return null;
            }
            return $notice;
        }
        $notification = Notification::where('user_id',$user->id)->find($delivery->source_id);
        if (!$notification || $this->isRetiredMarketNotice($notification)) return null;
        if ($delivery->category === 'market') {
            $product = $this->product($notification->link);
            if (!$product || !in_array(strtolower($product->status ?? ''), ['tersedia','aktif','available'], true)
                || !in_array((int)$product->region_id, array_map('intval',Region::garisLayananUntukWarga($user->region_id)),true)) return null;
            if (!empty($delivery->content_hash) && !hash_equals($delivery->content_hash,$this->productHash($product))) return null;
        }
        $path = parse_url($notification->link ?? '',PHP_URL_PATH) ?: '';
        if ($path === parse_url(route('user.activity'), PHP_URL_PATH)) {
            parse_str(parse_url($notification->link, PHP_URL_QUERY) ?? '', $params);
            if (isset($params['order'])) {
                $model = BookingAccess::TYPES[$params['tab'] ?? ''][0] ?? null;
                if (!$model || !ctype_digit((string)$params['order']) || !$model::where('user_id',$user->id)->whereKey($params['order'])->exists()) return null;
            }
        }
        if (preg_match('~/kabar-daerah/(\d+)$~',$path,$matches)) {
            $item = Announcement::visibleTo($user)->find($matches[1]);
            if (!$item) return null;
            if (!empty($delivery->content_hash) && !hash_equals($delivery->content_hash,$this->contentHash($item))) return null;
            if ($item->post_category === 'Berita' && $delivery->category === 'news') {
                $line = Region::garisLayananUntukWarga($item->region_id);
                $village = Region::whereIn('id',$line)->whereIn('type',['desa','kelurahan'])->value('id');
                if ($village && !in_array((int)$village, array_map('intval',Region::garisLayananUntukWarga($user->region_id)),true)) return null;
            }
        }
        if (preg_match('~/kelola-kabar/(\d+)$~',$path,$matches)) {
            $item = Announcement::find($matches[1]);
            if (!$item || !app(ContentAccess::class)->canInspect($user,$item)) return null;
        }
        return $notification;
    }

    private function contentHash(Announcement $item): string
    {
        return hash('sha256',json_encode($item->only(['title','description','workflow_state','target_audience_id','ends_at','rejection_reason'])));
    }

    private function product(?string $link): ?\Illuminate\Database\Eloquent\Model
    {
        $path = parse_url($link ?? '', PHP_URL_PATH) ?: '';
        $models = ['unit-penjualan-gas'=>\App\Models\Gas::class, 'unit-penyewaan-alat'=>\App\Models\Barang::class,
            'unit-penyewaan-mobil'=>\App\Models\Mobil::class, 'unit-peminjaman-fasilitas-umum'=>\App\Models\FasilitasUmum::class];
        foreach ($models as $prefix => $model) {
            if (preg_match('~^/'.$prefix.'/(\d+)$~', $path, $matches)) {
                $query = in_array($model,[\App\Models\Mobil::class,\App\Models\FasilitasUmum::class],true) ? $model::inCatalog() : $model::query();
                return $query->find($matches[1]);
            }
        }
        return null;
    }

    private function productHash(\Illuminate\Database\Eloquent\Model $product): string
    {
        return hash('sha256',json_encode($product->only(['region_id','status','stok','harga','harga_satuan','harga_sewa','jenis_gas','nama_produk','nama_mobil','nama_barang','nama_fasilitas','deskripsi'])));
    }

    private function adminOrderType(AdminNotification $notice): ?string
    {
        return match ($notice->type) {
            'gas_order'=>'gas', 'rental_request'=>'rental', 'mobil_order'=>'mobil',
            'fasilitas_order'=>'fasilitas', default=>null,
        };
    }

    public function urls(object $delivery): array
    {
        $params = ['kind'=>$delivery->source_kind,'notification'=>$delivery->source_id];
        return ['web'=>route('notification-link.web',$params), 'app'=>route('notification-link.app',$params)];
    }

    public function message(object $delivery, Notification|AdminNotification $source): string
    {
        $urls = $this->urls($delivery);
        $public = in_array($delivery->category,['news','announcements','market'],true);
        $summary = $public ? Str::limit(strip_tags($source->message ?? ''),220) : 'Ada pembaruan untuk akun atau layanan Anda. Masuk ke SegiDaerah untuk melihat detailnya.';
        $title = Str::limit(strip_tags($source->title),150);
        return "*SegiDaerah*\n\n*{$title}*\n{$summary}\n\nBaca selengkapnya:\nWeb: {$urls['web']}\nAplikasi: {$urls['app']}\n\nPengaturan pemberitahuan: ".route('notification-settings.index');
    }

    public function destination(Notification|AdminNotification $source): string
    {
        if ($this->isRetiredMarketNotice($source)) {
            return $source instanceof AdminNotification ? route('admin.notifications.index') : route('user.notifications');
        }
        if ($source instanceof AdminNotification) {
            $type = $this->adminOrderType($source);
            if ($type) return route('admin.aktivitas.permintaan-pengajuan.show',['id'=>$source->reference_id,'type'=>$type]);
            return route('admin.notifications.index');
        }
        $link = $source->link;
        if ($link) {
            $appHost = parse_url(config('app.url'),PHP_URL_HOST);
            if (str_starts_with($link,'/') && !str_starts_with($link,'//') && !str_contains($link,'\\')) return url($link);
            if (parse_url($link,PHP_URL_HOST) === $appHost && in_array(parse_url($link,PHP_URL_SCHEME),['http','https'])) return $link;
        }
        return route('user.notifications');
    }

    private function isRetiredMarketNotice(Notification|AdminNotification $notice): bool
    {
        if (preg_match('/pasar|market|toko_warga/', $notice->type ?? '')) return true;

        $link = $notice->link ?? '';
        $path = parse_url($link, PHP_URL_PATH) ?: '';
        parse_str(parse_url($link, PHP_URL_QUERY) ?? '', $query);

        return preg_match('~/(pasar-daerah|toko-warga)(/|$)~', $path) === 1
            || in_array('pasar', [$query['tab'] ?? null, $query['category'] ?? null, $query['type'] ?? null], true);
    }
}
