<?php

namespace App\Models\Concerns;

use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Schema;

trait HasPaymentSummary
{
    abstract public function walletReferenceType(): string;

    abstract public function subtotalAmount(): float;

    public function walletEntries()
    {
        return $this->hasMany(WalletTransaction::class, 'reference_id')
            ->where('reference_type', $this->walletReferenceType());
    }

    protected function paymentRegionId(): ?int
    {
        if (! empty($this->region_id)) {
            return (int) $this->region_id;
        }

        return match ($this->walletReferenceType()) {
            'rental' => $this->barang?->region_id ? (int) $this->barang->region_id : null,
            'gas' => $this->gas?->region_id ? (int) $this->gas->region_id : null,
            'mobil' => $this->mobil?->region_id ? (int) $this->mobil->region_id : null,
            'fasilitas' => $this->fasilitas?->region_id ? (int) $this->fasilitas->region_id : null,
            default => null,
        };
    }

    public function paymentReceived(): bool
    {
        $subtotal = $this->subtotalAmount();
        if ($subtotal <= 0.0) {
            return true;
        }

        if (! Schema::hasTable('wallet_transactions')) {
            return false;
        }

        $entries = $this->relationLoaded('walletEntries')
            ? $this->walletEntries
            : $this->walletEntries()->get();

        if ($entries->isEmpty()) {
            return false;
        }

        if (abs((float) $entries->sum('amount') - $subtotal) >= 0.005) {
            return false;
        }

        $regionId = $this->paymentRegionId();

        return $entries->every(function ($entry) use ($regionId) {
            if ($entry->status !== 'verified') {
                return false;
            }

            if ($regionId !== null && (int) $entry->region_id !== $regionId) {
                return false;
            }

            return true;
        });
    }

    /**
     * Nominal uang yang BENAR-BENAR telah dikonfirmasi/diterima pengelola.
     * - Lunas (verified): mengembalikan nominal terverifikasi (atau amount ledger).
     * - Kurang bayar (pending + verified_amount > 0): mengembalikan nominal parsial yang sudah masuk.
     * - Belum dibayar / ditolak / dibatalkan: mengembalikan 0.
     */
    public function paidAmount(): float
    {
        $subtotal = $this->subtotalAmount();
        if ($subtotal <= 0.0) {
            return 0.0;
        }

        if (! Schema::hasTable('wallet_transactions')) {
            return 0.0;
        }

        $entries = $this->relationLoaded('walletEntries')
            ? $this->walletEntries
            : $this->walletEntries()->get();

        if ($entries->isEmpty()) {
            return 0.0;
        }

        if ($this->paymentReceived()) {
            $verifiedSum = (float) $entries->sum(function ($entry) {
                $v = $entry->getAttribute('verified_amount');

                return $v !== null ? (float) $v : (float) $entry->amount;
            });

            return round(max(0.0, $verifiedSum), 2);
        }

        if (in_array($this->status, ['cancelled', 'rejected'], true)) {
            return 0.0;
        }

        // Nominal kurang bayar yang sudah dicatat pengelola sementara menunggu pelunasan.
        $partial = (float) $entries
            ->where('status', 'pending')
            ->sum(fn ($entry) => (float) ($entry->getAttribute('verified_amount') ?? 0));

        return round(min($subtotal, max(0.0, $partial)), 2);
    }

    public function remainingAmount(): float
    {
        $subtotal = $this->subtotalAmount();
        if ($subtotal <= 0.0) {
            return 0.0;
        }

        return round(max(0.0, $subtotal - $this->paidAmount()), 2);
    }

    public function paymentStatusLabel(): string
    {
        if ($this->subtotalAmount() <= 0.0) {
            return 'Gratis';
        }

        if (in_array($this->status, ['cancelled', 'rejected'], true)) {
            return 'Dibatalkan';
        }

        if ($this->paymentReceived()) {
            return 'Lunas';
        }

        if ($this->paidAmount() > 0.0) {
            return 'Kurang Bayar';
        }

        return 'Belum Lunas';
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->paymentStatusLabel()) {
            'Lunas' => 'bg-label-success',
            'Kurang Bayar' => 'bg-label-danger',
            'Gratis' => 'bg-label-info',
            'Dibatalkan' => 'bg-label-secondary',
            default => 'bg-label-warning',
        };
    }

    public function paymentProofPath(): ?string
    {
        return $this->walletReferenceType() === 'gas'
            ? ($this->proof_of_payment ?: null)
            : ($this->payment_proof ?: null);
    }

    /**
     * Ringkasan hasil pindai bukti transfer (OCR / Vision) dari ledger terbaru.
     */
    public function proofScanSummary(): ?array
    {
        if (! Schema::hasTable('wallet_transactions') || ! WalletTransaction::hasVerificationColumns()) {
            return null;
        }

        $entries = $this->relationLoaded('walletEntries')
            ? $this->walletEntries
            : $this->walletEntries()->get();

        $entry = $entries->sortByDesc('id')->first();
        if (! $entry) {
            return null;
        }

        $status = $entry->getAttribute('proof_scan_status');
        $detected = $entry->getAttribute('detected_proof_amount');
        $meta = $entry->getAttribute('proof_scan_meta');
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }
        $meta = is_array($meta) ? $meta : [];

        if ($status === null && $detected === null && empty($meta)) {
            return null;
        }

        return [
            'status' => $status,
            'detected_amount' => $detected !== null ? (float) $detected : null,
            'shortfall' => (float) ($meta['shortfall'] ?? 0),
            'excess' => (float) ($meta['excess'] ?? 0),
            'remaining_target' => (float) ($meta['remaining_target'] ?? $this->remainingAmount()),
            'bank' => $meta['bank'] ?? null,
            'message' => $meta['message'] ?? null,
            'scanned_at' => $meta['scanned_at'] ?? null,
        ];
    }

    public function latestPaymentNote(): ?string
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return null;
        }

        $entries = $this->relationLoaded('walletEntries')
            ? $this->walletEntries
            : $this->walletEntries()->get();

        $entry = $entries->sortByDesc('id')->first();

        return $entry && ! empty($entry->notes) ? (string) $entry->notes : null;
    }
}
