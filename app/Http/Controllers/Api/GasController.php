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
        $user = $request->user();

        // Get gas products that are available and belong to user's region
        $query = Gas::where('status', '!=', 'rusak');

        if ($user && $user->region_id) {
            $query->where('region_id', $user->region_id);
        }

        $gasItems = $query->orderBy('created_at', 'desc')->get();

        // Format image URLs
        $gasItems->transform(function ($item) {
            $item->image_url = asset('storage/' . $item->foto);
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
    public function show($id)
    {
        $gas = Gas::find($id);

        if (!$gas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk gas tidak ditemukan'
            ], 404);
        }

        // Include region to get BUMDes info
        $gas->load('region');
        
        $gas->image_url = asset('storage/' . $gas->foto);

        return response()->json([
            'status' => 'success',
            'data' => $gas
        ]);
    }
}
