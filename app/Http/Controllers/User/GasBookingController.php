<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;
use App\Models\SystemSetting;
use App\Models\TransactionReceipt;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GasBookingController extends Controller
{
    /**
     * Store gas order
     */
    public function store(Request $request)
    {
        return response()->json(app(\App\Services\GasOrdering::class)->store($request));
    }

    public function payment($id)
    {
        $order = GasOrder::where('user_id', Auth::id())->findOrFail($id);
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($order->gas?->region_id);
        $paid = $order->paymentReceived();
        return view('users.gas-payment', compact('order', 'setting', 'paid'));
    }

    public function paymentPending($id)
    {
        $order = GasOrder::where('user_id', Auth::id())->findOrFail($id);
        return redirect()->route('user.gas.payment', $order->id);
    }

    public function simulatePayment($id)
    {
        abort(404); // Real admin actions are used in tests too; never bypass stock/payment rules.
    }

    public function cancelPayment($id)
    {
        return app(\App\Services\GasOrderActions::class)->cancelUnpaid(request(), (int) $id);
    }

}
