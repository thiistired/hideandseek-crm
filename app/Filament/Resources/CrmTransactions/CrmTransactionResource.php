<?php

namespace App\Filament\Resources\CrmTransactions;

use App\Filament\Resources\CrmTransactions\Pages\CreateCrmTransaction;
use App\Filament\Resources\CrmTransactions\Pages\ListCrmTransactions;
use App\Filament\Resources\CrmTransactions\Pages\ViewCrmTransaction;
use App\Filament\Resources\CrmTransactions\Schemas\CrmTransactionForm;
use App\Filament\Resources\CrmTransactions\Schemas\CrmTransactionInfolist;
use App\Filament\Resources\CrmTransactions\Tables\CrmTransactionsTable;
use App\Models\CrmTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CrmTransactionResource extends Resource
{
    protected static ?string $model = CrmTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'transaction_code';
    protected static ?string $modelLabel = 'Giao dịch';
    protected static ?string $pluralModelLabel = 'Giao dịch';
    protected static ?string $navigationLabel = 'Giao dịch';

    public static function form(Schema $schema): Schema
    {
        return CrmTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CrmTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrmTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // Không có trang Sửa: giao dịch đã ghi nhận không được chỉnh sửa (giữ điểm và hạng nhất quán)
    public static function getPages(): array
    {
        return [
            'index' => ListCrmTransactions::route('/'),
            'create' => CreateCrmTransaction::route('/create'),
            'view' => ViewCrmTransaction::route('/{record}'),
        ];
    }
}
