<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Kategori;

class KategoriPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function view(?User $user, Kategori $kategori): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Kategori $kategori): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Kategori $kategori): bool
    {
        return $user->role === 'admin';
    }
}
