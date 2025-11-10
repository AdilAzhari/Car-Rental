<div>
    @isset($jsPath)
        <script>{!! file_get_contents($jsPath) !!}</script>
    @endisset
    @isset($cssPath)
        <style>{!! file_get_contents($cssPath) !!}</style>
    @endisset

    <div x-data="LivewireUISpotlight({
        componentId: '{{ $this->id() }}',
        placeholder: '{{ trans('livewire-ui-spotlight::spotlight.placeholder') }}',
        commands: @js($commands),
        showResultsWithoutInput: '{{ config('livewire-ui-spotlight.show_results_without_input') }}',
    })"
         x-init="init()"
         x-show="isOpen"
         x-cloak
         @foreach(config('livewire-ui-spotlight.shortcuts') as $key)
            @keydown.window.prevent.cmd.{{ $key }}="toggleOpen()"
            @keydown.window.prevent.ctrl.{{ $key }}="toggleOpen()"
         @endforeach
         @keydown.window.escape="isOpen = false"
         @toggle-spotlight.window="toggleOpen()"
         class="fixed z-50 px-4 pt-4 flex items-start justify-center inset-0 sm:pt-8">
        <div x-show="isOpen" @click="isOpen = false" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        </div>

        <div x-show="isOpen" x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-2xl ring-1 ring-black/5 dark:ring-white/10 transform transition-all max-w-2xl w-full">
            <div class="relative">
                <div class="absolute h-full right-5 flex items-center">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" wire:loading.delay>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <input @keydown.tab.prevent="" @keydown.prevent.stop.enter="go()" @keydown.prevent.arrow-up="selectUp()"
                       @keydown.prevent.arrow-down="selectDown()" x-ref="input" x-model="input"
                       type="text"
                       style="caret-color: #6b7280; border: 0 !important;"
                       class="appearance-none w-full bg-transparent px-6 py-5 text-gray-900 dark:text-gray-100 text-lg placeholder-gray-400 dark:placeholder-gray-500 focus:border-0 focus:border-transparent focus:shadow-none outline-none focus:outline-none"
                       x-bind:placeholder="inputPlaceholder">
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700" x-show="filteredItems().length > 0" style="display: none;">
                <ul x-ref="results" style="max-height: 320px;" class="overflow-y-auto">
                    <template x-for="(item, i) in filteredItems()" :key>
                        <li>
                            <button @click="go(item[0].item.id)" class="block w-full px-6 py-4 text-left border-l-4 border-transparent transition-all duration-150"
                                    :class="{
                                        'bg-gray-50 dark:bg-gray-800 border-l-blue-500': selected === i,
                                        'hover:bg-gray-50 dark:hover:bg-gray-800': selected !== i
                                    }">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span x-text="item[0].item.name" class="block font-medium"
                                              :class="{'text-gray-900 dark:text-gray-100': selected !== i, 'text-blue-700 dark:text-blue-300': selected === i }"></span>
                                        <span x-text="item[0].item.description" class="block text-sm mt-1"
                                              :class="{'text-gray-500 dark:text-gray-400': selected !== i, 'text-blue-600 dark:text-blue-400': selected === i }"></span>
                                    </div>
                                    <div x-show="selected === i" class="text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</div>
