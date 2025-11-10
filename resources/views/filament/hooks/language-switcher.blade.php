@php
    $currentLocale = app()->getLocale();
    $availableLocales = [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'icon' => '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm1 1v12h12V5H4zm2 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>'
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'icon' => '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm1 1v12h12V5H4zm2 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>'
        ]
    ];
@endphp

<div class="flex items-center gap-2 px-4">
    <div class="relative">
        <x-filament::dropdown>
            <x-slot name="trigger">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-language"
                    tooltip="{{ __('app.change_language') }}"
                    size="sm"
                >
                    {{ $availableLocales[$currentLocale]['native'] }}
                </x-filament::button>
            </x-slot>

            <x-filament::dropdown.list>
                @foreach($availableLocales as $localeCode => $locale)
                    <x-filament::dropdown.list.item
                        :active="$currentLocale === $localeCode"
                        icon="heroicon-o-globe-alt"
                        wire:click="$dispatch('switch-language', { locale: '{{ $localeCode }}' })"
                        style="cursor: pointer"
                    >
                        {{ $locale['native'] }} ({{ $locale['name'] }})
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen for language switch events
    document.addEventListener('switch-language', function(event) {
        const locale = event.detail.locale;

        // Create form to submit locale change
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;

        const localeInput = document.createElement('input');
        localeInput.type = 'hidden';
        localeInput.name = 'locale';
        localeInput.value = locale;

        form.appendChild(localeInput);
        document.body.appendChild(form);
        form.submit();
    });
});
</script>