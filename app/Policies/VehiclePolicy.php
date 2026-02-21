<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->owner_id || $user->isStaff();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->owner_id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->id === $vehicle->owner_id;
    }
}
