<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $categories = Category::when($user->region_id, function($query) use ($user) {
                return $query->where('region_id', $user->region_id);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
            
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
        ]);

        $category = Category::create([
            'region_id' => auth()->user()->region_id,
            'name' => $request->name,
            'type' => $request->type,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Kategori berhasil ditambahkan.'
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        // Authorization check
        if (auth()->user()->region_id && $category->region_id != auth()->user()->region_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
        ]);

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        // Authorization check
        if (auth()->user()->region_id && $category->region_id != auth()->user()->region_id) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
