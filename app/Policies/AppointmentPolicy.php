<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->tribe_id !== $appointment->tribe_id) {
            return false;
        }

        if ($user->isStaff() || $appointment->user_id === $user->id) {
            return true;
        }

        if (! $appointment->household_id) {
            return false;
        }

        return $appointment->household
            ? $appointment->household->members()->where('user_id', $user->id)->exists()
            : false;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->tribe_id !== $appointment->tribe_id) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return $appointment->user_id === $user->id
            && in_array($appointment->status, ['requested', 'confirmed', 'rescheduled'], true);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->tribe_id !== $appointment->tribe_id) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return $appointment->user_id === $user->id
            && in_array($appointment->status, ['requested', 'confirmed', 'rescheduled'], true);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }
}
