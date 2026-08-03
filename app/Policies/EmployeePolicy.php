<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * All admin roles can view employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * All admin roles can view a single employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Super Admin can create employees.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isHR();
    }

    /**
     * Only Super Admin can update employees.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $user->isSuperAdmin() || $user->isHR();
    }

    /**
     * Only Super Admin can delete employees.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->isSuperAdmin() || $user->isHR();
    }

    public function restore(User $user, Employee $employee): bool
    {
        return false;
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }
}