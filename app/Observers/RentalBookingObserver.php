<?php

namespace App\Observers;

use App\Models\RentalBooking;
use App\Services\ReceiptGeneratorService;
use Illuminate\Support\Facades\Log;

class RentalBookingObserver
{
    protected $receiptGenerator;

    public function __construct(ReceiptGeneratorService $receiptGenerator)
    {
        $this->receiptGenerator = $receiptGenerator;
    }

    /**
     * Handle the RentalBooking "created" event.
     */
    public function created(RentalBooking $booking): void
    {
        $this->generateReceipt($booking);
    }

    /**
     * Handle the RentalBooking "updated" event.
     */
    public function updated(RentalBooking $booking): void
    {
        // Regenerate receipt when important fields change
        if ($booking->isDirty(['status', 'confirmed_at', 'delivery_time', 'completion_time', 'return_time'])) {
            $this->generateReceipt($booking);
        }
    }

    /**
     * Generate receipt for booking
     */
    protected function generateReceipt(RentalBooking $booking): void
    {
        try {
            $path = $this->receiptGenerator->generateRentalReceipt($booking);
            
            // Update receipt path without triggering observer again
            $booking->updateQuietly(['receipt_path' => $path]);
            
            Log::info('Receipt generated for rental booking: ' . $booking->order_number);
        } catch (\Exception $e) {
            Log::error('Failed to generate receipt for rental booking ' . $booking->order_number . ': ' . $e->getMessage());
        }
    }
}
