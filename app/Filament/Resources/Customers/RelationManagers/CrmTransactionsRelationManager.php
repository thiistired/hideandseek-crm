<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CrmTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'crmTransactions';

    protected static ?string $title = 'Lịch sử mua hàng';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_code')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('items.product'))
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('transaction_code')->label('Mã giao dịch'),
                TextColumn::make('transaction_date')->label('Ngày giao dịch')->dateTime('d/m/Y H:i'),
                TextColumn::make('items.product.name')->label('Sản phẩm đã mua')->listWithLineBreaks(),
                TextColumn::make('total_amount')->label('Tổng tiền')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' đ')->alignEnd(),
                TextColumn::make('status')->label('Trạng thái')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'completed' ? 'Hoàn tất' : 'Đã hủy')
                    ->color(fn (string $state) => $state === 'completed' ? 'success' : 'danger'),
            ]);
    }
}
