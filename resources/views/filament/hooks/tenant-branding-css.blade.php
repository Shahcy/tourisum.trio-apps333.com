@php
    $tenant = \App\Support\TenantContext::current();
    $css = \App\Support\Branding\BrandCss::forTenant($tenant);
@endphp

{!! $css !!}
