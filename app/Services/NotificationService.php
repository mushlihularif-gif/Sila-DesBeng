<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\Region;
use App\Models\MutasiPenduduk;
use App\Models\PartnerApplication;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function orderLink($order, ?string $type = null): string
    {
        if (!$type) {
            foreach (BookingAccess::TYPES as $candidate => [$model]) {
                if ($order instanceof $model) { $type = $candidate; break; }
            }
        }
        return route('user.activity', isset(BookingAccess::TYPES[$type ?? ''])
            ? ['tab' => $type, 'order' => $order->id] : []);
    }

    // ==========================================
    // 1. VERIFIKASI IDENTITAS (KYC)
    // ==========================================

    public static function notifyKycSubmitted($user)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'kyc_submission',
                'title' => 'Pengajuan Verifikasi Terkirim',
                'message' => 'Data e-KTP dan foto verifikasi wajah Anda berhasil dikirim. Akun Anda sedang dalam proses peninjauan oleh Admin Desa.',
                'link' => route('kyc.index'),
                'icon' => 'bx bx-id-card',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyKycSubmitted]: ' . $e->getMessage());
        }
    }

    public static function notifyKycApproved($user)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'kyc_approved',
                'title' => 'Verifikasi Identitas Disetujui',
                'message' => 'Selamat! Pengajuan verifikasi identitas Anda telah disetujui. Akun Anda kini resmi berstatus Warga Terverifikasi.',
                'link' => route('profile'),
                'icon' => 'bx bx-badge-check',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyKycApproved]: ' . $e->getMessage());
        }
    }

    public static function notifyKycRejected($user, $notes)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'kyc_rejected',
                'title' => 'Verifikasi Identitas Ditolak',
                'message' => 'Mohon maaf, pengajuan verifikasi identitas Anda ditolak. Alasan: ' . $notes . '. Silakan periksa kembali dan ajukan ulang.',
                'link' => route('kyc.index'),
                'icon' => 'bx bx-x-circle',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyKycRejected]: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 3. PEMESANAN UNIT LAYANAN (GAS, SEWA, MOBIL, FASILITAS)
    // ==========================================

    public static function notifyOrderCreated($type, $order, $itemName, $userId = null)
    {
        try {
            $userId = $userId ?? ($order->user_id ?? auth()->id());
            if (!$userId) return;

            $title = 'Pesanan Berhasil Dibuat';
            $link = route('user.activity');
            $icon = 'bx bx-cart';

            switch ($type) {
                case 'gas':
                    $title = 'Pesanan Gas Berhasil Dibuat';
                    $message = 'Pesanan ' . $itemName . ' berhasil diajukan dan sedang menunggu konfirmasi Admin.';
                    $link = self::orderLink($order, 'gas');
                    $icon = 'bx bx-gas-pump';
                    break;
                case 'rental':
                    $title = 'Permintaan Sewa Alat Terkirim';
                    $message = 'Permintaan sewa untuk "' . $itemName . '" berhasil dikirim dan sedang menunggu konfirmasi Admin.';
                    $link = self::orderLink($order, 'rental');
                    $icon = 'bx bx-wrench';
                    break;
                case 'mobil':
                    $title = 'Pemesanan Sewa Transportasi Terkirim';
                    $message = 'Pemesanan transportasi "' . $itemName . '" berhasil dibuat dan sedang menunggu konfirmasi Admin.';
                    $link = self::orderLink($order, 'mobil');
                    $icon = 'bx bx-car';
                    break;
                case 'fasilitas':
                    $title = 'Permohonan Peminjaman Fasilitas Terkirim';
                    $message = 'Permohonan peminjaman "' . $itemName . '" berhasil diajukan dan menunggu persetujuan Admin.';
                    $link = self::orderLink($order, 'fasilitas');
                    $icon = 'bx bx-buildings';
                    break;
                default:
                    $message = 'Permintaan layanan "' . $itemName . '" berhasil diajukan dan sedang diproses.';
            }

            Notification::create([
                'user_id' => $userId,
                'type' => 'order_created',
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'icon' => $icon,
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyOrderCreated]: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 4. MUTASI DOMISILI (PINDAH DESA)
    // ==========================================

    public static function notifyMutasiSubmitted($mutasi)
    {
        try {
            Notification::create([
                'user_id' => $mutasi->user_id,
                'type' => 'mutasi_submitted',
                'title' => 'Pengajuan Pindah Domisili Terkirim',
                'message' => 'Permohonan pindah domisili Anda telah berhasil diajukan dan sedang menunggu persetujuan Admin Desa.',
                'link' => route('profile'),
                'icon' => 'bx bx-map-pin',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyMutasiSubmitted]: ' . $e->getMessage());
        }
    }

    public static function notifyMutasiApproved($mutasi)
    {
        try {
            Notification::create([
                'user_id' => $mutasi->user_id,
                'type' => 'mutasi_approved',
                'title' => 'Permohonan Pindah Domisili Disetujui',
                'message' => 'Selamat! Permohonan mutasi domisili Anda telah disetujui. Wilayah akun Anda telah resmi diperbarui.',
                'link' => route('profile'),
                'icon' => 'bx bx-check-double',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyMutasiApproved]: ' . $e->getMessage());
        }
    }

    public static function notifyMutasiApprovedByAsal($mutasi)
    {
        try {
            $fromName = $mutasi->fromRegion ? $mutasi->fromRegion->name : 'Desa Asal';
            $toName = $mutasi->toRegion ? $mutasi->toRegion->name : 'Desa Tujuan';
            Notification::create([
                'user_id' => $mutasi->user_id,
                'type' => 'mutasi_approved_asal',
                'title' => 'Pelepasan Pindah Desa Disetujui',
                'message' => "Permohonan pindah Anda telah disetujui dan dilepaskan oleh Pemerintah {$fromName}. Saat ini sedang menunggu verifikasi penerimaan dari Pemerintah {$toName}.",
                'link' => route('profile'),
                'icon' => 'bx bx-time-five',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyMutasiApprovedByAsal]: ' . $e->getMessage());
        }
    }

    public static function notifyMutasiRejected($mutasi, $reason)
    {
        try {
            Notification::create([
                'user_id' => $mutasi->user_id,
                'type' => 'mutasi_rejected',
                'title' => 'Permohonan Pindah Domisili Ditolak',
                'message' => 'Mohon maaf, permohonan mutasi domisili Anda ditolak. Alasan: ' . $reason,
                'link' => route('profile'),
                'icon' => 'bx bx-x-circle',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyMutasiRejected]: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 5. PENETAPAN & PENGAJUAN PENGURUS RT / RW
    // ==========================================

    public static function notifyPartnerApplicationSubmitted($application)
    {
        try {
            if ($application->user_id) {
                Notification::create([
                    'user_id' => $application->user_id,
                    'type' => 'kemitraan_submitted',
                    'title' => 'Pengajuan Pengurus Wilayah Terkirim',
                    'message' => 'Pengajuan Anda sebagai pengurus ' . $application->region_name . ' (' . $application->position . ') berhasil terkirim dan sedang menunggu peninjauan Admin Desa.',
                    'link' => null,
                    'icon' => 'bx bx-send',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyPartnerApplicationSubmitted]: ' . $e->getMessage());
        }
    }

    public static function notifyPartnerApplicationApproved($application, $regionName, $notes = null)
    {
        try {
            if ($application->user_id) {
                $msg = 'Selamat! Pengajuan Anda sebagai Pengurus Wilayah (' . $regionName . ') telah disetujui. Akun Anda kini memiliki akses ke sistem administrasi wilayah.';
                if ($notes) {
                    $msg .= ' Catatan: ' . $notes;
                }

                Notification::create([
                    'user_id' => $application->user_id,
                    'type' => 'kemitraan_approved',
                    'title' => 'Pengajuan Pengurus Wilayah Disetujui',
                    'message' => $msg,
                    'link' => route('beranda'),
                    'icon' => 'bx bx-award',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyPartnerApplicationApproved]: ' . $e->getMessage());
        }
    }

    public static function notifyPartnerApplicationRejected($application, $notes = null)
    {
        try {
            if ($application->user_id) {
                $msg = 'Mohon maaf, pengajuan Anda sebagai pengurus wilayah belum dapat disetujui oleh Pemerintah Desa.';
                if ($notes) {
                    $msg .= ' Alasan: ' . $notes;
                }

                Notification::create([
                    'user_id' => $application->user_id,
                    'type' => 'kemitraan_rejected',
                    'title' => 'Pengajuan Pengurus Wilayah Ditolak',
                    'message' => $msg,
                    'link' => null,
                    'icon' => 'bx bx-x-circle',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyPartnerApplicationRejected]: ' . $e->getMessage());
        }
    }

    public static function notifyRoleAssigned($user, $roleTitle, $regionName)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'role_promoted',
                'title' => 'Pengangkatan Jabatan Pengurus Wilayah',
                'message' => 'Selamat! Akun Anda telah resmi diangkat menjadi ' . $roleTitle . ' di ' . $regionName . '. Anda kini dapat mengelola layanan administrasi pada wilayah Anda.',
                'link' => route('beranda'),
                'icon' => 'bx bx-shield-quarter',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyRoleAssigned]: ' . $e->getMessage());
        }
    }

    public static function notifyRoleRevoked($user)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'role_revoked',
                'title' => 'Pembaruan Status Jabatan Wilayah',
                'message' => 'Masa tugas kepengurusan wilayah Anda telah berakhir. Akun Anda telah kembali menjadi status Warga.',
                'link' => route('beranda'),
                'icon' => 'bx bx-info-circle',
                'is_read' => false,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationService error [notifyRoleRevoked]: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 6. BROADCAST (PRODUK BARU, RESTOCK GAS, PENGUMUMAN/BERITA)
    // ==========================================

    public static function broadcastNewProduct($unitName, $productName, $regionId = null, $link = null)
    {
        if (!$regionId) return;
        try {
            $query = User::where('role', 'user');
            if ($regionId) {
                $descendantIds = Region::getDescendantIds($regionId);
                $descendantIds[] = $regionId;
                $query->whereIn('region_id', $descendantIds);
            }
            $targetUsers = $query->get();

            $title = 'Produk Baru Tersedia: ' . $productName;
            $message = 'Unit Layanan ' . $unitName . ' kini menyediakan "' . $productName . '". Silakan cek detail dan ketersediaannya.';

            foreach ($targetUsers as $targetUser) {
                Notification::create([
                    'user_id' => $targetUser->id,
                    'type' => 'produk_baru',
                    'title' => $title,
                    'message' => $message,
                    'link' => $link,
                    'icon' => 'bx bx-box',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService error [broadcastNewProduct]: ' . $e->getMessage());
        }
    }

    public static function broadcastStockUpdate($productName, $stock, $satuan = 'tabung', $regionId = null, $link = null)
    {
        if (!$regionId) return;
        try {
            $query = User::where('role', 'user');
            if ($regionId) {
                $descendantIds = Region::getDescendantIds($regionId);
                $descendantIds[] = $regionId;
                $query->whereIn('region_id', $descendantIds);
            }
            $targetUsers = $query->get();

            $title = 'Pembaruan Stok: ' . $productName;
            $message = 'Ketersediaan stok untuk ' . $productName . ' telah diperbarui. Tersedia ' . $stock . ' ' . $satuan . '. Silakan pesan jika Anda membutuhkan.';

            foreach ($targetUsers as $targetUser) {
                Notification::create([
                    'user_id' => $targetUser->id,
                    'type' => 'stok_update',
                    'title' => $title,
                    'message' => $message,
                    'link' => $link,
                    'icon' => 'bx bx-layer-plus',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService error [broadcastStockUpdate]: ' . $e->getMessage());
        }
    }

    public static function broadcastAnnouncement($announcement)
    {
        if (!$announcement->is_active || !in_array($announcement->workflow_state,['published','cancelled'],true)) return;
        try {
            $isBerita = ($announcement->post_category === 'Berita');
            $type = $isBerita ? 'berita' : 'pengumuman';
            $prefix = $isBerita ? '[Kabar Berita] ' : '[Pengumuman Desa] ';
            $title = $prefix . $announcement->title;
            $icon = $isBerita ? 'bx bx-news' : 'bx bx-broadcast';
            $link = route('announcements.show', $announcement->id);

            $query = User::where('role', 'user');
            if (!$isBerita && !$announcement->target_audience_id) return;
            if ($isBerita) {
                $village = Region::whereIn('id',Region::garisLayananUntukWarga($announcement->region_id))->whereIn('type',['desa','kelurahan'])->value('id');
                if (!$village) return;
                $query->whereIn('region_id',[$village,...Region::getDescendantIds($village)]);
            }
            if (!$isBerita) {
                $descendantIds = Region::getDescendantIds($announcement->target_audience_id);
                $descendantIds[] = $announcement->target_audience_id;
                $query->whereIn('region_id', $descendantIds);
            }
            $targetUsers = $query->get();

            $shortDesc = \Illuminate\Support\Str::limit(strip_tags($announcement->description), 140);
            if ($announcement->workflow_state === 'cancelled') {
                $title = '[Dibatalkan] '.$announcement->title;
                $shortDesc = \Illuminate\Support\Str::limit(strip_tags($announcement->rejection_reason),140);
            }

            foreach ($targetUsers as $targetUser) {
                Notification::create([
                    'user_id' => $targetUser->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $shortDesc,
                    'link' => $link,
                    'icon' => $icon,
                    'image' => null,
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService error [broadcastAnnouncement]: ' . $e->getMessage());
            throw $e; // Publication and its notification records must commit together.
        }
    }

    // ==========================================
    // 7. STATUS PESANAN & STOK ADMIN (EXISTING FLOW)
    // ==========================================

    /**
     * Notify user when order is approved
     */
    public function notifyOrderApproved($order, $type)
    {
        $itemName = match ($type) {
            'gas' => $order->item_name ?? 'Gas LPG',
            'mobil' => $order->mobil->nama_mobil ?? 'Transportasi',
            'fasilitas' => $order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum',
            default => $order->barang->nama_barang ?? 'Alat Sewa',
        };
        
        if ($type === 'gas') {
            $message = "Silahkan Ambil Gas, Pesanan Telah dikonfirmasi, NB : Jangan Lupa Tunjukkan Bukti Transaksi";
            $title = "Pesanan Gas Disetujui";
        } elseif ($type === 'mobil') {
            $title = "Pemesanan Sewa Transportasi Disetujui";
            if (($order->delivery_method ?? '') === 'antar') {
                $message = "Pemesanan transportasi {$itemName} telah dikonfirmasi dan siap untuk proses pengantaran.";
            } else {
                $message = "Pemesanan transportasi {$itemName} telah dikonfirmasi. Silahkan ambil di lokasi operasional.";
            }
        } elseif ($type === 'fasilitas') {
            $title = "Peminjaman Fasilitas Disetujui";
            $message = "Permohonan peminjaman {$itemName} telah disetujui oleh pengelola.";
        } else {
            // Rental Logic
            if (($order->delivery_method ?? '') === 'jemput') {
                $message = "Silahkan Ambil Alat Sewa, Pesanan Telah dikonfirmasi, NB : Jangan Lupa Tunjukkan Bukti Transaksi";
            } else {
                $message = "Pesanan dikonfirmasi. Alat sewa akan segera diproses untuk pengiriman.";
            }
            $title = "Penyewaan Disetujui";
        }

        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'approval_success',
            'user_id' => $order->user_id,
            'link' => self::orderLink($order),
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Notify user about specific status updates (Rental Delivery Flow)
     */
    public function notifyOrderStatusUpdate($order, $status)
    {
        $message = "";
        $title = "Update Status Pesanan";
        
        switch ($status) {
            case 'being_prepared':
                $message = "Pesanan alat sewa dipersiapkan.";
                break;
            case 'in_delivery':
                $message = "Pesanan alat sewa dalam perjalanan menuju lokasi mu.";
                break;
            case 'arrived':
                $message = "Pesanan alat sewa sudah tiba dilokasimu.";
                break;
            case 'completed':
                $message = "Waktu penyewaan alat telah selesai.";
                $title = "Penyewaan Selesai";
                break;
            default:
                $message = "Status pesanan diperbarui menjadi: " . $status;
        }

        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'status_update',
            'user_id' => $order->user_id,
            'link' => self::orderLink($order),
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Notify user when order is rejected
     */
    public function notifyOrderRejected($order, $reason, $type)
    {
        $itemName = match ($type) {
            'gas' => $order->item_name ?? 'Gas LPG',
            'mobil' => $order->mobil->nama_mobil ?? 'Transportasi',
            'fasilitas' => $order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum',
            default => $order->barang->nama_barang ?? 'Alat Sewa',
        };
        
        Notification::create([
            'title' => 'Permintaan Ditolak',
            'message' => "Mohon maaf, permintaan {$itemName} Anda ditolak. Alasan: {$reason}",
            'type' => 'rejection',
            'user_id' => $order->user_id,
            'link' => self::orderLink($order),
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Notify user and admin when stock is insufficient
     */
    public function notifyStockInsufficient($order, $type, $availableStock, $requestedQty)
    {
        $itemName = match ($type) {
            'gas' => $order->item_name ?? 'Gas LPG',
            'mobil' => $order->mobil->nama_mobil ?? 'Transportasi',
            'fasilitas' => $order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum',
            default => $order->barang->nama_barang ?? 'Alat Sewa',
        };
        
        // Notify user
        Notification::create([
            'title' => 'Stok Tidak Mencukupi',
            'message' => "Mohon maaf, stok {$itemName} tidak mencukupi. Silakan ajukan ulang atau hubungi admin.",
            'type' => 'approval_failed',
            'user_id' => $order->user_id,
            'link' => self::orderLink($order),
            'admin_id' => auth()->id(),
        ]);

        $regionId = null;
        if ($type === 'gas') {
            $regionId = $order->gas->region_id ?? null;
        } elseif ($type === 'rental') {
            $regionId = $order->barang->region_id ?? null;
        }

        // Notify admin
        AdminNotification::create([
            'type' => 'stock_alert',
            'reference_id' => $order->id,
            'region_id' => $regionId,
            'title' => 'Gagal Approve - Stok Tidak Cukup',
            'message' => "Gagal approve request #{$order->order_number}. Stok {$itemName} tidak cukup (tersisa: {$availableStock}, diminta: {$requestedQty})",
            'is_read' => false,
        ]);
    }

    /**
     * Notify user when rental is completed
     */
    public function notifyRentalCompleted($booking)
    {
        $itemName = $booking->barang->nama_barang ?? 'Alat';
        
        Notification::create([
            'title' => 'Penyewaan Selesai',
            'message' => "Terima kasih! Penyewaan {$itemName} telah selesai. Berikan penilaian Anda terhadap layanan kami.",
            'type' => 'rental_completed',
            'user_id' => $booking->user_id,
            'link' => self::orderLink($booking),
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Notify admin when stock is low
     */
    public function notifyLowStock($item, $type, $currentStock)
    {
        $itemName = $type === 'gas' ? $item->jenis_gas : $item->nama_barang;
        $satuan = $item->satuan ?? 'unit';
        
        AdminNotification::create([
            'type' => 'stock_low',
            'reference_id' => $item->id,
            'region_id' => $item->region_id ?? null,
            'title' => 'Stok Menipis',
            'message' => "Stok {$itemName} menipis! Tersisa: {$currentStock} {$satuan}. Segera restock.",
            'is_read' => false,
        ]);
    }

    /**
     * Notify admin when stock is depleted
     */
    public function notifyStockDepleted($item, $type)
    {
        $itemName = $type === 'gas' ? $item->jenis_gas : $item->nama_barang;
        
        AdminNotification::create([
            'type' => 'stock_depleted',
            'reference_id' => $item->id,
            'region_id' => $item->region_id ?? null,
            'title' => 'Stok Habis',
            'message' => "Stok {$itemName} HABIS! Segera restock atau nonaktifkan item.",
            'is_read' => false,
        ]);
    }
}

