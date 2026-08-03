<?php

namespace App\Policies;

use App\Models\TravelApproval;
use App\Models\User;

class TravelApprovalPolicy
{
    /**
     * All admin roles can view the approvals list.
     * Filtering to the approver's own records is handled
     * in TravelApprovalResource via modifyQueryUsing.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Assignees can view their approval; HR/super_admin can view any.
     */
    public function view(User $user, TravelApproval $travelApproval): bool
    {
        return $this->canActOn($user, $travelApproval);
    }

    /**
     * Approvals are created programmatically by the system.
     * No user should create them manually.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Only the assigned approver (or HR/super_admin override) may update.
     */
    public function update(User $user, TravelApproval $travelApproval): bool
    {
        return $this->canActOn($user, $travelApproval);
    }

    private function canActOn(User $user, TravelApproval $travelApproval): bool
    {
        if ($user->isHR() || $user->isSuperAdmin()) {
            return true;
        }

        return (int) $travelApproval->approver_id === (int) $user->id;
    }

    /**
     * Approvals are never deleted manually.
     */
    public function delete(User $user, TravelApproval $travelApproval): bool
    {
        return false;
    }

    public function restore(User $user, TravelApproval $travelApproval): bool
    {
        return false;
    }

    public function forceDelete(User $user, TravelApproval $travelApproval): bool
    {
        return false;
    }
}