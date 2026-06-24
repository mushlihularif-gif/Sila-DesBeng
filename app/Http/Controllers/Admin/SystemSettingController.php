<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemSetting;
use App\Models\Region;
use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class SystemSettingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Admin Pusat mengelola Region tingkat teratas (Kabupaten) atau Region miliknya sendiri
        $region = Region::with(['services', 'parent.parent'])->find($user->region_id);
        
        if (!$region) {
            // Fallback: ambil Region pertama jika belum punya
            $region = Region::first();
        }

        $allServices = Service::all();
        $activeServices = $region ? $region->services->pluck('id')->toArray() : [];
        $exclusiveServices = $region ? $region->services->where('pivot.is_exclusive', true)->pluck('id')->toArray() : [];

        return view('admin.system_settings.index', compact('region', 'allServices', 'activeServices', 'exclusiveServices'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            $region = Region::first();
        }

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $request->validate([
            'profile_text' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id'
        ]);

        // Update whatsapp_name inside payment_info JSON
        $paymentInfo = $region->payment_info ?? [];
        if ($request->has('whatsapp_name')) {
            $paymentInfo['whatsapp_name'] = $request->whatsapp_name;
        }
        $paymentInfo['whatsapp_active'] = $request->has('whatsapp_active');

        $region->update([
            'profile_text' => $request->profile_text,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'payment_info' => $paymentInfo,
        ]);

        // Sync services
        $syncData = [];
        if ($request->has('services')) {
            $exclusives = $request->input('exclusive_services', []);
            foreach ($request->services as $serviceId) {
                $syncData[$serviceId] = [
                    'is_active' => true,
                    'is_exclusive' => in_array($serviceId, $exclusives)
                ];
            }
        }
        $region->services()->sync($syncData);

        return redirect()->back()->with('success', 'Pengaturan Pemerintah Kabupaten berhasil diperbarui.');
    }

    public function reset()
    {
        // Fitur reset dihilangkan karena Region tidak boleh direset sembarangan
        return redirect()->route('admin.system-settings.index')->with('success', 'Reset dinonaktifkan untuk data Region.');
    }

    public function paymentIndex()
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);
        
        if (!$region) {
            $region = Region::first();
        }

        return view('admin.system_settings.payment', compact('region'));
    }

    public function paymentUpdate(Request $request)
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            $region = Region::first();
        }

        $paymentInfo = $region->payment_info ?? [];
        $paymentInfo['bank_name'] = $request->bank_name;
        $paymentInfo['account_number'] = $request->account_number;
        $paymentInfo['account_name'] = $request->account_name;
        $paymentInfo['bank_active'] = $request->has('bank_active');
        $paymentInfo['ewallet_name'] = $request->ewallet_name;
        $paymentInfo['ewallet_number'] = $request->ewallet_number;
        $paymentInfo['ewallet_account_name'] = $request->ewallet_account_name;
        $paymentInfo['ewallet_active'] = $request->has('ewallet_active');
        if ($request->has('payment_gateway_active')) {
            if (empty($request->midtrans_server_key) || empty($request->midtrans_client_key)) {
                return redirect()->back()->with('error', 'Gagal: Kunci API Midtrans (Server Key & Client Key) wajib diisi jika Anda mengaktifkan Payment Gateway Otomatis. Silakan daftar akun bisnis di midtrans.com terlebih dahulu.')->withInput();
            }
        }

        $paymentInfo['card_theme'] = $request->card_theme;
        $paymentInfo['cash_only_active'] = $request->has('cash_only_active');
        $paymentInfo['payment_gateway_active'] = $request->has('payment_gateway_active');
        $paymentInfo['midtrans_server_key'] = $request->midtrans_server_key;
        $paymentInfo['midtrans_client_key'] = $request->midtrans_client_key;

        $region->update([
            'payment_info' => $paymentInfo,
        ]);

        return redirect()->back()->with('success', 'Pengaturan Pembayaran Pusat berhasil diperbarui.');
    }
}