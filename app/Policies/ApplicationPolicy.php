<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->user_id || $user->isStaff();
    }

    public function update(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            && in_array($application->status, ['draft', 'submitted', 'info_requested'], true);
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            && $application->status === 'draft';
    }
}
