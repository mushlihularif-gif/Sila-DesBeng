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
     * Detail Pesan Chat untuk Admin
     */
    public function getMessages($service, $sessionId)
    {
        $admin = Auth::user();
        if (!$admin || !$admin->region_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $session = UnitChatSession::where('region_id', $admin->region_id)
            ->where('service_type', $service)
            ->findOrFail($sessionId);

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
        if (!$admin || !$admin->region_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $session = UnitChatSession::where('region_id', $admin->region_id)
            ->where('service_type', $service)
            ->findOrFail($sessionId);

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
        $admin = Auth::user();
        if (!$admin || !$admin->region_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $session = UnitChatSession::where('region_id', $admin->region_id)
            ->where('service_type', $service)
            ->findOrFail($sessionId);

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
