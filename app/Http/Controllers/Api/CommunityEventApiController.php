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

        $events = CommunityEvent::where('user_id',$userId)->orderBy('created_at', 'desc')->get()->map(function ($event) use ($userId) {
            $event->is_joined = EventParticipant::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->exists();
            $event->is_creator = ($event->user_id == $userId);
            return $event;
        });

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function store(Request $request)
    {
        return response()->json(['message'=>'Gunakan alur kiriman Kabar/Pengumuman untuk peninjauan wilayah.', 'workspace_url'=>route('content.index'), 'api_endpoint'=>url('/api/content')],409);
    }

    public function toggleJoin(Request $request, $id)
    {
        $event = CommunityEvent::where('user_id',$request->user()->id)->findOrFail($id);
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
