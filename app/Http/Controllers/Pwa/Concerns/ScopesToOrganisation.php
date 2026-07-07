<?php

namespace App\Http\Controllers\Pwa\Concerns;

use App\Models\Organisation;

trait ScopesToOrganisation
{
    private ?int $cachedOrganisationId = null;

    private function organisationId(): int
    {
        return $this->cachedOrganisationId ??= Organisation::where('slug', config('pwa.organisation_slug'))
            ->value('id')
            ?? abort(500, 'pwa.organisation_slug is not configured or does not match an organisation.');
    }
}