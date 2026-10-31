<?php

namespace App\Filament\Resources\CrmTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CrmTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('transaction_code')->label('Mã giao dịch')->searchable()->sortable(),
                TextColumn::make('customer.customer_code')->label('Mã KH')->searchable(),
                TextColumn::make('customer.full_name')->label('Khách hàng')->searchable(),
                TextColumn::make('transaction_date')->label('Ngày giao dịch')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('total_amount')->label('Tổng tiền')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' đ')->alignEnd()->sortable(),
                TextColumn::make('status')->label('Trạng thái')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'completed' ? 'Hoàn tất' : 'Đã hủy')
                    ->color(fn (string $state) => $state === 'completed' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship('customer', 'full_name')
                    ->searchable()
                    ->preload(),
                Filter::make('transaction_period')
                    ->label('Khoảng thời gian')
                    ->schema([
                        DatePicker::make('from')->label('Từ ngày')->native(false)->displayFormat('d/m/Y'),
                        DatePicker::make('until')->label('Đến ngày')->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('transaction_date', '>=', $d))
                        ->when($data['until'] ?? null, fn (Builder $q, $d) => $q->whereDate('transaction_date', '<=', $d))),
            ])
            ->recordActions([
                ViewAction::make()->label('Xem'),
            ]);
    }
}
