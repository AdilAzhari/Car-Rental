<div class="relative" x-data="{ open: false }">
    <!-- Notification Bell Button -->
    <button 
        @click="open = !open" 
        @click.away="open = false"
        class="relative flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 transition-colors duration-200"
        type="button"
        aria-label="{{ __('notifications.notifications') }}"
    >
        <!-- Bell Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Notification Badge -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center min-w-[20px] shadow-lg">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-12 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
        x-cloak
    >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('notifications.notifications') }}
            </h3>
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 font-medium"
                >
                    {{ __('notifications.mark_all_read') }}
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div class="border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <div class="flex items-start space-x-3">
                            <!-- Notification Icon -->
                            <div class="flex-shrink-0">
                                @php
                                    $iconClass = match($notification['type']) {
                                        'App\Notifications\WelcomeNewUser' => 'text-green-500',
                                        'App\Notifications\BookingConfirmed' => 'text-blue-500',
                                        'App\Notifications\PaymentReceived' => 'text-green-500',
                                        'App\Notifications\VehicleApproved' => 'text-green-500',
                                        default => 'text-gray-500'
                                    };
                                    
                                    $icon = match($notification['type']) {
                                        'App\Notifications\WelcomeNewUser' => 'heroicon-s-user-plus',
                                        'App\Notifications\BookingConfirmed' => 'heroicon-s-calendar-days',
                                        'App\Notifications\PaymentReceived' => 'heroicon-s-currency-dollar',
                                        'App\Notifications\VehicleApproved' => 'heroicon-s-truck',
                                        default => 'heroicon-s-bell'
                                    };
                                @endphp
                                
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                                    <x-filament::icon :icon="$icon" class="w-4 h-4 {{ $iconClass }}" />
                                </div>
                            </div>
                            
                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $notification['data']['title'] ?? __('notifications.new_notification') }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $notification['data']['message'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                                </p>
                            </div>
                            
                            <!-- Unread Indicator -->
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons (if any) -->
                        @if(isset($notification['data']['action_url']))
                            <div class="mt-3 ml-11">
                                <a 
                                    href="{{ $notification['data']['action_url'] }}"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-400 dark:bg-blue-900 dark:hover:bg-blue-800 transition-colors duration-150"
                                >
                                    {{ $notification['data']['action_text'] ?? __('notifications.view') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-600">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                        {{ __('notifications.no_notifications') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('notifications.no_notifications_description') }}
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if(count($notifications) > 0)
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-b-lg border-t border-gray-200 dark:border-gray-600">
                <a 
                    href="{{ url('/admin/notifications') }}" 
                    class="block w-full text-center text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 py-2"
                >
                    {{ __('notifications.view_all') }}
                </a>
            </div>
        @endif
    </div>
</div>