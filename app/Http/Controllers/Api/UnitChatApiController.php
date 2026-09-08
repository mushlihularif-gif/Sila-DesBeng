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
        $user = Auth::guard('sanctum')->user() ?: Auth::guard('web')->user();
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

        $user = Auth::guard('sanctum')->user() ?: Auth::guard('web')->user();
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
            'item_data' => $request->get('item_data'),
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

            $botReply = "Petugas akan segera membalas chat ini.";
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
        $user = Auth::guard('sanctum')->user() ?: Auth::guard('web')->user();
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
                'message' => "Petugas akan segera membalas chat ini.",
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
        $user = Auth::guard('sanctum')->user() ?: Auth::guard('web')->user();
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
                return "Halo {$userName}! Selamat datang di layanan Pembelian Gas BUMDes. Ada yang bisa kami bantu? Anda bisa menanyakan ketersediaan stok, pengantaran, atau informasi tukar tabung. Untuk bantuan lebih lanjut, silakan klik 'Chat Petugas'.";
            case 'penyewaan':
                return "Halo {$userName}! Selamat datang di layanan Penyewaan Alat BUMDes. Silakan tanyakan ketersediaan alat, ketentuan sewa, atau durasi peminjaman. Jika butuh bantuan langsung dari admin, silakan klik 'Chat Petugas'.";
            case 'mobil':
                return "Halo {$userName}! Selamat datang di layanan Penyewaan Mobil BUMDes. Anda bisa bertanya seputar ketersediaan mobil, jadwal, dan syarat penyewaan. Klik 'Chat Petugas' untuk langsung terhubung dengan admin.";
            case 'fasilitas_umum':
                return "Halo {$userName}! Selamat datang di layanan Fasilitas Umum BUMDes. Silakan tanyakan jadwal kosong, kapasitas ruangan, atau detail fasilitas lainnya. Untuk pemesanan langsung, Anda bisa klik 'Chat Petugas'.";
            default:
                return "Halo {$userName}! Selamat datang. Ada yang bisa kami bantu seputar layanan ini? Silakan klik 'Chat Petugas' jika butuh bantuan admin.";
        }
    }

    private function generateBotAnswer($service, $query)
    {
        $q = strtolower($query);

        if ($service === 'gas') {
            if (Str::contains($q, ['stok', 'ada', 'ready', 'tersedia'])) {
                return "Ketersediaan tabung gas selalu kami perbarui di halaman produk. Anda bisa langsung memesan jika stoknya masih ada. Jika butuh dalam jumlah banyak, silakan klik 'Chat Petugas' ya.";
            }
            if (Str::contains($q, ['antar', 'kirim', 'ongkir', 'sampai rumah'])) {
                return "Kami menyediakan layanan antar jemput gas ke rumah Anda. Biaya pengiriman akan disesuaikan dengan jarak lokasi Anda. Pastikan alamat pengiriman sudah benar saat memesan ya.";
            }
            if (Str::contains($q, ['tukar', 'kosong', 'bawa'])) {
                return "Untuk pembelian isi ulang, mohon pastikan Anda sudah menyiapkan tabung kosong dengan ukuran yang sama dan dalam kondisi baik saat petugas kami datang atau saat Anda mengambil pesanan.";
            }
            return "Pesan Anda sudah kami catat. Agar bisa dijawab lebih lengkap oleh admin, silakan tekan tombol 'Chat Petugas' ya.";
        }

        if ($service === 'penyewaan') {
            if (Str::contains($q, ['sop', 'rusak', 'tanggung', 'ganti', 'syarat'])) {
                return "Sebagai informasi, penyewa diharapkan menjaga kondisi alat tetap baik selama masa sewa. Jika terjadi kerusakan akibat kelalaian, biaya perbaikan akan menjadi tanggung jawab penyewa.";
            }
            if (Str::contains($q, ['tarif', 'harga', 'biaya', 'ongkos'])) {
                return "Biaya penyewaan dihitung berdasarkan lama hari penyewaan. Harga detailnya sudah tertera langsung di halaman detail alat tersebut ya.";
            }
            return "Pertanyaan Anda sudah masuk ke sistem kami. Untuk konfirmasi ketersediaan alat atau info lainnya, silakan tekan tombol 'Chat Petugas'.";
        }

        if ($service === 'mobil') {
            if (Str::contains($q, ['supir', 'driver', 'petugas'])) {
                return "Layanan penyewaan mobil operasional kami sudah termasuk supir (driver) dari petugas BUMDes untuk memastikan keamanan dan kenyamanan perjalanan Anda.";
            }
            if (Str::contains($q, ['syarat', 'dokumen', 'jaminan'])) {
                return "Persyaratan utama untuk penyewaan mobil adalah KTP warga setempat yang masih berlaku dan persetujuan surat tanggung jawab penggunaan kendaraan.";
            }
            return "Pesan Anda telah kami terima. Untuk memastikan ketersediaan jadwal mobil atau melakukan pemesanan, silakan tekan tombol 'Chat Petugas' ya.";
        }

        if ($service === 'fasilitas_umum') {
            if (Str::contains($q, ['jadwal', 'kosong', 'tanggal', 'booking'])) {
                return "Anda dapat melihat ketersediaan tanggal langsung dari halaman fasilitas ini. Jika ingin memastikan jadwal yang spesifik, silakan pilih tanggalnya atau hubungi admin kami.";
            }
            if (Str::contains($q, ['kapasitas', 'muat', 'orang'])) {
                return "Setiap fasilitas memiliki kapasitas yang berbeda-beda, mulai dari rapat kecil hingga acara pernikahan yang bisa menampung ratusan orang. Informasi detail ada di deskripsi fasilitas ya.";
            }
            return "Pesan Anda tentang fasilitas umum sudah kami terima. Silakan tekan tombol 'Chat Petugas' agar admin kami bisa langsung membantu kebutuhan acara Anda.";
        }

        return "Pesan Anda telah kami terima. Untuk respon cepat langsung dari petugas desa, silakan klik tombol 'Chat Petugas'.";
    }
}
