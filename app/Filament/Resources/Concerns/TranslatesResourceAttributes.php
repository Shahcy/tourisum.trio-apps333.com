<?php

namespace App\Filament\Resources\Concerns;

trait TranslatesResourceAttributes
{
    public static function getNavigationLabel(): string
    {
        return static::translateValue(parent::getNavigationLabel());
    }

    public static function getNavigationGroup(): ?string
    {
        return static::translateValue(parent::getNavigationGroup());
    }

    public static function getModelLabel(): string
    {
        return static::translateValue(parent::getModelLabel());
    }

    public static function getTitleCaseModelLabel(): string
    {
        if (! static::hasTitleCaseModelLabel()) {
            return static::getModelLabel();
        }

        return static::translateValue(parent::getTitleCaseModelLabel());
    }

    public static function getPluralModelLabel(): string
    {
        return static::translateValue(parent::getPluralModelLabel());
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        if (! static::hasTitleCaseModelLabel()) {
            return static::getPluralModelLabel();
        }

        return static::translateValue(parent::getTitleCasePluralModelLabel());
    }

    protected static function translateValue(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        return __($value);
    }
}
