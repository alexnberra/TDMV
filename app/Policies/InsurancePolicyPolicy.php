<?php

namespace App\Policies;

use App\Models\InsurancePolicy;
use App\Models\User;

class InsurancePolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, InsurancePolicy $insurancePolicy): bool
    {
        if ($user->tribe_id !== $insurancePolicy->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $insurancePolicy->user_id === $user->id
            || $insurancePolicy->vehicle->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, InsurancePolicy $insurancePolicy): bool
    {
        if ($user->tribe_id !== $insurancePolicy->tribe_id) {
            return false;
        }

        return $user->isStaff() || $insurancePolicy->user_id === $user->id;
    }

    public function delete(User $user, InsurancePolicy $insurancePolicy): bool
    {
        if ($user->tribe_id !== $insurancePolicy->tribe_id) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return $insurancePolicy->user_id === $user->id && ! $insurancePolicy->is_verified;
    }

    public function restore(User $user, InsurancePolicy $insurancePolicy): bool
    {
        return false;
    }

    public function forceDelete(User $user, InsurancePolicy $insurancePolicy): bool
    {
        return false;
    }
}
