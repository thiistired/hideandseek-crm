<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Dashboard (FR05): bộ lọc khoảng thời gian áp dụng cho các chỉ số theo kỳ.
 */
class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Khoảng thời gian')
                ->description('Để trống để xem toàn bộ dữ liệu. Ngày kết thúc cũng là ngày phân tích của biểu đồ nhóm RFM (để trống thì lấy ngày hôm nay).')
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Từ ngày')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->live(),
                    DatePicker::make('endDate')
                        ->label('Đến ngày')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->live(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
