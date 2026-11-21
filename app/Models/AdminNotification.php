<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected static function booted(): void
    {
        static::created(fn (self $notification) => app(\App\Services\NotificationDeliveryService::class)->enqueueAdmin($notification));
    }
    protected $fillable = [
        'type',
        'reference_id',
        'region_id',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Notifikasi platform untuk super admin; notifikasi wilayah untuk pengelola
     * wilayah tersebut. Staf hanya melihat jenis layanan yang ditugaskan.
     */
    public function scopeUntukPengguna($query, ?User $pengguna)
    {
        if (!$pengguna) return $query->whereRaw('1 = 0');
        if ($pengguna->bolehAksesPlatform()) {
            return $query->whereNull('region_id');
        }

        if ($pengguna->isStaff()) {
            $types = [];
            foreach ([
                'gas' => ['gas_order', 'unit_chat_gas', 'cancellation_gas', 'stock_low_gas', 'stock_depleted_gas', 'stock_alert_gas'],
                'sewa_alat' => ['rental_request', 'unit_chat_penyewaan', 'cancellation_rental', 'stock_low_rental', 'stock_depleted_rental', 'stock_alert_rental'],
                'sewa_mobil' => ['mobil_order', 'unit_chat_mobil', 'cancellation_mobil', 'stock_low_mobil', 'stock_depleted_mobil', 'stock_alert_mobil'],
                'fasilitas_umum' => ['fasilitas_order', 'unit_chat_fasilitas', 'cancellation_fasilitas', 'stock_low_fasilitas', 'stock_depleted_fasilitas', 'stock_alert_fasilitas'],
                'kabar_informasi' => ['kabar_status', 'announcement_submission'],
            ] as $unit => $list) {
                if ($pengguna->hasUnitPermission($unit)) {
                    $types = array_merge($types, $list);
                }
            }

            if (empty($types)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('region_id', $pengguna->region_id)->whereIn('type', $types);
        }
        $regionId = $pengguna->region_id;

        if (! $regionId) {
            return $query->whereRaw('1 = 0');
        }

        $wilayah = Region::getDescendantIds($regionId);
        $wilayah[] = $regionId;

        return $query->where(function ($q) use ($wilayah) {
            $q->whereIn('region_id', $wilayah);
        });
    }

    /**
     * Metadata kategori, label, ikon, warna, dan URL tujuan yang seragam
     * untuk lonceng navbar, halaman daftar notifikasi admin, dan tautan keluar.
     */
    public function resolvePresentation(?User $viewer = null): array
    {
        $type = (string) $this->type;
        $safeRoute = static function (string $name, array $params = [], string $fallback = '/admin/notifications'): string {
            try {
                if (\Illuminate\Support\Facades\Route::has($name)) {
                    return route($name, $params);
                }
            } catch (\Throwable $e) {
            }

            return url($fallback);
        };

        $isSuper = $viewer && $viewer->bolehAksesPlatform();

        if ($type === 'unit_chat_gas') {
            return ['category' => 'chat', 'label' => 'Chat Gas', 'icon' => 'bx-chat', 'color' => 'danger', 'url' => $safeRoute('admin.unit.penjualan_gas.index', ['tab' => 'chat'])];
        }
        if ($type === 'unit_chat_penyewaan') {
            return ['category' => 'chat', 'label' => 'Chat Sewa Alat', 'icon' => 'bx-chat', 'color' => 'warning', 'url' => $safeRoute('admin.unit.penyewaan.index', ['tab' => 'chat'])];
        }
        if ($type === 'unit_chat_mobil') {
            return ['category' => 'chat', 'label' => 'Chat Transportasi', 'icon' => 'bx-chat', 'color' => 'info', 'url' => $safeRoute('admin.unit.mobil.index', ['tab' => 'chat'])];
        }
        if ($type === 'unit_chat_fasilitas') {
            return ['category' => 'chat', 'label' => 'Chat Fasilitas', 'icon' => 'bx-chat', 'color' => 'success', 'url' => $safeRoute('admin.unit.fasilitas_umum.index', ['tab' => 'chat'])];
        }
        if (in_array($type, ['rental_request', 'rental_order', 'rental', 'cancellation_rental'], true)) {
            $label = $type === 'cancellation_rental' ? 'Batal Sewa Alat' : 'Sewa Alat';

            return ['category' => 'rental', 'label' => $label, 'icon' => 'bx-wrench', 'color' => 'warning', 'url' => $safeRoute('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'rental'])];
        }
        if (in_array($type, ['mobil_order', 'rental_mobil', 'mobil', 'cancellation_mobil'], true)) {
            $label = $type === 'cancellation_mobil' ? 'Batal Transportasi' : 'Transportasi';

            return ['category' => 'mobil', 'label' => $label, 'icon' => 'bx-car', 'color' => 'info', 'url' => $safeRoute('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'mobil'])];
        }
        if (in_array($type, ['fasilitas_order', 'rental_fasilitas', 'fasilitas', 'cancellation_fasilitas'], true)) {
            $label = $type === 'cancellation_fasilitas' ? 'Batal Fasilitas' : 'Fasilitas Umum';

            return ['category' => 'fasilitas', 'label' => $label, 'icon' => 'bx-building-house', 'color' => 'success', 'url' => $safeRoute('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'fasilitas'])];
        }
        if (in_array($type, ['gas_order', 'gas', 'cancellation_gas'], true)) {
            $label = $type === 'cancellation_gas' ? 'Batal Gas' : 'Gas LPG';

            return ['category' => 'gas', 'label' => $label, 'icon' => 'bxs-gas-pump', 'color' => 'danger', 'url' => $safeRoute('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'gas'])];
        }
        if ($type === 'cancellation_request') {
            return ['category' => 'lainnya', 'label' => 'Minta Batal', 'icon' => 'bx-x-circle', 'color' => 'danger', 'url' => $safeRoute('admin.aktivitas.permintaan-pengajuan.index')];
        }
        if (in_array($type, ['stock_low_gas', 'stock_depleted_gas', 'stock_alert_gas'], true)) {
            return ['category' => 'gas', 'label' => 'Stok Gas', 'icon' => 'bx-error', 'color' => 'danger', 'url' => $safeRoute('admin.unit.penjualan_gas.index')];
        }
        if (in_array($type, ['stock_low_rental', 'stock_depleted_rental', 'stock_alert_rental'], true)) {
            return ['category' => 'rental', 'label' => 'Stok Alat', 'icon' => 'bx-error', 'color' => 'warning', 'url' => $safeRoute('admin.unit.penyewaan.index')];
        }
        if (in_array($type, ['stock_low_mobil', 'stock_depleted_mobil', 'stock_alert_mobil'], true)) {
            return ['category' => 'mobil', 'label' => 'Armada', 'icon' => 'bx-error', 'color' => 'info', 'url' => $safeRoute('admin.unit.mobil.index')];
        }
        if (in_array($type, ['stock_low_fasilitas', 'stock_depleted_fasilitas', 'stock_alert_fasilitas'], true)) {
            return ['category' => 'fasilitas', 'label' => 'Fasilitas', 'icon' => 'bx-error', 'color' => 'success', 'url' => $safeRoute('admin.unit.fasilitas_umum.index')];
        }
        if (in_array($type, ['stock_low', 'stock_depleted', 'stock_alert'], true)) {
            return ['category' => 'lainnya', 'label' => 'Peringatan Stok', 'icon' => 'bx-error', 'color' => 'warning', 'url' => $safeRoute('admin.notifications.index')];
        }
        if (in_array($type, ['kabar_status', 'announcement_submission', 'pengumuman'], true)) {
            $url = ($this->reference_id && \Illuminate\Support\Facades\Route::has('content.show'))
                ? $safeRoute('content.show', ['announcement' => $this->reference_id])
                : $safeRoute('content.index');

            return ['category' => 'kabar', 'label' => 'Kabar & Pengumuman', 'icon' => 'bx-news', 'color' => 'primary', 'url' => $url];
        }
        if (in_array($type, ['kyc', 'kyc_verification'], true)) {
            $url = ($this->reference_id && \Illuminate\Support\Facades\Route::has('admin.kyc.show'))
                ? $safeRoute('admin.kyc.show', ['id' => $this->reference_id])
                : $safeRoute('admin.kyc.index');

            return ['category' => 'kyc', 'label' => 'Verifikasi KYC', 'icon' => 'bx-id-card', 'color' => 'info', 'url' => $url];
        }
        if ($type === 'mutasi') {
            return ['category' => 'mutasi', 'label' => 'Mutasi Warga', 'icon' => 'bx-user-pin', 'color' => 'secondary', 'url' => $safeRoute('admin.warga.mutasi.index')];
        }
        if (in_array($type, ['kemitraan', 'partner_application'], true)) {
            return ['category' => 'kemitraan', 'label' => 'Kemitraan', 'icon' => 'bx-buildings', 'color' => 'primary', 'url' => $safeRoute('admin.kemitraan.index')];
        }
        if ($type === 'penarikan_saldo') {
            $url = $isSuper
                ? $safeRoute('admin.sistem-platform.penarikan-saldo.index')
                : $safeRoute('admin.keuangan.index');

            return ['category' => 'keuangan', 'label' => 'Penarikan Saldo', 'icon' => 'bx-wallet', 'color' => 'success', 'url' => $url];
        }
        if ($type === 'layanan_baru') {
            $url = $isSuper
                ? $safeRoute('admin.sistem-platform.monitoring')
                : $safeRoute('admin.region-settings.index');

            return ['category' => 'lainnya', 'label' => 'Layanan Baru', 'icon' => 'bx-grid-alt', 'color' => 'primary', 'url' => $url];
        }
        if ($type === 'warga_baru') {
            return ['category' => 'akun', 'label' => 'Warga Baru', 'icon' => 'bx-user-plus', 'color' => 'success', 'url' => $safeRoute('admin.manajemen-pengguna.index')];
        }

        return ['category' => 'lainnya', 'label' => 'Aktivitas', 'icon' => 'bx-bell', 'color' => 'primary', 'url' => $safeRoute('admin.notifications.index')];
    }
}
