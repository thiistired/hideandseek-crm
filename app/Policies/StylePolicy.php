<?php

namespace App\Policies;

use App\Models\Style;
use App\Models\User;

class StylePolicy
{
    private function isManager(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Style $record): bool { return true; }

    public function create(User $user): bool { return false; }

    public function update(User $user, Style $record): bool { return false; }

    public function delete(User $user, Style $record): bool { return false; }

    public function deleteAny(User $user): bool { return false; }

    public function restore(User $user, Style $record): bool { return false; }

    public function forceDelete(User $user, Style $record): bool { return false; }
}
