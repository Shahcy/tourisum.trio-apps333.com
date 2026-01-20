<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();
        $data['tenant_id'] = Filament::getTenant()?->getKey()
            ?? ($data['tenant_id'] ?? null)
            ?? $user?->tenant_id;

        // Prevent accidental null tenant_id writes
        abort_if(! $data['tenant_id'], 403, 'Tenant context is missing for customer creation.');

        return $data;
    }
}
