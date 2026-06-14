<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // Tambahkan ini untuk type hinting jika perlu di update()

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan umum.
     */
    public function index()
    {
        // Ganti 'settings.index' dengan view yang benar-benar Anda miliki untuk pengaturan umum
        // Misalnya: return view('admin.settings.general');
        return view('settings.index'); // Sesuaikan dengan view Anda
    }

    /**
     * Menyimpan perubahan pengaturan umum.
     */
    public function update(Request $request) // Tambahkan Request $request untuk menangani input
    {
        // Tambahkan logika validasi dan update di sini jika diperlukan
        // Misalnya:
        // $request->validate([...]);
        // Simpan ke database atau config...

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Menampilkan halaman Profil iSewa (profil sistem)
     */
    public function showIsewaProfile(Request $request)
    {
        $search = $request->get('search');
        
        $developers = [
            'wahid' => [
                'name' => 'Wahid Riono',
                'prodi' => 'D4 Information System Security',
                'jurusan' => 'Teknik Informatika',
                'kampus' => 'Politeknik Negeri Bengkalis',
                'image' => 'wahid.jpg'
            ],
            'mushlihul' => [
                'name' => 'Mushlihul Arif',
                'prodi' => 'D4 Information System Security',
                'jurusan' => 'Teknik Informatika',
                'kampus' => 'Politeknik Negeri Bengkalis',
                'image' => 'mushlihul.jpg'
            ],
            'safika' => [
                'name' => 'Safika',
                'prodi' => 'D4 Information System Security',
                'jurusan' => 'Teknik Informatika',
                'kampus' => 'Politeknik Negeri Bengkalis',
                'image' => 'safika.jpg'
            ]
        ];
        
        // Filter developers by search
        if ($search) {
            $developers = array_filter($developers, function($dev) use ($search) {
                return stripos($dev['name'], $search) !== false;
            });
        }
        
        return view('admin.isewa.profile', compact('developers', 'search'));
    }

    /**
     * Menampilkan halaman Profil Pengembang
     */
    public function showDeveloperProfile($name)
    {
        $developers = [
            'wahid' => [
                'name' => 'Wahid Riono',
                'prodi' => 'D4 Information System Security',
                'jurusan' => 'Teknik Informatika',
                'kampus' => 'Politeknik Negeri Bengkalis',
                'image' => 'wahid.jpg'
            ],
            'mushlihul' => [
                'name' => 'Mushlihul Arif',
                'prodi' => 'D4 Information System Security',
                'jurusan' => 'Teknik Informatika',
                'kampus' => 'Politeknik Negeri Bengkalis',
                'image' => 'mushlihul.jpg'
            ],
            'safika' => [
                'name' => 'Safika',
                'prodi' => 'D4 Information System Security',
                'jurusan' => 'Teknik Informatika',
                'kampus' => 'Politeknik Negeri Bengkalis',
                'image' => 'safika.jpg'
            ]
        ];

        if (!isset($developers[$name])) {
            abort(404, 'Pengembang tidak ditemukan.');
        }

        $currentDeveloper = $developers[$name];
        return view('admin.isewa.developer.profile', compact('currentDeveloper', 'name'));
    }
}