<?php

namespace App\Filament\Resources\CrmTransactions\Pages;

use App\Filament\Resources\CrmTransactions\CrmTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCrmTransaction extends EditRecord
{
    protected static string $resource = CrmTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
