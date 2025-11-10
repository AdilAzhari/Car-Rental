<div class="space-y-4">
    <!-- Basic Info -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Log Name</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->log_name }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Event</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ ucfirst($record->event) }}</p>
        </div>
    </div>

    <!-- Description -->
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->description }}</p>
    </div>

    <!-- Subject -->
    @if($record->subject_type)
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">
            {{ class_basename($record->subject_type) }} #{{ $record->subject_id }}
        </p>
    </div>
    @endif

    <!-- Causer -->
    @if($record->causer)
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Performed By</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">
            {{ $record->causer->name }} ({{ $record->causer->email }})
        </p>
    </div>
    @endif

    <!-- Properties -->
    @if($record->properties && count($record->properties) > 0)
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Changes</h3>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            @if(isset($record->properties['attributes']))
            <div class="mb-3">
                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">New Values</h4>
                <div class="space-y-1">
                    @foreach($record->properties['attributes'] as $key => $value)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-gray-400">{{ $key }}:</span>
                        <span class="text-gray-900 dark:text-white font-mono">{{ is_array($value) ? json_encode($value) : $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($record->properties['old']))
            <div>
                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Old Values</h4>
                <div class="space-y-1">
                    @foreach($record->properties['old'] as $key => $value)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-gray-400">{{ $key }}:</span>
                        <span class="text-gray-900 dark:text-white font-mono line-through">{{ is_array($value) ? json_encode($value) : $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Timestamp -->
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Timestamp</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">
            {{ $record->created_at->format('F d, Y \a\t H:i:s') }}
            <span class="text-gray-500 dark:text-gray-400">({{ $record->created_at->diffForHumans() }})</span>
        </p>
    </div>
</div>
