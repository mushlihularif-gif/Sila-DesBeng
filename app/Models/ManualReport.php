<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualReport extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Terapkan isolasi wilayah secara otomatis untuk Admin RT/RW
        static::addGlobalScope(new \App\Models\Scopes\RegionIsolationScope('creator'));

        static::saving(function ($model) {
            // Sanitize text fields to prevent stored XSS
            if ($model->isDirty('description')) {
                $model->description = strip_tags($model->description, '<b><i><u><br><p><ul><ol><li>');
            }
            if ($model->isDirty('name')) {
                $model->name = strip_tags($model->name);
            }
        });
    }

    protected $fillable = [
        'category',
        'name',
        'description',
        'amount',
        'quantity',
        'payment_method',
        'transaction_date',
        'created_by',
        'proof_image'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'quantity' => 'integer'
    ];

    /**
     * Relasi: Laporan manual milik pengguna (pembuat)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cakupan: Filter berdasarkan kategori
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Cakupan: Filter berdasarkan rentang tanggal
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Cakupan: Filter berdasarkan tahun ini
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('transaction_date', date('Y'));
    }

    /**
     * Aksesor: Ambil jumlah terformat
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Aksesor: Ambil total (jumlah * kuantitas)
     */
    public function getTotalAttribute()
    {
        return $this->amount * $this->quantity;
    }

    /**
     * Aksesor: Ambil total terformat
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Aksesor: Ambil warna badge kategori
     */
    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'penyewaan' => 'warning',
            'gas' => 'danger',
            'lainnya' => 'info'
        ];

        return $badges[$this->category] ?? 'secondary';
    }

    /**
     * Aksesor: Ambil label kategori
     */
    public function getCategoryLabelAttribute()
    {
        $labels = [
            'penyewaan' => 'Penyewaan Alat',
            'gas' => 'Penjualan Gas',
            'lainnya' => 'Lainnya'
        ];

        return $labels[$this->category] ?? $this->category;
    }
}
