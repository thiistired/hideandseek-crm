<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    private function isManager(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Customer $record): bool { return true; }

    public function create(User $user): bool { return true; }

    public function update(User $user, Customer $record): bool { return true; }

    public function delete(User $user, Customer $record): bool { return $this->isManager($user); }

    public function deleteAny(User $user): bool { return false; }

    public function restore(User $user, Customer $record): bool { return false; }

    public function forceDelete(User $user, Customer $record): bool { return false; }
}
