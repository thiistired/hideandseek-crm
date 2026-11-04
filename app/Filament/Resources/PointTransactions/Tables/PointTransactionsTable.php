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
                TextColumn::make('customer_id')->numeric()->sortable(),
                TextColumn::make('points_change')->numeric()->sortable(),
                TextColumn::make('reason')->searchable(),
                TextColumn::make('reference_transaction_id')->numeric()->sortable(),
                TextColumn::make('created_by')->numeric()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}