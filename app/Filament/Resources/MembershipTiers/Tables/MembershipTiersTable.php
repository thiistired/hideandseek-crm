<?php

namespace App\Filament\Resources\MembershipTiers\Tables;

use App\Models\MembershipTier;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('min_spending')
            ->columns([
                TextColumn::make('name')->label('Hạng')->badge(),
                TextColumn::make('min_spending')->label('Chi tiêu tối thiểu')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' đ')->alignEnd(),
                TextColumn::make('point_rate')->label('Hệ số điểm')->numeric(decimalPlaces: 2),
                TextColumn::make('customers_count')->label('Số khách hàng')->counts('customers'),
                TextColumn::make('description')->label('Mô tả')->limit(50)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()->label('Sửa'),
                DeleteAction::make()
                    ->label('Xóa')
                    ->modalHeading('Xóa hạng thành viên')
                    ->modalSubmitActionLabel('Xóa')
                    ->before(function (MembershipTier $record, DeleteAction $action) {
                        if ($record->customers()->exists()) {
                            Notification::make()
                                ->title('Không thể xóa hạng')
                                ->body('Hạng này đang có khách hàng sử dụng nên không được xóa.')
                                ->danger()->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }
}
