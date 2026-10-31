<?php

namespace App\Filament\Resources\CrmTransactions\Pages;

use App\Filament\Resources\CrmTransactions\CrmTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrmTransactions extends ListRecords
{
    protected static string $resource = CrmTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
