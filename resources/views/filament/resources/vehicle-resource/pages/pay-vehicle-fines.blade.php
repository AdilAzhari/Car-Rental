<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Payment Gateway Notice --}}
        <div class="rounded-lg border-2 border-yellow-300 bg-yellow-50 p-6 text-center dark:border-yellow-700 dark:bg-yellow-900/20">
            <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100">
                Payment Gateway Integration In Progress
            </h3>
            <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                Online payment functionality will be available soon. You will be able to pay all fines securely using credit/debit cards, FPX, and e-wallets.
            </p>
        </div>

        @if ($this->hasAnyFines())
            {{-- Total Amount Due --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Total Amount Due</h2>
                <p class="mt-4 text-4xl font-bold text-red-600 dark:text-red-400">
                    RM {{ number_format($this->getTotalFines(), 2) }}
                </p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Vehicle: {{ $record->make }} {{ $record->model }} ({{ $record->plate_number }})
                </p>
            </div>

            {{-- Insurance Fine --}}
            @if ($this->hasInsuranceFine())
                <div class="rounded-lg border border-red-200 bg-white p-6 shadow-sm dark:border-red-800 dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Insurance Fine</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Insurance expired on {{ $record->insurance_expiry->format('d M Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                RM {{ number_format($record->insurance_fine_amount, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Traffic Violations --}}
            @if ($this->hasTrafficViolations())
                <div class="rounded-lg border border-orange-200 bg-white p-6 shadow-sm dark:border-orange-800 dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Traffic Violations</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $record->total_violations_count }} pending violation(s)
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                RM {{ number_format($record->total_fines_amount, 2) }}
                            </p>
                        </div>
                    </div>

                    @if ($record->traffic_violations && count($record->traffic_violations) > 0)
                        <div class="mt-4 space-y-2">
                            @foreach ($record->traffic_violations as $index => $violation)
                                @if ($violation['status'] === 'pending')
                                    <div class="rounded border border-gray-200 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-900/50">
                                        <div class="flex justify-between">
                                            <span class="font-medium">{{ $violation['violation_type'] ?? 'N/A' }}</span>
                                            <span class="font-semibold text-orange-600 dark:text-orange-400">
                                                RM {{ number_format($violation['fine_amount'] ?? 0, 2) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-gray-600 dark:text-gray-400">
                                            {{ $violation['date'] ?? 'N/A' }} • {{ $violation['location'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Parking Violations --}}
            @if ($this->hasParkingViolations())
                <div class="rounded-lg border border-blue-200 bg-white p-6 shadow-sm dark:border-blue-800 dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Parking Violations</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $record->total_parking_violations_count }} pending violation(s)
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                RM {{ number_format($record->total_parking_fines_amount, 2) }}
                            </p>
                        </div>
                    </div>

                    @if ($record->parking_violations && count($record->parking_violations) > 0)
                        <div class="mt-4 space-y-2">
                            @foreach ($record->parking_violations as $index => $violation)
                                @if ($violation['status'] === 'pending')
                                    <div class="rounded border border-gray-200 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-900/50">
                                        <div class="flex justify-between">
                                            <span class="font-medium">{{ $violation['violation_type'] ?? 'N/A' }}</span>
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                                RM {{ number_format($violation['fine_amount'] ?? 0, 2) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-gray-600 dark:text-gray-400">
                                            {{ $violation['date'] ?? 'N/A' }} • {{ $violation['location'] ?? 'N/A' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                            Authority: {{ $violation['authority'] ?? 'N/A' }} • Ref: {{ $violation['reference_number'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Payment Information --}}
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-6 dark:border-blue-700 dark:bg-blue-900/20">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                    What happens next?
                </h3>
                <div class="mt-4 space-y-2 text-sm text-blue-800 dark:text-blue-200">
                    <p>✓ Pay securely using Credit/Debit cards</p>
                    <p>✓ Use online banking (FPX)</p>
                    <p>✓ Pay via e-wallets (Touch 'n Go, GrabPay, etc.)</p>
                    <p>✓ Get instant payment confirmation</p>
                    <p>✓ Receive official payment receipts via email</p>
                </div>
            </div>
        @else
            {{-- No Fines --}}
            <div class="rounded-lg border border-green-200 bg-green-50 p-12 text-center dark:border-green-700 dark:bg-green-900/20">
                <h3 class="text-xl font-semibold text-green-900 dark:text-green-100">
                    No Outstanding Fines
                </h3>
                <p class="mt-2 text-green-700 dark:text-green-300">
                    This vehicle has no pending fines or violations. All payments are up to date!
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
