<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();
        $data['tenant_id'] = $user->tenant_id;
        $data['type'] = $data['type'] ?? $data['booking_type'] ?? 'domestic';
        $data['total_amount'] = $data['total_amount'] ?? $data['grand_total'] ?? $data['subtotal'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;

        return $data;
    }
}
