<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('customer_code')->label('Mã khách hàng'),
            TextEntry::make('full_name')->label('Họ và tên'),
            TextEntry::make('phone')->label('Số điện thoại'),
            TextEntry::make('email')->label('Email')->placeholder('—'),
            TextEntry::make('gender')->label('Giới tính')
                ->formatStateUsing(fn (?string $state) => match ($state) {
                    'male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác', default => '—',
                }),
            TextEntry::make('date_of_birth')->label('Ngày sinh')->date('d/m/Y')->placeholder('—'),
            TextEntry::make('address')->label('Địa chỉ')->placeholder('—')->columnSpanFull(),
            TextEntry::make('membershipTier.name')->label('Hạng thành viên')->badge(),
            TextEntry::make('current_points')->label('Điểm hiện tại'),
            TextEntry::make('total_spent')->label('Tổng chi tiêu (giao dịch hoàn tất)')
                ->state(fn ($record) => (float) $record->crmTransactions()->where('status', 'completed')->sum('total_amount'))
                ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')->suffix(' đ'),
            TextEntry::make('created_at')->label('Ngày tạo')->dateTime('d/m/Y H:i'),
        ]);
    }
}
