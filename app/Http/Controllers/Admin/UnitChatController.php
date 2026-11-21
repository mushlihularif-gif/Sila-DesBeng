<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitChatMessage;
use App\Models\UnitChatSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitChatController extends Controller
{
    private const SERVICE_PERMISSIONS = [
        'gas' => 'gas',
        'penyewaan' => 'sewa_alat',
        'mobil' => 'sewa_mobil',
        'fasilitas_umum' => 'fasilitas_umum',
    ];

    /**
     * Validasi akses Admin Desa / Staf untuk unit layanan terkait.
     */
    private function authorizeUnitAdmin(string $service): ?User
    {
        $admin = Auth::user();
        $permission = self::SERVICE_PERMISSIONS[$service] ?? null;

        if (!$admin || !$permission || !$admin->isAdmin() || !$admin->region_id || !$admin->hasUnitPermission($permission)) {
            return null;
        }

        return $admin;
    }

    /**
     * Cari session dengan otorisasi wilayah dan unit yang ketat.
     */
    private function findSession(string $service, $sessionId): ?UnitChatSession
    {
        $admin = $this->authorizeUnitAdmin($service);
        if (!$admin) {
            return null;
        }

        return UnitChatSession::where('service_type', $service)
            ->where('region_id', $admin->region_id)
            ->find($sessionId);
    }

    /**
     * Daftar sesi chat warga secara real-time untuk panel Admin Desa & Staf.
     */
    public function getSessions(Request $request, $service)
    {
        if (!array_key_exists($service, self::SERVICE_PERMISSIONS)) {
            return response()->json(['status' => 'error', 'message' => 'Unit layanan tidak ditemukan.'], 404);
        }

        $admin = $this->authorizeUnitAdmin($service);
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke chat layanan ini.'], 403);
        }

        $sessions = UnitChatSession::visibleForAdmin($service, (int) $admin->region_id)
            ->with(['user', 'latestMessage'])
            ->get();

        $payload = $sessions->map(function (UnitChatSession $chat) use ($admin) {
            $displayName = $chat->displayUserName();
            $userPhoto = null;
            if ($chat->user) {
                try {
                    $userPhoto = $chat->user->profile_photo_url;
                } catch (\Throwable $e) {
                    $userPhoto = null;
                }
            }

            $clearedBeforeId = $chat->adminClearedBeforeId((int) $admin->id);
            $latestMsg = $chat->latestMessage;
            $hasVisibleAfterClear = !$clearedBeforeId || ($latestMsg && (int) $latestMsg->id > $clearedBeforeId);

            $lastSenderType = $hasVisibleAfterClear ? $latestMsg?->sender_type : null;
            $unread = $hasVisibleAfterClear ? (int) $chat->unread_admin_count : 0;
            $needsReply = $hasVisibleAfterClear && $chat->status !== 'resolved' && (
                $unread > 0 || ($chat->status === 'escalated' && $lastSenderType !== 'admin')
            );

            return [
                'id' => $chat->id,
                'user_id' => $chat->user_id,
                'user_name' => $displayName,
                'user_photo' => $userPhoto,
                'status' => $chat->status,
                'item_label' => !empty($chat->item_reference) ? $chat->item_label : null,
                'last_message' => $hasVisibleAfterClear
                    ? ($chat->last_message ?: 'Memulai percakapan...')
                    : 'Obrolan dibersihkan (tampilan Anda)',
                'last_message_time' => $chat->last_message_at
                    ? $chat->last_message_at->format('H:i')
                    : ($chat->updated_at ? $chat->updated_at->format('H:i') : ''),
                'unread_admin_count' => $unread,
                'last_sender_type' => $lastSenderType,
                'needs_reply' => $needsReply,
                'cleared_for_me' => !$hasVisibleAfterClear,
            ];
        })->values()->all();

        $totalUnread = array_sum(array_column($payload, 'unread_admin_count'));

        return response()->json([
            'status' => 'success',
            'data' => [
                'sessions' => $payload,
                'total_sessions' => count($payload),
                'total_unread' => (int) $totalUnread,
            ],
        ]);
    }

    /**
     * Detail Pesan Chat untuk Admin Desa & Staf
     */
    public function getMessages($service, $sessionId)
    {
        $admin = Auth::user();
        $session = $this->findSession($service, $sessionId);
        if (!$session || !$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi percakapan tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        // Reset unread count for admin when opened & tandai notifikasi chat terkait sebagai dibaca
        if ((int) $session->unread_admin_count > 0) {
            $session->update(['unread_admin_count' => 0]);
        }
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('admin_notifications')) {
                $chatNotifType = [
                    'gas' => 'unit_chat_gas',
                    'penyewaan' => 'unit_chat_penyewaan',
                    'mobil' => 'unit_chat_mobil',
                    'fasilitas_umum' => 'unit_chat_fasilitas',
                ][$service] ?? ('unit_chat_' . $service);

                \App\Models\AdminNotification::where('type', $chatNotifType)
                    ->where('reference_id', $session->id)
                    ->where('region_id', $session->region_id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menandai notifikasi chat sebagai dibaca: ' . $e->getMessage());
        }

        $session->load('user:id,name,avatar');
        $clearedBeforeId = $session->adminClearedBeforeId((int) $admin->id);
        $messagesQuery = $session->messages()->with('sender:id,name,role');
        if ($clearedBeforeId > 0) {
            $messagesQuery->where('id', '>', $clearedBeforeId);
        }

        $messages = $messagesQuery->get()->map(function ($msg) {
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
            $productInfo = $this->resolveProductInfo($service, $session->item_label, $session->region_id);
        }

        $userPhoto = null;
        if ($session->user) {
            try {
                $userPhoto = $session->user->profile_photo_url;
            } catch (\Throwable $e) {
                $userPhoto = null;
            }
        }

        $displayName = $session->displayUserName();
        $lastMsg = end($messages);
        $lastSenderType = is_array($lastMsg) ? ($lastMsg['sender_type'] ?? null) : null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => [
                    'id' => $session->id,
                    'service_type' => $session->service_type,
                    'region_id' => $session->region_id,
                    'user_id' => $session->user_id,
                    'user_name' => $displayName,
                    'status' => $session->status,
                    'item_reference' => $session->item_reference,
                    'item_label' => !empty($session->item_reference) ? $session->item_label : null,
                    'last_message' => $session->last_message,
                    'last_sender_type' => $lastSenderType,
                    'unread_admin_count' => 0,
                ],
                'messages' => $messages,
                'user_photo' => $userPhoto,
                'product_info' => $productInfo,
            ],
        ]);
    }

    /**
     * Resolusi metadata produk berdasarkan nama item reference dan unit layanan
     */
    private function resolveProductInfo($service, $reference, $regionId)
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
                $item = \App\Models\Mobil::where('region_id', $regionId)->where('nama_mobil', $refClean)->first();
                if ($item) {
                    $info['title'] = $item->nama_mobil;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/mobil.png');
                    $info['price'] = 'Rp ' . number_format($item->harga_mulai ?? $item->harga_sewa ?? $item->harga_dalam_desa ?? 0, 0, ',', '.') . ' / hari';
                    $info['category'] = ucfirst($item->kategori ?? 'Transportasi');
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.mobil.show')) {
                        $info['url'] = route('admin.unit.mobil.show', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/mobil.png');
                }
            } elseif ($service === 'gas') {
                $item = \App\Models\Gas::where('region_id', $regionId)->where('jenis_gas', $refClean)->first();
                if ($item) {
                    $info['title'] = $item->jenis_gas;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/F2.png');
                    $info['price'] = 'Rp ' . number_format($item->harga_satuan ?? 0, 0, ',', '.') . ' / tabung';
                    $info['category'] = 'Pangkalan Gas';
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.penjualan_gas.show')) {
                        $info['url'] = route('admin.unit.penjualan_gas.show', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/F2.png');
                }
            } elseif ($service === 'penyewaan' || $service === 'alat') {
                $item = \App\Models\Barang::where('region_id', $regionId)->where('nama_barang', $refClean)->first();
                if ($item) {
                    $info['title'] = $item->nama_barang;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/F1.png');
                    $info['price'] = 'Rp ' . number_format($item->harga_sewa ?? 0, 0, ',', '.') . ($item->billing_basis === 'per_day' ? ' / hari' : ' / kegiatan');
                    $info['category'] = ucfirst($item->kategori ?? 'Alat & Mesin');
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.penyewaan.show')) {
                        $info['url'] = route('admin.unit.penyewaan.show', $item->id);
                    }
                } else {
                    $info['image'] = asset('User/img/elemen/F1.png');
                }
            } elseif ($service === 'fasilitas' || $service === 'fasilitas_umum') {
                $item = \App\Models\FasilitasUmum::where('region_id', $regionId)->where('nama_fasilitas', $refClean)->first();
                if ($item) {
                    $info['title'] = $item->nama_fasilitas;
                    $info['image'] = $item->foto ? asset('storage/' . $item->foto) : asset('User/img/elemen/fasilitas.png');
                    $info['price'] = $item->harga_sewa ? 'Rp ' . number_format($item->harga_sewa, 0, ',', '.') . ' / hari' : 'Gratis / Kebijakan Desa';
                    $info['category'] = ucfirst($item->kategori ?? 'Fasilitas Umum');
                    $info['status'] = $item->status ?? 'Tersedia';
                    if (\Route::has('admin.unit.fasilitas_umum.show')) {
                        $info['url'] = route('admin.unit.fasilitas_umum.show', $item->id);
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
     * Balas Chat dari Admin Desa / Staf ke Warga
     */
    public function replyChat(Request $request, $service, $sessionId)
    {
        $admin = $this->authorizeUnitAdmin($service);
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin membalas obrolan layanan ini.'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $session = $this->findSession($service, $sessionId);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi percakapan tidak ditemukan.',
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
            'unread_admin_count' => 0,
            'unread_user_count' => $session->unread_user_count + 1,
            'status' => 'escalated',
        ]);

        // Kirim notifikasi balasan chat ke akun warga / RT / RW jika sedang login
        \App\Services\NotificationService::notifyUserChatReply(
            (string) $service,
            $session,
            (string) ($admin->name ?? 'Petugas Layanan'),
            (string) $request->message
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan balasan berhasil dikirim.',
            'data' => [
                'chat_message' => [
                    'id' => $msg->id,
                    'session_id' => $msg->session_id,
                    'sender_type' => 'admin',
                    'sender_id' => $admin->id,
                    'sender_name' => $admin->name,
                    'message' => (string) $msg->message,
                    'time_formatted' => $msg->created_at ? $msg->created_at->format('H:i') : '',
                    'created_at' => $msg->created_at ? $msg->created_at->toISOString() : null,
                ],
                'session_status' => $session->status,
            ],
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
                'message' => 'Sesi percakapan tidak ditemukan.',
            ], 404);
        }

        $session->update([
            'status' => 'resolved',
            'unread_admin_count' => 0,
        ]);

        UnitChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'sender_id' => null,
            'message' => 'Sesi obrolan ini telah ditandai selesai oleh Petugas Layanan. Terima kasih sudah menghubungi kami.',
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi obrolan berhasil ditandai selesai.',
        ]);
    }

    /**
     * Bersihkan isi chat hanya di tampilan akun Admin Desa / Staf ini
     * (tidak menghapus riwayat di sisi warga maupun pengelola lain).
     */
    public function clearChat($service, $sessionId)
    {
        $admin = $this->authorizeUnitAdmin($service);
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin membersihkan obrolan ini.'], 403);
        }

        $session = $this->findSession($service, $sessionId);
        if (!$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi percakapan tidak ditemukan.',
            ], 404);
        }

        $clearedBeforeId = $session->clearForAdmin((int) $admin->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Obrolan di tampilan Anda berhasil dibersihkan.',
            'data' => [
                'cleared_before_id' => $clearedBeforeId,
            ],
        ]);
    }
}
