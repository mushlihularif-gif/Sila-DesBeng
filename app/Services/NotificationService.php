<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\AdminNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Notify user when order is approved
     */
    public function notifyOrderApproved($order, $type)
    {
        $itemName = $type === 'gas' ? ($order->item_name ?? 'Gas') : ($order->barang->nama_barang ?? 'Alat');
        
        if ($type === 'gas') {
            $message = "Silahkan Ambil Gas, Pesanan Telah dikonfirmasi, NB : Jangan Lupa Tunjukkan Bukti Transaksi";
            $title = "Pesanan Gas Disetujui";
        } else {
            // Rental Logic
            if ($order->delivery_method === 'jemput') {
                $message = "Silahkan Ambil Alat Sewa, Pesanan Telah dikonfirmasi, NB : Jangan Lupa Tunjukkan Bukti Transaksi";
            } else {
                // Delivery method is 'antar' (or others)
                $message = "Pesanan dikonfirmasi. Alat sewa akan segera diproses untuk pengiriman.";
            }
            $title = "Penyewaan Disetujui";
        }

        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'approval_success',
            'user_id' => $order->user_id,
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
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Notify user when order is rejected
     */
    public function notifyOrderRejected($order, $reason, $type)
    {
        $itemName = $type === 'gas' ? ($order->item_name ?? 'Gas') : ($order->barang->nama_barang ?? 'Alat');
        
        Notification::create([
            'title' => 'Permintaan Ditolak',
            'message' => "Mohon maaf, permintaan {$itemName} Anda ditolak. Alasan: {$reason}",
            'type' => 'rejection',
            'user_id' => $order->user_id,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Notify user and admin when stock is insufficient
     */
    public function notifyStockInsufficient($order, $type, $availableStock, $requestedQty)
    {
        $itemName = $type === 'gas' ? $order->item_name : $order->barang->nama_barang;
        
        // Notify user
        Notification::create([
            'title' => 'Stok Tidak Mencukupi',
            'message' => "Mohon maaf, stok {$itemName} tidak mencukupi. Silakan ajukan ulang atau hubungi admin.",
            'type' => 'approval_failed',
            'user_id' => $order->user_id,
            'admin_id' => auth()->id(),
        ]);

        // Notify admin
        AdminNotification::create([
            'type' => 'stock_alert',
            'reference_id' => $order->id,
            'title' => 'Gagal Approve - Stok Tidak Cukup',
            'message' => "⚠️ Gagal approve request #{$order->order_number}. Stok {$itemName} tidak cukup (tersisa: {$availableStock}, diminta: {$requestedQty})",
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
            'message' => "Terima kasih! Penyewaan {$itemName} telah selesai. Jangan lupa beri rating ⭐",
            'type' => 'rental_completed',
            'user_id' => $booking->user_id,
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
            'title' => 'Stok Menipis',
            'message' => "⚠️ Stok {$itemName} menipis! Tersisa: {$currentStock} {$satuan}. Segera restock.",
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
            'title' => 'Stok Habis',
            'message' => "🚨 Stok {$itemName} HABIS! Segera restock atau nonaktifkan item.",
            'is_read' => false,
        ]);
    }
}
