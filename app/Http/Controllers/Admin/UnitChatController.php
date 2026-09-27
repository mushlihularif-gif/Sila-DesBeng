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

        $messages = $session->messages()->with('sender')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $session,
                'messages' => $messages,
            ]
        ]);
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
                'chat_message' => $msg,
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
