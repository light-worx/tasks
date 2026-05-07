<?php

namespace App\Filament\Resources\ApiClients;

use App\Filament\Resources\ApiClients\Pages\ApiClientCredentials;
use App\Filament\Resources\ApiClients\Pages\CreateApiClient;
use App\Filament\Resources\ApiClients\Pages\EditApiClient;
use App\Filament\Resources\ApiClients\Pages\ListApiClients;
use App\Filament\Resources\ApiClients\Schemas\ApiClientForm;
use App\Filament\Resources\ApiClients\Tables\ApiClientsTable;
use App\Models\ApiClient;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApiClientResource extends Resource
{
    protected static ?string $model = ApiClient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApiClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiClientsTable::configure($table);
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
            'index' => ListApiClients::route('/'),
            'create' => CreateApiClient::route('/create'),
            'edit' => EditApiClient::route('/{record}/edit'),
            'credentials' => ApiClientCredentials::route('/{record}/credentials'),
        ];
    }

    public static function afterCreate($record): void
    {
        if ($record->plain_secret) {

            Notification::make()
                ->title('API Client Created')
                ->body(
                    "Client ID: {$record->client_id}\nSecret: {$record->plain_secret}"
                )
                ->success()
                ->persistent()
                ->send();
        }
    }
}
