<?php

namespace App\Policies;

use App\Models\Penjualan;
use App\Models\User;

class PenjualanPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function view(?User $user, Penjualan $penjualan): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        // both admin and kasir allowed to create penjualan
        return in_array($user->role, ['admin', 'kasir']);
    }

    public function update(User $user, Penjualan $penjualan): bool
    {
        // only admin can update/delete penjualan
        return $user->role === 'admin';
    }

    public function delete(User $user, Penjualan $penjualan): bool
    {
        return $user->role === 'admin';
    }
}
