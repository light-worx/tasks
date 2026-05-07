<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organisation_id')
                    ->relationship('organisation', 'name'),
                Select::make('created_by_client_id')
                    ->relationship('client', 'name'),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_private')
                    ->required(),
                TextInput::make('owner_email')
                    ->email(),
            ]);
    }
}
