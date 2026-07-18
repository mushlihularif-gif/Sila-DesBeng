<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where(function ($query) {
                $query->where('user_id', auth()->id())
                      ->orWhere(function ($q) {
                          $q->whereNull('user_id')
                            ->where('type', 'pesan_admin');
                      });
            })
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unreadCount' => $unreadCount
            ]
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca'
        ]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai sudah dibaca'
        ]);
    }

    public function deleteAll()
    {
        try {
            DB::beginTransaction();

            Notification::where('user_id', auth()->id())
                ->where('type', '!=', 'pesan_admin')
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
