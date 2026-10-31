<?php

namespace App\Filament\Widgets;

use App\Models\MembershipTier;
use Filament\Widgets\ChartWidget;

/**
 * Phân bố khách hàng theo hạng thành viên: phản ánh trạng thái hiện tại (FR05.2).
 */
class CustomerTierChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Phân bố khách hàng theo hạng';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $tiers = MembershipTier::query()->withCount('customers')->orderBy('min_spending')->get();

        return [
            'labels' => $tiers->map(fn ($t) => "{$t->name}: {$t->customers_count}")->all(),
            'datasets' => [[
                'data' => $tiers->pluck('customers_count')->all(),
                'backgroundColor' => ['#a0b6e0', '#2f5496', '#c77700', '#7b3fa0'],
            ]],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
