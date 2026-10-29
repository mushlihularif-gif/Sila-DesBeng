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
            ->visibleTo(auth('sanctum')->user());

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

        $news = $query->orderBy('published_at', 'desc')->limit(100)->get();

        // Format to match mobile expectations
        $formatted = $news->map(function ($item) {
            $images = [];
            if ($item->image_path) {
                $images[] = $item->mediaUrl();
            }
            if ($item->images && $item->images->count() > 0) {
                foreach ($item->images as $img) {
                    $imgUrl = $img->url;
                    if (!in_array($imgUrl, $images)) {
                        $images[] = $imgUrl;
                    }
                }
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
                'image' => count($images) > 0 ? $images[0] : null,
                'images' => $images,
                'location' => $item->location,
                'author' => $item->admin ? $item->admin->name : 'Admin Desa',
                'publisher' => $item->publisher?->name,
                'workflow_state' => $item->workflow_state,
                'published_at' => $item->published_at?->toIso8601String(),
                'ends_at' => $item->ends_at?->toIso8601String(),
                'cancellation_reason' => $item->workflow_state === 'cancelled' ? $item->rejection_reason : null,
                'views_count' => (int)$item->views_count,
                'photo_credits' => $item->images->pluck('contributor_name')->filter()->unique()->values(),
                'text_credits' => \Illuminate\Support\Facades\DB::table('announcement_sources')->where('announcement_id',$item->id)->where('is_staged',false)->where('uses_text',true)->pluck('contributor_name')->unique()->values(),
            ];
        })->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ])->header('Cache-Control','private, no-store');
    }

    /**
     * Get specific news detail
     */
    public function show($id)
    {
        $item = Announcement::with(['region', 'admin', 'images'])->visibleTo(auth('sanctum')->user())->findOrFail($id);
        
        $images = [];
        if ($item->image_path) {
            $images[] = $item->mediaUrl();
        }
        if ($item->images && $item->images->count() > 0) {
            foreach ($item->images as $img) {
                // Avoid duplicating the main image if it's somehow in both
                $imgUrl = $img->url;
                if (!in_array($imgUrl, $images)) {
                    $images[] = $imgUrl;
                }
            }
        }

        app(\App\Services\ContentViewService::class)->record($item, request());
        $formatted = [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->type ?? 'Pengumuman',
            'date' => $item->event_date ? $item->event_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
            'desc' => $item->description,
            'image' => count($images) > 0 ? $images[0] : null,
            'images' => $images,
            'location' => $item->location,
            'author' => $item->admin ? $item->admin->name : 'Admin Desa',
                'publisher' => $item->publisher?->name,
                'workflow_state' => $item->workflow_state,
                'published_at' => $item->published_at?->toIso8601String(),
                'ends_at' => $item->ends_at?->toIso8601String(),
                'cancellation_reason' => $item->workflow_state === 'cancelled' ? $item->rejection_reason : null,
                'views_count' => (int)$item->views_count,
                'photo_credits' => $item->images->pluck('contributor_name')->filter()->unique()->values(),
                'text_credits' => \Illuminate\Support\Facades\DB::table('announcement_sources')->where('announcement_id',$item->id)->where('is_staged',false)->where('uses_text',true)->pluck('contributor_name')->unique()->values(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ])->header('Cache-Control','private, no-store');
    }
}
