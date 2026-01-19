<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

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
            'secondary_color' => $tenant?->secondary_color,
            'accent_color' => $tenant?->accent_color,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('Company settings'))
                ->schema([
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

                    ColorPicker::make('secondary_color')
                        ->label(__('Secondary color (auto)'))
                        ->disabled()
                        ->dehydrated(),

                    ColorPicker::make('accent_color')
                        ->label(__('Accent color (auto)'))
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(2),
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
        Notification::make()
            ->title('Company settings updated')
            ->success()
            ->send();
    }
}
