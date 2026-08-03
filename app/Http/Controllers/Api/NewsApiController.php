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
        $query = Announcement::with(['region', 'admin'])
            ->where('is_active', true);

        // Filter by type if provided
        if ($request->filled('type') && $request->type !== 'Semua') {
            $query->where('type', $request->type);
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
            return [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->type ?? 'Pengumuman', // Fallback
                'date' => $item->event_date ? $item->event_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
                'desc' => $item->description,
                'image' => $item->image_path ? asset('storage/' . $item->image_path) : 'https://picsum.photos/seed/' . $item->id . '/400/300',
                'location' => $item->location,
                'author' => $item->admin ? $item->admin->name : 'Admin Desa',
            ];
        });

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
        $item = Announcement::with(['region', 'admin'])->findOrFail($id);
        
        $formatted = [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->type ?? 'Pengumuman',
            'date' => $item->event_date ? $item->event_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
            'desc' => $item->description,
            'image' => $item->image_path ? asset('storage/' . $item->image_path) : 'https://picsum.photos/seed/' . $item->id . '/400/300',
            'location' => $item->location,
            'author' => $item->admin ? $item->admin->name : 'Admin Desa',
        ];

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }
}
