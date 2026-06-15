<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Laporan $laporan)
    {
        // Validasi hanya pemilik laporan yang bisa rating
        if ($laporan->user_id !== auth()->id()) {
            return back()->with('error', '❌ Anda tidak berhak memberikan rating untuk laporan ini.');
        }

        // Validasi status harus selesai
        if ($laporan->status !== 'Selesai') {
            return back()->with('error', '❌ Rating hanya bisa diberikan untuk laporan yang sudah selesai.');
        }

        // Validasi sudah rating atau belum
        if ($laporan->rating) {
            return back()->with('error', '❌ Anda sudah memberikan rating untuk laporan ini.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        Rating::create([
            'laporan_id' => $laporan->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'feedback' => $validated['feedback'],
            'is_published' => true
        ]);

        return back()->with('success', '⭐ Terima kasih atas rating Anda!');
    }

    public function update(Request $request, Laporan $laporan)
    {
        $rating = $laporan->rating;

        if (!$rating || $rating->user_id !== auth()->id()) {
            return back()->with('error', '❌ Anda tidak berhak mengubah rating ini.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $rating->update($validated);

        return back()->with('success', '✅ Rating berhasil diperbarui!');
    }

    public function togglePublish(Rating $rating)
    {
        $rating->update([
            'is_published' => !$rating->is_published
        ]);

        return back()->with('success', '✅ Status publikasi rating berhasil diubah!');
    }
}