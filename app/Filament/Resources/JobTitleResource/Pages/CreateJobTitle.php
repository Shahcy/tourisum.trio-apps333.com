<?php

namespace App\Filament\Resources\JobTitleResource\Pages;

use App\Filament\Resources\JobTitleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateJobTitle extends CreateRecord
{
    protected static string $resource = JobTitleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()?->getKey();
        return $data;
    }
}
