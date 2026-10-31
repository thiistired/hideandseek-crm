<?php

namespace App\Filament\Resources\PointTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PointTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship('customer', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('points_change')
                    ->label('Số điểm điều chỉnh')
                    ->helperText('Nhập số dương để cộng điểm, số âm để trừ điểm.')
                    ->required()
                    ->integer()
                    ->notIn([0]),
                Textarea::make('reason')
                    ->label('Lý do')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}