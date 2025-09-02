<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Guild Rankings') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Guild Rankings</h3>
                    <p class="text-gray-600">The most powerful guilds battling for dominance</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="text-sm text-gray-500">
                        Rankings based on ladder points and guild level
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings Component -->
        <livewire:guild-ranking />
    </div>
</x-app-layout>