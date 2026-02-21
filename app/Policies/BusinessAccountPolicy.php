<?php

namespace App\Policies;

use App\Models\BusinessAccount;
use App\Models\User;

class BusinessAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, BusinessAccount $businessAccount): bool
    {
        if ($user->tribe_id !== $businessAccount->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $businessAccount->owner_user_id === $user->id
            || $businessAccount->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, BusinessAccount $businessAccount): bool
    {
        if ($user->tribe_id !== $businessAccount->tribe_id) {
            return false;
        }

        return $user->isStaff() || $businessAccount->owner_user_id === $user->id;
    }

    public function delete(User $user, BusinessAccount $businessAccount): bool
    {
        return $this->update($user, $businessAccount);
    }

    public function restore(User $user, BusinessAccount $businessAccount): bool
    {
        return false;
    }

    public function forceDelete(User $user, BusinessAccount $businessAccount): bool
    {
        return false;
    }
}
