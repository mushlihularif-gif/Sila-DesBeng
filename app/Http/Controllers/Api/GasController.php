<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use Illuminate\Http\Request;

class GasController extends Controller
{
    /**
     * Get list of gas products available for the user's region
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        // Get gas products that are available and belong to user's region
        $query = app(\App\Services\GasOrdering::class)->visibleQuery($user);

        $gasItems = $query->orderBy('created_at', 'desc')->get();

        // Format image URLs
        $gasItems->transform(function ($item) {
            $item->image_url = $item->foto ? asset('storage/' . $item->foto) : null;
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $gasItems
        ]);
    }

    /**
     * Get specific gas product details
     */
    public function show(Request $request, $id)
    {
        $gas = app(\App\Services\GasOrdering::class)->visibleQuery($request->user('sanctum') ?? $request->user())->find($id);

        if (!$gas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk gas tidak ditemukan'
            ], 404);
        }

        // Include region & public payment instructions for mobile checkout
        $gas->load('region:id,name,type');
        $paymentInfo = \App\Support\ProfilPembayaranWilayah::untuk($gas->region_id)->publik();
        $gas->payment_info = $paymentInfo;
        if ($gas->region) {
            $gas->region->setAttribute('payment_info', $paymentInfo);
        }
        
        $gas->image_url = $gas->foto ? asset('storage/' . $gas->foto) : null;

        return response()->json([
            'status' => 'success',
            'data' => $gas
        ]);
    }
}
