<x-filament-panels::page>
    <style>
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .animate-fade-in {
            animation: fadeInScale 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-slide-in-right {
            animation: slideInRight 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .timeline-item {
            animation: slideInRight 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            animation-fill-mode: both;
        }

        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }

        .hover-scale {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-scale:hover {
            transform: scale(1.05) translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
        }

        .shimmer-bg {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            background-size: 1000px 100%;
            animation: shimmer 3s infinite;
        }
    </style>

    <div class="space-y-8">
        {{-- Hero Insurance Status Card --}}
        <div class="animate-fade-in relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 p-12 shadow-2xl">
            {{-- Background Decorations --}}
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="shimmer-bg absolute inset-0 opacity-20"></div>

            <div class="relative">
                <div class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                    <div class="flex-1">
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 backdrop-blur-sm">
                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span class="text-sm font-bold text-white">Insurance Management</span>
                        </div>
                        <h1 class="text-5xl font-black tracking-tight text-white">
                            Insurance Coverage
                        </h1>
                        <p class="mt-3 text-xl font-medium text-blue-100">
                            {{ $record->make }} {{ $record->model }}
                        </p>
                        <p class="mt-1 font-mono text-lg text-blue-200/80">
                            {{ $record->plate_number }}
                        </p>
                    </div>

                    <div class="flex-shrink-0">
                        @if ($this->isInsuranceExpired())
                            <div class="rounded-2xl bg-red-500/30 px-6 py-4 backdrop-blur-md ring-2 ring-red-400/50 hover-scale">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-red-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-lg font-black text-white">EXPIRED</p>
                                        <p class="text-xs font-semibold text-red-100">Requires renewal</p>
                                    </div>
                                </div>
                            </div>
                        @elseif ($this->getDaysUntilExpiry() <= 30)
                            <div class="rounded-2xl bg-amber-500/30 px-6 py-4 backdrop-blur-md ring-2 ring-amber-400/50 hover-scale">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-lg font-black text-white">EXPIRING SOON</p>
                                        <p class="text-xs font-semibold text-amber-100">Renew now</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl bg-green-500/30 px-6 py-4 backdrop-blur-md ring-2 ring-green-400/50 hover-scale">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-green-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-lg font-black text-white">ACTIVE</p>
                                        <p class="text-xs font-semibold text-green-100">Fully covered</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-12 grid gap-6 lg:grid-cols-2">
                    <div class="hover-scale group relative overflow-hidden rounded-3xl bg-white/10 p-8 backdrop-blur-lg ring-1 ring-white/20">
                        <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="relative">
                            <div class="mb-3 inline-flex rounded-xl bg-white/20 p-2">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold uppercase tracking-widest text-blue-200">Expiry Date</p>
                            <p class="mt-4 text-5xl font-black text-white">
                                {{ $record->insurance_expiry?->format('d M Y') ?? 'Not Set' }}
                            </p>
                            @if ($record->insurance_expiry && !$this->isInsuranceExpired())
                                <p class="mt-4 flex items-center gap-2 text-base font-bold text-blue-100">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ abs($this->getDaysUntilExpiry()) }} days remaining
                                </p>
                            @elseif ($record->insurance_expiry)
                                <p class="mt-4 flex items-center gap-2 text-base font-bold text-red-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Expired {{ abs($this->getDaysUntilExpiry()) }} days ago
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="hover-scale group relative overflow-hidden rounded-3xl bg-white/10 p-8 backdrop-blur-lg ring-1 ring-white/20">
                        <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="relative">
                            <div class="mb-3 inline-flex rounded-xl bg-white/20 p-2">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold uppercase tracking-widest text-blue-200">Coverage Type</p>
                            <p class="mt-4 text-5xl font-black text-white">
                                {{ $record->insurance_included ? 'Included' : 'Not Included' }}
                            </p>
                            <p class="mt-4 flex items-center gap-2 text-base font-bold text-blue-100">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Comprehensive coverage
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fine Alert --}}
        @if ($this->hasFine())
            <div class="animate-slide-in-right overflow-hidden rounded-3xl border-2 border-red-300 bg-gradient-to-br from-red-50 via-rose-50 to-red-50 shadow-2xl dark:border-red-700 dark:from-red-900/30 dark:via-rose-900/30 dark:to-red-900/30">
                <div class="flex items-stretch">
                    <div class="flex w-2 flex-shrink-0 bg-gradient-to-b from-red-500 to-rose-600"></div>
                    <div class="flex flex-1 items-start gap-6 p-8">
                        <div class="flex-shrink-0">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 shadow-xl ring-4 ring-red-100 dark:ring-red-800/50">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-black text-red-900 dark:text-red-100">
                                ⚠️ Outstanding Insurance Fine
                            </h3>
                            <p class="mt-3 text-lg font-medium leading-relaxed text-red-700 dark:text-red-300">
                                Your vehicle has an expired insurance with an unpaid fine of
                                <span class="inline-flex items-baseline gap-1 rounded-xl bg-red-100 px-3 py-1 font-black dark:bg-red-800/50">
                                    <span class="text-base">RM</span>
                                    <span class="text-2xl">{{ number_format($record->insurance_fine_amount, 2) }}</span>
                                </span>.
                                Please renew your insurance and settle the fine as soon as possible to avoid additional penalties.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Renewal Reminder --}}
        @if ($record->insurance_expiry && !$this->isInsuranceExpired() && $this->getDaysUntilExpiry() <= 30)
            <div class="animate-slide-in-right overflow-hidden rounded-3xl border border-amber-300 bg-gradient-to-br from-amber-50 via-yellow-50 to-amber-50 shadow-xl dark:border-amber-700 dark:from-amber-900/20 dark:via-yellow-900/20 dark:to-amber-900/20">
                <div class="flex items-stretch">
                    <div class="flex w-2 flex-shrink-0 bg-gradient-to-b from-amber-500 to-yellow-600"></div>
                    <div class="flex items-start gap-4 p-6">
                        <div class="flex-shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 shadow-lg">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-xl font-black text-amber-900 dark:text-amber-100">
                                Insurance Expiring Soon
                            </p>
                            <p class="mt-2 font-medium text-amber-700 dark:text-amber-300">
                                Your insurance will expire in <span class="font-black">{{ $this->getDaysUntilExpiry() }} days</span>. Consider renewing it now to avoid any lapses in coverage.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment History --}}
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-10 py-8 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                <h3 class="text-3xl font-black text-gray-900 dark:text-white">Payment History</h3>
                <p class="mt-2 font-medium text-gray-600 dark:text-gray-400">Recent insurance payments and renewals</p>
            </div>

            <div class="space-y-1 px-10 py-8">
                {{-- Timeline Item 1 --}}
                <div class="timeline-item group relative pl-10">
                    <div class="absolute left-0 top-0 flex h-full flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg ring-4 ring-white dark:ring-gray-800">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="h-full w-0.5 bg-gradient-to-b from-gray-300 to-transparent dark:from-gray-600"></div>
                    </div>
                    <div class="hover-scale mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 shadow-lg dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                        <div class="flex items-center justify-between p-6">
                            <div class="flex-1">
                                <h4 class="text-xl font-black text-gray-900 dark:text-white">Annual Insurance Renewal</h4>
                                <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ now()->subYear()->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-black text-gray-900 dark:text-white">RM 1,200.00</p>
                                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 px-4 py-1 text-xs font-black text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    PAID
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Timeline Item 2 --}}
                <div class="timeline-item group relative pl-10">
                    <div class="absolute left-0 top-0 flex h-full flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg ring-4 ring-white dark:ring-gray-800">
                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="h-full w-0.5 bg-gradient-to-b from-gray-300 to-transparent dark:from-gray-600"></div>
                    </div>
                    <div class="hover-scale mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 shadow-lg dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                        <div class="flex items-center justify-between p-6">
                            <div class="flex-1">
                                <h4 class="text-xl font-black text-gray-900 dark:text-white">Insurance Policy Update</h4>
                                <p class="mt-1 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ now()->subMonths(6)->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-black text-gray-900 dark:text-white">RM 150.00</p>
                                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 px-4 py-1 text-xs font-black text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    PAID
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- End of Timeline --}}
                <div class="timeline-item relative pl-10">
                    <div class="absolute left-0 top-0 flex h-8 items-center">
                        <div class="h-8 w-8 rounded-full bg-gray-300 ring-4 ring-white dark:bg-gray-600 dark:ring-gray-800"></div>
                    </div>
                    <div class="flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/50 py-10 dark:border-gray-600 dark:bg-gray-900/20">
                        <p class="font-bold text-gray-500 dark:text-gray-400">No older payment records</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Gateway Notice --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 p-12 text-center shadow-2xl dark:from-amber-900/20 dark:via-yellow-900/20 dark:to-orange-900/20">
            <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-gradient-to-br from-amber-400/20 to-yellow-500/20 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-gradient-to-br from-orange-400/20 to-amber-500/20 blur-3xl"></div>

            <div class="relative">
                <div class="mx-auto mb-8 inline-flex rounded-2xl bg-gradient-to-br from-amber-500 via-yellow-500 to-orange-600 p-4 shadow-xl ring-4 ring-amber-100/50 dark:ring-amber-800/30">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-4xl font-black text-amber-900 dark:text-amber-100">
                    🚀 Payment Gateway Integration In Progress
                </h3>
                <p class="mx-auto mt-5 max-w-3xl text-xl font-medium leading-relaxed text-amber-700 dark:text-amber-300">
                    Online payment for insurance renewal and fines will be available soon. You'll be able to pay securely using credit/debit cards, FPX, and e-wallets.
                </p>

                <div class="mx-auto mt-10 grid max-w-4xl gap-4 sm:grid-cols-4">
                    <div class="hover-scale rounded-2xl bg-white/70 p-5 backdrop-blur-sm dark:bg-gray-800/70">
                        <div class="mb-2 text-3xl">💳</div>
                        <p class="text-sm font-black text-amber-900 dark:text-amber-100">Multiple Payment Methods</p>
                    </div>
                    <div class="hover-scale rounded-2xl bg-white/70 p-5 backdrop-blur-sm dark:bg-gray-800/70">
                        <div class="mb-2 text-3xl">⚡</div>
                        <p class="text-sm font-black text-amber-900 dark:text-amber-100">Instant Confirmation</p>
                    </div>
                    <div class="hover-scale rounded-2xl bg-white/70 p-5 backdrop-blur-sm dark:bg-gray-800/70">
                        <div class="mb-2 text-3xl">🔒</div>
                        <p class="text-sm font-black text-amber-900 dark:text-amber-100">Secure & Safe</p>
                    </div>
                    <div class="hover-scale rounded-2xl bg-white/70 p-5 backdrop-blur-sm dark:bg-gray-800/70">
                        <div class="mb-2 text-3xl">📧</div>
                        <p class="text-sm font-black text-amber-900 dark:text-amber-100">Email Receipts</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Important Information --}}
        <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-50 shadow-2xl dark:from-blue-900/20 dark:via-indigo-900/20 dark:to-blue-900/20">
            <div class="border-b border-blue-200 bg-gradient-to-r from-blue-100/50 to-indigo-100/50 px-10 py-8 dark:border-blue-700 dark:from-blue-800/50 dark:to-indigo-800/50">
                <h3 class="text-3xl font-black text-blue-900 dark:text-blue-100">
                    📋 Important Information
                </h3>
            </div>
            <div class="space-y-3 p-10">
                @foreach([
                    'Insurance must be renewed before the expiry date to avoid fines',
                    'Expired insurance may result in additional penalties',
                    'Vehicles with expired insurance cannot be rented out',
                    'Keep your insurance documents up to date',
                    'Contact your insurance provider for policy details'
                ] as $info)
                    <div class="hover-scale flex items-start gap-4 rounded-2xl bg-white/60 p-5 backdrop-blur-sm dark:bg-gray-800/60">
                        <div class="mt-1 flex-shrink-0">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600">
                                <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <p class="flex-1 font-bold text-blue-900 dark:text-blue-200">{{ $info }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
