@php
    $tenant = \Filament\Facades\Filament::getTenant();
    $css = \App\Support\Branding\BrandCss::forTenant($tenant);
@endphp

{!! $css !!}
