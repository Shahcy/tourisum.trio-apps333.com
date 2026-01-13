<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        $data['tenant_id'] = $user->tenant_id;
        $data['number'] = 'INV-' . str_pad((string)(\App\Models\Invoice::count() + 1), 6, '0', STR_PAD_LEFT);

        return $data;
    }
}
