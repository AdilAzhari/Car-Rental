<!-- User Menu Items -->
<x-filament::dropdown.list>
    <x-filament::dropdown.list.item
        :href="url('/admin/profile')"
        icon="heroicon-m-user-circle"
    >
        {{ __('resources.my_profile') }}
    </x-filament::dropdown.list.item>
    
    <x-filament::dropdown.list.item
        :href="url('/admin')"
        icon="heroicon-m-home"
    >
        {{ __('resources.dashboard') }}
    </x-filament::dropdown.list.item>
</x-filament::dropdown.list>