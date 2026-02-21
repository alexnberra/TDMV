<?php

namespace App\Policies;

use App\Models\HouseholdMember;
use App\Models\User;

class HouseholdMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, HouseholdMember $householdMember): bool
    {
        $household = $householdMember->household;

        if (! $household || $user->tribe_id !== $household->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $household->owner_user_id === $user->id
            || $householdMember->user_id === $user->id
            || $household->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, HouseholdMember $householdMember): bool
    {
        $household = $householdMember->household;

        if (! $household || $user->tribe_id !== $household->tribe_id) {
            return false;
        }

        return $user->isStaff() || $household->owner_user_id === $user->id;
    }

    public function delete(User $user, HouseholdMember $householdMember): bool
    {
        return $this->update($user, $householdMember);
    }

    public function restore(User $user, HouseholdMember $householdMember): bool
    {
        return false;
    }

    public function forceDelete(User $user, HouseholdMember $householdMember): bool
    {
        return false;
    }
}
