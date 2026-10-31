<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Thêm khách hàng';

    // Mã khách hàng KH + 4 chữ số tự sinh, duy nhất (FR01.1); hạng Member và 0 điểm là mặc định của cơ sở dữ liệu (BR01)
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $last = Customer::query()->max('customer_code');
        $number = $last ? ((int) substr($last, 2)) + 1 : 1;
        $data['customer_code'] = 'KH' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Đã thêm khách hàng';
    }
}
