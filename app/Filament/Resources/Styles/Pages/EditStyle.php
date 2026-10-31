<?php

namespace App\Filament\Resources\Styles\Pages;

use App\Filament\Resources\Styles\StyleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStyle extends EditRecord
{
    protected static string $resource = StyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
