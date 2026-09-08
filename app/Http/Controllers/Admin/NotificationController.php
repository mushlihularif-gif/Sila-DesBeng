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
        
        // Hanya notifikasi yang berhak dilihat pengguna ini, diurutkan dari
        // yang terbaru. Tanpa untukPengguna(), halaman ini menampilkan
        // notifikasi seluruh wilayah kepada siapa pun yang membukanya.
        $notifications = \App\Models\AdminNotification::untukPengguna(auth()->user())
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['title', 'message'], $search);
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

    public function markAsRead(Request $request, $id)
    {
        // Dibatasi untukPengguna(): tanpa itu, siapa pun yang menebak id bisa
        // menandai notifikasi wilayah lain sudah dibaca, dan admin wilayah itu
        // kehilangan penanda tanpa pernah membukanya.
        $notification = \App\Models\AdminNotification::untukPengguna(auth()->user())
            ->findOrFail($id);

        $notification->is_read = true;
        $notification->save();

        // Lonceng memanggil ini lewat fetch() sambil halaman berpindah ke
        // tujuan notifikasi, jadi jawabannya tidak boleh berupa redirect.
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        // Tandai sebagai sudah dibaca, TERBATAS pada notifikasi milik pengguna
        // ini. Tanpa untukPengguna(), satu klik dari admin desa mana pun akan
        // menandai notifikasi seluruh wilayah lain sebagai sudah dibaca, dan
        // admin wilayah itu tidak akan pernah tahu ada yang masuk.
        \App\Models\AdminNotification::untukPengguna(auth()->user())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sebagai sudah dibaca.');
    }

    public function destroy($id)
    {
        try {
            // Satu-satunya pemanggil yang masih tanpa untukPengguna(), padahal
            // ini yang paling merusak: menghapus notifikasi wilayah lain
            // menghilangkannya untuk selamanya, bukan sekadar menandainya.
            $notification = \App\Models\AdminNotification::untukPengguna(auth()->user())
                ->findOrFail($id);
            $notification->delete();

            return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
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