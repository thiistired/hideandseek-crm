<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Services\RfmScoringService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Phân bố khách hàng theo nhóm RFM, tính tại ngày kết thúc của kỳ (hoặc hôm nay nếu để trống).
 */
class RfmSegmentChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Phân bố theo nhóm RFM';

    private const SEGMENTS = ['VIP', 'Thường xuyên', 'Nguy cơ rời bỏ', 'Chưa phân nhóm'];

    protected function getType(): string
    {
        return 'doughnut';
    }

    private function asOf(): Carbon
    {
        $end = $this->pageFilters['endDate'] ?? null;

        return $end ? Carbon::parse($end) : Carbon::now();
    }

    private function counts(): array
    {
        $scored = app(RfmScoringService::class)->calculateAndSegment($this->asOf());

        $counts = array_fill_keys(self::SEGMENTS, 0);
        foreach ($scored as $row) {
            $counts[$row['segment']]++;
        }
        // Khách chưa có giao dịch hoàn tất đến ngày phân tích
        $counts['Chưa phân nhóm'] = max(0, Customer::count() - $scored->count());

        return $counts;
    }

    public function getDescription(): ?string
    {
        $counts = $this->counts();
        $hasData = ($counts['VIP'] + $counts['Thường xuyên'] + $counts['Nguy cơ rời bỏ']) > 0;
        $label = 'Ngày phân tích: ' . $this->asOf()->format('d/m/Y');

        return $hasData ? $label : $label . ' – Không có dữ liệu giao dịch để phân nhóm.';
    }

    protected function getData(): array
    {
        $counts = $this->counts();
        $hasData = ($counts['VIP'] + $counts['Thường xuyên'] + $counts['Nguy cơ rời bỏ']) > 0;

        return [
            'labels' => array_map(fn ($name) => "{$name}: {$counts[$name]}", self::SEGMENTS),
            'datasets' => [[
                // Không có dữ liệu thì để biểu đồ trống
                'data' => $hasData ? array_values($counts) : [0, 0, 0, 0],
                'backgroundColor' => ['#2e7d32', '#1f5fbf', '#c62828', '#9e9e9e'],
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
