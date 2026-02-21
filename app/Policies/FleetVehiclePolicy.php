<?php

namespace App\Policies;

use App\Models\FleetVehicle;
use App\Models\User;

class FleetVehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, FleetVehicle $fleetVehicle): bool
    {
        if ($user->tribe_id !== $fleetVehicle->businessAccount->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $fleetVehicle->businessAccount->owner_user_id === $user->id
            || $fleetVehicle->businessAccount->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, FleetVehicle $fleetVehicle): bool
    {
        return $this->delete($user, $fleetVehicle);
    }

    public function delete(User $user, FleetVehicle $fleetVehicle): bool
    {
        if ($user->tribe_id !== $fleetVehicle->businessAccount->tribe_id) {
            return false;
        }

        return $user->isStaff() || $fleetVehicle->businessAccount->owner_user_id === $user->id;
    }

    public function restore(User $user, FleetVehicle $fleetVehicle): bool
    {
        return false;
    }

    public function forceDelete(User $user, FleetVehicle $fleetVehicle): bool
    {
        return false;
    }
}
