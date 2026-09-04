<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitChatSession;
use App\Models\UnitChatMessage;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UnitChatApiController extends Controller
{
    /**
     * Ambil riwayat chat untuk warga (Web & Mobile Flutter)
     */
    public function getChatHistory(Request $request, $service)
    {
        $user = Auth::guard('sanctum')->user();
        $sessionToken = $request->header('X-Chat-Session-Token') ?: $request->get('session_token');
        $regionId = $request->get('region_id') ?: ($user ? $user->region_id : null);

        if (!$regionId) {
            // Default ke region pertama jika tidak ditentukan
            $firstRegion = Region::first();
            $regionId = $firstRegion ? $firstRegion->id : 1;
        }

        $session = null;
        if ($user) {
            $session = UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$session && $sessionToken) {
            $session = UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('session_token', $sessionToken)
                ->first();
        }

        if (!$session) {
            $newToken = $sessionToken ?: Str::random(32);
            $userName = $user ? $user->name : 'Warga';

            $session = UnitChatSession::create([
                'service_type' => $service,
                'region_id' => $regionId,
                'user_id' => $user ? $user->id : null,
                'session_token' => $newToken,
                'user_name' => $userName,
                'status' => 'bot',
                'unread_admin_count' => 0,
                'unread_user_count' => 0,
            ]);

            // Pesan sambutan awal dari bot sesuai layanan
            $greeting = $this->getWelcomeMessage($service, $userName);
            UnitChatMessage::create([
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => $greeting,
                'is_read' => true,
            ]);
        } else {
            // Reset unread untuk user saat membuka
            $session->update(['unread_user_count' => 0]);
        }

        $messages = $session->messages()->with('sender')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $session,
                'messages' => $messages,
                'is_escalated' => ($session->status === 'escalated'),
            ]
        ]);
    }

    /**
     * Kirim pesan dari warga ke chat layanan
     */
    public function sendChatMessage(Request $request, $service)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::guard('sanctum')->user();
        $sessionToken = $request->header('X-Chat-Session-Token') ?: $request->get('session_token');
        $regionId = $request->get('region_id') ?: ($user ? $user->region_id : null);
        $itemRef = $request->get('item_reference');

        if (!$regionId) {
            $firstRegion = Region::first();
            $regionId = $firstRegion ? $firstRegion->id : 1;
        }

        $session = null;
        if ($user) {
            $session = UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$session && $sessionToken) {
            $session = UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('session_token', $sessionToken)
                ->first();
        }

        if (!$session) {
            $sessionToken = $sessionToken ?: Str::random(32);
            $session = UnitChatSession::create([
                'service_type' => $service,
                'region_id' => $regionId,
                'user_id' => $user ? $user->id : null,
                'session_token' => $sessionToken,
                'user_name' => $user ? $user->name : 'Warga',
                'status' => 'bot',
                'item_reference' => $itemRef,
                'unread_admin_count' => 0,
                'unread_user_count' => 0,
            ]);
        } elseif ($itemRef && !$session->item_reference) {
            $session->update(['item_reference' => $itemRef]);
        }

        // Simpan pesan user
        $userMsg = UnitChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'user',
            'sender_id' => $user ? $user->id : null,
            'message' => $request->message,
            'is_read' => false,
        ]);

        $session->update([
            'last_message' => $request->message,
            'last_message_at' => now(),
            'unread_admin_count' => $session->unread_admin_count + 1,
        ]);

        $lowerMsg = strtolower($request->message);
        $isEscalateRequest = Str::contains($lowerMsg, ['chat admin', 'petugas', 'pengelola', 'hubungi admin', 'bicara dengan admin', 'manusia']);

        // Jika user minta eskalasi
        if ($isEscalateRequest && $session->status !== 'escalated') {
            $session->update(['status' => 'escalated']);

            $botReply = "Percakapan Anda telah dialihkan langsung ke Petugas Layanan BUMDes. Petugas akan segera membaca dan merespons pesan Anda di sini. Mohon ditunggu.";
            $botMsg = UnitChatMessage::create([
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => $botReply,
                'is_read' => true,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'session_token' => $session->session_token,
                    'user_message' => $userMsg,
                    'bot_message' => $botMsg,
                    'is_escalated' => true,
                ]
            ]);
        }

        // Jika masih dalam status bot, sediakan respons asisten cerdas otomatis
        if ($session->status === 'bot') {
            $botReply = $this->generateBotAnswer($service, $request->message);
            $botMsg = UnitChatMessage::create([
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => $botReply,
                'is_read' => true,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'session_token' => $session->session_token,
                    'user_message' => $userMsg,
                    'bot_message' => $botMsg,
                    'is_escalated' => false,
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_token' => $session->session_token,
                'user_message' => $userMsg,
                'bot_message' => null,
                'is_escalated' => ($session->status === 'escalated'),
            ]
        ]);
    }

    /**
     * Eskalasi manual dari warga ke Petugas Admin
     */
    public function escalateChat(Request $request, $service)
    {
        $user = Auth::guard('sanctum')->user();
        $sessionToken = $request->header('X-Chat-Session-Token') ?: $request->get('session_token');
        $regionId = $request->get('region_id') ?: ($user ? $user->region_id : null);

        if (!$regionId) {
            $firstRegion = Region::first();
            $regionId = $firstRegion ? $firstRegion->id : 1;
        }

        $session = null;
        if ($user) {
            $session = UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$session && $sessionToken) {
            $session = UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('session_token', $sessionToken)
                ->first();
        }

        if ($session) {
            $session->update([
                'status' => 'escalated',
                'unread_admin_count' => $session->unread_admin_count + 1,
            ]);

            $botMsg = UnitChatMessage::create([
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => "Percakapan telah dialihkan ke Petugas Layanan BUMDes. Petugas akan segera membalas di room chat ini.",
                'is_read' => true,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Chat berhasil dialihkan ke admin.',
                'data' => [
                    'session' => $session,
                    'bot_message' => $botMsg,
                ]
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Sesi chat tidak ditemukan'], 404);
    }

    /**
     * Ambil jumlah pesan belum dibaca per unit layanan untuk badge / notifikasi
     */
    public function getUnreadCounts(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $sessionToken = $request->header('X-Chat-Session-Token') ?: $request->get('session_token');

        $counts = [
            'gas' => 0,
            'penyewaan' => 0,
            'mobil' => 0,
            'fasilitas_umum' => 0,
            'total' => 0,
        ];

        if ($user) {
            $sessions = UnitChatSession::where('user_id', $user->id)->get();
            foreach ($sessions as $s) {
                if (isset($counts[$s->service_type])) {
                    $counts[$s->service_type] = $s->unread_user_count;
                    $counts['total'] += $s->unread_user_count;
                }
            }
        } elseif ($sessionToken) {
            $sessions = UnitChatSession::where('session_token', $sessionToken)->get();
            foreach ($sessions as $s) {
                if (isset($counts[$s->service_type])) {
                    $counts[$s->service_type] = $s->unread_user_count;
                    $counts['total'] += $s->unread_user_count;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $counts
        ]);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    private function getWelcomeMessage($service, $userName)
    {
        switch ($service) {
            case 'gas':
                return "Halo {$userName}! Selamat datang di layanan pesan Penjualan Gas LPG BUMDes. Asisten otomatis kami siap membantu pertanyaan seputar stok tabung, ketentuan penukaran, dan status pengantaran. Klik tombol 'Chat Petugas' jika ingin berbicara langsung dengan admin desa.";
            case 'penyewaan':
                return "Halo {$userName}! Selamat datang di layanan Penyewaan Alat & Mesin BUMDes. Anda dapat menanyakan ketersediaan alat, ketentuan SOP tanggung jawab sewa, serta durasi peminjaman. Untuk berbicara dengan petugas, silakan klik 'Chat Petugas'.";
            case 'mobil':
                return "Halo {$userName}! Selamat datang di layanan Penyewaan Mobil / Kendaraan Operasional BUMDes. Tanyakan seputar jadwal armada, opsi dengan supir / lepas kunci, dan syarat rental. Untuk berbicara dengan admin, klik 'Chat Petugas'.";
            case 'fasilitas_umum':
                return "Halo {$userName}! Selamat datang di Pusat Informasi Fasilitas Umum & Gedung Serbaguna BUMDes. Silakan tanyakan ketersediaan jadwal pemakaian gedung, kapasitas, atau fasilitas publik lainnya.";
            default:
                return "Halo {$userName}! Ada yang bisa kami bantu seputar layanan desa ini?";
        }
    }

    private function generateBotAnswer($service, $query)
    {
        $q = strtolower($query);

        if ($service === 'gas') {
            if (Str::contains($q, ['stok', 'ada', 'ready', 'tersedia'])) {
                return "Stok tabung gas kami selalu diperbarui secara berkala pada katalog. Anda dapat langsung memilih jenis tabung di halaman pemesanan. Jika memerlukan tabung dalam jumlah mendesak, silakan klik 'Chat Petugas' agar admin memeriksa fisik tabung di pangkalan.";
            }
            if (Str::contains($q, ['antar', 'kirim', 'ongkir', 'sampai rumah'])) {
                return "Layanan pengantaran gas ke rumah tersedia untuk wilayah desa kami. Biaya antar disesuaikan dengan jarak tempuh dusun. Pastikan alamat Anda tertera jelas saat membuat pesanan.";
            }
            if (Str::contains($q, ['tukar', 'kosong', 'bawa'])) {
                return "Untuk pembelian isi ulang, warga diwajibkan membawa tabung kosong yang sesuai (misalnya Elpiji 3kg ditukar dengan tabung 3kg dalam kondisi layak).";
            }
            return "Terima kasih atas pertanyaannya seputar gas Elpiji. Jika Anda memerlukan kepastian cepat dari petugas pengelola, silakan klik tombol 'Chat Petugas'.";
        }

        if ($service === 'penyewaan') {
            if (Str::contains($q, ['sop', 'rusak', 'tanggung', 'ganti'])) {
                return "Ketentuan sewa mengacu pada SOP desa: Penyewa wajib menjaga keutuhan alat. Kerusakan akibat kelalaian pemakaian menjadi tanggung jawab penyewa sesuai ketentuan berlaku.";
            }
            if (Str::contains($q, ['tarif', 'harga', 'biaya', 'ongkos'])) {
                return "Tarif sewa alat dihitung per hari atau per durasi yang dipilih pada formulir. Rincian biaya transparan dan tertera pada setiap kartu barang.";
            }
            return "Informasi alat Anda telah kami terima. Untuk informasi jadwal pengambilan alat secara langsung, silakan klik tombol 'Chat Petugas'.";
        }

        if ($service === 'mobil') {
            if (Str::contains($q, ['supir', 'driver', 'lepas kunci'])) {
                return "Penyewaan mobil operasional desa menyediakan opsi Lepas Kunci (dengan verifikasi KTP/SIM) maupun Dengan Supir berpengalaman dari petugas BUMDes.";
            }
            if (Str::contains($q, ['syarat', 'dokumen', 'jaminan'])) {
                return "Persyaratan umum penyewaan kendaraan meliputi: KTP asli warga desa/Kecamatan, SIM yang berlaku, dan menyetujui formulir komitmen tanggung jawab kendaraan.";
            }
            return "Armada mobil operasional kami siap melayani perjalanan dinas maupun warga. Klik 'Chat Petugas' untuk memesan supir atau memastikan jadwal kosong armada.";
        }

        if ($service === 'fasilitas_umum') {
            if (Str::contains($q, ['jadwal', 'kosong', 'tanggal', 'booking'])) {
                return "Jadwal gedung serbaguna atau lapangan dapat dicek pada kalender pemesanan di halaman detail fasilitas. Silakan ajukan tanggal yang diinginkan.";
            }
            if (Str::contains($q, ['kapasitas', 'muat', 'orang'])) {
                return "Kapasitas Gedung Serbaguna Desa dapat menampung hingga 300-500 orang untuk acara pernikahan, rapat warga, atau kegiatan olahraga tertutup.";
            }
            return "Pusat pelayanan fasilitas desa siap memfasilitasi kebutuhan acara Anda. Klik 'Chat Petugas' untuk koordinasi izin dan teknis penggunaan fasilitas.";
        }

        return "Pesan Anda telah kami catat. Untuk respon langsung dari pengelola desa, silakan klik tombol 'Chat Petugas'.";
    }
}
