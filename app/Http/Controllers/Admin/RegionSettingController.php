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
            if (in_array($user->role, ['super_admin', 'admin'])) {
                $user->region_id = $kabupaten->id;
            } else {
                $user->region_id = $desa->id;
            }
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

        // Fallback untuk admin kabupaten jika region_id belum diset
        if (!$region && in_array($user->role, ['super_admin', 'admin'])) {
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

        $mobilServiceId = \App\Models\Service::where('name', 'Penyewaan Mobil')->value('id');
        $alatServiceId = \App\Models\Service::where('name', 'Penyewaan Alat')->value('id');
        $gasServiceId = \App\Models\Service::where('name', 'Penjualan Gas')->value('id');
        $fasilitasServiceId = \App\Models\Service::where('name', 'Fasilitas Umum')->value('id');

        $selectedServices = $request->input('services', []);

        $isMobilDeliveryActive = $request->has('mobil_delivery_antar_active') 
            || $request->has('mobil_delivery_jemput_active')
            || $request->has('mobil_sewa_delivery_antar_active') 
            || $request->has('mobil_sewa_delivery_jemput_active')
            || $request->has('mobil_rental_delivery_antar_active') 
            || $request->has('mobil_rental_delivery_jemput_active');

        if (in_array($mobilServiceId, $selectedServices) && !$isMobilDeliveryActive) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Mobil (Sewa/Rental) harus diaktifkan!')->withInput();
        }
        if (in_array($alatServiceId, $selectedServices) && !$request->has('alat_delivery_antar_active') && !$request->has('alat_delivery_jemput_active')) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Alat harus diaktifkan!')->withInput();
        }
        if (in_array($gasServiceId, $selectedServices) && !$request->has('gas_delivery_antar_active') && !$request->has('gas_delivery_jemput_active')) {
            return redirect()->back()->with('error', 'Gagal: Minimal satu metode pengiriman untuk Gas harus diaktifkan!')->withInput();
        }
        // Pengaturan pengiriman untuk Fasilitas Umum telah ditiadakan

        // Update whatsapp_name inside payment_info JSON
        $paymentInfo = $region->payment_info ?? [];
        if ($request->has('whatsapp_name')) {
            $paymentInfo['whatsapp_name'] = $request->whatsapp_name;
        }
        $paymentInfo['whatsapp_active'] = $request->has('whatsapp_active');
        
        $paymentInfo['mobil_delivery_antar_active'] = $request->has('mobil_delivery_antar_active') || $request->has('mobil_sewa_delivery_antar_active');
        $paymentInfo['mobil_delivery_jemput_active'] = $request->has('mobil_delivery_jemput_active') || $request->has('mobil_sewa_delivery_jemput_active');
        $paymentInfo['mobil_lepas_kunci_antar_active'] = $request->has('mobil_lepas_kunci_antar_active');
        
        // Kompatibilitas key lama
        $paymentInfo['mobil_sewa_delivery_antar_active'] = $paymentInfo['mobil_delivery_antar_active'];
        $paymentInfo['mobil_sewa_delivery_jemput_active'] = $paymentInfo['mobil_delivery_jemput_active'];
        $paymentInfo['mobil_rental_delivery_antar_active'] = $request->has('mobil_rental_delivery_antar_active');
        $paymentInfo['mobil_rental_delivery_jemput_active'] = $request->has('mobil_rental_delivery_jemput_active');
        
        $paymentInfo['alat_delivery_antar_active'] = $request->has('alat_delivery_antar_active');
        $paymentInfo['alat_delivery_jemput_active'] = $request->has('alat_delivery_jemput_active');
        
        $paymentInfo['gas_delivery_antar_active'] = $request->has('gas_delivery_antar_active');
        $paymentInfo['gas_delivery_jemput_active'] = $request->has('gas_delivery_jemput_active');

        $paymentInfo['pasar_delivery_antar_active'] = $request->has('pasar_delivery_antar_active');
        $paymentInfo['pasar_delivery_jemput_active'] = $request->has('pasar_delivery_jemput_active');

        $paymentInfo['fasilitas_delivery_antar_active'] = $request->has('fasilitas_delivery_antar_active');
        $paymentInfo['fasilitas_delivery_jemput_active'] = $request->has('fasilitas_delivery_jemput_active');



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

        // Layanan Publik Kabupaten (Pasar Daerah & Kabar dan Informasi Daerah) selalu aktif dan terbuka umum
        $publicServiceIds = \App\Models\Service::whereIn('slug', ['pasar-daerah', 'pengumuman'])->pluck('id')->toArray();
        foreach ($publicServiceIds as $pubId) {
            $syncData[$pubId] = [
                'is_active' => true,
                'is_exclusive' => false
            ];
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

    public function toggleDelivery(Request $request)
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|boolean'
        ]);

        $user = auth()->user();
        $region = Region::find($user->region_id);
        
        if (!$region && in_array($user->role, ['super_admin', 'admin'])) {
            $region = Region::first();
        }

        if (!$region) {
            return response()->json(['success' => false, 'message' => 'Region tidak ditemukan.'], 404);
        }

        $paymentInfo = $region->payment_info ?? [];
        $paymentInfo[$request->field] = $request->value;
        
        $region->update(['payment_info' => $paymentInfo]);

        return response()->json([
            'success' => true, 
            'message' => 'Pengaturan berhasil disinkronkan.',
            'field' => $request->field,
            'value' => $request->value
        ]);
    }
}
