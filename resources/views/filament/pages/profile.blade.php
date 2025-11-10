<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Form --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <form wire:submit="updateProfile" class="space-y-6">
                    {{ $this->form }}

                    {{-- Form Actions --}}
                    <div class="flex justify-end">
                        {{ ($this->updateProfileAction)(['size' => 'lg']) }}
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>