<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Sửa khách hàng';

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()->label('Xem')];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Đã lưu thay đổi';
    }
}
