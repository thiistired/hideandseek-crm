<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmTransactionItem extends Model
{
    protected $guarded = [];

    // Bảng crm_transaction_items chỉ có created_at
    const UPDATED_AT = null;

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CrmTransaction::class, 'transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}