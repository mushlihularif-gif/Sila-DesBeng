<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class SystemSettingController extends Controller
{
    public function index()
    {
        // Ambil setting pertama (atau buat baru jika belum ada)
        $setting = SystemSetting::first();
        if (!$setting) {
            $setting = SystemSetting::create([
                'location_name' => 'BUMDes Desa Pematang Duku Timur',
                'latitude' => -0.5000000,
                'longitude' => 101.0000000,
                'address' => 'Jl. Raya No. 123, Desa Pematang Duku Timur',
                'bank_name' => 'Bank Syariah Indonesia',
                'bank_account_number' => '12345678989',
                'bank_account_holder' => 'BUMDes Desa Pematang Duku Timur',
                'payment_methods' => ['transfer', 'tunai'],
                'card_background_type' => 'gradient',
                'card_gradient_style' => 'blue',
                'whatsapp_number' => '+6281234567890',
                'office_address' => 'Jl. Kantor BUMDes, Desa Pematang Duku Timur',
                'operating_hours' => 'Senin - Sabtu, 08:00 - 17:00',
            ]);
        }

        return view('admin.system_settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        // Check if sensitive fields are being changed (Sudo Mode)
        $sensitiveFields = ['whatsapp_number', 'bank_account_number', 'bank_account_holder', 'bank_name'];
        $changingSensitive = false;
        foreach ($sensitiveFields as $field) {
            if ($request->has($field)) {
                $changingSensitive = true;
                break;
            }
        }

        if ($changingSensitive) {
            $request->validate([
                'admin_password' => 'required',
            ]);

            if (!Hash::check($request->admin_password, auth()->user()->password)) {
                return back()->with('error', 'Password admin tidak valid. Perubahan data sensitif dibatalkan.');
            }

            // Log sensitive change
            ActivityLog::create([
                'action' => 'update_sensitive_settings',
                'description' => 'Admin updated sensitive system settings (bank/WhatsApp)',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
        }

        $request->validate([
            'location_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_account_holder' => 'nullable|string|max:255',
            'card_background_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'card_background_type' => 'nullable|in:gradient,image',
            'card_gradient_style' => 'nullable|string|max:50',
            'cash_payment_description' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'office_address' => 'nullable|string',
            'operating_hours' => 'nullable|string',
        ]);

        $setting = SystemSetting::first();
        if (!$setting) {
            $setting = new SystemSetting();
        }

        // Update basic fields
        $setting->location_name = $request->input('location_name');
        $setting->latitude = $request->input('latitude');
        $setting->longitude = $request->input('longitude');
        $setting->address = $request->input('address');
        $setting->bank_name = $request->input('bank_name');
        $setting->bank_account_number = $request->input('bank_account_number');
        $setting->bank_account_holder = $request->input('bank_account_holder');
        $setting->card_background_type = $request->input('card_background_type', 'gradient');
        $setting->card_gradient_style = $request->input('card_gradient_style', 'blue');
        $setting->cash_payment_description = $request->input('cash_payment_description');
        $setting->whatsapp_number = $request->input('whatsapp_number');
        $setting->office_address = $request->input('office_address');
        $setting->operating_hours = $request->input('operating_hours');

        // Handle payment methods (checkbox array)
        $setting->payment_methods = $request->input('payment_methods', []);

        // Handle card background image upload
        if ($request->hasFile('card_background_image')) {
            // Delete old image if exists
            if ($setting->card_background_image && \Storage::disk('public')->exists($setting->card_background_image)) {
                \Storage::disk('public')->delete($setting->card_background_image);
            }
            
            // Store new image
            $path = $request->file('card_background_image')->store('system', 'public');
            $setting->card_background_image = $path;
        }

        $setting->save();

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function reset()
    {
        $setting = SystemSetting::first();
        if ($setting) {
            // Delete card background image if exists
            if ($setting->card_background_image && \Storage::disk('public')->exists($setting->card_background_image)) {
                \Storage::disk('public')->delete($setting->card_background_image);
            }
            $setting->delete();
        }

        return redirect()->route('admin.system-settings.index')->with('success', 'Pengaturan sistem berhasil direset.');
    }
}