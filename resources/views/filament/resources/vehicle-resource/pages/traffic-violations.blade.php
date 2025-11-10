<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Statistics Cards --}}
        <div class="grid gap-6 md:grid-cols-3">
            {{-- Total Violations --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Violations</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $record->total_violations_count ?? count($record->traffic_violations ?? []) }}
                </p>
            </div>

            {{-- Pending Violations --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                    {{ collect($record->traffic_violations ?? [])->where('status', 'pending')->count() }}
                </p>
            </div>

            {{-- Total Fines --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Fines</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    RM {{ number_format($record->total_fines_amount ?? 0, 2) }}
                </p>
            </div>
        </div>

        {{-- Info Banner --}}
        @if ($record->violations_last_checked)
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-700 dark:bg-blue-900/20">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                    Last checked: {{ $record->violations_last_checked->format('d M Y H:i') }}
                </p>
                <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                    Violations are retrieved via SMS from JPJ (Road Transport Department). Use the "Check Violations" button to refresh.
                </p>
            </div>
        @endif

        {{-- Payment Gateway Notice --}}
        <div class="rounded-lg border-2 border-yellow-300 bg-yellow-50 p-4 text-center dark:border-yellow-700 dark:bg-yellow-900/20">
            <h3 class="text-base font-semibold text-yellow-900 dark:text-yellow-100">
                Payment Gateway Integration In Progress
            </h3>
            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                Online payment for traffic violations will be available soon.
            </p>
        </div>

        {{-- Violations Table --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @php
                $violations = $this->getViolations();
            @endphp

            @if(count($violations) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Violation Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Location
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Fine Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Reference
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @foreach($violations as $violation)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $violation['type'] ?? $violation['violation_type'] ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($violation['date'])->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $violation['location'] ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                        RM {{ number_format($violation['fine_amount'], 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold leading-5
                                            {{ $violation['status'] === 'pending' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                            {{ $violation['status'] === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : '' }}
                                            {{ $violation['status'] === 'resolved' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : '' }}">
                                            {{ ucfirst($violation['status']) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-500 dark:text-gray-400">
                                        {{ $violation['reference'] ?? $violation['reference_number'] ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-12">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">No Traffic Violations</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">This vehicle has no recorded traffic violations.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
