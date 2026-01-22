@php
    $tenant = \App\Support\TenantContext::current();
    $logoUrl = null;
    if ($tenant?->logo_path) {
        $logoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($tenant->logo_path);
    }
@endphp

@if ($tenant)
    <div class="flex items-center gap-3">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $tenant->name }}" class="h-8 w-8 rounded" />
        @endif
        <div class="leading-tight">
            <div class="text-sm font-semibold">{{ $tenant->name }}</div>
            @if ($tenant->domain)
                <div class="text-xs text-gray-500">{{ $tenant->domain }}</div>
            @endif
        </div>
    </div>
@endif
