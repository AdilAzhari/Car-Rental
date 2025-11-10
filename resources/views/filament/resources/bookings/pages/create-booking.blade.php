<x-filament-panels::page>
    <div class="max-w-none">
        <!-- Booking Creation Header -->
        <div class="mb-6 p-4 rounded-lg border bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">
                        {{ __('resources.create_new_booking') }}
                    </h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        {{ __('resources.fill_booking_form_details') }}
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('resources.complete_booking_details') }}
                    </div>
                    <div class="flex space-x-3">
                        <x-filament::button
                            type="button"
                            color="gray"
                            tag="a"
                            :href="$this->getResource()::getUrl('index')"
                        >
                            {{ __('resources.cancel') }}
                        </x-filament::button>

                        <x-filament::button type="submit">
                            {{ __('resources.create_booking') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>