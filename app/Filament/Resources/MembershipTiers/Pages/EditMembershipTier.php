<?php

namespace App\Filament\Resources\MembershipTiers\Pages;

use App\Filament\Resources\MembershipTiers\MembershipTierResource;
use App\Models\MembershipTier;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMembershipTier extends EditRecord
{
    protected static string $resource = MembershipTierResource::class;

    protected static ?string $title = 'Sửa hạng thành viên';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Xóa')
                ->before(function (MembershipTier $record, DeleteAction $action) {
                    if ($record->customers()->exists()) {
                        Notification::make()
                            ->title('Không thể xóa hạng')
                            ->body('Hạng này đang có khách hàng sử dụng nên không được xóa.')
                            ->danger()->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Đã lưu thay đổi';
    }
}
