@if($start_date && $end_date)
    @php
        $days = \Carbon\Carbon::parse($start_date)->diffInDays(\Carbon\Carbon::parse($end_date)) + 1;
        $vehicle = $vehicle_id ? \App\Models\Vehicle::find($vehicle_id) : null;
        $dailyRate = $vehicle ? $vehicle->daily_rate : 0;
        $subtotal = $days * $dailyRate;
    @endphp
    
    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
        <div class="flex items-center space-x-2 mb-3">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-purple-900 dark:text-purple-100">{{ __('resources.rental_summary') }}</h4>
        </div>
        
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-purple-700 dark:text-purple-300">{{ __('resources.rental_period') }}:</span>
                <span class="font-medium text-purple-900 dark:text-purple-100">{{ $days }} {{ $days == 1 ? __('resources.day') : __('resources.days') }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-purple-700 dark:text-purple-300">{{ __('resources.start_date') }}:</span>
                <span class="font-medium text-purple-900 dark:text-purple-100">{{ \Carbon\Carbon::parse($start_date)->format('d M Y') }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-purple-700 dark:text-purple-300">{{ __('resources.end_date') }}:</span>
                <span class="font-medium text-purple-900 dark:text-purple-100">{{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}</span>
            </div>
            
            @if($vehicle)
                <div class="border-t border-purple-200 dark:border-purple-700 pt-2 mt-2">
                    <div class="flex justify-between items-center">
                        <span class="text-purple-700 dark:text-purple-300">{{ __('resources.daily_rate') }}:</span>
                        <span class="font-medium text-purple-900 dark:text-purple-100">MYR {{ number_format($dailyRate, 2) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center font-semibold text-base mt-2">
                        <span class="text-purple-800 dark:text-purple-200">{{ __('resources.estimated_total') }}:</span>
                        <span class="text-purple-900 dark:text-purple-100">MYR {{ number_format($subtotal, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="mt-3 p-2 bg-white/50 dark:bg-white/5 rounded border border-purple-100 dark:border-purple-700">
            <p class="text-xs text-purple-600 dark:text-purple-400 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                {{ __('resources.pricing_note') }}
            </p>
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 9v2m0 0v2m0-2h8m-8 0H4"/>
        </svg>
        <p class="mt-2 text-sm">{{ __('resources.select_dates_first') }}</p>
    </div>
@endif