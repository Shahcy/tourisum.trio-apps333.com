<x-filament-panels::page>

    <div class="grid gap-6">

        {{-- أزرار سريعة --}}
        <x-filament::section>
            <x-slot name="heading">اختصارات</x-slot>

            <div class="flex flex-wrap gap-3">
                <x-filament::button color="primary" tag="a" :href="\App\Filament\Resources\EmployeeResource::getUrl()">
                    الموظفون
                </x-filament::button>

                <x-filament::button color="gray" tag="a" :href="\App\Filament\Resources\AttendanceResource::getUrl()">
                    الحضور
                </x-filament::button>

                <x-filament::button color="warning" tag="a" :href="\App\Filament\Resources\LeaveRequestResource::getUrl()">
                    طلبات الإجازة
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Widgets --}}
        @livewire(\App\Filament\Widgets\Hr\HrStats::class)

        <div class="grid gap-6 lg:grid-cols-2">
            @livewire(\App\Filament\Widgets\Hr\EmployeeRecentAttendances::class)
            @livewire(\App\Filament\Widgets\Hr\PendingLeaveRequests::class)
        </div>

    </div>

</x-filament-panels::page>
