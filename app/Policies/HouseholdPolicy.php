<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

class HouseholdPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Household $household): bool
    {
        if ($user->tribe_id !== $household->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $household->owner_user_id === $user->id
            || $household->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Household $household): bool
    {
        if ($user->tribe_id !== $household->tribe_id) {
            return false;
        }

        return $user->isStaff() || $household->owner_user_id === $user->id;
    }

    public function delete(User $user, Household $household): bool
    {
        return $this->update($user, $household);
    }

    public function restore(User $user, Household $household): bool
    {
        return false;
    }

    public function forceDelete(User $user, Household $household): bool
    {
        return false;
    }
}
