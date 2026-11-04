<?php

namespace App\Filament\Resources\PointTransactions\Pages;

use App\Filament\Resources\PointTransactions\PointTransactionResource;
use App\Services\LoyaltyService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePointTransaction extends CreateRecord
{
    protected static string $resource = PointTransactionResource::class;

    // Mọi thay đổi điểm đều đi qua LoyaltyService, không ghi thẳng vào bảng
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(LoyaltyService::class)->adjustPoints(
                (int) $data['customer_id'],
                (int) $data['points_change'],
                (string) $data['reason'],
                (int) Auth::id(),
            );
        } catch (ValidationException $e) {
            throw ValidationException::withMessages([
                'data.points_change' => collect($e->errors())->flatten()->first(),
            ]);
        }
    }
}