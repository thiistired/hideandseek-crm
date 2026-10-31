<?php

namespace App\Filament\Resources\CrmTransactions\Schemas;

use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CrmTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Khách hàng')
                ->options(fn () => Customer::query()->orderBy('customer_code')->get()
                    ->mapWithKeys(fn ($c) => [$c->id => "{$c->customer_code} – {$c->full_name}"]))
                ->searchable()
                ->required(),

            DateTimePicker::make('transaction_date')
                ->label('Ngày giao dịch')
                ->default(now())
                ->maxDate(now())
                ->seconds(false)
                ->required(),

            Repeater::make('items')
                ->label('Sản phẩm')
                ->addActionLabel('Thêm sản phẩm')
                ->minItems(1)
                ->defaultItems(1)
                ->columnSpanFull()
                ->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotal($get('items'), $set, 'total_amount'))
                ->schema([
                    Select::make('product_id')
                        ->label('Sản phẩm')
                        ->options(fn () => Product::query()->where('status', 'active')->orderBy('product_code')->get()
                            ->mapWithKeys(fn ($p) => [$p->id => "{$p->product_code} – {$p->name}"]))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::refreshLine($get, $set)),

                    TextInput::make('quantity')
                        ->label('Số lượng')
                        ->numeric()->integer()->minValue(1)->default(1)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::refreshLine($get, $set)),

                    TextInput::make('unit_price')
                        ->label('Đơn giá (đ)')
                        ->disabled()->dehydrated(false),

                    TextInput::make('subtotal')
                        ->label('Thành tiền (đ)')
                        ->disabled()->dehydrated(false),
                ])
                ->columns(4),

            TextInput::make('total_amount')
                ->label('Tổng tiền (đ)')
                ->disabled()->dehydrated(false),

            Textarea::make('note')
                ->label('Ghi chú')
                ->columnSpanFull(),
        ]);
    }

    /** Cập nhật đơn giá, thành tiền của dòng đang sửa rồi tính lại tổng. */
    private static function refreshLine(Get $get, Set $set): void
    {
        $price = (float) (Product::find($get('product_id'))?->price ?? 0);
        $qty = max(0, (int) $get('quantity'));
        $set('unit_price', number_format($price, 0, ',', '.'));
        $set('subtotal', number_format($price * $qty, 0, ',', '.'));
        self::updateTotal($get('../../items'), $set, '../../total_amount');
    }

    private static function updateTotal(?array $items, Set $set, string $path): void
    {
        $total = 0;
        foreach ($items ?? [] as $item) {
            $price = (float) (Product::find($item['product_id'] ?? null)?->price ?? 0);
            $total += $price * max(0, (int) ($item['quantity'] ?? 0));
        }
        $set($path, number_format($total, 0, ',', '.'));
    }
}
