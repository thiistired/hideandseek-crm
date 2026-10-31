<?php

namespace App\Policies;

use App\Models\MembershipTier;
use App\Models\User;

class MembershipTierPolicy
{
    private function isManager(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, MembershipTier $record): bool { return true; }

    public function create(User $user): bool { return $this->isManager($user); }

    public function update(User $user, MembershipTier $record): bool { return $this->isManager($user); }

    public function delete(User $user, MembershipTier $record): bool { return $this->isManager($user); }

    public function deleteAny(User $user): bool { return false; }

    public function restore(User $user, MembershipTier $record): bool { return false; }

    public function forceDelete(User $user, MembershipTier $record): bool { return false; }
}
