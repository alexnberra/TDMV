<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleInspection;

class VehicleInspectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, VehicleInspection $vehicleInspection): bool
    {
        if ($user->tribe_id !== $vehicleInspection->tribe_id) {
            return false;
        }

        return $user->isStaff()
            || $vehicleInspection->user_id === $user->id
            || $vehicleInspection->vehicle->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, VehicleInspection $vehicleInspection): bool
    {
        if ($user->tribe_id !== $vehicleInspection->tribe_id) {
            return false;
        }

        return $user->isStaff() || $vehicleInspection->user_id === $user->id;
    }

    public function delete(User $user, VehicleInspection $vehicleInspection): bool
    {
        return $this->update($user, $vehicleInspection);
    }

    public function restore(User $user, VehicleInspection $vehicleInspection): bool
    {
        return false;
    }

    public function forceDelete(User $user, VehicleInspection $vehicleInspection): bool
    {
        return false;
    }
}
