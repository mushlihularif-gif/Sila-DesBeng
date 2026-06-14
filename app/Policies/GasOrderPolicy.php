<?php

namespace App\Policies;

use App\Models\GasOrder;
use App\Models\User;

class GasOrderPolicy
{
    /**
     * Determine whether the user can view the gas order.
     * Hanya pemilik order atau admin yang bisa melihat.
     */
    public function view(User $user, GasOrder $order): bool
    {
        return $user->id === $order->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the gas order.
     * Hanya pemilik order atau admin yang bisa mengubah.
     */
    public function update(User $user, GasOrder $order): bool
    {
        return $user->id === $order->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the gas order.
     * Hanya pemilik order atau admin yang bisa menghapus.
     */
    public function delete(User $user, GasOrder $order): bool
    {
        return $user->id === $order->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can cancel the gas order.
     * Hanya pemilik order yang bisa membatalkan, dan order harus bisa dibatalkan.
     */
    public function cancel(User $user, GasOrder $order): bool
    {
        return $user->id === $order->user_id && $order->canBeCancelled();
    }

    /**
     * Determine whether the user can verify the gas order.
     * Hanya admin yang bisa memverifikasi order.
     */
    public function verify(User $user, GasOrder $order): bool
    {
        return $user->role === 'admin';
    }
}
