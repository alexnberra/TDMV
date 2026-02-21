<?php

namespace App\Policies;

use App\Models\EmissionsTest;
use App\Models\User;

class EmissionsTestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, EmissionsTest $emissionsTest): bool
    {
        if ($user->tribe_id !== $emissionsTest->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $emissionsTest->user_id === $user->id
            || $emissionsTest->vehicle->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, EmissionsTest $emissionsTest): bool
    {
        if ($user->tribe_id !== $emissionsTest->tribe_id) {
            return false;
        }

        return $user->isStaff() || $emissionsTest->user_id === $user->id;
    }

    public function delete(User $user, EmissionsTest $emissionsTest): bool
    {
        return $this->update($user, $emissionsTest);
    }

    public function restore(User $user, EmissionsTest $emissionsTest): bool
    {
        return false;
    }

    public function forceDelete(User $user, EmissionsTest $emissionsTest): bool
    {
        return false;
    }
}
