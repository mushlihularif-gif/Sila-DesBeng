<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCredential;
use App\Models\SystemSetting;
use App\Models\WalletTransaction;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminSettingController extends Controller
{
    /**
     * Panel Integrasi Payment Gateway (satu kredensial platform, dipakai semua region)
     */
    /**
     * Panel Integrasi & API Key Platform.
     *
     * Daftar kartu kredensial dirender dari config/api_providers.php, jadi
     * menambah provider baru tidak perlu menyentuh controller maupun view ini.
     */
    public function gateway()
    {
        $settings  = SystemSetting::instance();
        $providers = config('api_providers', []);
        $tersimpan = ApiCredential::allCached();

        return view('admin.super_sistem.gateway', compact('settings', 'providers', 'tersimpan'));
    }

    /**
     * Simpan pengaturan gateway yang sifatnya bisnis, bukan kredensial
     * (penyedia aktif & fee platform).
     */
    public function gatewayUpdate(Request $request)
    {
        $validated = $request->validateWithBag('umum', [
            'gateway_provider'        => 'nullable|in:midtrans,xendit,oy',
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
        ], [
            'platform_fee_percentage.required' => 'Fee platform wajib diisi.',
        ]);

        $settings = SystemSetting::instance();
        $settings->gateway_provider = $validated['gateway_provider'] ?? null;
        $settings->platform_fee_percentage = $validated['platform_fee_percentage'];
        $settings->save();

        Log::info('SuperAdmin: Pengaturan gateway umum diperbarui oleh ' . auth()->user()->email);

        return redirect()
            ->route('admin.sistem-platform.gateway')
            ->with('success', 'Pengaturan gateway berhasil diperbarui.');
    }

    /**
     * Simpan (TIMPA) kredensial satu kategori.
     *
     * Semua field kategori wajib diisi — kalau ada yang kosong, form ditolak
     * dengan peringatan dan data lama tetap utuh. Penyimpanan memakai
     * updateOrCreate sehingga satu kategori selamanya hanya punya satu baris.
     */
    public function credentialUpdate(Request $request, string $category)
    {
        $provider = config("api_providers.{$category}");

        abort_if(! $provider, 404, 'Kategori kredensial tidak dikenal.');

        [$rules, $attributes, $messages] = $this->buildCredentialValidation($provider);

        $validated = $request->validateWithBag($category, $rules, $messages, $attributes);

        $credentials = [];

        foreach ($provider['fields'] as $field => $definition) {
            if (($definition['type'] ?? 'text') === 'boolean') {
                $credentials[$field] = $request->boolean($field);
                continue;
            }

            $credentials[$field] = trim((string) ($validated[$field] ?? ''));
        }

        ApiCredential::put($category, $credentials, auth()->user()->email);

        Log::info("SuperAdmin: Kredensial [{$category}] ditimpa oleh " . auth()->user()->email);

        return redirect()
            ->route('admin.sistem-platform.gateway')
            ->with('success', "Kredensial {$provider['label']} berhasil diterapkan.");
    }

    /**
     * Hapus kredensial satu kategori sehingga aplikasi kembali memakai nilai .env.
     */
    public function credentialDestroy(string $category)
    {
        $provider = config("api_providers.{$category}");

        abort_if(! $provider, 404, 'Kategori kredensial tidak dikenal.');

        ApiCredential::forget($category);

        Log::info("SuperAdmin: Kredensial [{$category}] dihapus oleh " . auth()->user()->email);

        return redirect()
            ->route('admin.sistem-platform.gateway')
            ->with('success', "Kredensial {$provider['label']} dihapus. Sistem kembali memakai nilai dari file .env.");
    }

    /**
     * Susun aturan validasi satu kategori dari definisi di config/api_providers.php.
     *
     * Setiap field teks jadi: required + string + batas min/max karakter dari
     * config + aturan tambahan (regex/starts_with) kalau ada.
     */
    private function buildCredentialValidation(array $provider): array
    {
        $rules = [];
        $attributes = [];
        $messages = [];

        foreach ($provider['fields'] as $field => $definition) {
            $attributes[$field] = $definition['label'];

            if (($definition['type'] ?? 'text') === 'boolean') {
                $rules[$field] = ['nullable', 'boolean'];
                continue;
            }

            $baris = ['required', 'string'];

            if (isset($definition['min'])) {
                $baris[] = 'min:' . $definition['min'];
                $messages["{$field}.min"] = ":attribute minimal {$definition['min']} karakter.";
            }

            if (isset($definition['max'])) {
                $baris[] = 'max:' . $definition['max'];
                $messages["{$field}.max"] = ":attribute maksimal {$definition['max']} karakter.";
            }

            if (($definition['type'] ?? 'text') === 'select' && ! empty($definition['options'])) {
                $baris[] = 'in:' . implode(',', array_keys($definition['options']));
            }

            foreach ($definition['rules'] ?? [] as $tambahan) {
                $baris[] = $tambahan;
            }

            $messages["{$field}.required"] = ':attribute wajib diisi.';

            if (isset($definition['hint'])) {
                $messages["{$field}.regex"] = ":attribute formatnya tidak sesuai. {$definition['hint']}";
                $messages["{$field}.starts_with"] = ":attribute formatnya tidak sesuai. {$definition['hint']}";
            }

            $rules[$field] = $baris;
        }

        return [$rules, $attributes, $messages];
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

    /**
     * Monitoring biaya server/domain/hosting - murni pencatatan & pengingat.
     * Tidak ditarik dari saldo wallet/fee platform, tetap dibayar lewat APBD
     * lewat Diskominfotik; ini cuma bantu supaya tidak lupa jatuh tempo.
     */
    public function expenses()
    {
        $expenses = OperationalExpense::with('payer:id,name')->orderBy('due_date')->get();

        return view('admin.super_sistem.expenses', compact('expenses'));
    }

    public function expensesStore(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:domain,hosting,ssl,api_service,lainnya',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:bulanan,tahunan,sekali_bayar',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        OperationalExpense::create($validated);

        return redirect()->back()->with('success', 'Item biaya operasional berhasil ditambahkan.');
    }

    public function expensesMarkPaid(OperationalExpense $expense)
    {
        $expense->markPaidAndRenew(auth()->id());

        Log::info('SuperAdmin: Expense "' . $expense->item_name . '" marked paid by ' . auth()->user()->email);

        return redirect()->back()->with('success', 'Tagihan ditandai lunas.');
    }

    public function expensesDestroy(OperationalExpense $expense)
    {
        $expense->delete();

        return redirect()->back()->with('success', 'Item biaya operasional dihapus.');
    }
}
