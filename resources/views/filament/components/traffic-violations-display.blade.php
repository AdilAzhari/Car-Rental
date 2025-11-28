@php
    use Carbon\Carbon;
    $violations = $getRecord()?->traffic_violations ?? [];
    $lastChecked = $getRecord()?->violations_last_checked;
    $totalFines = $getRecord()?->total_fines_amount ?? 0;
    $hasPending = $getRecord()?->has_pending_violations ?? false;
@endphp

@if(is_array($violations) && count($violations) > 0)
    <div class="space-y-6">
        <!-- Summary Dashboard -->
        <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-slate-50 to-gray-50 dark:from-gray-900 dark:to-slate-900">
            <div class="absolute inset-0 bg-grid-gray-100/25 dark:bg-grid-gray-800/25 [background-size:20px_20px]"></div>
            <div class="relative p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('resources.traffic_violations') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ count($violations) }} violation{{ count($violations) > 1 ? 's' : '' }} found from JPJ SMS
                        </p>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-red-700 dark:text-red-300">From SMS</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Fines</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">RM{{ number_format($totalFines, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Violations</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ count($violations) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($lastChecked)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Last checked: {{ $lastChecked->format('d M Y, H:i') }} via SMS</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Violations List -->
        <div class="space-y-4">
            @foreach($violations as $index => $violation)
                <div class="group relative overflow-hidden rounded-xl border transition-all duration-200 hover:shadow-lg border-red-200 dark:border-red-800 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/10 dark:to-pink-900/10 hover:border-red-300 dark:hover:border-red-700">

                    <!-- Status indicator line -->
                    <div class="absolute left-0 top-0 w-1 h-full bg-red-500"></div>

                    <div class="p-6 pl-8">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $violation['type'] ?? __('resources.traffic_violation') }}
                                    </h4>
                                </div>

                                @if(isset($violation['fine_amount']))
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-900/30">
                                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <span class="font-semibold text-red-700 dark:text-red-300">
                                            RM {{ number_format($violation['fine_amount'], 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="text-right">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">#{{ $index + 1 }}</span>
                            </div>
                        </div>

                        <!-- SMS Message - PROMINENT DISPLAY -->
                        @if(isset($violation['sms_message']) && $violation['sms_message'])
                            <div class="mt-4 p-5 rounded-lg bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">JPJ SMS Message</h5>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Original violation notice from JPJ</p>
                                    </div>
                                </div>
                                <div class="p-4 rounded-md bg-white dark:bg-red-950/20 border border-red-100 dark:border-red-900">
                                    <p class="text-sm font-medium text-red-900 dark:text-red-100 whitespace-pre-line leading-relaxed">{{ $violation['sms_message'] }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Additional Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-4">
                            @if(isset($violation['received_at']))
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Received</span>
                                        <p class="text-gray-900 dark:text-gray-100">{{ Carbon::parse($violation['received_at'])->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="relative overflow-hidden rounded-xl border border-green-200 dark:border-green-700 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10">
        <div class="absolute inset-0 bg-grid-green-100/25 dark:bg-grid-green-800/25 [background-size:20px_20px]"></div>
        <div class="relative text-center py-12 px-6">
            <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-green-900 dark:text-green-100 mb-2">{{ __('resources.no_violations') }}</h3>
            <p class="text-green-600 dark:text-green-400 mb-4 max-w-md mx-auto">{{ __('resources.no_violations_description') }}</p>
            @if($lastChecked)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-900/30 text-xs text-green-700 dark:text-green-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Last checked: {{ $lastChecked->format('d M Y, H:i') }} via SMS</span>
                </div>
            @endif
        </div>
    </div>
@endif
