<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function defaultNotificationRoute(): string
    {
        $role = auth()->user()?->role;

        return in_array($role, ['super_admin', 'admin_desa', 'staff'], true)
            ? 'admin.notifications.index'
            : 'user.notifications';
    }

    // Halaman daftar notifikasi
    public function index(Request $request)
    {
        return redirect()->route($this->defaultNotificationRoute(), $request->query());
    }

    // Mark as read + redirect ke detail
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if (! empty($notification->link)) {
            return redirect($notification->link);
        }

        return redirect()->route($this->defaultNotificationRoute())
            ->with('success', 'Notifikasi telah dibaca!');
    }

    // Mark all as read
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca!');
    }

    // Get unread notifications (dropdown)
    public function getUnread()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    // Delete notification
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus!');
    }

    // Clear all read notifications
    public function clearRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', true)
            ->delete();

        return back()->with('success', 'Notifikasi yang sudah dibaca berhasil dihapus!');
    }
}
