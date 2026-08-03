<?php

namespace App\Policies;

use App\Models\TravelSource;
use App\Models\User;

class TravelSourcePolicy
{
    /**
     * All admin roles can view travel sources.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * All admin roles can view a single travel source.
     */
    public function view(User $user, TravelSource $travelSource): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Super Admin can create travel sources.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only Super Admin can update travel sources.
     */
    public function update(User $user, TravelSource $travelSource): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Only Super Admin can delete travel sources.
     */
    public function delete(User $user, TravelSource $travelSource): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, TravelSource $travelSource): bool
    {
        return false;
    }

    public function forceDelete(User $user, TravelSource $travelSource): bool
    {
        return false;
    }
}