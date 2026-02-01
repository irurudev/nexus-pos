<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Only admin can view any users
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Only admin can view a user
     */
    public function view(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Only admin can create user
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Only admin can update user
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Only admin can delete user
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }
}
