<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;

class MobilRentalUserController extends Controller
{
    public function index()
    {
        $items = Mobil::where('status', '!=', 'rusak')
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return view('users.mobil-rental-equipment', compact('items'));
    }

    public function show($id)
    {
        $item = Mobil::findOrFail($id);
        
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.mobil-rental-detail', compact('item', 'setting'));
    }
}
