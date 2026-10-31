<?php

namespace App\Filament\Resources\PointTransactions;

use App\Filament\Resources\PointTransactions\Pages\CreatePointTransaction;
use App\Filament\Resources\PointTransactions\Pages\ListPointTransactions;
use App\Filament\Resources\PointTransactions\Pages\ViewPointTransaction;
use App\Filament\Resources\PointTransactions\Schemas\PointTransactionForm;
use App\Filament\Resources\PointTransactions\Schemas\PointTransactionInfolist;
use App\Filament\Resources\PointTransactions\Tables\PointTransactionsTable;
use App\Models\PointTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PointTransactionResource extends Resource
{
    protected static ?string $model = PointTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $modelLabel = 'Lịch sử điểm';
    protected static ?string $pluralModelLabel = 'Lịch sử điểm';
    protected static ?string $navigationLabel = 'Lịch sử điểm';

    // Chỉ Quản lý được thêm điều chỉnh điểm (UC08)
    public static function canCreate(): bool
    {
        return Auth::user()?->role === 'manager';
    }

    public static function form(Schema $schema): Schema
    {
        return PointTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PointTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPointTransactions::route('/'),
            'create' => CreatePointTransaction::route('/create'),
            'view' => ViewPointTransaction::route('/{record}'),
        ];
    }
}