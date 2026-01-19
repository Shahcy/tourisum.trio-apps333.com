@php
    $tenant = \Filament\Facades\Filament::getTenant();
@endphp

@if ($tenant)
    <div class="ms-2 text-sm font-semibold">
        {{ $tenant->name }}
    </div>
@endif
