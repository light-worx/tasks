<?php

namespace App\Filament\Resources\ApiClients\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ApiClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                    ])
                    ->required()
                    ->default('active'),
                Select::make('organisation_id')
                    ->relationship('organisation', 'name'),
                Toggle::make('can_view_all_tasks')
                    ->required(),
                Toggle::make('can_lookup_assigned_tasks')
                    ->required(),
                DateTimePicker::make('last_used_at'),
            ]);
    }
}
