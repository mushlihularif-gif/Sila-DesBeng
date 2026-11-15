<?php

namespace App\Support;

use App\Models\ManualReport;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;

class FinanceRecords
{
    public const TYPES = ['rental' => 'penyewaan', 'gas' => 'gas', 'mobil' => 'mobil', 'fasilitas' => 'fasilitas', 'pasar' => 'pasar'];

    public static function authorize(bool $write = false): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->role === 'admin_desa' && $user->region_id
            || (! $write && $user->role === 'super_admin')), 403);
    }

    public static function payments(?string $type = null): Builder
    {
        self::authorize();

        return WalletTransaction::query()->where('status', 'verified')
            ->whereIn('type', ['masuk', 'ditahan', 'dilepas'])
            ->whereIn('reference_type', array_keys(self::TYPES))
            ->when($type, fn ($q) => $q->where('reference_type', $type))
            ->when(auth()->user()->role !== 'super_admin', fn ($q) => $q->where('region_id', auth()->user()->region_id));
    }

    public static function manual(): Builder
    {
        self::authorize();

        // Historical rows without region_id fall back to their creator. Once set,
        // the owning region never follows the creator's subsequent relocation.
        return ManualReport::query()->when(auth()->user()->role !== 'super_admin', function ($q) {
            $region = auth()->user()->region_id;
            $q->where(fn ($q) => $q->where('region_id', $region)->orWhere(fn ($q) => $q->whereNull('region_id')
                ->whereHas('creator', fn ($q) => $q->where('region_id', $region))));
        });
    }

    public static function summary(int $year, ?int $month = null): array
    {
        $paid = self::payments()->whereYear('created_at', $year)
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->selectRaw('reference_type, SUM(amount) as revenue, COUNT(DISTINCT reference_id) as transactions')
            ->groupBy('reference_type')->get()->keyBy('reference_type');
        $manual = self::manual()->whereYear('transaction_date', $year)
            ->when($month, fn ($q) => $q->whereMonth('transaction_date', $month))
            ->selectRaw('category, SUM(amount * quantity) as revenue, COUNT(*) as transactions')
            ->groupBy('category')->get()->keyBy('category');
        $result = [];
        foreach (self::TYPES + ['lainnya' => 'lainnya'] as $type => $category) {
            $result[$type] = ['revenue' => (float) ($paid->get($type)?->revenue ?? 0) + (float) ($manual->get($category)?->revenue ?? 0),
                'transactions' => (int) ($paid->get($type)?->transactions ?? 0) + (int) ($manual->get($category)?->transactions ?? 0)];
        }
        $total = array_sum(array_column($result, 'revenue'));
        foreach ($result as &$row) {
            $row['percentage'] = $total > 0 ? round($row['revenue'] / $total * 100, 1) : 0;
        }
        unset($row);
        $result['total'] = ['revenue' => $total, 'transactions' => array_sum(array_column($result, 'transactions'))];

        return $result + ['year' => $year, 'month' => $month];
    }
}
