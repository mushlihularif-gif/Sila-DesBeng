<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Service;

class RegionSettingController extends Controller
{
    /**
     * Halaman pembayaran wilayah - rekening bank dan sakelar gateway -
     * menentukan ke mana uang wilayah mendarat, bukan sekadar operasional unit.
     * Sidebar sudah menyembunyikannya dari staf, tapi route-nya sendiri hanya
     * dijaga 'role:admin' pseudo-role yang IKUT meloloskan staff (lihat
     * CheckRole::handle). Tanpa penjaga ini, staf gas bisa membuka
     * /pengaturan-pembayaran-wilayah lewat URL langsung dan mengganti rekening
     * tujuan pencairan - keputusan yang seharusnya hanya milik kepala desa/camat.
     */
    private function pastikanAdminWilayah(): void
    {
        abort_unless(
            auth()->user()?->role === 'admin_desa' && auth()->user()->region_id && in_array(auth()->user()->region?->type, ['desa', 'kelurahan'], true),
            403,
            'Halaman ini khusus admin wilayah.'
        );
    }

    public function index()
    {
        $user = auth()->user();

        $this->pastikanAdminWilayah();
        $region = Region::with(['services', 'parent.parent'])->find($user->region_id);

        if (!$region) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan wilayah mana pun.');
        }

        $allServices = Service::available()->get();
        $activeServices = $region->services->where('pivot.is_active', true)->pluck('id')->toArray();
        $exclusiveServices = $region->services->where('pivot.is_exclusive', true)->pluck('id')->toArray();
        $publicAccessServices = $region->services->where('pivot.is_active', true)->where('pivot.is_exclusive', false)->pluck('id')->toArray();

        $hasFasilitasKendaraan = \App\Models\FasilitasUmum::inCatalog()->where('region_id', $region->id)
            ->where(function($q) {
                $q->where('nama_fasilitas', 'like', '%mobil%')
                  ->orWhere('nama_fasilitas', 'like', '%bus%')
                  ->orWhere('nama_fasilitas', 'like', '%pick%up%');
            })->exists();

