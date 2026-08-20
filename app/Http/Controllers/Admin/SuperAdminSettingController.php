<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\WalletTransaction;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminSettingController extends Controller
{
    /**
     * Panel Integrasi Payment Gateway (satu kredensial platform, dipakai semua region)
     */
    public function gateway()
    {
        $settings = SystemSetting::first() ?? new SystemSetting();

        return view('admin.super_sistem.gateway', compact('settings'));
    }

    public function gatewayUpdate(Request $request)
    {
        $validated = $request->validate([
            'gateway_provider' => 'nullable|in:midtrans,xendit,oy',
            'gateway_secret_key' => 'nullable|string',
            'gateway_public_key' => 'nullable|string',
            'gateway_is_production' => 'nullable|boolean',
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();

        $settings->gateway_provider = $validated['gateway_provider'] ?? $settings->gateway_provider;
        // Jangan timpa key yang sudah tersimpan kalau field dikosongkan (biar tidak perlu isi ulang tiap update)
        if ($request->filled('gateway_secret_key')) {
            $settings->gateway_secret_key = $validated['gateway_secret_key'];
        }
        if ($request->filled('gateway_public_key')) {
            $settings->gateway_public_key = $validated['gateway_public_key'];
        }
        $settings->gateway_is_production = $request->has('gateway_is_production');
        $settings->platform_fee_percentage = $validated['platform_fee_percentage'];
        $settings->save();

        Log::info('SuperAdmin: Gateway settings updated by ' . auth()->user()->email);

        return redirect()->back()->with('success', 'Pengaturan Payment Gateway berhasil diperbarui.');
    }

    /**
     * Monitoring kesehatan transaksi lintas region (agregat, bukan nominal per desa)
     */
    public function monitoring()
    {
        $unitModels = [
            'Gas' => GasOrder::class,
            'Sewa Alat' => RentalBooking::class,
            'Sewa Mobil' => MobilBooking::class,
            'Fasilitas Umum' => FasilitasUmumBooking::class,
        ];

        $stats = [];
        foreach ($unitModels as $label => $modelClass) {
            $stats[$label] = [
                'total' => $modelClass::count(),
                'selesai' => $modelClass::where('status', 'completed')->count(),
                'gagal' => $modelClass::whereIn('status', ['rejected', 'cancelled'])->count(),
                'menunggu' => $modelClass::where('status', 'pending')->count(),
            ];
        }

        $walletHealth = [
            'total_tertahan' => WalletTransaction::where('type', 'ditahan')->where('status', 'pending')->count(),
            'total_gagal_verifikasi' => WalletTransaction::where('status', 'rejected')->count(),
            'jumlah_region_aktif' => WalletTransaction::distinct('region_id')->count('region_id'),
        ];

        return view('admin.super_sistem.monitoring', compact('stats', 'walletHealth'));
    }

    /**
     * Log keamanan & audit (siapa buat akun staf, percobaan akses ditolak, dst)
     */
    public function securityLog()
    {
        $logPath = storage_path('logs/laravel.log');
        $lines = [];

        if (file_exists($logPath)) {
            $handle = fopen($logPath, 'r');
            $buffer = [];
            while (!feof($handle)) {
                $line = fgets($handle);
                if ($line !== false && (str_contains($line, 'SECURITY:') || str_contains($line, 'SuperAdmin:'))) {
                    $buffer[] = trim($line);
                }
            }
            fclose($handle);
            $lines = array_slice(array_reverse($buffer), 0, 200);
        }

        $recentStaffAccounts = \App\Models\User::where('role', 'staff')
            ->whereNotNull('created_by')
            ->with('creator:id,name,email')
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.super_sistem.security-log', compact('lines', 'recentStaffAccounts'));
    }
}
