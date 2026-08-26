<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    /**
     * Get list of all news/announcements
     */
    public function index(Request $request)
    {
        $query = Announcement::with(['region', 'admin', 'images'])
            ->where('is_active', true);

        // Filter by type if provided
        if ($request->filled('type') && $request->type !== 'Semua') {
            $type = $request->type;
            if ($type === 'Acara / Event' || $type === 'Event' || $type === 'Acara') {
                $query->whereIn('type', ['Event', 'Acara / Event', 'Acara', 'kegiatan_sosial']);
            } elseif ($type === 'Gotong Royong') {
                $query->whereIn('type', ['Gotong Royong', 'gotong_royong']);
            } elseif ($type === 'Pengumuman') {
                $query->whereIn('type', ['Pengumuman', 'rapat', 'umum']);
            } else {
                $query->where('type', $type);
            }
        }

        // Filter by post_category ('Berita' or 'Pengumuman')
        if ($request->filled('post_category')) {
            $query->where('post_category', $request->post_category);
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $news = $query->orderBy('created_at', 'desc')->get();

        // Format to match mobile expectations
        $formatted = $news->map(function ($item) {
            $image = null;
            if ($item->image_path) {
                $image = asset('storage/' . $item->image_path);
            } elseif ($item->images && $item->images->count() > 0) {
                $image = asset('storage/' . $item->images->first()->image_path);
            }

            $displayCategory = $item->type ?? 'Pengumuman';
            if ($item->type === 'kegiatan_sosial') $displayCategory = 'Acara / Event';
            if ($item->type === 'gotong_royong') $displayCategory = 'Gotong Royong';
            if ($item->type === 'rapat') $displayCategory = 'Pengumuman';

            return [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $displayCategory,
                'date' => $item->event_date ? $item->event_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
                'desc' => $item->description,
                'image' => $image,
                'location' => $item->location,
                'author' => $item->admin ? $item->admin->name : 'Admin Desa',
            ];
        })->toArray();

        // Also include CommunityEvent if querying for Pengumuman or all
        if (!$request->filled('post_category') || $request->post_category === 'Pengumuman') {
            $eventQuery = \App\Models\CommunityEvent::query();

            if ($request->filled('type') && $request->type !== 'Semua') {
                $type = $request->type;
                if ($type === 'Acara / Event' || $type === 'Event' || $type === 'Acara') {
                    $eventQuery->whereIn('tipe', ['kegiatan_sosial', 'acara', 'Event']);
                } elseif ($type === 'Gotong Royong') {
                    $eventQuery->whereIn('tipe', ['gotong_royong', 'Gotong Royong']);
                } elseif ($type === 'Pengumuman') {
                    $eventQuery->whereIn('tipe', ['rapat', 'pengumuman', 'Pengumuman']);
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $eventQuery->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('catatan', 'like', "%{$search}%")
                      ->orWhere('lokasi', 'like', "%{$search}%");
                });
            }

            $events = $eventQuery->orderBy('created_at', 'desc')->get();

            $eventItems = $events->map(function ($ev) {
                $displayCategory = 'Gotong Royong';
                if ($ev->tipe === 'kegiatan_sosial' || $ev->tipe === 'acara') $displayCategory = 'Acara / Event';
                if ($ev->tipe === 'rapat' || $ev->tipe === 'pengumuman') $displayCategory = 'Pengumuman';

                $desc = $ev->catatan ?? '';
                if ($ev->jadwal) {
                    $desc = ($desc ? $desc . "\n" : '') . "Jadwal: " . $ev->jadwal;
                }

                $image = null;
                if ($ev->poster_path) {
                    $image = asset('storage/' . $ev->poster_path);
                }

                return [
                    'id' => 900000 + $ev->id,
                    'title' => $ev->judul,
                    'category' => $displayCategory,
                    'date' => $ev->created_at ? $ev->created_at->format('Y-m-d') : date('Y-m-d'),
                    'desc' => $desc,
                    'image' => $image,
                    'location' => $ev->lokasi . ($ev->rw ? ' (' . $ev->rw . ($ev->rt ? ' - ' . $ev->rt : '') . ')' : ''),
                    'author' => $ev->koordinator ?? 'Koordinator Wilayah',
                ];
            })->toArray();

            $formatted = array_merge($formatted, $eventItems);
        }

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }

    /**
     * Get specific news detail
     */
    public function show($id)
    {
        $item = Announcement::with(['region', 'admin', 'images'])->findOrFail($id);
        
        $image = null;
        if ($item->image_path) {
            $image = asset('storage/' . $item->image_path);
        } elseif ($item->images && $item->images->count() > 0) {
            $image = asset('storage/' . $item->images->first()->image_path);
        }

        $formatted = [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->type ?? 'Pengumuman',
            'date' => $item->event_date ? $item->event_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
            'desc' => $item->description,
            'image' => $image,
            'location' => $item->location,
            'author' => $item->admin ? $item->admin->name : 'Admin Desa',
        ];

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }
}
