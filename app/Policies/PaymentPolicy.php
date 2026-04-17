<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('payments.viewAny', 'web');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.view', 'web') && $payment->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payments.create', 'web');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.update', 'web') && $payment->user_id === $user->id;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.delete', 'web') && $payment->user_id === $user->id;
    }
}
