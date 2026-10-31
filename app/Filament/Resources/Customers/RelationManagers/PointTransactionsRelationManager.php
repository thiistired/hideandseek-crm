<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Customers\CustomerResource;
use App\Services\LoyaltyService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PointTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointTransactions';

    protected static ?string $title = 'Lịch sử điểm';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Ngày')->dateTime('d/m/Y H:i'),
                TextColumn::make('points_change')->label('Điểm')
                    ->formatStateUsing(fn (int $state) => $state > 0 ? '+' . $state : (string) $state)
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger')
                    ->alignEnd(),
                TextColumn::make('reason')->label('Lý do'),
                TextColumn::make('referenceTransaction.transaction_code')->label('Giao dịch')->placeholder('—'),
            ])
            ->headerActions([
                Action::make('adjustPoints')
                    ->label('Điều chỉnh điểm')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->visible(fn () => Auth::user()?->role === 'manager')
                    ->modalHeading('Điều chỉnh điểm thủ công')
                    ->modalDescription('Nhập số điểm cộng (dương) hoặc trừ (âm) và lý do. Hạng thành viên không thay đổi.')
                    ->modalSubmitActionLabel('Lưu điều chỉnh')
                    ->schema([
                        TextInput::make('points')
                            ->label('Số điểm điều chỉnh')
                            ->required()
                            ->integer()
                            ->validationMessages([
                                'required' => 'Phải nhập số điểm.',
                                'integer'  => 'Số điểm phải là số nguyên.',
                            ]),
                        Textarea::make('reason')
                            ->label('Lý do')
                            ->required()
                            ->validationMessages(['required' => 'Phải nhập lý do điều chỉnh.']),
                    ])
                    ->action(function (array $data, Action $action) {
// Chặn cả khi gọi trực tiếp, không chỉ ẩn nút (FR00.1)
                        abort_unless(Auth::user()?->role === 'manager', 403);

                        $customer = $this->getOwnerRecord();

                        try {
                            app(LoyaltyService::class)->adjustPoints(
                                $customer->id,
                                (int) $data['points'],
                                (string) $data['reason'],
                                (int) Auth::id(),
                            );
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Không thể điều chỉnh điểm')
                                ->body(collect($e->errors())->flatten()->implode(' '))
                                ->danger()
                                ->send();
                            $action->halt();
                        }

                        Notification::make()->title('Đã điều chỉnh điểm')->success()->send();

                        $this->redirect(CustomerResource::getUrl('view', ['record' => $customer]));
                    }),
            ]);
    }
}