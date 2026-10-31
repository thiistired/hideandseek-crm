<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with('membershipTier')
                ->withSum(['crmTransactions as completed_spent' => fn ($q) => $q->where('status', 'completed')], 'total_amount'))
            ->defaultSort('customer_code')
            ->searchPlaceholder('Tìm theo mã, tên, số điện thoại, email…')
            ->columns([
                TextColumn::make('customer_code')->label('Mã KH')->searchable()->sortable(),
                TextColumn::make('full_name')->label('Họ và tên')->searchable()->sortable(),
                TextColumn::make('phone')->label('Số điện thoại')->searchable(),
                TextColumn::make('email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('membershipTier.name')->label('Hạng')->badge(),
                TextColumn::make('current_points')->label('Điểm')->numeric()->alignEnd()->sortable(),
                TextColumn::make('completed_spent')->label('Tổng chi tiêu')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->default(0)->suffix(' đ')->alignEnd()->sortable(),
            ])
            ->filters([
                SelectFilter::make('membership_tier_id')
                    ->label('Hạng thành viên')
                    ->relationship('membershipTier', 'name'),
                SelectFilter::make('gender')
                    ->label('Giới tính')
                    ->options(['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác']),
            ])
            ->recordActions([
                ViewAction::make()->label('Xem'),
                EditAction::make()->label('Sửa'),
                DeleteAction::make()
                    ->label('Xóa')
                    ->modalHeading('Xóa khách hàng')
                    ->modalDescription('Bạn có chắc muốn xóa khách hàng này không?')
                    ->modalSubmitActionLabel('Xóa')
                    ->before(function (Customer $record, DeleteAction $action) {
                        // BR08: không xóa khách đã có giao dịch hoặc lịch sử điểm
                        if ($record->crmTransactions()->exists() || $record->pointTransactions()->exists()) {
                            Notification::make()
                                ->title('Không thể xóa khách hàng')
                                ->body('Khách hàng này đã có giao dịch hoặc lịch sử điểm nên không được xóa để giữ toàn vẹn dữ liệu.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    })
                    ->successNotificationTitle('Đã xóa khách hàng'),
            ]);
    }
}
