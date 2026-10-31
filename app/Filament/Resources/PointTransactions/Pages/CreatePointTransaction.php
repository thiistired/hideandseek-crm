<?php

namespace App\Filament\Resources\PointTransactions\Pages;

use App\Filament\Resources\PointTransactions\PointTransactionResource;
use App\Models\Customer;
use App\Models\PointTransaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePointTransaction extends CreateRecord
{
    protected static string $resource = PointTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::lockForUpdate()->findOrFail($data['customer_id']);

            if ($customer->current_points + $data['points_change'] < 0) {
                throw ValidationException::withMessages([
                    'data.points_change' => 'Điểm sau điều chỉnh không được âm.',
                ]);
            }

            $record = PointTransaction::create($data);
            $customer->increment('current_points', $data['points_change']);

            return $record;
        });
    }
}