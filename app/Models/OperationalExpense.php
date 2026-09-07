<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalExpense extends Model
{
    protected $fillable = [
        'item_name',
        'category',
        'amount',
        'billing_cycle',
        'due_date',
        'status',
        'proof_path',
        'notes',
        'paid_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Status tampilan berdasarkan jarak ke tanggal jatuh tempo.
     * Tidak menimpa kolom "status" (yang menandai lunas/belum) - ini murni untuk badge UI.
     */
    public function getDueBadgeAttribute(): string
    {
        if ($this->status === 'lunas') {
            return 'lunas';
        }

        $daysLeft = now()->startOfDay()->diffInDays($this->due_date, false);

        if ($daysLeft < 0) {
            return 'terlambat';
        }

        if ($daysLeft <= 14) {
            return 'mendekati_jatuh_tempo';
        }

        return 'aman';
    }

    /**
     * Tandai lunas dan perpanjang jatuh tempo berikutnya sesuai siklus tagihan.
     */
    public function markPaidAndRenew(int $userId): void
    {
        $nextDueDate = match ($this->billing_cycle) {
            'bulanan' => $this->due_date->copy()->addMonth(),
            'tahunan' => $this->due_date->copy()->addYear(),
            default => $this->due_date,
        };

        $this->update([
            'status' => $this->billing_cycle === 'sekali_bayar' ? 'lunas' : 'jatuh_tempo',
            'due_date' => $this->billing_cycle === 'sekali_bayar' ? $this->due_date : $nextDueDate,
            'paid_by' => $userId,
        ]);
    }
}
