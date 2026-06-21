<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        // Auto-seed if empty
        if (Banner::count() == 0) {
            $files = ['entrance.png', 'biru.png', 'ppq.png'];
            foreach ($files as $index => $file) {
                $source = public_path('User/img/elemen/' . $file);
                if(file_exists($source)) {
                    $dest = storage_path('app/public/banners/' . $file);
                    if(!is_dir(dirname($dest))) {
                        mkdir(dirname($dest), 0755, true);
                    }
                    if(!file_exists($dest)) {
                        copy($source, $dest);
                    }
                    Banner::firstOrCreate(
                        ['image_path' => 'banners/' . $file],
                        ['title' => 'Banner ' . ($index + 1), 'description' => 'Banner default sistem', 'is_active' => true, 'sort_order' => $index + 1]
                    );
                }
            }
        }

        $banners = Banner::orderBy('sort_order', 'asc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'target_url' => 'nullable|url',
        ], [
            'image.max' => 'Gagal mengunggah: Ukuran file gambar tidak boleh lebih dari 5MB.',
            'image.mimes' => 'Gagal mengunggah: Format file harus berupa jpeg, png, jpg, atau gif.',
            'image.required' => 'Gagal mengunggah: Gambar banner wajib diisi.',
            'image.image' => 'Gagal mengunggah: File yang diunggah harus berupa gambar.',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_path' => $path,
            'target_url' => $request->target_url,
            'is_active' => $request->has('is_active'),
            'sort_order' => Banner::max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'Banner berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'target_url' => 'nullable|url',
        ], [
            'image.max' => 'Gagal mengunggah: Ukuran file gambar tidak boleh lebih dari 5MB.',
            'image.mimes' => 'Gagal mengunggah: Format file harus berupa jpeg, png, jpg, atau gif.',
            'image.image' => 'Gagal mengunggah: File yang diunggah harus berupa gambar.',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'target_url' => $request->target_url,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? $banner->sort_order,
        ];

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->back()->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        if (Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }
        
        $banner->delete();

        return redirect()->back()->with('success', 'Banner berhasil dihapus.');
    }
}