        return view('admin.region_settings.index', compact('region', 'allServices', 'activeServices', 'exclusiveServices', 'publicAccessServices', 'hasFasilitasKendaraan'));
    }

    public function update(Request $request)
    {
        $this->pastikanAdminWilayah();
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
            'services.*' => [\Illuminate\Validation\Rule::exists('services', 'id')->whereNotIn('slug', Service::RETIRED_SLUGS)]
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


        $paymentInfo['fasilitas_delivery_antar_active'] = $request->has('fasilitas_delivery_antar_active');
        $paymentInfo['fasilitas_delivery_jemput_active'] = $request->has('fasilitas_delivery_jemput_active');



        $region->update([
            'profile_text' => $request->profile_text,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'payment_info' => $paymentInfo,
        ]);

        // Sync services (Default bawaan: Khusus Warga Desa / is_exclusive = true;
        // hanya terbuka untuk desa lain bila sakelar public_services[] dinyalakan).
        $syncData = [];
        if ($request->has('services')) {
            $usePublicMode = $request->input('access_toggle_mode') === 'public' || $request->has('public_services');
            $publicIds = array_map('intval', $request->input('public_services', []));
            $exclusiveIds = array_map('intval', $request->input('exclusive_services', []));
            foreach ($request->services as $serviceId) {
                $sid = (int) $serviceId;
                if ($usePublicMode) {
                    $isExclusive = ! in_array($sid, $publicIds, true);
                } elseif ($request->has('exclusive_services')) {
                    $isExclusive = in_array($sid, $exclusiveIds, true);
                } else {
                    $isExclusive = true;
                }
                $syncData[$serviceId] = [
                    'is_active' => true,
                    'is_exclusive' => $isExclusive,
                ];
            }
        }

        // Kabar dan Informasi Daerah selalu aktif dan terbuka umum
        $publicServiceIds = \App\Models\Service::whereIn('slug', ['pengumuman'])->pluck('id')->toArray();
        foreach ($publicServiceIds as $pubId) {
            $syncData[$pubId] = [
                'is_active' => true,
                'is_exclusive' => false
            ];
        }

        // Jaga agar Fasilitas Umum tetap aktif jika wilayah memiliki gedung/fasilitas umum
        $hasGedung = \App\Models\FasilitasUmum::inCatalog()->where('region_id', $region->id)->exists();
        if ($hasGedung) {
            $fasilitasServiceId = \App\Models\Service::where('slug', 'fasilitas-umum')->value('id');
            if ($fasilitasServiceId && !isset($syncData[$fasilitasServiceId])) {
                $syncData[$fasilitasServiceId] = [
                    'is_active' => true,
                    'is_exclusive' => true
                ];
            }
        }

        $previousActiveServiceIds = $region->services()
            ->wherePivot('is_active', true)
            ->pluck('services.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $region->services()->sync($syncData);

        // Broadcast notifikasi layanan baru ke warga/RT/RW & Super Admin hanya untuk layanan yang baru saja diaktifkan
        $newlyActivatedIds = array_values(array_diff(
            array_map('intval', array_keys($syncData)),
            $previousActiveServiceIds
        ));
        if (! empty($newlyActivatedIds)) {
            Service::whereIn('id', $newlyActivatedIds)
                ->where('slug', '!=', 'pengumuman')
                ->get()
                ->each(fn (Service $svc) => \App\Services\NotificationService::broadcastServiceActivated($region, $svc));
        }

        return redirect()->back()->with('success', 'Pengaturan Wilayah berhasil diperbarui.');
    }

    public function paymentIndex()
    {
        $this->pastikanAdminWilayah();
        $user = auth()->user();
        $region = Region::find($user->region_id);
        
        if (!$region) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan wilayah mana pun.');
        }

        return view('admin.region_settings.payment', compact('region'));
    }

    public function paymentUpdate(Request $request)
    {
        $this->pastikanAdminWilayah();
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $bankActive = $request->has('bank_active');
        $cashActive = $request->has('cash_only_active');
        $ewalletActive = $request->has('ewallet_active');

        $bankName = trim((string) $request->input('bank_name', ''));
        $accountNumber = trim((string) $request->input('account_number', ''));
        $accountName = trim((string) $request->input('account_name', ''));

        $ewalletName = trim((string) $request->input('ewallet_name', ''));
        $ewalletNumber = trim((string) $request->input('ewallet_number', ''));
        $ewalletAccountName = trim((string) $request->input('ewallet_account_name', ''));

        if (! $bankActive && ! $cashActive && ! $ewalletActive) {
            return redirect()->back()
                ->with('error', 'Gagal: Minimal satu metode pembayaran (Transfer Bank, Tunai, atau E-Wallet) harus diaktifkan.')
                ->withInput();
        }

        if ($bankActive && ($bankName === '' || $accountNumber === '')) {
            return redirect()->back()
                ->with('error', 'Gagal: Pilih Bank dan isi Nomor Rekening jika Transfer Bank Manual diaktifkan.')
                ->withInput();
        }

        if ($ewalletActive && ($ewalletName === '' || $ewalletNumber === '')) {
            return redirect()->back()
                ->with('error', 'Gagal: Pilih layanan E-Wallet dan isi Nomor HP / E-Wallet jika E-Wallet diaktifkan.')
                ->withInput();
        }

        $paymentInfo = $region->payment_info ?? [];
        $paymentInfo['bank_name'] = $bankName ?: null;
        $paymentInfo['account_number'] = $accountNumber ?: null;
        $paymentInfo['account_name'] = $accountName ?: null;
        $paymentInfo['bank_active'] = $bankActive;

        $paymentInfo['ewallet_name'] = $ewalletName ?: null;
        $paymentInfo['ewallet_number'] = $ewalletNumber ?: null;
        $paymentInfo['ewallet_account_name'] = $ewalletAccountName ?: null;
        $paymentInfo['ewallet_active'] = $ewalletActive;

        $paymentInfo['card_theme'] = $request->input('card_theme', 'blue');
        $paymentInfo['cash_only_active'] = $cashActive;

        $region->update([
            'payment_info' => $paymentInfo,
        ]);

        return redirect()->back()->with('success', 'Pengaturan Pembayaran Wilayah berhasil diperbarui.');
    }

    public function toggleDelivery(Request $request)
    {
        $this->pastikanAdminWilayah();
        $fields = ['mobil_delivery_antar_active', 'mobil_delivery_jemput_active', 'mobil_lepas_kunci_antar_active',
            'mobil_sewa_delivery_antar_active', 'mobil_sewa_delivery_jemput_active',
            'mobil_rental_delivery_antar_active', 'mobil_rental_delivery_jemput_active',
            'alat_delivery_antar_active', 'alat_delivery_jemput_active', 'gas_delivery_antar_active',
            'gas_delivery_jemput_active', 'fasilitas_delivery_antar_active', 'fasilitas_delivery_jemput_active'];
        $request->validate(['field'=>['required', \Illuminate\Validation\Rule::in($fields)], 'value'=>'required|boolean']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $region = Region::lockForUpdate()->findOrFail(auth()->user()->region_id);
            $info = $region->payment_info ?? [];
            $info[$request->field] = $request->boolean('value');
            // Dua nama formulir lama/baru menunjuk pilihan yang sama.
            foreach (['antar', 'jemput'] as $method) {
                $aliases = ["mobil_delivery_{$method}_active", "mobil_sewa_delivery_{$method}_active"];
                if (in_array($request->field, $aliases, true)) foreach ($aliases as $key) $info[$key] = $request->boolean('value');
            }
            $region->update(['payment_info'=>$info]);
        });
        return response()->json(['success'=>true, 'message'=>'Pengaturan berhasil disinkronkan.', 'field'=>$request->field, 'value'=>$request->boolean('value')]);
    }
}
