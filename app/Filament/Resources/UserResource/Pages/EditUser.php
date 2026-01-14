<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles()->pluck('name')->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        $roles = (array) ($this->data['roles'] ?? []);
        $this->record->syncRoles($roles);
    }
}
