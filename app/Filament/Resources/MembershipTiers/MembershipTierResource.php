<?php

namespace App\Filament\Resources\MembershipTiers;

use App\Filament\Resources\MembershipTiers\Pages\CreateMembershipTier;
use App\Filament\Resources\MembershipTiers\Pages\EditMembershipTier;
use App\Filament\Resources\MembershipTiers\Pages\ListMembershipTiers;
use App\Filament\Resources\MembershipTiers\Schemas\MembershipTierForm;
use App\Filament\Resources\MembershipTiers\Tables\MembershipTiersTable;
use App\Models\MembershipTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MembershipTierResource extends Resource
{
    protected static ?string $model = MembershipTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Hạng thành viên';
    protected static ?string $pluralModelLabel = 'Hạng thành viên';
    protected static ?string $navigationLabel = 'Hạng thành viên';

    public static function form(Schema $schema): Schema
    {
        return MembershipTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipTiersTable::configure($table);
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
            'index' => ListMembershipTiers::route('/'),
            'create' => CreateMembershipTier::route('/create'),
            'edit' => EditMembershipTier::route('/{record}/edit'),
        ];
    }
}
