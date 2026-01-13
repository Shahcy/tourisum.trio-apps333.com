<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::button wire:click="save">
            حفظ
        </x-filament::button>
    </div>
</x-filament-panels::page>
