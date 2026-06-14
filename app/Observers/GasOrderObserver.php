<?php

namespace App\Observers;

use App\Models\GasOrder;
use App\Services\ReceiptGeneratorService;
use Illuminate\Support\Facades\Log;

class GasOrderObserver
{
    protected $receiptGenerator;

    public function __construct(ReceiptGeneratorService $receiptGenerator)
    {
        $this->receiptGenerator = $receiptGenerator;
    }

    /**
     * Handle the GasOrder "created" event.
     */
    public function created(GasOrder $order): void
    {
        $this->generateReceipt($order);
    }

    /**
     * Handle the GasOrder "updated" event.
     */
    public function updated(GasOrder $order): void
    {
        // Regenerate receipt when important fields change
        if ($order->isDirty(['status', 'confirmed_at', 'delivery_time', 'completion_time'])) {
            $this->generateReceipt($order);
        }
    }

    /**
     * Generate receipt for order
     */
    protected function generateReceipt(GasOrder $order): void
    {
        try {
            $path = $this->receiptGenerator->generateGasReceipt($order);
            
            // Update receipt path without triggering observer again
            $order->updateQuietly(['receipt_path' => $path]);
            
            Log::info('Receipt generated for gas order: ' . $order->order_number);
        } catch (\Exception $e) {
            Log::error('Failed to generate receipt for gas order ' . $order->order_number . ': ' . $e->getMessage());
        }
    }
}
