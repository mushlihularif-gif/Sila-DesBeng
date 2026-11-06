<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketShippingService
{
    public function village(?int $regionId): ?Region
    {
        $seen = [];
        while ($regionId && !isset($seen[$regionId]) && count($seen) < 8) {
            $seen[$regionId] = true;
            $region = Region::find($regionId);
            if (!$region) return null;
            if (in_array($region->type, ['desa', 'kelurahan'], true)) return $region;
            $regionId = $region->parent_id;
        }
        return null;
    }

    public function configuration(Region $seller): array
    {
        $settings = $seller->settings ?? [];
        return $settings['market_shipping'] ?? [
            'pickup' => true, 'delivery' => true,
            'village' => ['enabled' => true, 'fee' => (int) ($settings['ongkir_dalam_desa'] ?? 0)],
            'district' => ['enabled' => true, 'fee' => (int) ($settings['ongkir_luar_desa'] ?? 10000)],
            'outside' => ['enabled' => false, 'fee' => 0],
            'overrides' => [],
        ];
    }

    public function validateSettings(array $input): array
    {
        $rules = ['pickup' => 'required|boolean', 'delivery' => 'required|boolean',
            'overrides' => 'sometimes|array|max:50',
            'overrides.*.region_id' => ['required', 'integer', 'distinct', Rule::exists('regions', 'id')->whereIn('type', ['desa', 'kelurahan', 'kecamatan'])],
            'overrides.*.fee' => 'required|integer|min:0|max:10000000'];
        foreach (['village', 'district', 'outside'] as $zone) {
            $rules[$zone.'.enabled'] = 'required|boolean';
            $rules[$zone.'.fee'] = 'required|integer|min:0|max:10000000';
        }
        $data = Validator::make($input, $rules, [
            '*.fee.required' => 'Isi ongkir atau pilih Gratis.',
            'overrides.*.region_id.distinct' => 'Wilayah yang sama cukup ditambahkan satu kali.',
        ])->validate();
        if (!$data['pickup'] && !$data['delivery']) {
            throw ValidationException::withMessages(['shipping.pickup' => 'Pilih ambil di toko, diantar oleh toko, atau keduanya.']);
        }
        $result = ['pickup' => (bool) $data['pickup'], 'delivery' => (bool) $data['delivery']];
        foreach (['village', 'district', 'outside'] as $zone) {
            $result[$zone] = ['enabled' => (bool) $data[$zone]['enabled'], 'fee' => (int) $data[$zone]['fee']];
        }
        $result['overrides'] = array_map(fn ($row) => ['region_id' => (int) $row['region_id'], 'fee' => (int) $row['fee']], $data['overrides'] ?? []);
        if ($result['delivery'] && !collect(['village', 'district', 'outside'])->contains(fn ($zone) => $result[$zone]['enabled']) && !$result['overrides']) {
            throw ValidationException::withMessages(['shipping.delivery' => 'Pilih setidaknya satu wilayah yang bisa diantar.']);
        }
        return $result;
    }

    public function quote(Region $seller, ?Region $destination, string $method): array
    {
        $config = $this->configuration($seller);
        $unavailable = ['available' => false, 'cost' => null, 'message' => $config['pickup']
            ? 'Toko belum mengantar ke alamat ini. Anda bisa memilih ambil di toko.'
            : 'Toko belum mengantar ke alamat ini. Pilih alamat lain.'];
        if ($method === 'jemput') return $config['pickup']
            ? ['available' => true, 'cost' => 0, 'message' => 'Ambil di toko — tanpa ongkir.']
            : ['available' => false, 'cost' => null, 'message' => 'Toko ini hanya melayani pengiriman.'];
        if ($method !== 'antar' || !$config['delivery'] || !$destination || !in_array($destination->type, ['desa', 'kelurahan'], true)
            || $destination->parent?->type !== 'kecamatan' || $seller->parent?->type !== 'kecamatan') return $unavailable;

        // Tarif desa mengalahkan tarif kecamatan. Tarif tidak dijumlahkan.
        foreach ([$destination->id, $destination->parent_id] as $id) {
            foreach ($config['overrides'] as $row) {
                if ((int) $row['region_id'] === (int) $id) return ['available' => true, 'cost' => (int) $row['fee'], 'message' => 'Ongkir sesuai wilayah tujuan.'];
            }
        }
        $zone = (int) $destination->id === (int) $seller->id ? 'village'
            : ((int) $destination->parent_id === (int) $seller->parent_id ? 'district' : 'outside');
        return $config[$zone]['enabled']
            ? ['available' => true, 'cost' => (int) $config[$zone]['fee'], 'message' => 'Ongkir sesuai wilayah tujuan.'] : $unavailable;
    }
}
