<?php

namespace App\Filament\Resources\MembershipTiers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MembershipTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Tên hạng')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'required' => 'Vui lòng nhập tên hạng.',
                    'unique' => 'Tên hạng này đã tồn tại.',
                ]),

            TextInput::make('min_spending')
                ->label('Chi tiêu tối thiểu (đ)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->validationMessages([
                    'required' => 'Vui lòng nhập chi tiêu tối thiểu.',
                    'min' => 'Chi tiêu tối thiểu không được âm.',
                ]),

            TextInput::make('point_rate')
                ->label('Hệ số điểm')
                ->required()
                ->numeric()
                ->minValue(0.01)
                ->maxValue(999)
                ->validationMessages([
                    'required' => 'Vui lòng nhập hệ số điểm.',
                    'min' => 'Hệ số điểm phải lớn hơn 0.',
                ]),

            Textarea::make('description')
                ->label('Mô tả')
                ->columnSpanFull(),
        ]);
    }
}
