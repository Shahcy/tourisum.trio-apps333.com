<?php

namespace App\Observers;

use App\Models\Provider;
use App\Models\Tenant;
use App\Support\Branding\LogoPaletteExtractor;

class TenantObserver
{
    public function saving(Tenant $tenant): void
    {
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

        if ($logoChanged && ($palette['primary_color'] ?? null)) {
            $tenant->primary_color = $palette['primary_color'];
        }

        if ($logoChanged && ($palette['secondary_color'] ?? null)) {
            $tenant->secondary_color = $palette['secondary_color'];
        }

        if ($logoChanged && ($palette['accent_color'] ?? null)) {
            $tenant->accent_color = $palette['accent_color'];
        }
    }

    public function created(Tenant $tenant): void
    {
        $existing = Provider::query()
            ->where('tenant_id', $tenant->id)
            ->pluck('code')
            ->all();

        $defaults = Provider::defaults();

        foreach ($defaults as $provider) {
            if (in_array($provider['code'], $existing, true)) {
                continue;
            }

            Provider::create([
                'tenant_id' => $tenant->id,
                'name' => $provider['name'],
                'code' => $provider['code'],
                'type' => 'gds',
                'mode' => 'manual',
                'status' => 'pending',
            ]);
        }
    }
}
