<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('product_code')
            ->columns([
                TextColumn::make('product_code')->label('Mã sản phẩm')->searchable()->sortable(),
                TextColumn::make('name')->label('Tên sản phẩm')->searchable(),
                TextColumn::make('category.name')->label('Nhóm sản phẩm')->sortable(),
                TextColumn::make('style.name')->label('Phong cách')->sortable(),
                TextColumn::make('price')->label('Đơn giá')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(' đ')->alignEnd()->sortable(),
                TextColumn::make('status')->label('Trạng thái')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'active' ? 'Đang kinh doanh' : 'Ngừng kinh doanh')
                    ->color(fn (string $state) => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('Nhóm sản phẩm')->relationship('category', 'name'),
                SelectFilter::make('style_id')->label('Phong cách')->relationship('style', 'name'),
                SelectFilter::make('status')->label('Trạng thái')
                    ->options(['active' => 'Đang kinh doanh', 'inactive' => 'Ngừng kinh doanh']),
            ])
            ->recordActions([
                ViewAction::make()->label('Xem'),
            ]);
    }
}
