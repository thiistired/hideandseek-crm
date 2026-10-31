<?php

namespace App\Filament\Resources\CrmTransactions\Pages;

use App\Filament\Resources\CrmTransactions\CrmTransactionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCrmTransaction extends ViewRecord
{
    protected static string $resource = CrmTransactionResource::class;

    // Giao dịch chỉ xem, không sửa, không hủy (đúng phạm vi MVP)
    protected function getHeaderActions(): array
    {
        return [];
    }
}
