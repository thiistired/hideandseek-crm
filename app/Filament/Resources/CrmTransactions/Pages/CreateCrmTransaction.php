<?php

namespace App\Filament\Resources\CrmTransactions\Pages;

use App\Filament\Resources\CrmTransactions\CrmTransactionResource;
use App\Services\LoyaltyService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateCrmTransaction extends CreateRecord
{
    protected static string $resource = CrmTransactionResource::class;

    protected static ?string $title = 'Thêm giao dịch';

    // Thay cách lưu mặc định bằng nghiệp vụ tích điểm / xét hạng
    protected function handleRecordCreation(array $data): Model
    {
        return app(LoyaltyService::class)->recordTransaction(
            (int) $data['customer_id'],
            $data['transaction_date'],
            $data['items'] ?? [],
            $data['note'] ?? null,
            (int) Auth::id(),
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Đã ghi nhận giao dịch và cập nhật điểm, hạng';
    }
}
