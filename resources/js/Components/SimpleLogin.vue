<template>
    <div v-if="!isAuthenticated" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4 w-full">
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Login Required</h3>
                <p class="text-sm text-gray-600 mt-2">Please log in to make a reservation</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        v-model="loginForm.email"
                        type="email"
                        required
                        placeholder="Enter your email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        v-model="loginForm.password"
                        type="password"
                        required
                        placeholder="Enter your password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div v-if="loginError" class="text-red-600 text-sm">
                    {{ loginError }}
                </div>

                <div class="flex space-x-3">
                    <button
                        type="submit"
                        :disabled="loginLoading"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-2 px-4 rounded-lg font-medium"
                    >
                        {{ loginLoading ? 'Logging in...' : 'Login' }}
                    </button>
                    <button
                        @click="showRegister = true"
                        type="button"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg font-medium"
                    >
                        Register
                    </button>
                </div>
            </form>

            <!-- Quick Test Login -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">For testing purposes:</p>
                <button
                    @click="quickLogin"
                    :disabled="loginLoading"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium text-sm"
                >
                    Quick Login (Test User)
                </button>
            </div>
        </div>

        <!-- Register Modal -->
        <div v-if="showRegister" class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-75">
            <div class="bg-white rounded-lg p-6 max-w-md mx-4 w-full">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create Account</h3>
                </div>

                <form @submit.prevent="handleRegister" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            v-model="registerForm.name"
                            type="text"
                            required
                            placeholder="Your full name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            v-model="registerForm.email"
                            type="email"
                            required
                            placeholder="your@email.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            v-model="registerForm.password"
                            type="password"
                            required
                            placeholder="Choose a password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input
                            v-model="registerForm.password_confirmation"
                            type="password"
                            required
                            placeholder="Confirm your password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div v-if="registerError" class="text-red-600 text-sm">
                        {{ registerError }}
                    </div>

                    <div class="flex space-x-3">
                        <button
                            type="submit"
                            :disabled="registerLoading"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-2 px-4 rounded-lg font-medium"
                        >
                            {{ registerLoading ? 'Creating...' : 'Create Account' }}
                        </button>
                        <button
                            @click="showRegister = false"
                            type="button"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg font-medium"
                        >
                            Back to Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Props
defineProps({
    isAuthenticated: {
        type: Boolean,
        default: false
    }
})

// Events
const emit = defineEmits(['authenticated'])

// Reactive data
const showRegister = ref(false)
const loginLoading = ref(false)
const registerLoading = ref(false)
const loginError = ref('')
const registerError = ref('')

const loginForm = ref({
    email: '',
    password: ''
})

const registerForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

// Methods
const handleLogin = async () => {
    loginLoading.value = true
    loginError.value = ''

    try {
        const response = await axios.post('/login', loginForm.value)
        emit('authenticated')
    } catch (error) {
        console.error('Login error:', error)
        loginError.value = error.response?.data?.message || 'Login failed. Please try again.'
    } finally {
        loginLoading.value = false
    }
}

const handleRegister = async () => {
    registerLoading.value = true
    registerError.value = ''

    try {
        await axios.post('/register', registerForm.value)
        // Auto-login after registration
        loginForm.value.email = registerForm.value.email
        loginForm.value.password = registerForm.value.password
        showRegister.value = false
        await handleLogin()
    } catch (error) {
        console.error('Registration error:', error)
        registerError.value = error.response?.data?.message || 'Registration failed. Please try again.'
    } finally {
        registerLoading.value = false
    }
}

const quickLogin = async () => {
    // Use the first existing user for testing
    loginForm.value.email = 'test@example.com'
    loginForm.value.password = 'password'

    // Try to login, if fails, create the test user
    try {
        await handleLogin()
    } catch (error) {
        // If login fails, create test user first
        registerForm.value = {
            name: 'Test User',
            email: 'test@example.com',
            password: 'password',
            password_confirmation: 'password'
        }
        await handleRegister()
    }
}

// Set CSRF token for axios
onMounted(async () => {
    try {
        await axios.get('/sanctum/csrf-cookie')
    } catch (error) {
        console.error('CSRF error:', error)
    }
})
</script>