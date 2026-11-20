<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BumdesMember;
use App\Models\SystemSetting;

class BumdesUserController extends Controller
{
    public function show(\Illuminate\Http\Request $request)
    {
        $request->validate(['id' => 'nullable|integer|min:1']);
        if (!$request->filled('id')) {
            return redirect()->route('bumdes.profil');
        }
        $region = \App\Models\Region::with(['services' => fn ($q) => $q->wherePivot('is_active', true)])
            ->findOrFail($request->integer('id'));
        \App\Support\BengkalisScope::assertContains($region, false);
        abort_unless(in_array($region->type, ['desa', 'kelurahan'], true), 404);
        $members = BumdesMember::where('region_id', $region->id)->orderBy('level')->orderBy('order')->get();
        $activeServices = $region->services->pluck('name')->all();

        // Fetch WhatsApp number from region or fallback to system settings
        if ($region && $region->contact_phone) {
            $whatsappNumber = $region->contact_phone;
        } else {
            $settings = SystemSetting::first();
            $whatsappNumber = $settings->whatsapp_number ?? '';
        }
        
        // Generate WhatsApp link (remove all non-numeric characters except +)
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');
        
        $isWhatsappActive = $region && isset($region->payment_info['whatsapp_active']) ? $region->payment_info['whatsapp_active'] : false;
        
        return view('users.bumdes-detail', compact('members', 'whatsappLink', 'whatsappNumber', 'region', 'activeServices', 'isWhatsappActive'));
    }
}
