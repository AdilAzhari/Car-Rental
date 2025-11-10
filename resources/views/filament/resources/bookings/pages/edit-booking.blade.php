<x-filament-panels::page>
    <div class="max-w-none">
        <!-- Booking Status Header -->
        @if($this->getRecord())
            <div class="mb-6 p-4 rounded-lg border-2 
                @if($this->getRecord()->status === 'confirmed') bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800
                @elseif($this->getRecord()->status === 'pending') bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800
                @elseif($this->getRecord()->status === 'cancelled') bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800
                @else bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800
                @endif">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold 
                            @if($this->getRecord()->status === 'confirmed') text-green-800 dark:text-green-200
                            @elseif($this->getRecord()->status === 'pending') text-yellow-800 dark:text-yellow-200
                            @elseif($this->getRecord()->status === 'cancelled') text-red-800 dark:text-red-200
                            @else text-blue-800 dark:text-blue-200
                            @endif">
                            {{ __('resources.booking_id') }}: #{{ $this->getRecord()->id }}
                        </h3>
                        <p class="text-sm mt-1 
                            @if($this->getRecord()->status === 'confirmed') text-green-700 dark:text-green-300
                            @elseif($this->getRecord()->status === 'pending') text-yellow-700 dark:text-yellow-300
                            @elseif($this->getRecord()->status === 'cancelled') text-red-700 dark:text-red-300
                            @else text-blue-700 dark:text-blue-300
                            @endif">
                            {{ __('resources.status') }}: {{ ucfirst($this->getRecord()->status) }} | 
                            {{ __('resources.created') }}: {{ $this->getRecord()->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold 
                            @if($this->getRecord()->status === 'confirmed') text-green-800 dark:text-green-200
                            @elseif($this->getRecord()->status === 'pending') text-yellow-800 dark:text-yellow-200
                            @elseif($this->getRecord()->status === 'cancelled') text-red-800 dark:text-red-200
                            @else text-blue-800 dark:text-blue-200
                            @endif">
                            MYR {{ number_format($this->getRecord()->total_amount ?? 0, 2) }}
                        </div>
                        <p class="text-xs uppercase tracking-wide 
                            @if($this->getRecord()->status === 'confirmed') text-green-600 dark:text-green-400
                            @elseif($this->getRecord()->status === 'pending') text-yellow-600 dark:text-yellow-400
                            @elseif($this->getRecord()->status === 'cancelled') text-red-600 dark:text-red-400
                            @else text-blue-600 dark:text-blue-400
                            @endif">
                            {{ __('resources.total_amount') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        @if($this->getRecord())
                            {{ __('resources.booking_last_updated') }}: 
                            <span class="font-medium">{{ $this->getRecord()->updated_at->diffForHumans() }}</span>
                        @endif
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
                            {{ __('resources.save_changes') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>