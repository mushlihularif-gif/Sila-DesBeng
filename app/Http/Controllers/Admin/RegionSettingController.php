<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Service;

class RegionSettingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Bootstrapping initial admin region if not connected
        if (empty($user->region_id)) {
            $kabupaten = Region::firstOrCreate(
                ['name' => 'Bengkalis', 'type' => 'kabupaten'],
                ['profile_text' => 'Kabupaten Bengkalis']
            );
            $kecamatan = Region::firstOrCreate(
                ['name' => 'Bengkalis', 'type' => 'kecamatan', 'parent_id' => $kabupaten->id],
                ['profile_text' => 'Kecamatan Bengkalis']
            );
            $desa = Region::firstOrCreate(
                ['name' => 'Pematang Duku Timur', 'type' => 'desa', 'parent_id' => $kecamatan->id],
                ['profile_text' => 'Pemerintahan Desa Pematang Duku Timur, Kecamatan Bengkalis, Kabupaten Bengkalis.']
            );
            
            $user->region_id = $desa->id;
            $user->save();
        }

        if ($user->role === 'super_admin') {
            // Super Admin can select a region to manage, for now just show their own or top-level.
            $region = Region::with(['services', 'parent.parent'])->find($user->region_id);
        } else {
            $region = Region::with(['services', 'parent.parent'])->find($user->region_id);
        }

        if (!$region) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan wilayah mana pun.');
        }

        $allServices = Service::all();
        $activeServices = $region->services->pluck('id')->toArray();
        $exclusiveServices = $region->services->where('pivot.is_exclusive', true)->pluck('id')->toArray();

        $hasFasilitasKendaraan = \App\Models\FasilitasUmum::where('region_id', $region->id)
            ->where(function($q) {
                $q->where('nama_fasilitas', 'like', '%mobil%')
                  ->orWhere('nama_fasilitas', 'like', '%ambulan%')
                  ->orWhere('nama_fasilitas', 'like', '%bus%')
                  ->orWhere('nama_fasilitas', 'like', '%pick%up%');
            })->exists();

        return view('admin.region_settings.index', compact('region', 'allServices', 'activeServices', 'exclusiveServices', 'hasFasilitasKendaraan'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);

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

        $mobilServiceId = \App\Models\Service::where('name', 'Penyewaan Mobil')->value('id');
        $alatServiceId = \App\Models\Service::where('name', 'Penyewaan Alat')->value('id');
        $gasServiceId = \App\Models\Service::where('name', 'Penjualan Gas')->value('id');
        $fasilitasServiceId = \App\Models\Service::where('name', 'Fasilitas Umum')->value('id');

        $selectedServices = $request->input('services', []);

        if (in_array($mobilServiceId, $selectedServices) && !$request->has('mobil_delivery_antar_active') && !$request->has('mobil_delivery_jemput_active')) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Mobil harus diaktifkan!')->withInput();
        }
        if (in_array($alatServiceId, $selectedServices) && !$request->has('alat_delivery_antar_active') && !$request->has('alat_delivery_jemput_active')) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Alat harus diaktifkan!')->withInput();
        }
        if (in_array($gasServiceId, $selectedServices) && !$request->has('gas_delivery_antar_active') && !$request->has('gas_delivery_jemput_active')) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Gas harus diaktifkan!')->withInput();
        }
        if (in_array($fasilitasServiceId, $selectedServices) && !$request->has('fasilitas_delivery_antar_active') && !$request->has('fasilitas_delivery_jemput_active')) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Fasilitas Umum harus diaktifkan!')->withInput();
        }

        // Update whatsapp_name inside payment_info JSON
        $paymentInfo = $region->payment_info ?? [];
        if ($request->has('whatsapp_name')) {
            $paymentInfo['whatsapp_name'] = $request->whatsapp_name;
        }
        $paymentInfo['whatsapp_active'] = $request->has('whatsapp_active');
        
        $paymentInfo['mobil_delivery_antar_active'] = $request->has('mobil_delivery_antar_active');
        $paymentInfo['mobil_delivery_jemput_active'] = $request->has('mobil_delivery_jemput_active');
        
        $paymentInfo['alat_delivery_antar_active'] = $request->has('alat_delivery_antar_active');
        $paymentInfo['alat_delivery_jemput_active'] = $request->has('alat_delivery_jemput_active');
        
        $paymentInfo['gas_delivery_antar_active'] = $request->has('gas_delivery_antar_active');
        $paymentInfo['gas_delivery_jemput_active'] = $request->has('gas_delivery_jemput_active');

        $paymentInfo['fasilitas_delivery_antar_active'] = $request->has('fasilitas_delivery_antar_active');
        $paymentInfo['fasilitas_delivery_jemput_active'] = $request->has('fasilitas_delivery_jemput_active');

        // Store defaults
        if ($request->has('mobil_bbm')) {
            $paymentInfo['mobil_bbm_default'] = $request->mobil_bbm;
            // Bulk update existing mobils
            \App\Models\Mobil::where('region_id', $region->id)->update([
                'bbm_ditanggung' => $request->mobil_bbm,
                'opsi_supir' => $request->mobil_supir
            ]);
        }
        if ($request->has('fasilitas_bbm')) {
            $paymentInfo['fasilitas_bbm_default'] = $request->fasilitas_bbm;
            // Bulk update existing fasilitas umums
            \App\Models\FasilitasUmum::where('region_id', $region->id)->update([
                'bbm_ditanggung' => $request->fasilitas_bbm,
                'opsi_supir' => $request->fasilitas_supir
            ]);
        }
        if ($request->has('mobil_supir')) {
            $paymentInfo['mobil_supir_default'] = $request->mobil_supir;
        }
        if ($request->has('fasilitas_supir')) {
            $paymentInfo['fasilitas_supir_default'] = $request->fasilitas_supir;
        }

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

        return redirect()->back()->with('success', 'Pengaturan Wilayah berhasil diperbarui.');
    }

    public function paymentIndex()
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);
        
        if (!$region) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan wilayah mana pun.');
        }

        return view('admin.region_settings.payment', compact('region'));
    }

    public function paymentUpdate(Request $request)
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
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

        return redirect()->back()->with('success', 'Pengaturan Pembayaran Wilayah berhasil diperbarui.');
    }
}
