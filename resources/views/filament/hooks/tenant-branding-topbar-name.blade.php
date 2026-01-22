@php
    $tenant = \App\Support\TenantContext::current();
@endphp

@if ($tenant)
    <div class="ms-2 text-sm font-semibold">
        {{ $tenant->name }}
    </div>
@endif
