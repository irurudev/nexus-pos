<?php

namespace App\Policies;

use App\Models\Barang;
use App\Models\User;

class BarangPolicy
{
    public function viewAny(?User $user): bool
    {
        // Anyone authenticated can list/view
        return $user !== null;
    }

    public function view(?User $user, Barang $barang): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Barang $barang): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Barang $barang): bool
    {
        return $user->role === 'admin';
    }
}
