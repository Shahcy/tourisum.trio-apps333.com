<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Support\Branding\LogoPaletteExtractor;

class TenantObserver
{
    public function saving(Tenant $tenant): void
    {
        // إذا GD غير موجودة، لا تعمل palette extraction
        if (! \extension_loaded('gd')) {
            return;
        }

        $logoChanged = $tenant->isDirty('logo_path');

        if (! $logoChanged && $tenant->primary_color) {
            return;
        }

        $palette = LogoPaletteExtractor::extractFromPublicDiskPath($tenant->logo_path);

        if (! $palette) {
            return;
        }

        if (! $tenant->primary_color && ($palette['primary_color'] ?? null)) {
            $tenant->primary_color = $palette['primary_color'];
        }

        if (! $tenant->secondary_color && ($palette['secondary_color'] ?? null)) {
            $tenant->secondary_color = $palette['secondary_color'];
        }

        if (! $tenant->accent_color && ($palette['accent_color'] ?? null)) {
            $tenant->accent_color = $palette['accent_color'];
        }
    }
}
