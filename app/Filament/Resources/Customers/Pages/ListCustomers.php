<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Khách hàng';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Thêm khách hàng')];
    }

    // Hiển thị đủ 20 khách trên một trang để chụp ảnh và kiểm thử
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
