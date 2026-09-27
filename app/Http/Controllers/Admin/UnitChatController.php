<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitChatSession;
use App\Models\UnitChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitChatController extends Controller
{
    /**
     * Cari session dengan otorisasi wilayah yang fleksibel dan aman
     */
    private function findSession($service, $sessionId)
    {
        $admin = Auth::user();
        if (!$admin) {
            return null;
        }

        $query = UnitChatSession::where('service_type', $service);

        // Jika bukan super_admin dan memiliki region_id, filter berdasarkan region_id
        if (!in_array($admin->role, ['super_admin', 'admin']) && $admin->region_id) {
            $query->where('region_id', $admin->region_id);
        } elseif ($admin->region_id) {
            $query->where(function ($q) use ($admin) {
                $q->where('region_id', $admin->region_id)
                  ->orWhereNull('region_id');
            });
        }

        return $query->find($sessionId);
    }

    /**
     * Detail Pesan Chat untuk Admin
     */
    public function getMessages($service, $sessionId)
    {
        $session = $this->findSession($service, $sessionId);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi percakapan tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        // Reset unread count for admin
        $session->update(['unread_admin_count' => 0]);

        $session->load('user');
        $messages = $session->messages()->with('sender')->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'session_id' => $msg->session_id,
                'sender_type' => (string) $msg->sender_type,
                'sender_id' => $msg->sender_id,
                'message' => (string) ($msg->message ?? ''),
                'item_data' => $msg->item_data,
                'is_read' => (bool) $msg->is_read,
                'time_formatted' => $msg->created_at ? $msg->created_at->format('H:i') : '',
                'created_at' => $msg->created_at ? $msg->created_at->toISOString() : null,
                'sender_name' => $msg->sender ? $msg->sender->name : null,
            ];
        })->values()->all();

        $productInfo = null;
        if (!empty($session->item_reference)) {
            $productInfo = $this->resolveProductInfo($service, $session->item_reference);
        }

        $userPhoto = null;
        if ($session->user) {
            try {
                $userPhoto = $session->user->profile_photo_url;
            } catch (\Throwable $e) {
                $userPhoto = null;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $session,
                'messages' => $messages,
                'user_photo' => $userPhoto,
                'product_info' => $productInfo,
            ]
        ]);
    }

    /**
     * Resolusi metadata produk berdasarkan nama item reference dan unit layanan
     */
    private function resolveProductInfo($service, $reference)
    {
        $refClean = trim($reference);
        if (empty($refClean)) {
            return null;
        }

        $info = [
            'title' => $refClean,
            'image' => null,
            'price' => null,
            'category' => ucfirst($service),
            'status' => 'Tersedia',
            'url' => '#',
        ];

        try {
            if ($service === 'mobil') {
                $item = \App\Models\Mobil::where('nama_mobil', 'like', "%{$refClean}%")->first();
                if ($item) {
                    $info['title'] = $item->nama_mobil;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/mobil.png');
                    $info['price'] = 'Rp ' . number_format($item->harga_sewa ?? $item->harga_dalam_desa ?? 0, 0, ',', '.') . ' / hari';
                    $info['category'] = ucfirst($item->kategori ?? 'Transportasi');
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.mobil.edit')) {
                        $info['url'] = route('admin.unit.mobil.edit', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/mobil.png');
                }
            } elseif ($service === 'gas') {
                $item = \App\Models\Gas::where('jenis_gas', 'like', "%{$refClean}%")->first();
                if ($item) {
                    $info['title'] = $item->jenis_gas;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/F2.png');
                    $info['price'] = 'Rp ' . number_format($item->harga_satuan ?? 0, 0, ',', '.') . ' / tabung';
                    $info['category'] = 'Pangkalan Gas';
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.penjualan_gas.edit')) {
                        $info['url'] = route('admin.unit.penjualan_gas.edit', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/F2.png');
                }
            } elseif ($service === 'penyewaan' || $service === 'alat') {
                $item = \App\Models\Barang::where('nama_barang', 'like', "%{$refClean}%")->first();
                if ($item) {
                    $info['title'] = $item->nama_barang;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/F1.png');
                    $info['price'] = 'Rp ' . number_format($item->harga_sewa ?? 0, 0, ',', '.') . ' / hari';
                    $info['category'] = ucfirst($item->kategori ?? 'Alat & Mesin');
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.penyewaan.edit')) {
                        $info['url'] = route('admin.unit.penyewaan.edit', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/F1.png');
                }
            } elseif ($service === 'fasilitas' || $service === 'fasilitas_umum') {
                $item = \App\Models\FasilitasUmum::where('nama_fasilitas', 'like', "%{$refClean}%")->first();
                if ($item) {
                    $info['title'] = $item->nama_fasilitas;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/fasilitas.png');
                    $info['price'] = $item->harga_sewa ? 'Rp ' . number_format($item->harga_sewa, 0, ',', '.') . ' / hari' : 'Gratis / Kebijakan Desa';
                    $info['category'] = ucfirst($item->kategori ?? 'Fasilitas Umum');
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.fasilitas_umum.edit')) {
                        $info['url'] = route('admin.unit.fasilitas_umum.edit', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/fasilitas.png');
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error resolving product info in chat: ' . $e->getMessage());
        }

        return $info;
    }

    /**
     * Balas Chat dari Admin ke Warga
     */
    public function replyChat(Request $request, $service, $sessionId)
    {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $session = $this->findSession($service, $sessionId);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi percakapan tidak ditemukan.'
            ], 404);
        }

        $msg = UnitChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        $session->update([
            'last_message' => $request->message,
            'last_message_at' => now(),
            'unread_user_count' => $session->unread_user_count + 1,
            'status' => 'escalated',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan balasan berhasil dikirim.',
            'data' => [
                'chat_message' => [
                    'id' => $msg->id,
                    'session_id' => $msg->session_id,
                    'sender_type' => 'admin',
                    'sender_id' => $admin->id,
                    'message' => (string) $msg->message,
                    'time_formatted' => $msg->created_at ? $msg->created_at->format('H:i') : '',
                    'created_at' => $msg->created_at ? $msg->created_at->toISOString() : null,
                ],
            ]
        ]);
    }

    /**
     * Tandai Sesi Chat Selesai
     */
    public function resolveChat($service, $sessionId)
    {
        $session = $this->findSession($service, $sessionId);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi percakapan tidak ditemukan.'
            ], 404);
        }

        $session->update([
            'status' => 'resolved',
        ]);

        UnitChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'sender_id' => null,
            'message' => 'Sesi obrolan ini telah ditandai selesai oleh Petugas Layanan BUMDes. Terima kasih sudah menghubungi kami.',
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi obrolan berhasil ditandai selesai.',
        ]);
    }
}
