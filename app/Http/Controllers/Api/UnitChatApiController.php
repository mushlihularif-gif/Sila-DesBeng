<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\UnitChatMessage;
use App\Models\UnitChatSession;
use App\Models\User;
use App\Services\ChatSessionToken;
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
        $this->validateChat($request, $service);
        $user = $this->resolveAuthenticatedUser();
        $sessionToken = ChatSessionToken::read($request);
        $regionId = $this->resolveRegionId($request, $user);

        $session = $this->findExistingSession($service, $regionId, $user, $sessionToken);

        if (!$session) {
            $newToken = Str::random(64);
            $userName = $this->displayUserName($user);

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

            $greeting = $this->getWelcomeMessage($service, $userName);
            UnitChatMessage::create([
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => $greeting,
                'is_read' => true,
            ]);
        } else {
            $updates = ['unread_user_count' => 0];
            if ($user) {
                $freshName = $this->displayUserName($user);
                if ($freshName !== '' && $session->user_name !== $freshName) {
                    $updates['user_name'] = $freshName;
                }
            }
            $session->update($updates);
        }

        $clearBeforeId = $session->userClearedBeforeId();
        $messagesQuery = $session->messages()->with('sender:id,name,avatar');
        if ($clearBeforeId > 0) {
            $messagesQuery->where('id', '>', $clearBeforeId);
        }

        $messages = $messagesQuery->get()->map(function ($msg) {
            $msg->message = str_replace(
                ['BUMDes', 'BUMDES', 'bumdes', 'Petugas Desa', 'petugas desa'],
                ['Layanan Daerah', 'Layanan Daerah', 'layanan daerah', 'Petugas Layanan', 'petugas layanan'],
                $msg->message
            );
            return $msg;
        })->values();

        $sessionPayload = clone $session;
        if ($messages->isEmpty() && $clearBeforeId > 0) {
            $sessionPayload->last_message = null;
            $messages = collect([[
                'id' => 'cleared_' . $clearBeforeId,
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => 'Riwayat obrolan di tampilan Anda sudah dibersihkan. Silakan kirim pesan baru jika ada yang ingin ditanyakan.',
                'item_data' => null,
                'is_read' => true,
                'created_at' => now()->toISOString(),
            ]]);
        }

        $region = Region::find($regionId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $sessionPayload,
                'session_token' => $session->session_token,
                'region_id' => $regionId,
                'region_name' => $region ? $region->name : null,
                'messages' => $messages,
                'is_escalated' => ($session->status === 'escalated'),
            ],
        ]);
    }

    /**
     * Kirim pesan dari warga ke chat layanan
     */
    public function sendChatMessage(Request $request, $service)
    {
        $this->validateChat($request, $service);
        $request->validate([
            'message' => 'required|string|max:1000',
            'item_data' => 'nullable|max:4000',
            'item_reference' => 'nullable|string|max:4000',
        ]);

        $user = $this->resolveAuthenticatedUser();
        $sessionToken = ChatSessionToken::read($request);
        $regionId = $this->resolveRegionId($request, $user);
        $itemData = $this->normalizeItemData($request->input('item_data'));
        $itemRef = $this->sanitizeItemReference($request->input('item_reference'), $itemData);

        $session = $this->findExistingSession($service, $regionId, $user, $sessionToken);

        if (!$session) {
            $newToken = Str::random(64);
            $userName = $this->displayUserName($user);
            $session = UnitChatSession::create([
                'service_type' => $service,
                'region_id' => $regionId,
                'user_id' => $user ? $user->id : null,
                'session_token' => $newToken,
                'user_name' => $userName,
                'status' => 'bot',
                'item_reference' => $itemRef,
                'unread_admin_count' => 0,
                'unread_user_count' => 0,
            ]);
        } else {
            $metaUpdates = [];
            if ($itemRef) {
                $metaUpdates['item_reference'] = $itemRef;
            }
            if ($user) {
                $freshName = $this->displayUserName($user);
                if ($freshName !== '' && $session->user_name !== $freshName) {
                    $metaUpdates['user_name'] = $freshName;
                }
            }
            if (!empty($metaUpdates)) {
                $session->update($metaUpdates);
            }
        }

        // Simpan pesan user
        $userMsg = UnitChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'user',
            'sender_id' => $user ? $user->id : null,
            'message' => $request->message,
            'item_data' => $itemData,
            'is_read' => false,
        ]);

        // Jika sesi sebelumnya sudah selesai/ditutup dan warga mengirim pesan baru, buka kembali untuk petugas
        $sessionUpdates = [
            'last_message' => $request->message,
            'last_message_at' => now(),
            'unread_admin_count' => $session->unread_admin_count + 1,
        ];
        if (in_array($session->status, ['resolved', 'closed'], true)) {
            $sessionUpdates['status'] = 'escalated';
        }
        $session->update($sessionUpdates);

        $lowerMsg = strtolower($request->message);
        $isEscalateRequest = Str::contains($lowerMsg, ['chat admin', 'petugas', 'pengelola', 'hubungi admin', 'bicara dengan admin', 'manusia']);

        // Jika user minta eskalasi
        if ($isEscalateRequest && $session->status !== 'escalated') {
            $session->update(['status' => 'escalated']);

            $botReply = 'Pesan Anda sudah diteruskan ke Petugas Layanan. Petugas akan segera membalas chat ini.';
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
                    'region_id' => $regionId,
                    'user_message' => $userMsg,
                    'bot_message' => $botMsg,
                    'is_escalated' => true,
                ],
            ]);
        }

        // Jika masih dalam status bot, sediakan respons asisten otomatis (pesan warga tetap masuk ke panel Admin Desa & Staf)
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
                    'region_id' => $regionId,
                    'user_message' => $userMsg,
                    'bot_message' => $botMsg,
                    'is_escalated' => false,
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_token' => $session->session_token,
                'region_id' => $regionId,
                'user_message' => $userMsg,
                'bot_message' => null,
                'is_escalated' => ($session->status === 'escalated'),
            ],
        ]);
    }

    /**
     * Eskalasi manual dari warga ke Petugas Admin
     */
    public function escalateChat(Request $request, $service)
    {
        $this->validateChat($request, $service);
        $user = $this->resolveAuthenticatedUser();
        $sessionToken = ChatSessionToken::read($request);
        $regionId = $this->resolveRegionId($request, $user);

        $session = $this->findExistingSession($service, $regionId, $user, $sessionToken);

        if (!$session) {
            $newToken = Str::random(64);
            $userName = $this->displayUserName($user);
            $session = UnitChatSession::create([
                'service_type' => $service,
                'region_id' => $regionId,
                'user_id' => $user ? $user->id : null,
                'session_token' => $newToken,
                'user_name' => $userName,
                'status' => 'escalated',
                'last_message' => 'Warga meminta terhubung dengan Petugas Layanan.',
                'last_message_at' => now(),
                'unread_admin_count' => 1,
                'unread_user_count' => 0,
            ]);
        } else {
            $wasEscalated = $session->status === 'escalated';
            $session->update([
                'status' => 'escalated',
                'last_message' => $session->last_message ?: 'Warga meminta terhubung dengan Petugas Layanan.',
                'last_message_at' => $session->last_message_at ?: now(),
                'unread_admin_count' => $wasEscalated ? max(1, (int) $session->unread_admin_count) : ((int) $session->unread_admin_count + 1),
            ]);
        }

        $botMsg = UnitChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'sender_id' => null,
            'message' => 'Terhubung dengan Petugas Layanan. Silakan tulis pertanyaan Anda, petugas akan segera membalas di sini.',
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Chat berhasil dialihkan ke petugas layanan.',
            'data' => [
                'session' => $session,
                'session_token' => $session->session_token,
                'region_id' => $regionId,
                'bot_message' => $botMsg,
                'is_escalated' => true,
            ],
        ]);
    }

    /**
     * Bersihkan isi chat hanya di sisi warga (seperti WhatsApp: tidak menghapus riwayat Admin Desa / Staf).
     */
    public function clearChat(Request $request, $service)
    {
        $this->validateChat($request, $service);
        $user = $this->resolveAuthenticatedUser();
        $sessionToken = ChatSessionToken::read($request);
        $regionId = $this->resolveRegionId($request, $user);

        $session = $this->findExistingSession($service, $regionId, $user, $sessionToken);
        $clearedBeforeId = 0;

        if ($session) {
            $clearedBeforeId = $session->clearForUser();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Obrolan di tampilan Anda berhasil dibersihkan.',
            'data' => [
                'cleared_before_id' => $clearedBeforeId,
                'session_token' => $session?->session_token,
            ],
        ]);
    }

    /**
     * Ambil jumlah pesan belum dibaca per unit layanan untuk badge / notifikasi
     */
    public function getUnreadCounts(Request $request)
    {
        $request->validate(['region_id' => 'nullable|integer|exists:regions,id']);
        $user = $this->resolveAuthenticatedUser();
        $sessionToken = ChatSessionToken::read($request);
        $filterRegionId = $request->filled('region_id')
            ? Region::resolveServiceRegionId((int) $request->input('region_id'))
            : null;

        $counts = [
            'gas' => 0,
            'penyewaan' => 0,
            'mobil' => 0,
            'fasilitas_umum' => 0,
            'total' => 0,
        ];

        $query = null;
        if ($user) {
            $query = UnitChatSession::where('user_id', $user->id);
        } elseif ($sessionToken) {
            $query = UnitChatSession::whereNull('user_id')->where('session_token', $sessionToken);
        }

        if ($query) {
            if ($filterRegionId) {
                $query->where('region_id', $filterRegionId);
            }
            foreach ($query->get() as $s) {
                if (isset($counts[$s->service_type])) {
                    $unread = (int) $s->unread_user_count;
                    $counts[$s->service_type] += $unread;
                    $counts['total'] += $unread;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $counts,
        ]);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    private function resolveAuthenticatedUser(): ?User
    {
        return Auth::guard('sanctum')->user() ?: Auth::guard('web')->user();
    }

    private function displayUserName(?User $user): string
    {
        $name = trim((string) ($user?->name ?? ''));

        return $name !== '' ? $name : 'Warga';
    }

    private function resolveRegionId(Request $request, ?User $user): int
    {
        $requestedRaw = $request->input('region_id');
        if ($requestedRaw !== null && $requestedRaw !== '' && is_numeric($requestedRaw)) {
            $resolved = Region::resolveServiceRegionId((int) $requestedRaw);
            if ($resolved) {
                $region = Region::find($resolved);
                // Jika yang dikirim adalah wilayah induk (kabupaten/kecamatan) dan warga punya desa sendiri di bawahnya,
                // arahkan ke desa warga supaya masuk ke inbox Admin Desa / Staf.
                if ($region && in_array(strtolower((string) $region->type), ['kabupaten', 'kecamatan'], true) && $user?->region_id) {
                    $userVillageId = Region::resolveServiceRegionId((int) $user->region_id);
                    if ($userVillageId) {
                        $userVillage = Region::find($userVillageId);
                        if ($userVillage && in_array(strtolower((string) $userVillage->type), ['desa', 'kelurahan'], true)) {
                            return $userVillageId;
                        }
                    }
                }

                return $resolved;
            }
        }

        if ($user && $user->region_id) {
            $userRegionId = Region::resolveServiceRegionId((int) $user->region_id);
            if ($userRegionId) {
                return $userRegionId;
            }
        }

        return Region::defaultServiceVillageId();
    }

    private function findExistingSession(string $service, int $regionId, ?User $user, ?string $sessionToken): ?UnitChatSession
    {
        if ($user) {
            return UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->where('user_id', $user->id)
                ->orderByDesc('id')
                ->first();
        }

        if ($sessionToken) {
            return UnitChatSession::where('service_type', $service)
                ->where('region_id', $regionId)
                ->whereNull('user_id')
                ->where('session_token', $sessionToken)
                ->orderByDesc('id')
                ->first();
        }

        return null;
    }

    private function normalizeItemData(mixed $raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function sanitizeItemReference(?string $raw, ?array $itemData = null): ?string
    {
        $clean = trim((string) $raw);
        if ($clean === '' && is_array($itemData) && !empty($itemData['name'])) {
            $clean = trim((string) $itemData['name']);
        }
        if ($clean === '') {
            return null;
        }
        if (str_starts_with($clean, '{') || str_starts_with($clean, '[')) {
            $decoded = json_decode($clean, true);
            if (is_array($decoded) && is_string($decoded['name'] ?? null) && trim($decoded['name']) !== '') {
                $clean = trim($decoded['name']);
            }
        }

        return Str::limit($clean, 240, '');
    }

    private function getWelcomeMessage($service, $userName)
    {
        switch ($service) {
            case 'gas':
                return "Halo {$userName}! Selamat datang di Layanan Gas. Silakan tanyakan ketersediaan stok, pengantaran, atau informasi tukar tabung. Pesan Anda langsung masuk ke Petugas Layanan, atau klik 'Chat Petugas' untuk terhubung tanpa asisten otomatis.";
            case 'penyewaan':
                return "Halo {$userName}! Selamat datang di Layanan Sewa Alat. Silakan tanyakan ketersediaan alat, ketentuan sewa, atau durasi peminjaman. Pesan Anda langsung masuk ke Petugas Layanan, atau klik 'Chat Petugas' untuk terhubung langsung.";
            case 'mobil':
                return "Halo {$userName}! Selamat datang di Layanan Penyewaan Transportasi. Anda bisa bertanya seputar ketersediaan armada, jadwal, dan syarat penyewaan. Pesan Anda langsung masuk ke Petugas Layanan.";
            case 'fasilitas_umum':
                return "Halo {$userName}! Selamat datang di Layanan Fasilitas Umum. Silakan tanyakan jadwal kosong, kapasitas ruangan, atau detail fasilitas lainnya. Pesan Anda langsung masuk ke Petugas Layanan.";
            default:
                return "Halo {$userName}! Selamat datang. Ada yang bisa kami bantu seputar layanan ini? Pesan Anda akan langsung masuk ke Petugas Layanan.";
        }
    }

    private function generateBotAnswer($service, $query)
    {
        $q = strtolower($query);

        if ($service === 'gas') {
            if (Str::contains($q, ['stok', 'ada', 'ready', 'tersedia'])) {
                return "Ketersediaan tabung gas selalu kami perbarui di halaman produk. Anda bisa langsung memesan jika stoknya masih ada. Pesan Anda juga sudah kami teruskan ke Petugas Layanan jika butuh konfirmasi langsung.";
            }
            if (Str::contains($q, ['antar', 'kirim', 'ongkir', 'sampai rumah'])) {
                return "Kami menyediakan layanan antar jemput gas ke rumah Anda. Biaya pengiriman akan disesuaikan dengan jarak lokasi Anda. Pastikan alamat pengiriman sudah benar saat memesan ya.";
            }
            if (Str::contains($q, ['tukar', 'kosong', 'bawa'])) {
                return "Untuk pembelian isi ulang, mohon pastikan Anda sudah menyiapkan tabung kosong dengan ukuran yang sama dan dalam kondisi baik saat petugas kami datang atau saat Anda mengambil pesanan.";
            }
            return "Pesan Anda sudah masuk ke Petugas Layanan. Petugas akan segera membalas di sini, atau klik 'Chat Petugas' untuk mode obrolan langsung.";
        }

        if ($service === 'penyewaan') {
            if (Str::contains($q, ['sop', 'rusak', 'tanggung', 'ganti', 'syarat'])) {
                return "Sebagai informasi, penyewa diharapkan menjaga kondisi alat tetap baik selama masa sewa. Jika terjadi kerusakan akibat kelalaian, biaya perbaikan akan menjadi tanggung jawab penyewa.";
            }
            if (Str::contains($q, ['tarif', 'harga', 'biaya', 'ongkos'])) {
                return "Biaya penyewaan dihitung berdasarkan lama hari atau kegiatan penyewaan. Harga detailnya sudah tertera langsung di halaman detail alat tersebut ya.";
            }
            return "Pertanyaan Anda sudah masuk ke Petugas Layanan. Petugas akan segera membalas konfirmasi ketersediaan alat atau info lainnya di sini.";
        }

        if ($service === 'mobil') {
            if (Str::contains($q, ['supir', 'driver', 'petugas'])) {
                return "Layanan penyewaan transportasi kami tersedia dengan supir dari petugas pengelola untuk memastikan keamanan dan kenyamanan perjalanan Anda.";
            }
            if (Str::contains($q, ['syarat', 'dokumen', 'jaminan'])) {
                return "Persyaratan utama untuk penyewaan transportasi adalah identitas warga yang masih berlaku dan persetujuan ketentuan penggunaan kendaraan.";
            }
            return "Pesan Anda sudah masuk ke Petugas Layanan. Petugas akan segera membalas untuk memastikan ketersediaan jadwal transportasi Anda.";
        }

        if ($service === 'fasilitas_umum') {
            if (Str::contains($q, ['jadwal', 'kosong', 'tanggal', 'booking'])) {
                return "Anda dapat melihat ketersediaan tanggal langsung dari halaman fasilitas ini. Pesan Anda juga sudah masuk ke Petugas Layanan untuk konfirmasi jadwal.";
            }
            if (Str::contains($q, ['kapasitas', 'muat', 'orang'])) {
                return "Setiap fasilitas memiliki kapasitas yang berbeda-beda. Informasi detail ada di deskripsi fasilitas, dan Petugas Layanan juga sudah menerima pesan Anda.";
            }
            return "Pesan Anda tentang fasilitas umum sudah masuk ke Petugas Layanan. Petugas kami akan segera membalas di sini.";
        }

        return "Pesan Anda sudah masuk ke Petugas Layanan. Mohon tunggu balasan dari petugas kami di sini.";
    }

    private function validateChat(Request $request, string $service): void
    {
        abort_unless(in_array($service, ['gas', 'penyewaan', 'mobil', 'fasilitas_umum'], true), 404);
        $request->validate(['region_id' => 'nullable|integer|exists:regions,id']);
    }
}
