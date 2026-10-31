<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    private function isManager(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Category $record): bool { return true; }

    public function create(User $user): bool { return false; }

    public function update(User $user, Category $record): bool { return false; }

    public function delete(User $user, Category $record): bool { return false; }

    public function deleteAny(User $user): bool { return false; }

    public function restore(User $user, Category $record): bool { return false; }

    public function forceDelete(User $user, Category $record): bool { return false; }
}
