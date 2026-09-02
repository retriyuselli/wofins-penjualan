<?php

namespace App\Filament\Resources\ContractTemplates;

use App\Filament\Resources\Concerns\CachesNavigationBadge;
use App\Filament\Resources\ContractTemplates\Pages\CreateContractTemplate;
use App\Filament\Resources\ContractTemplates\Pages\EditContractTemplate;
use App\Filament\Resources\ContractTemplates\Pages\ListContractTemplates;
use App\Filament\Resources\ContractTemplates\Schemas\ContractTemplateForm;
use App\Filament\Resources\ContractTemplates\Tables\ContractTemplatesTable;
use App\Models\ContractTemplate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ContractTemplateResource extends Resource
{
    use CachesNavigationBadge;

    protected static ?string $model = ContractTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Template Kontrak';

    protected static ?string $modelLabel = 'Template Kontrak';

    protected static ?string $pluralModelLabel = 'Template Kontrak';

    protected static string|\UnitEnum|null $navigationGroup = 'Administrasi';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ContractTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractTemplates::route('/'),
            'create' => CreateContractTemplate::route('/create'),
            'edit' => EditContractTemplate::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin']);
    }

    public static function canDelete(Model $record): bool
    {
        if ($record instanceof ContractTemplate && $record->is_system_default) {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Template teks draft kontrak';
    }
}
