<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityEvent;
use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityEventApiController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $events = CommunityEvent::orderBy('created_at', 'desc')->get()->map(function ($event) use ($userId) {
            $event->is_joined = EventParticipant::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->exists();
            return $event;
        });

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'tipe' => 'required|in:gotong_royong,rapat,kegiatan_sosial',
            'target_scope' => 'nullable|string',
            'rw' => 'nullable|string',
            'rt' => 'nullable|string',
            'koordinator' => 'nullable|string',
            'jadwal' => 'nullable|string',
            'lokasi' => 'nullable|string',
            'catatan' => 'nullable|string',
            'peralatan' => 'nullable|array',
            'poster' => 'nullable|image|max:5120',
        ]);

        $posterPath = null;
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('events/posters', 'public');
        }

        $event = CommunityEvent::create([
            'user_id' => $request->user()->id,
            'judul' => $request->judul,
            'tipe' => $request->tipe,
            'target_scope' => $request->target_scope,
            'rw' => $request->rw,
            'rt' => $request->rt,
            'koordinator' => $request->koordinator,
            'jadwal' => $request->jadwal,
            'lokasi' => $request->lokasi,
            'catatan' => $request->catatan,
            'peralatan' => $request->peralatan,
            'poster_path' => $posterPath,
            'status' => 'upcoming',
            'jumlah_peserta' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dibuat dan dibroadcast.',
            'data' => $event,
        ], 201);
    }

    public function toggleJoin(Request $request, $id)
    {
        $event = CommunityEvent::findOrFail($id);
        $userId = $request->user()->id;

        $existing = EventParticipant::where('event_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $event->decrement('jumlah_peserta');
            return response()->json([
                'success' => true,
                'joined' => false,
                'message' => 'Anda membatalkan kehadiran.',
                'jumlah_peserta' => $event->fresh()->jumlah_peserta,
            ]);
        } else {
            EventParticipant::create([
                'event_id' => $id,
                'user_id' => $userId,
            ]);
            $event->increment('jumlah_peserta');
            return response()->json([
                'success' => true,
                'joined' => true,
                'message' => 'Anda terdaftar hadir!',
                'jumlah_peserta' => $event->fresh()->jumlah_peserta,
            ]);
        }
    }
}
