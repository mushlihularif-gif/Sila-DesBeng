<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BumdesMember;
use App\Models\SystemSetting;

class BumdesUserController extends Controller
{
    public function show(\Illuminate\Http\Request $request)
    {
        $regionId = $request->query('id');
        
        // Fetch organizational structure ordered by position
        $members = BumdesMember::when($regionId, function($q) use ($regionId) {
            return $q->where('region_id', $regionId);
        }, function($q) {
            return $q->whereNull('region_id')->orWhere('region_id', 0);
        })->orderBy('order')->get();
        
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

        // Fetch WhatsApp number from region or fallback to system settings
        if ($region && $region->contact_phone) {
            $whatsappNumber = $region->contact_phone;
        } else {
            $settings = SystemSetting::first();
            $whatsappNumber = $settings->whatsapp_number ?? '+6281234567890';
        }
        
        // Generate WhatsApp link (remove all non-numeric characters except +)
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');
        
        $isWhatsappActive = $region && isset($region->payment_info['whatsapp_active']) ? $region->payment_info['whatsapp_active'] : false;
        
        return view('users.bumdes-detail', compact('members', 'whatsappLink', 'whatsappNumber', 'region', 'activeServices', 'isWhatsappActive'));
    }
}
