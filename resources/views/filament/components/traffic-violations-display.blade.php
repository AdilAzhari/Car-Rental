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
                            {{ count($violations) }} violation{{ count($violations) > 1 ? 's' : '' }} found
                        </p>
                    </div>
                    @if($hasPending)
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-medium text-red-700 dark:text-red-300">Action Required</span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pending</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ collect($violations)->where('status', 'pending')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Resolved</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ collect($violations)->where('status', '!=', 'pending')->count() }}</p>
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
                @php
                    $isPending = ($violation['status'] ?? '') === 'pending';
                    $statusColor = $isPending ? 'red' : 'green';
                @endphp

                <div class="group relative overflow-hidden rounded-xl border transition-all duration-200 hover:shadow-lg
                    {{ $isPending
                        ? 'border-red-200 dark:border-red-800 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/10 dark:to-pink-900/10 hover:border-red-300 dark:hover:border-red-700'
                        : 'border-green-200 dark:border-green-800 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10 hover:border-green-300 dark:hover:border-green-700'
                    }}">

                    <!-- Status indicator line -->
                    <div class="absolute left-0 top-0 w-1 h-full {{ $isPending ? 'bg-red-500' : 'bg-green-500' }}"></div>

                    <div class="p-6 pl-8">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $violation['type'] ?? $violation['violation_type'] ?? __('resources.traffic_violation') }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                        {{ $isPending
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                            : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        }}">
                                        {{ ucfirst($violation['status'] ?? 'Unknown') }}
                                    </span>
                                </div>

                                @if(isset($violation['fine_amount']))
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg {{ $isPending ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30' }}">
                                        <svg class="w-4 h-4 {{ $isPending ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <span class="font-semibold {{ $isPending ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                                            RM {{ number_format($violation['fine_amount'], 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="text-right">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">#{{ $index + 1 }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            @if(isset($violation['date']))
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Date</span>
                                        <p class="text-gray-900 dark:text-gray-100">{{ Carbon::parse($violation['date'])->format('d M Y') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($violation['location']))
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Location</span>
                                        <p class="text-gray-900 dark:text-gray-100">{{ $violation['location'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($violation['reference']) || isset($violation['reference_number']))
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Reference</span>
                                        <p class="text-gray-900 dark:text-gray-100 font-mono text-xs">{{ $violation['reference'] ?? $violation['reference_number'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($violation['due_date']) && $isPending)
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-orange-700 dark:text-orange-300">Due Date</span>
                                        <p class="text-orange-900 dark:text-orange-100">{{ Carbon::parse($violation['due_date'])->format('d M Y') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($violation['paid_date']) && !$isPending)
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700 dark:text-green-300">Paid Date</span>
                                        <p class="text-green-900 dark:text-green-100">{{ Carbon::parse($violation['paid_date'])->format('d M Y') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if(isset($violation['description']) && $violation['description'])
                            <div class="mt-4 p-4 rounded-lg {{ $isPending ? 'bg-red-50 dark:bg-red-900/20' : 'bg-green-50 dark:bg-green-900/20' }} border {{ $isPending ? 'border-red-100 dark:border-red-800' : 'border-green-100 dark:border-green-800' }}">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Description</h5>
                                <p class="text-sm {{ $isPending ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                                    {{ $violation['description'] }}
                                </p>
                            </div>
                        @endif
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
