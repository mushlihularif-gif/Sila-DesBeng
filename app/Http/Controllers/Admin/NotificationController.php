<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        // Ambil semua notifikasi untuk admin, diurutkan dari yang terbaru
        // Ambil semua notifikasi untuk admin, diurutkan dari yang terbaru (termasuk yang dihapus user)
        $notifications = Notification::with(['user', 'admin'])
            ->withTrashed() // Tampilkan juga yang di-soft delete oleh user
            ->when($search, function ($query, $search) {
                return $query->where('title', 'LIKE', "%{$search}%")
                           ->orWhere('message', 'LIKE', "%{$search}%")
                           ->orWhereHas('user', function($q) use ($search) {
                               $q->where('name', 'LIKE', "%{$search}%");
                           });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.notifications.index', compact('notifications', 'search'));
    }

    public function create()
    {
        // Ambil semua user untuk dituju notifikasi (jika mengirim ke user)
        $users = User::all();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'nullable|exists:users,id', // Bisa kosong jika broadcast
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image
        ]);

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'type' => 'pesan_admin', // Tipe untuk notifikasi dari admin
            'admin_id' => auth()->id(), // ID admin yang sedang login
            'user_id' => $request->user_id, // Jika ditujukan ke user tertentu
            'sent_at' => now(),
        ];

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
            $data['image'] = $imagePath;
        }

        $notification = Notification::create($data);

        return redirect()->route('admin.notifications.index')->with('success', 'Notifikasi berhasil dikirim.');
    }

    public function markAsRead($id)
    {
        $notification = Notification::withTrashed()->findOrFail($id);
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        // Jika notifikasi terkait user, bisa redirect ke halaman user tertentu
        // Untuk sekarang, kembali ke index
        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        // Tandai semua notifikasi milik admin sebagai dibaca
        Notification::where('admin_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sebagai sudah dibaca.');
    }

    public function destroy($id)
    {
        try {
            $notification = Notification::withTrashed()->findOrFail($id);
            $notification->forceDelete(); // Hard Delete

            return redirect()->back()->with('success', 'Notifikasi berhasil dihapus permanen.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus notifikasi: ' . $e->getMessage());
        }
    }

    public function deleteAll()
    {
        try {
            // Hard delete dinonaktifkan demi keamanan audit log (Log Wiping Defense)
            return redirect()->back()->with('error', 'Penghapusan massal dinonaktifkan untuk menjaga integritas Audit Trail.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus semua notifikasi: ' . $e->getMessage());
        }
    }
}