<?php

namespace App\Policies;

use App\Models\RentalBooking;
use App\Models\User;

class RentalBookingPolicy
{
    /**
     * Determine whether the user can view the booking.
     * Hanya pemilik booking atau admin yang bisa melihat.
     */
    public function view(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the booking.
     * Hanya pemilik booking atau admin yang bisa mengubah.
     */
    public function update(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the booking.
     * Hanya pemilik booking atau admin yang bisa menghapus.
     */
    public function delete(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can cancel the booking.
     * Hanya pemilik booking yang bisa membatalkan, dan booking harus bisa dibatalkan.
     */
    public function cancel(User $user, RentalBooking $booking): bool
    {
        return $user->id === $booking->user_id && $booking->canBeCancelled();
    }

    /**
     * Determine whether the user can verify the booking.
     * Hanya admin yang bisa memverifikasi booking.
     */
    public function verify(User $user, RentalBooking $booking): bool
    {
        return $user->role === 'admin';
    }
}
