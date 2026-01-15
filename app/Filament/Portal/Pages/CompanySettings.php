<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class CompanySettings extends Page implements HasForms
{
    use InteractsWithForms;
    use TranslatesPageAttributes;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Company Settings';
    protected static ?string $title = 'Company Settings';
    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.portal.pages.company-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->form->fill([
            'name' => $tenant?->name,
            'domain' => $tenant?->domain,
            'logo_path' => $tenant?->logo_path,
            'primary_color' => $tenant?->primary_color,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')->label(__('Company name'))->required()->maxLength(255),
            TextInput::make('domain')->label(__('Company domain'))->maxLength(255),

            FileUpload::make('logo_path')
                ->label(__('Logo'))
                ->disk('public')
                ->directory('tenant-logos')
                ->image()
                ->imagePreviewHeight('120')
                ->maxSize(2048),

            ColorPicker::make('primary_color')
                ->label(__('Primary color'))
                ->helperText(__('Used for branding (basic).')),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function save(): void
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return;
        }

        $tenant->update($this->form->getState());
        $this->notify('success', __('Saved.'));
    }
}
