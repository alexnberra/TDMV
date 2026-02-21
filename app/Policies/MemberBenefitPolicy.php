<?php

namespace App\Policies;

use App\Models\MemberBenefit;
use App\Models\User;

class MemberBenefitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, MemberBenefit $memberBenefit): bool
    {
        if ($user->tribe_id !== $memberBenefit->tribe_id) {
            return false;
        }

        return $user->isStaff() || $memberBenefit->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, MemberBenefit $memberBenefit): bool
    {
        if ($user->tribe_id !== $memberBenefit->tribe_id) {
            return false;
        }

        return $user->isStaff();
    }

    public function delete(User $user, MemberBenefit $memberBenefit): bool
    {
        return $this->update($user, $memberBenefit);
    }

    public function restore(User $user, MemberBenefit $memberBenefit): bool
    {
        return false;
    }

    public function forceDelete(User $user, MemberBenefit $memberBenefit): bool
    {
        return false;
    }
}
