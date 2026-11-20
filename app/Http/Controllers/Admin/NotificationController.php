<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function recipients()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin_desa', 'super_admin'], true), 403);
        $query = User::where('role', 'user');
        if ($user->role === 'admin_desa') {
            abort_unless($user->region_id, 403);
            $query->whereIn('region_id', array_merge([$user->region_id], \App\Models\Region::getDescendantIds($user->region_id)));
        }
        return $query;
    }

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
        $users = $this->recipients()->orderBy('name')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image
        ]);

        $this->recipients()->findOrFail($request->integer('user_id'));
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

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai sudah dibaca.'
            ]);
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca.');
    }

    public function markAllAsRead(Request $request)
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

        // Dua skrip lonceng hidup berdampingan di layout: yang satu mengirim
        // X-Requested-With, yang satu Accept: application/json. Keduanya
        // diterima supaya tidak ada yang menerima redirect di tengah fetch().
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil ditandai sebagai sudah dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sebagai sudah dibaca.');
    }


    public function destroy(Request $request, $id)
    {
        abort_if(auth()->user()?->isStaff(), 403, 'Staf tidak memiliki izin menghapus notifikasi.');

        try {
            // Dibatasi untukPengguna() agar admin hanya dapat menghapus notifikasi
            // milik wilayahnya sendiri.
            $notification = \App\Models\AdminNotification::untukPengguna(auth()->user())
                ->findOrFail($id);
            $notification->delete();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi berhasil dihapus.',
                ]);
            }

            return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan.',
                ], 404);
            }

            return redirect()->back()->with('error', 'Notifikasi tidak ditemukan.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi belum berhasil dihapus. Silakan coba kembali.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Notifikasi belum berhasil dihapus. Silakan coba kembali.');
        }
    }

    public function deleteAll(Request $request)
    {
        abort_if(auth()->user()?->isStaff(), 403, 'Staf tidak memiliki izin menghapus notifikasi.');

        try {
            // Hanya bersihkan notifikasi yang memang menjadi hak akses pengguna saat ini
            // (sesuai wilayah admin desa / platform super admin).
            $deleted = \App\Models\AdminNotification::untukPengguna(auth()->user())->delete();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'deleted' => $deleted,
                    'message' => 'Semua notifikasi berhasil dibersihkan.',
                ]);
            }

            return redirect()->back()->with('success', 'Semua notifikasi berhasil dibersihkan.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi belum berhasil dibersihkan. Silakan coba kembali.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Notifikasi belum berhasil dibersihkan. Silakan coba kembali.');
        }
    }
}