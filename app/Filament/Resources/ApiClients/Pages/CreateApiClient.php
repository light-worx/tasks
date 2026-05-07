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

        // store for redirect (IMPORTANT)
        $this->data['plain_secret'] = $plainSecret;

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