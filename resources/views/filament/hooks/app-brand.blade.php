@php
    $tenant = \App\Support\TenantContext::current();
    $tenantLogo = $tenant?->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($tenant->logo_path) : null;
    $fallbackLogo = asset('storage/brand/app_logo.png');
    $logo = $tenantLogo ?: $fallbackLogo;
    $name = $tenant?->name ?? config('app.name');
@endphp

<div class="app-brand">
    <div class="app-brand__logo">
        <img src="{{ $logo }}" alt="{{ $name }}" />
    </div>
    <span class="app-brand__name">{{ $name }}</span>
</div>
