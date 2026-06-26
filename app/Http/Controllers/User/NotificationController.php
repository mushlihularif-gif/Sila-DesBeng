<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Ambil semua notifikasi untuk pengguna yang diautentikasi, diurutkan dari yang terbaru
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

        // Hitung notifikasi yang belum dibaca
        $unreadCount = $notifications->where('is_read', false)->count();

        return view('users.notifications', compact('notifications', 'unreadCount'));
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

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai sudah dibaca'
            ]);
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sebagai sudah dibaca'
            ]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sebagai sudah dibaca');
    }
    public function deleteAll()
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $count = Notification::where('user_id', auth()->id())
                ->where('type', '!=', 'pesan_admin') // Kecuali pesan dari admin
                ->delete();

            \Illuminate\Support\Facades\DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Semua notifikasi berhasil dihapus'
                ]);
            }

            return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error deleting notifications: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
