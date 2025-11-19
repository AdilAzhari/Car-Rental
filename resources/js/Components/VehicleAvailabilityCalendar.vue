<template>
    <div class="vehicle-availability-calendar">
        <!-- Calendar Header -->
        <div class="calendar-header mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Select Your Rental Dates
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Red dates are unavailable. Click on available dates to select your rental period.
            </p>
        </div>

        <!-- Date Range Display -->
        <div v-if="selectedStartDate || selectedEndDate" class="selected-range mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">
                Selected Period:
            </div>
            <div class="text-blue-700 dark:text-blue-300">
                {{ formatSelectedRange() }}
            </div>
            <div v-if="selectedStartDate && selectedEndDate" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                {{ calculateDuration() }} day{{ calculateDuration() !== 1 ? 's' : '' }} •
                Total: ${{ calculateTotalCost() }}
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="text-sm text-red-700 dark:text-red-400">
                {{ error }}
            </div>
        </div>

        <!-- Calendar Grid -->
        <div v-if="!loading && availability.length" class="calendar-grid">
            <!-- Month Navigation -->
            <div class="flex justify-between items-center mb-4">
                <button
                    @click="navigateMonth(-1)"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :disabled="loading"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <h4 class="text-lg font-medium">
                    {{ formatMonthYear }}
                </h4>

                <button
                    @click="navigateMonth(1)"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :disabled="loading"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Calendar Days Headers -->
            <div class="grid grid-cols-7 gap-1 mb-2">
                <div v-for="day in dayHeaders" :key="day" class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 py-2">
                    {{ day }}
                </div>
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-1">
                <button
                    v-for="day in availability"
                    :key="day.date"
                    @click="selectDate(day.date)"
                    :disabled="!day.is_available || day.is_past"
                    :class="getDayClasses(day)"
                    :title="getDayTitle(day)"
                    class="relative h-10 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    {{ formatDay(day.date) }}

                    <!-- Blocked indicator -->
                    <div v-if="day.is_blocked" class="absolute inset-0 bg-red-500 bg-opacity-20 rounded-lg"></div>

                    <!-- Selected range indicator -->
                    <div v-if="isInSelectedRange(day.date)" class="absolute inset-0 bg-blue-500 bg-opacity-20 rounded-lg"></div>
                </button>
            </div>
        </div>

        <!-- Alternative Dates Section -->
        <div v-if="alternatives.length && hasConflict" class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <h4 class="font-medium text-yellow-900 dark:text-yellow-100 mb-3">
                Alternative Available Dates
            </h4>
            <div class="space-y-2">
                <button
                    v-for="alternative in alternatives"
                    :key="`${alternative.start_date}-${alternative.end_date}`"
                    @click="selectAlternative(alternative)"
                    class="block w-full text-left p-3 bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:bg-yellow-50 dark:hover:bg-yellow-900/30 transition-colors"
                >
                    <div class="font-medium">
                        {{ formatDate(alternative.start_date) }} - {{ formatDate(alternative.end_date) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ alternative.duration_days }} day{{ alternative.duration_days !== 1 ? 's' : '' }}
                        <span v-if="alternative.days_from_preferred !== 0">
                            • {{ Math.abs(alternative.days_from_preferred) }} days {{ alternative.days_from_preferred > 0 ? 'after' : 'before' }} preferred
                        </span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Booking Action -->
        <div v-if="selectedStartDate && selectedEndDate" class="mt-6">
            <button
                @click="confirmSelection"
                :disabled="loading || hasConflict"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-3 px-4 rounded-lg transition-colors"
            >
                Continue with Selected Dates
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { format, parse, addMonths, subMonths, differenceInDays, isAfter, isBefore } from 'date-fns'

const props = defineProps({
    vehicleId: {
        type: Number,
        required: true
    },
    dailyRate: {
        type: Number,
        default: 0
    },
    minDate: {
        type: String,
        default: () => format(new Date(), 'yyyy-MM-dd')
    }
})

const emit = defineEmits(['dateSelected', 'error'])

// Reactive data
const loading = ref(false)
const error = ref('')
const currentDate = ref(new Date())
const availability = ref([])
const alternatives = ref([])
const selectedStartDate = ref('')
const selectedEndDate = ref('')
const blockedRanges = ref([])
const hasConflict = ref(false)

// Day headers for calendar
const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

// Computed properties
const formatMonthYear = computed(() => {
    return format(currentDate.value, 'MMMM yyyy')
})

// Methods
const loadAvailability = async () => {
    if (!props.vehicleId) return

    loading.value = true
    error.value = ''

    try {
        const startDate = format(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1), 'yyyy-MM-dd')
        const endDate = format(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0), 'yyyy-MM-dd')

        const response = await fetch(`/api/vehicles/${props.vehicleId}/availability/calendar?start_date=${startDate}&end_date=${endDate}`, {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error('Failed to load availability')
        }

        const data = await response.json()

        if (data.success) {
            availability.value = data.availability
            alternatives.value = data.alternatives || []
            blockedRanges.value = data.blocked_ranges || []
        } else {
            throw new Error(data.message || 'Failed to load availability')
        }
    } catch (err) {
        error.value = err.message
        emit('error', err.message)
    } finally {
        loading.value = false
    }
}

const navigateMonth = (direction) => {
    if (direction > 0) {
        currentDate.value = addMonths(currentDate.value, 1)
    } else {
        currentDate.value = subMonths(currentDate.value, 1)
    }
}

