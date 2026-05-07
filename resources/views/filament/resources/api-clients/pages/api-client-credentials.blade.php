<x-filament-panels::page>
    <div class="space-y-4">

        <div>
            <strong>Client ID:</strong><br>
            {{ $record->client_id }}
        </div>

        <div>
            <strong>Client Secret:</strong><br>
            {{ $secret ?? 'N/A' }}
        </div>

        <p class="text-red-600">
            Save this secret now — it will not be shown again.
        </p>

    </div>
</x-filament-panels::page>