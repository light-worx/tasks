<?php

namespace App\Filament\Resources\ApiClients\Pages;

use App\Filament\Resources\ApiClients\ApiClientResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateApiClient extends CreateRecord
{
    protected static string $resource = ApiClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $plainSecret = Str::random(64);
        $this->data['plain_secret'] = $plainSecret;
        $data['client_id'] = 'cli_' . Str::random(24);
        $data['client_secret'] = Hash::make($plainSecret);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('credentials', [
            'record' => $this->record->id,
            'secret' => $this->data['plain_secret'] ?? null,
        ]);
    }
}