<?php

namespace App\Filament\Resources\PointTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PointTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.full_name')
                    ->label('Khách hàng'),
                TextEntry::make('points_change')
                    ->label('Điểm thay đổi')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . $state),
                TextEntry::make('reason')
                    ->label('Lý do'),
                TextEntry::make('referenceTransaction.transaction_code')
                    ->label('Giao dịch liên quan')
                    ->placeholder('Điều chỉnh thủ công'),
                TextEntry::make('creator.name')
                    ->label('Người thực hiện'),
                TextEntry::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}