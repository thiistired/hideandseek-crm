<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Services\RecommendationService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Prototype gợi ý sản phẩm (PR01, UC17): chỉ đọc dữ liệu, theo luật cơ bản L1–L5.
 */
class ProductRecommendation extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Gợi ý sản phẩm';

    protected static ?string $title = 'Gợi ý sản phẩm (prototype)';

    protected static ?string $slug = 'goi-y-san-pham';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.product-recommendation';

    public ?int $customerId = null;

    protected function getViewData(): array
    {
        $customers = Customer::query()->orderBy('customer_code')->get(['id', 'customer_code', 'full_name']);

        $customer = $this->customerId ? $customers->firstWhere('id', $this->customerId) : null;
        $result = $customer ? app(RecommendationService::class)->forCustomer(Customer::find($customer->id)) : null;

        return [
            'customers' => $customers,
            'customer'  => $customer,
            'result'    => $result,
        ];
    }
}
