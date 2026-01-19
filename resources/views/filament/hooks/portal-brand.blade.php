@php
    $tenant = Filament\Facades\Filament::getTenant();
    $logo = $tenant?->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null;
    $initial = \Illuminate\Support\Str::of($tenant?->name ?? config('app.name'))
        ->substr(0, 1)
        ->upper();
@endphp

<div class="flex items-center gap-3 rtl:flex-row-reverse portal-brand">
    <div
        class="h-10 w-10 rounded-full overflow-hidden border border-white/10 bg-white/10 flex items-center justify-center">
        @if ($logo)
            <img src="{{ $logo }}" alt="logo" class="h-full w-full object-cover" />
        @else
            <span class="text-white font-semibold">{{ $initial }}</span>
        @endif
    </div>
    <span class="font-semibold text-base text-white">
        {{ $tenant->name ?? config('app.name') }}
    </span>

</div>
