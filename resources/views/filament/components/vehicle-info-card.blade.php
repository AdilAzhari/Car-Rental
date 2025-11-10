@if($vehicle)
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-green-900 dark:text-green-100">
                    {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
                </h4>
                <p class="text-sm text-green-700 dark:text-green-300 font-mono">{{ $vehicle->plate_number }}</p>
                
                <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                    <div class="flex items-center space-x-1">
                        <span class="text-green-600 dark:text-green-400">💰</span>
                        <span class="text-green-800 dark:text-green-200 font-medium">MYR {{ number_format($vehicle->daily_rate, 2) }}/day</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="text-green-600 dark:text-green-400">👥</span>
                        <span class="text-green-700 dark:text-green-300">{{ $vehicle->seats }} seats</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="text-green-600 dark:text-green-400">⚙️</span>
                        <span class="text-green-700 dark:text-green-300">{{ ucfirst($vehicle->transmission->value) }}</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="text-green-600 dark:text-green-400">⛽</span>
                        <span class="text-green-700 dark:text-green-300">{{ ucfirst($vehicle->fuel_type->value) }}</span>
                    </div>
                </div>
                
                <div class="flex items-center mt-3 space-x-2">
                    @if($vehicle->is_available)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            ✓ {{ __('resources.available') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            ✗ {{ __('resources.unavailable') }}
                        </span>
                    @endif
                    @if($vehicle->insurance_included)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            🛡️ {{ __('resources.insured') }}
                        </span>
                    @endif
                </div>
                
                @if($vehicle->location)
                    <p class="text-xs text-green-600 dark:text-green-400 mt-2">
                        📍 {{ $vehicle->location }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="mt-2 text-sm">{{ __('resources.select_vehicle_first') }}</p>
    </div>
@endif