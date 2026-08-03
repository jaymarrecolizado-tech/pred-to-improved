<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;

class DivisionPolicy
{
    /**
     * All admin roles can view divisions.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * All admin roles can view a single division.
     */
    public function view(User $user, Division $division): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Super Admin can create divisions.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only Super Admin can update divisions.
     */
    public function update(User $user, Division $division): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only Super Admin can delete divisions.
     */
    public function delete(User $user, Division $division): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Division $division): bool
    {
        return false;
    }

    public function forceDelete(User $user, Division $division): bool
    {
        return false;
    }
}