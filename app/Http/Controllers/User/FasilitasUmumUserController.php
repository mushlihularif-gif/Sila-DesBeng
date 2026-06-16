<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;

class FasilitasUmumUserController extends Controller
{
    public function index()
    {
        $items = FasilitasUmum::where('status', '!=', 'Tidak Tersedia')
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return view('users.fasilitas-umum-equipment', compact('items'));
    }

    public function show($id)
    {
        $item = FasilitasUmum::findOrFail($id);
        
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.fasilitas-umum-detail', compact('item', 'setting'));
    }
}
