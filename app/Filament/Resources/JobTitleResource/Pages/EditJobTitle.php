<?php

namespace App\Filament\Resources\JobTitleResource\Pages;

use App\Filament\Resources\JobTitleResource;
use Filament\Facades\Filament;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobTitle extends EditRecord
{
    protected static string $resource = JobTitleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()?->getKey();
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
