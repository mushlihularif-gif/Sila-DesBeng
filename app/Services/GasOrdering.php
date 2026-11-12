<?php

namespace App\Services;

use App\Models\{AdminNotification, FamilyCard, Gas, GasOrder, Region, TransactionReceipt, User, WalletTransaction};
use App\Support\ProfilPembayaranWilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GasOrdering
{
    public function visibleQuery(?User $user)
    {
        $line = Region::garisLayananUntukWarga($user?->region_id);
        return Gas::query()->where('status', '!=', 'rusak')->whereHas('region', function ($q) use ($line) {
            $q->where(function ($q) use ($line) {
                $q->whereHas('services', fn ($s) => $s->where('slug', 'penjualan-gas')->where('region_services.is_active', true)
                    ->where(fn ($p) => $p->where('region_services.is_exclusive', false)->orWhereIn('regions.id', $line)))
                    // Legacy own-region products are visible only when no explicit setting exists.
                    ->orWhere(fn ($own) => $own->whereIn('id', $line)->whereDoesntHave('services', fn ($s) => $s->where('slug', 'penjualan-gas')));
            });
        });
    }

    public function household(User $user, bool $lock = false): ?FamilyCard
    {
        if (!$user->nik_hash) return null;
        $query = FamilyCard::whereIn('status', ['approved', 'verified'])
            ->whereHas('members', fn ($q) => $q->where('nik_hash', $user->nik_hash));
        return ($lock ? $query->lockForUpdate() : $query)->first();
    }

    public function crisis(Gas $gas): bool
    {
        $region = $gas->region;
        return $gas->isSubsidized() && (bool) ($region?->settings['crisis_mode_gas'] ?? $region?->is_gas_crisis ?? false);
    }

    public function checkEligibility(User $user, Gas $gas, int $quantity, ?FamilyCard $family = null): void
    {
        abort_unless($this->visibleQuery($user)->whereKey($gas->id)->exists(), 403, 'Layanan gas ini tidak tersedia untuk wilayah akun Anda.');
        if ($gas->status !== 'tersedia' || $quantity < 1 || !$gas->hasStock($quantity)) {
            throw ValidationException::withMessages(['quantity' => 'Gas sedang tidak tersedia atau stok tidak mencukupi.']);
        }
        if (!$this->crisis($gas)) return;
        if ($user->verification_status !== 'verified' || !$family) {
            throw ValidationException::withMessages(['kk' => 'Untuk gas 3 kg dalam mode krisis, verifikasi identitas dan KK terlebih dahulu di halaman Gas Daerah.']);
        }
        $settings = $gas->region->settings ?? [];
        $limit = max(1, (int) ($settings['gas_quota_limit'] ?? 1));
        $days = max(1, (int) ($settings['gas_quota_days'] ?? 7));
        $memberIds = User::whereIn('nik_hash', $family->members()->pluck('nik_hash'))->pluck('id');
        $subsidizedIds = Gas::where('region_id', $gas->region_id)->get()->filter(fn ($item) => $item->isSubsidized())->modelKeys();
        $used = GasOrder::withoutGlobalScopes()->where('created_at', '>=', now()->subDays($days))
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->whereIn('gas_id', $subsidizedIds)
            ->where(fn ($q) => $q->where('nomor_kk_hash', $family->no_kk_hash)->orWhereIn('user_id', $memberIds))->sum('quantity');
        if ($used + $quantity > $limit) {
            throw ValidationException::withMessages(['quantity' => "Kuota KK maksimal {$limit} tabung per {$days} hari. Sisa kuota: ".max(0, $limit - $used).'. Pesanan menunggu juga dihitung.']);
        }
    }

    public function store(Request $request): array
    {
        abort_unless($request->user(), 401);
        $data = $request->validate([
            'gas_id' => 'required|integer|exists:gas,id', 'quantity' => 'required|integer|min:1|max:100',
            'delivery_method' => 'required|in:antar,jemput', 'buyer_name' => 'required|string|max:255',
            'buyer_address' => 'required|string|max:255', 'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180', 'payment_method' => 'required|in:tunai,transfer,ewallet',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'idempotency_key' => 'nullable|uuid',
        ]);
        $path = null;
        try {
            return DB::transaction(function () use ($request, $data, &$path) {
                $user = User::lockForUpdate()->findOrFail($request->user()->id);
                $key = $data['idempotency_key'] ?? null;
                $payload = $data;
                unset($payload['payment_proof'], $payload['idempotency_key']);
                $hash = hash('sha256', json_encode($payload));
                if ($key && $existing = GasOrder::withTrashed()->where('user_id', $user->id)->where('idempotency_key', $key)->first()) {
                    abort_unless(hash_equals($existing->submission_hash ?? '', $hash), 409, 'Permintaan sebelumnya berbeda. Buka ulang formulir untuk pesanan baru.');
                    return $this->result($existing);
                }
                // Serializes all family members, including orders for different 3 kg products.
                $family = $this->household($user, true);
                $gas = Gas::with('region')->lockForUpdate()->findOrFail($data['gas_id']);
                $this->checkEligibility($user, $gas, (int) $data['quantity'], $family);
                $profile = ProfilPembayaranWilayah::untuk($gas->region_id);
                if (!in_array($data['payment_method'], $profile->metodeTersedia(), true)) {
                    throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran ini belum tersedia di wilayah penyedia.']);
                }
                $deliveryEnabled = $gas->region->payment_info['gas_delivery_'.$data['delivery_method'].'_active'] ?? true;
                if (!$deliveryEnabled) {
                    throw ValidationException::withMessages(['delivery_method' => 'Metode pengambilan/pengiriman ini dinonaktifkan pengelola.']);
                }
                if ($data['payment_method'] !== 'tunai' && $request->hasFile('payment_proof')) {
                    $path = $request->file('payment_proof')->store('payment_proofs/gas', 'public');
                    abort_unless($path, 503, 'Bukti pembayaran gagal disimpan.');
                }
                $order = new GasOrder([
                    'order_number' => 'GAS-'.Str::ulid(), 'user_id' => $user->id, 'gas_id' => $gas->id,
                    'item_name' => $gas->jenis_gas, 'quantity' => $data['quantity'], 'price' => $gas->harga_satuan,
                    'order_date' => now(), 'delivery_method' => $data['delivery_method'], 'payment_method' => ucfirst($data['payment_method']),
                    'address' => $data['buyer_address'], 'full_name' => $data['buyer_name'], 'email' => $user->email,
                    'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null,
                    'status' => 'pending', 'proof_of_payment' => $path,
                ]);
                $order->idempotency_key = $key;
                $order->submission_hash = $hash;
                $order->nomor_kk_hash = $family?->no_kk_hash;
                $order->save();
                $total = $gas->harga_satuan * $data['quantity'];
                TransactionReceipt::create([
                    'booking_type' => 'gas', 'booking_id' => $order->id, 'receipt_number' => 'GAS-'.Str::ulid(),
                    'user_id' => $user->id, 'region_id' => $gas->region_id, 'item_name' => $gas->jenis_gas,
                    'quantity' => $order->quantity, 'total_amount' => $total, 'payment_method' => $data['payment_method'],
                ]);
                WalletTransaction::catatPemasukan($gas->region_id, 'gas', $order->id, $total, $data['payment_method'], $path);
                AdminNotification::create([
                    'type' => 'gas_order', 'reference_id' => $order->id, 'region_id' => $gas->region_id,
                    'title' => 'Pesanan Gas Baru', 'message' => 'Pesanan '.$order->order_number.' menunggu pemeriksaan.', 'is_read' => false,
                ]);
                NotificationService::notifyOrderCreated('gas', $order, $gas->jenis_gas.' ('.$order->quantity.' tabung)');
                return $this->result($order);
            });
        } catch (\Throwable $e) {
            if ($path) Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    private function result(GasOrder $order): array
    {
        $receipt = TransactionReceipt::where('booking_type', 'gas')->where('booking_id', $order->id)->firstOrFail();
        return ['success' => true, 'message' => 'Pesanan gas tersimpan. Pengelola akan meninjaunya.',
            'order_id' => $order->id, 'receipt_id' => $receipt->id, 'receipt_number' => $receipt->receipt_number,
            'payment_url' => route('user.gas.payment', $order->id)];
    }
}
