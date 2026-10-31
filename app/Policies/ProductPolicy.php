<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    private function isManager(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Product $record): bool { return true; }

    public function create(User $user): bool { return false; }

    public function update(User $user, Product $record): bool { return false; }

    public function delete(User $user, Product $record): bool { return false; }

    public function deleteAny(User $user): bool { return false; }

    public function restore(User $user, Product $record): bool { return false; }

    public function forceDelete(User $user, Product $record): bool { return false; }
}
