<?php

namespace App\Policies;

use App\Models\DisabilityPlacard;
use App\Models\User;

class DisabilityPlacardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, DisabilityPlacard $disabilityPlacard): bool
    {
        if ($user->tribe_id !== $disabilityPlacard->tribe_id) {
            return false;
        }

        return $user->isStaff() || $disabilityPlacard->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, DisabilityPlacard $disabilityPlacard): bool
    {
        if ($user->tribe_id !== $disabilityPlacard->tribe_id) {
            return false;
        }

        return $user->isStaff();
    }

    public function delete(User $user, DisabilityPlacard $disabilityPlacard): bool
    {
        if ($user->tribe_id !== $disabilityPlacard->tribe_id) {
            return false;
        }

        return $user->isStaff() || ($disabilityPlacard->user_id === $user->id && $disabilityPlacard->status === 'pending');
    }

    public function restore(User $user, DisabilityPlacard $disabilityPlacard): bool
    {
        return false;
    }

    public function forceDelete(User $user, DisabilityPlacard $disabilityPlacard): bool
    {
        return false;
    }
}
