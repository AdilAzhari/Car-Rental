<x-filament-panels::page>
    <style>
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }
            50% {
                box-shadow: 0 0 40px rgba(59, 130, 246, 0.6);
            }
        }

        .animate-slide-in {
            animation: slideInUp 0.6s ease-out;
        }

        .stat-card {
            animation: slideInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }

        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
        }

        .dark .glass-effect {
            background-color: rgba(17, 24, 39, 0.75);
        }
    </style>

    <div class="space-y-8">
        {{-- Hero Statistics Section --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Total Violations Card --}}
            <div class="stat-card hover-lift group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 to-blue-100 p-8 shadow-xl ring-1 ring-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 dark:ring-blue-700/50">
                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-gradient-to-br from-blue-400/30 to-blue-600/30 blur-3xl"></div>
                <div class="relative">
                    <div class="mb-4 inline-flex rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-3 shadow-lg ring-2 ring-blue-100 dark:ring-blue-800/50">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">Total Violations</p>
                    <p class="mt-3 text-6xl font-black tracking-tight text-blue-900 dark:text-blue-100">
                        {{ $record->total_parking_violations_count ?? count($record->parking_violations ?? []) }}
                    </p>
                    <p class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-400">Recorded offenses</p>
                </div>
            </div>

            {{-- Pending Card --}}
            <div class="stat-card hover-lift group relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-50 to-rose-100 p-8 shadow-xl ring-1 ring-red-200 dark:from-red-900/30 dark:to-rose-800/30 dark:ring-red-700/50">
                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-gradient-to-br from-red-400/30 to-red-600/30 blur-3xl"></div>
                <div class="relative">
                    <div class="mb-4 inline-flex rounded-xl bg-gradient-to-br from-red-500 to-rose-600 p-3 shadow-lg ring-2 ring-red-100 dark:ring-red-800/50">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-widest text-red-600 dark:text-red-400">Pending Payment</p>
                    <p class="mt-3 text-6xl font-black tracking-tight text-red-900 dark:text-red-100">
                        {{ collect($record->parking_violations ?? [])->where('status', 'pending')->count() }}
                    </p>
                    <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">Awaiting settlement</p>
                </div>
            </div>

            {{-- Total Fines Card --}}
            <div class="stat-card hover-lift group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 to-orange-100 p-8 shadow-xl ring-1 ring-amber-200 dark:from-amber-900/30 dark:to-orange-800/30 dark:ring-amber-700/50">
                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-gradient-to-br from-amber-400/30 to-orange-600/30 blur-3xl"></div>
                <div class="relative">
                    <div class="mb-4 inline-flex rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-3 shadow-lg ring-2 ring-amber-100 dark:ring-amber-800/50">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400">Total Amount Due</p>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-amber-900 dark:text-amber-100">RM</span>
                        <span class="text-5xl font-black tracking-tight text-amber-900 dark:text-amber-100">{{ number_format($record->total_parking_fines_amount ?? 0, 2) }}</span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-amber-700 dark:text-amber-400">Outstanding balance</p>
                </div>
            </div>
        </div>

        {{-- Info Banner --}}
        @if ($record->parking_violations_last_checked)
            <div class="rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 p-6 shadow-sm dark:from-blue-900/20 dark:to-indigo-900/20">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                            <div class="h-6 w-6 rounded bg-gradient-to-br from-blue-500 to-indigo-600"></div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-900 dark:text-blue-100">
                            Last checked: {{ $record->parking_violations_last_checked->format('d M Y H:i') }}
                        </p>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                            Parking violations are retrieved from municipal authorities (DBKL, MPSJ, MBPJ, etc.)
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Municipal Authorities Legend --}}
        <div class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
            <h3 class="mb-6 text-lg font-bold text-gray-900 dark:text-white">Municipal Authorities</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="group flex items-center gap-3 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 px-4 py-3 transition-all hover:scale-105 dark:from-blue-900/20 dark:to-blue-900/30">
                    <div class="h-3 w-3 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg"></div>
                    <span class="text-sm font-semibold text-blue-900 dark:text-blue-300">DBKL</span>
                </div>
                <div class="group flex items-center gap-3 rounded-xl bg-gradient-to-br from-green-50 to-green-100 px-4 py-3 transition-all hover:scale-105 dark:from-green-900/20 dark:to-green-900/30">
                    <div class="h-3 w-3 rounded-full bg-gradient-to-br from-green-400 to-green-600 shadow-lg"></div>
                    <span class="text-sm font-semibold text-green-900 dark:text-green-300">MPSJ</span>
                </div>
                <div class="group flex items-center gap-3 rounded-xl bg-gradient-to-br from-yellow-50 to-yellow-100 px-4 py-3 transition-all hover:scale-105 dark:from-yellow-900/20 dark:to-yellow-900/30">
                    <div class="h-3 w-3 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 shadow-lg"></div>
                    <span class="text-sm font-semibold text-yellow-900 dark:text-yellow-300">MBPJ</span>
                </div>
                <div class="group flex items-center gap-3 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 px-4 py-3 transition-all hover:scale-105 dark:from-purple-900/20 dark:to-purple-900/30">
                    <div class="h-3 w-3 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 shadow-lg"></div>
                    <span class="text-sm font-semibold text-purple-900 dark:text-purple-300">MBSA</span>
                </div>
                <div class="group flex items-center gap-3 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-3 transition-all hover:scale-105 dark:from-gray-700 dark:to-gray-800">
                    <div class="h-3 w-3 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 shadow-lg"></div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-300">Others</span>
                </div>
            </div>
        </div>

        {{-- Payment Gateway Notice --}}
        <div class="animate-slide-in relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 p-12 text-center shadow-2xl dark:from-amber-900/20 dark:via-yellow-900/20 dark:to-orange-900/20">
            <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-gradient-to-br from-amber-400/20 to-yellow-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-gradient-to-br from-orange-400/20 to-amber-500/20 blur-3xl"></div>

            <div class="relative">
                <div class="mx-auto mb-6 inline-flex rounded-2xl bg-gradient-to-br from-amber-500 via-yellow-500 to-orange-600 p-4 shadow-xl ring-4 ring-amber-100 dark:ring-amber-800/30">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-amber-900 dark:text-amber-100">
                    🚀 Payment Gateway Integration In Progress
                </h3>
                <p class="mx-auto mt-4 max-w-3xl text-lg font-medium leading-relaxed text-amber-700 dark:text-amber-300">
                    Online payment for parking violations will be available soon. You'll be able to pay fines from multiple authorities at once using credit/debit cards, FPX, and e-wallets.
                </p>
                <div class="mx-auto mt-8 grid max-w-3xl gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/60 p-4 backdrop-blur-sm dark:bg-gray-800/60">
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-100">💳 Multiple Payment Methods</p>
                    </div>
                    <div class="rounded-2xl bg-white/60 p-4 backdrop-blur-sm dark:bg-gray-800/60">
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-100">⚡ Instant Confirmation</p>
                    </div>
                    <div class="rounded-2xl bg-white/60 p-4 backdrop-blur-sm dark:bg-gray-800/60">
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-100">🔒 Secure & Safe</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Violations Table --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
            @php
                $violations = $this->getViolations();
            @endphp

            @if(count($violations) > 0)
                <div class="px-6 py-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Violation Records</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Detailed list of all parking violations</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-y border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Authority</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Date & Time</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Location</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($violations as $violation)
                                <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                    <td class="px-6 py-5">
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $violation['violation_type'] ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold shadow-sm
                                            {{ $violation['authority'] === 'DBKL' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                            {{ $violation['authority'] === 'MPSJ' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                            {{ $violation['authority'] === 'MBPJ' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : '' }}
                                            {{ $violation['authority'] === 'MBSA' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : '' }}">
                                            {{ $violation['authority'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($violation['date'])->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-5 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $violation['location'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">RM {{ number_format($violation['fine_amount'], 2) }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold shadow-sm
                                            {{ $violation['status'] === 'pending' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : '' }}
                                            {{ $violation['status'] === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                            {{ $violation['status'] === 'resolved' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                            {{ $violation['status'] === 'appealed' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : '' }}">
                                            {{ ucfirst($violation['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ $violation['reference_number'] ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-20">
                    <div class="mb-6 inline-flex rounded-2xl bg-green-100 p-6 dark:bg-green-900/20">
                        <div class="h-16 w-16 rounded-xl bg-gradient-to-br from-green-400 to-green-600"></div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">No Parking Violations</h3>
                    <p class="mt-2 text-center text-gray-500 dark:text-gray-400">This vehicle has a clean record with no parking violations.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
