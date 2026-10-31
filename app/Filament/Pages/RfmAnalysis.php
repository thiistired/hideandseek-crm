<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Services\RfmScoringService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Phân tích RFM (FR04): R, F, M, điểm R-F-M và nhóm của từng khách tại một ngày phân tích.
 */
class RfmAnalysis extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Phân tích RFM';

    protected static ?string $title = 'Phân tích RFM';

    protected static ?string $slug = 'phan-tich-rfm';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.rfm-analysis';

    /** Trạng thái của biểu mẫu lọc: asOf (ngày phân tích) và segment (nhóm). */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['asOf' => now()->toDateString(), 'segment' => null]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('asOf')
                    ->label('Ngày phân tích')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->required()
                    ->live(),
                Select::make('segment')
                    ->label('Nhóm')
                    ->placeholder('Tất cả')
                    ->options([
                        'VIP'            => 'VIP',
                        'Thường xuyên'   => 'Thường xuyên',
                        'Nguy cơ rời bỏ' => 'Nguy cơ rời bỏ',
                        'Chưa phân nhóm' => 'Chưa phân nhóm',
                    ])
                    ->live(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    protected function getViewData(): array
    {
        try {
            $date = Carbon::parse($this->data['asOf'] ?? now()->toDateString());
        } catch (\Throwable $e) {
            $date = now();
        }

        $scores = app(RfmScoringService::class)->calculateAndSegment($date)->keyBy('customer_id');

        $rows = Customer::query()->orderBy('customer_code')->get(['id', 'customer_code', 'full_name'])
            ->map(function ($c) use ($scores) {
                $s = $scores->get($c->id);

                return [
                    'code'      => $c->customer_code,
                    'name'      => $c->full_name,
                    'recency'   => $s['recency'] ?? null,
                    'frequency' => $s['frequency'] ?? null,
                    'monetary'  => $s['monetary'] ?? null,
                    'rfm'       => $s ? "{$s['r_score']}-{$s['f_score']}-{$s['m_score']}" : null,
                    'segment'   => $s['segment'] ?? 'Chưa phân nhóm',
                ];
            });

        $counts = [];
        foreach (['VIP', 'Thường xuyên', 'Nguy cơ rời bỏ', 'Chưa phân nhóm'] as $name) {
            $counts[$name] = $rows->where('segment', $name)->count();
        }

        $segment = $this->data['segment'] ?? null;
        $visible = $segment ? $rows->where('segment', $segment) : $rows;

        return [
            'asOfLabel' => $date->format('d/m/Y'),
            'counts'    => $counts,
            'rows'      => $visible->values(),
            'total'     => $rows->count(),
        ];
    }
}
