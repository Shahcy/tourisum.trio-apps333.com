<x-filament-panels::page>
    <div class="space-y-6">
        {{ ->form }}

        <x-filament::button wire:click="save">
            Save
        </x-filament::button>
    </div>
</x-filament-panels::page>
