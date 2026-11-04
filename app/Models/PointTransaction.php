<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    protected $guarded = [];

    // Bảng point_transactions chỉ có created_at, không có updated_at
    const UPDATED_AT = null;

    // Lịch sử điểm chỉ được ghi thêm: không sửa, không xóa
    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Lịch sử điểm chỉ được ghi thêm, không được sửa.'));
        static::deleting(fn () => throw new \LogicException('Lịch sử điểm không được xóa.'));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function referenceTransaction(): BelongsTo
    {
        return $this->belongsTo(CrmTransaction::class, 'reference_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}