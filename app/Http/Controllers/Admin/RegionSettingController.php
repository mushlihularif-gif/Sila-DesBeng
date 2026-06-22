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

        return view('admin.region_settings.index', compact('region', 'allServices', 'activeServices', 'exclusiveServices'));
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
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_name' => 'nullable|string',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id'
        ]);

        // Update payment_info as JSON
        $paymentInfo = [
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
        ];

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

        return redirect()->back()->with('success', 'Pengaturan wilayah dan layanan berhasil diperbarui.');
    }
}
