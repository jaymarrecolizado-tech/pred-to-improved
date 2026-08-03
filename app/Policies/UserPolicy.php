<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * All admin roles can view the user list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * All admin roles can view a single user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Super Admin can create users.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only Super Admin can update users.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only Super Admin can delete users.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}