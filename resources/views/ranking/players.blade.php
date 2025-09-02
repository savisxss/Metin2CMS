<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Player Rankings') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Player Rankings</h3>
                    <p class="text-gray-600">Discover the most powerful warriors in our realm</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                            Online
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div>
                            Offline
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings Component -->
        <livewire:player-ranking />
    </div>
</x-app-layout>