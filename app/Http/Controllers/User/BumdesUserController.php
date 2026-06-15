<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BumdesMember;
use App\Models\SystemSetting;

class BumdesUserController extends Controller
{
    public function show(\Illuminate\Http\Request $request)
    {
        // Fetch organizational structure ordered by position
        $members = BumdesMember::orderBy('order')->get();
        
        // Fetch WhatsApp number from system settings
        $settings = SystemSetting::first();
        $whatsappNumber = $settings->whatsapp_number ?? '+6281234567890';
        
        // Generate WhatsApp link (remove all non-numeric characters except +)
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');
        
        // Fetch region and its active services if id is provided
        $regionId = $request->query('id');
        $region = null;
        $activeServices = [];
        
        if ($regionId) {
            $region = \App\Models\Region::with(['services' => function($q) {
                $q->where('is_active', true);
            }])->find($regionId);
            
            if ($region) {
                $activeServices = $region->services->pluck('name')->toArray();
            }
        }
        
        return view('users.bumdes-detail', compact('members', 'whatsappLink', 'whatsappNumber', 'region', 'activeServices'));
    }
}
