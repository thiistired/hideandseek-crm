<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('full_name')
                ->label('Họ và tên')
                ->required()
                ->maxLength(100)
                ->validationMessages([
                    'required' => 'Vui lòng nhập họ và tên.',
                    'max' => 'Họ và tên tối đa 100 ký tự.',
                ]),

            TextInput::make('phone')
                ->label('Số điện thoại')
                ->required()
                ->tel()
                ->regex('/^(0|\+84)[0-9]{9,10}$/')
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'required' => 'Vui lòng nhập số điện thoại.',
                    'regex' => 'Số điện thoại không đúng định dạng (ví dụ 0912345678).',
                    'unique' => 'Số điện thoại này đã thuộc về một khách hàng khác.',
                ]),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(150)
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'email' => 'Email không đúng định dạng.',
                    'unique' => 'Email này đã thuộc về một khách hàng khác.',
                ]),

            Select::make('gender')
                ->label('Giới tính')
                ->options(['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác']),

            DatePicker::make('date_of_birth')
                ->label('Ngày sinh')
                ->maxDate(now())
                ->displayFormat('d/m/Y'),

            TextInput::make('address')
                ->label('Địa chỉ')
                ->maxLength(255),

            // Chỉ xem: hạng và điểm do hệ thống tự cập nhật (BR06, BR07)
            TextInput::make('membership_tier_name')
                ->label('Hạng thành viên')
                ->disabled()->dehydrated(false)
                ->formatStateUsing(fn ($record) => $record?->membershipTier?->name)
                ->visibleOn('edit'),

            TextInput::make('current_points')
                ->label('Điểm hiện tại')
                ->disabled()->dehydrated(false)
                ->visibleOn('edit'),
        ]);
    }
}
