<?php

namespace App\Filament\Resources\ApiClients\Pages;

use App\Filament\Resources\ApiClients\ApiClientResource;
use App\Models\ApiClient;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ApiClientCredentials extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ApiClientResource::class;

    protected string $view = 'filament.resources.api-clients.pages.api-client-credentials';

    public ?string $secret = null;

    public function mount(ApiClient $record, ?string $secret = null): void
    {
        $this->record = $record;
        $this->secret = request()->query('secret');
    }
}
