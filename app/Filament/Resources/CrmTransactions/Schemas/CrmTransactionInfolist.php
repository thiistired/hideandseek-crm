<?php

namespace App\Filament\Resources\CrmTransactions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CrmTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('transaction_code')->label('Mã giao dịch'),
            TextEntry::make('customer.full_name')->label('Khách hàng'),
            TextEntry::make('transaction_date')->label('Ngày giao dịch')->dateTime('d/m/Y H:i'),
            TextEntry::make('status')->label('Trạng thái')->badge()
                ->formatStateUsing(fn (string $state) => $state === 'completed' ? 'Hoàn tất' : 'Đã hủy')
                ->color(fn (string $state) => $state === 'completed' ? 'success' : 'danger'),
            TextEntry::make('total_amount')->label('Tổng tiền')
                ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')->suffix(' đ'),
            TextEntry::make('creator.name')->label('Người ghi nhận'),
            TextEntry::make('note')->label('Ghi chú')->placeholder('—')->columnSpanFull(),

            RepeatableEntry::make('items')
                ->label('Các dòng sản phẩm')
                ->columnSpanFull()
                ->columns(4)
                ->schema([
                    TextEntry::make('product.name')->label('Sản phẩm'),
                    TextEntry::make('quantity')->label('Số lượng'),
                    TextEntry::make('unit_price')->label('Đơn giá')
                        ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')->suffix(' đ'),
                    TextEntry::make('subtotal')->label('Thành tiền')
                        ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')->suffix(' đ'),
                ]),
        ]);
    }
}
