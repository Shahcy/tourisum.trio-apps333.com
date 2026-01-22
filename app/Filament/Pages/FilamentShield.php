<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use Filament\Pages\Page;

class FilamentShield extends Page
{
    use TranslatesPageAttributes;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'saas.group';
    protected static ?string $navigationLabel = 'filament_shield.label';
    protected static ?string $title = 'filament_shield.label';
    protected static ?int $navigationSort = 98;

    protected static string $view = 'filament.pages.filament-shield';
}
