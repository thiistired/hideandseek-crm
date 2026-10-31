<?php

namespace App\Filament\Widgets;

use App\Models\CrmTransaction;
use App\Models\Customer;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Bốn thẻ chỉ số của dashboard (FR05.1). Chỉ tính giao dịch hoàn tất (BR04).
 */
class DashboardStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int|array|null
    {
        return 4;
    }

    protected function getStats(): array
    {
        $start = $this->pageFilters['startDate'] ?? null;
        $end = $this->pageFilters['endDate'] ?? null;

        $transactions = CrmTransaction::query()
            ->where('status', 'completed')
            ->when($start, fn ($q) => $q->whereDate('transaction_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('transaction_date', '<=', $end));

        $newCustomers = Customer::query()
            ->when($start, fn ($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('created_at', '<=', $end))
            ->count();

        $count = (clone $transactions)->count();
        $total = (float) (clone $transactions)->sum('total_amount');

        return [
            Stat::make('Tổng số khách hàng', number_format(Customer::count(), 0, ',', '.'))
                ->description('Tất cả hồ sơ hiện có, không phụ thuộc kỳ'),
            Stat::make('Khách hàng mới (kỳ)', number_format($newCustomers, 0, ',', '.')),
            Stat::make('Giao dịch hoàn tất (kỳ)', number_format($count, 0, ',', '.')),
            Stat::make('Tổng giá trị (kỳ)', number_format($total, 0, ',', '.') . ' đ'),
            
        ];
    }
}