const selectDate = (date) => {
    if (!selectedStartDate.value || (selectedStartDate.value && selectedEndDate.value)) {
        // Start new selection
        selectedStartDate.value = date
        selectedEndDate.value = ''
        hasConflict.value = false
    } else if (selectedStartDate.value && !selectedEndDate.value) {
        // Complete the range
        if (isAfter(parse(date, 'yyyy-MM-dd', new Date()), parse(selectedStartDate.value, 'yyyy-MM-dd', new Date()))) {
            selectedEndDate.value = date
            checkRangeAvailability()
        } else {
            // Selected date is before start date, restart selection
            selectedStartDate.value = date
            selectedEndDate.value = ''
        }
    }
}

const selectAlternative = (alternative) => {
    selectedStartDate.value = alternative.start_date
    selectedEndDate.value = alternative.end_date
    hasConflict.value = false

    // Navigate calendar to show the selected month
    const altDate = parse(alternative.start_date, 'yyyy-MM-dd', new Date())
    currentDate.value = new Date(altDate.getFullYear(), altDate.getMonth(), 1)
    loadAvailability()
}

const checkRangeAvailability = async () => {
    if (!selectedStartDate.value || !selectedEndDate.value) return

    loading.value = true

    try {
        // Get CSRF cookie first
        await fetch('/sanctum/csrf-cookie')

        const response = await fetch('/api/vehicles/check-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
            body: JSON.stringify({
                vehicle_id: props.vehicleId,
                start_date: selectedStartDate.value,
                end_date: selectedEndDate.value
            })
        })

        const data = await response.json()

        if (data.success) {
            hasConflict.value = data.has_conflicts
            if (data.has_conflicts && data.resolution_options) {
                alternatives.value = data.resolution_options.find(opt => opt.type === 'alternative_dates')?.suggestions || []
            }
        }
    } catch (err) {
        console.error('Failed to check availability:', err)
    } finally {
        loading.value = false
    }
}

const confirmSelection = () => {
    if (selectedStartDate.value && selectedEndDate.value && !hasConflict.value) {
        emit('dateSelected', {
            startDate: selectedStartDate.value,
            endDate: selectedEndDate.value,
            duration: calculateDuration(),
            totalCost: calculateTotalCost()
        })
    }
}

const getDayClasses = (day) => {
    const classes = ['border']

    if (day.is_past) {
        classes.push('text-gray-400', 'bg-gray-100', 'dark:bg-gray-800', 'cursor-not-allowed')
    } else if (day.is_blocked) {
        classes.push('text-red-700', 'bg-red-100', 'border-red-200', 'dark:bg-red-900/30', 'dark:border-red-800', 'cursor-not-allowed')
    } else if (!day.is_available) {
        classes.push('text-gray-500', 'bg-gray-200', 'dark:bg-gray-700', 'cursor-not-allowed')
    } else {
        classes.push('text-gray-900', 'bg-white', 'border-gray-200', 'hover:bg-blue-50', 'dark:bg-gray-800', 'dark:text-gray-100', 'dark:border-gray-700', 'dark:hover:bg-blue-900/20')

        if (day.date === selectedStartDate.value || day.date === selectedEndDate.value) {
            classes.push('bg-blue-600', 'text-white', 'border-blue-600')
        } else if (isInSelectedRange(day.date)) {
            classes.push('bg-blue-100', 'border-blue-200', 'dark:bg-blue-900/30', 'dark:border-blue-800')
        }
    }

    return classes.join(' ')
}

const getDayTitle = (day) => {
    if (day.is_past) return 'Past date'
    if (day.is_blocked) return 'Already booked'
    if (!day.is_available) return 'Not available'
    return `Available for booking - ${day.day_name}`
}

const isInSelectedRange = (date) => {
    if (!selectedStartDate.value || !selectedEndDate.value) return false

    const checkDate = parse(date, 'yyyy-MM-dd', new Date())
    const startDate = parse(selectedStartDate.value, 'yyyy-MM-dd', new Date())
    const endDate = parse(selectedEndDate.value, 'yyyy-MM-dd', new Date())

    return isAfter(checkDate, startDate) && isBefore(checkDate, endDate)
}

const formatDay = (dateString) => {
    return format(parse(dateString, 'yyyy-MM-dd', new Date()), 'd')
}

const formatDate = (dateString) => {
    return format(parse(dateString, 'yyyy-MM-dd', new Date()), 'MMM d, yyyy')
}

const formatSelectedRange = () => {
    if (!selectedStartDate.value) return 'No dates selected'
    if (!selectedEndDate.value) return `From ${formatDate(selectedStartDate.value)} - Select end date`
    return `${formatDate(selectedStartDate.value)} - ${formatDate(selectedEndDate.value)}`
}

const calculateDuration = () => {
    if (!selectedStartDate.value || !selectedEndDate.value) return 0

    const start = parse(selectedStartDate.value, 'yyyy-MM-dd', new Date())
    const end = parse(selectedEndDate.value, 'yyyy-MM-dd', new Date())

    return differenceInDays(end, start) + 1
}

const calculateTotalCost = () => {
    const duration = calculateDuration()
    return (duration * props.dailyRate).toFixed(2)
}

// Watch for changes
watch(currentDate, loadAvailability)

// Load initial data
onMounted(() => {
    loadAvailability()
})
</script>

<style scoped>
.vehicle-availability-calendar {
    max-width: 28rem;
    margin-left: auto;
    margin-right: auto;
}

.calendar-grid {
    width: 100%;
}

.selected-range {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom hover effects */
.calendar-grid button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dark .calendar-grid button:hover:not(:disabled) {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
</style>