@php
    $tenant = \App\Support\TenantContext::current();
    $logo = $tenant?->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null;
    $name = $tenant?->name ?? config('app.name');
    $initial = \Illuminate\Support\Str::of($name)->substr(0, 1)->upper();
@endphp

<div class="tenant-brand">
    <div class="tenant-brand__logo">
        @if ($logo)
            <img src="{{ $logo }}" alt="{{ $name }}" />
        @else
            <span>{{ $initial }}</span>
        @endif
    </div>
    <span class="tenant-brand__name">{{ $name }}</span>
</div>
