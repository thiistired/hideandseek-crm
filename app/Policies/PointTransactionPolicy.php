<?php

namespace App\Policies;

use App\Models\PointTransaction;
use App\Models\User;

/** Lịch sử điểm chỉ để xem; điểm chỉ thay đổi qua LoyaltyService. */
class PointTransactionPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, PointTransaction $record): bool { return true; }
    public function create(User $user): bool { return false; }
    public function update(User $user, PointTransaction $record): bool { return false; }
    public function delete(User $user, PointTransaction $record): bool { return false; }
    public function deleteAny(User $user): bool { return false; }
    public function restore(User $user, PointTransaction $record): bool { return false; }
    public function forceDelete(User $user, PointTransaction $record): bool { return false; }
}