<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmbulansUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $region = Region::find($user->region_id);
        
        if (!$region) {
            return redirect()->back()->with('error', 'Data wilayah tidak ditemukan.');
        }

        // Ambil data ambulans saja
        $ambulansList = Mobil::with('supirs')
                             ->where('region_id', $region->id)
                             ->where('kategori', 'ambulans')
                             ->where('status', '!=', 'rusak')
                             ->get();

        $regionSettings = $region->settings ?? [];

        return view('users.ambulans-layanan', compact('ambulansList', 'regionSettings', 'region'));
    }
}
