<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Services\MarketShippingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketShippingController extends Controller
{
    public function quote(Request $request, MarketShippingService $shipping)
    {
        $data = $request->validate([
            'seller_id' => 'required_without:store_id|nullable|integer',
            'store_id' => 'nullable|integer',
            'delivery_method' => 'required|in:antar,jemput',
            'delivery_region_id' => ['nullable', 'required_if:delivery_method,antar', 'integer',
                Rule::exists('regions', 'id')->whereIn('type', ['desa', 'kelurahan'])],
        ]);
        if (!empty($data['store_id'])) {
            $store=\App\Models\MarketStore::where('status','approved')->where('is_open',true)->findOrFail($data['store_id']);
            $seller=$store->shippingRegion();
        } else $seller = Region::whereIn('type', ['desa', 'kelurahan'])
            ->whereHas('parent', fn ($q) => $q->where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis'))->findOrFail($data['seller_id']);
        return response()->json($shipping->quote($seller, isset($data['delivery_region_id']) ? Region::find($data['delivery_region_id']) : null, $data['delivery_method']))
            ->header('Cache-Control', 'private, no-store');
    }
}
