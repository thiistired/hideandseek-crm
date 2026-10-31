<?php

namespace App\Filament\Resources\PointTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.full_name')
                    ->label('Khách hàng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points_change')
                    ->label('Điểm thay đổi')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . $state)
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Lý do')
                    ->searchable(),
                TextColumn::make('referenceTransaction.transaction_code')
                    ->label('Giao dịch liên quan')
                    ->placeholder('Điều chỉnh thủ công'),
                TextColumn::make('creator.name')
                    ->label('Người thực hiện'),
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}