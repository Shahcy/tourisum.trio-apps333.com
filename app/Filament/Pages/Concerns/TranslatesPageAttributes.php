<?php

namespace App\Filament\Pages\Concerns;

use Illuminate\Contracts\Support\Htmlable;

trait TranslatesPageAttributes
{
    public static function getNavigationGroup(): ?string
    {
        return static::translateValue(parent::getNavigationGroup());
    }

    public static function getNavigationLabel(): string
    {
        return static::translateValue(parent::getNavigationLabel());
    }

    public static function getNavigationParentItem(): ?string
    {
        return static::translateValue(parent::getNavigationParentItem());
    }

    public function getTitle(): string | Htmlable
    {
        $title = parent::getTitle();

        if ($title instanceof Htmlable) {
            return $title;
        }

        return static::translateValue((string) $title) ?? $title;
    }

    protected static function translateValue(?string $value): ?string
    {
        if (! filled($value)) {
            return $value;
        }

        return __($value);
    }
}
