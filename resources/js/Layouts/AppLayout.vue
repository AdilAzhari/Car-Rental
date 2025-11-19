<template>
  <div class="min-h-screen bg-background">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-50 shadow-sm">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="flex justify-between items-center h-18">
          <!-- Enhanced Logo -->
          <Link href="/" class="flex items-center space-x-3 group">
            <div class="relative">
              <img
                src="/images/logo.jpg"
                alt="SENTIENTS A.I Logo"
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl object-cover shadow-lg transform group-hover:scale-105 transition-all duration-300"
                @error="handleImageError"
              />
              <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full"></div>
            </div>
            <div>
              <span class="text-lg sm:text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                SENTIENTS A.I
              </span>
              <p class="hidden sm:block text-xs text-gray-500 font-medium -mt-1">Car Rental System</p>
            </div>
          </Link>

          <!-- Enhanced Desktop Navigation -->
          <div class="hidden lg:flex items-center space-x-1">
            <Link
              href="/"
              class="nav-link"
              :class="{ 'nav-link-active': $page.url === '/' }"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
              Home
            </Link>
            <Link
              href="/cars"
              class="nav-link"
              :class="{ 'nav-link-active': $page.url.startsWith('/cars') }"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
              </svg>
              Vehicles
            </Link>
            <template v-if="$page.props.auth?.user">
              <Link
                href="/my-bookings"
                class="nav-link"
                :class="{ 'nav-link-active': $page.url.startsWith('/my-bookings') }"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                My Bookings
              </Link>
              <Link
                href="/dashboard"
                class="nav-link"
                :class="{ 'nav-link-active': $page.url.startsWith('/dashboard') }"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Dashboard
              </Link>
            </template>
            <a href="#contact" class="nav-link">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              Contact
            </a>
          </div>

          <!-- Enhanced User Menu -->
          <div class="flex items-center space-x-4">
            <!-- Language Switcher -->
            <LanguageSwitcher />
            <template v-if="$page.props.auth?.user">
              <!-- Notifications -->
              <div class="relative">
                <button class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors duration-200 relative">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5 5-5m-5 5H6"/>
                  </svg>
                  <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                </button>
              </div>

              <!-- Quick Actions -->
              <div class="hidden lg:flex items-center gap-2">
                <Link href="/cars" class="action-button">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  Rent Now
                </Link>
              </div>

              <!-- User Dropdown -->
              <div class="relative" ref="userDropdown">
                <button
                  @click="showUserMenu = !showUserMenu"
                  class="flex items-center space-x-3 p-2 rounded-xl hover:bg-gray-50 transition-all duration-200 group"
                >
                  <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-200">
                      <span class="text-sm font-bold text-white">
                        {{ $page.props.auth?.user?.name?.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full"></div>
                  </div>
                  <div class="hidden sm:block text-left">
                    <p class="font-semibold text-gray-900 text-sm">{{ $page.props.auth?.user?.name }}</p>
                    <p class="text-xs text-gray-500">Premium Member</p>
                  </div>
                  <svg class="w-4 h-4 transition-transform duration-200 text-gray-400" :class="{ 'rotate-180': showUserMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>

                <!-- Dropdown Menu -->
                <transition name="dropdown">
                  <div
                    v-if="showUserMenu"
                    class="absolute right-0 mt-2 w-48 bg-card rounded-lg border border-border shadow-premium py-2 z-50"
                  >
                    <Link
                      href="/dashboard"
                      class="block px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors duration-200"
                    >
                      Dashboard
                    </Link>
                    <Link
                      href="/my-bookings"
                      class="block px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors duration-200"
                    >
                      My Bookings
                    </Link>
                    <hr class="my-2 border-border">
                    <Link
                      href="/logout"
                      method="post"
                      class="block px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition-colors duration-200"
                    >
                      Sign Out
                    </Link>
                  </div>
                </transition>
              </div>
            </template>
            <template v-else>
              <Link
                href="/login"
                class="text-gray-600 hover:text-blue-600 font-medium transition-colors duration-200 px-4 py-2 rounded-lg hover:bg-gray-50"
              >
                Sign In
              </Link>
              <Link
                href="/register"
                class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-2.5 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-blue-200"
              >
                Get Started
              </Link>
            </template>

            <!-- Mobile Menu Button -->
            <button
              @click="showMobileMenu = !showMobileMenu"
              class="md:hidden p-2 text-foreground hover:text-primary transition-colors duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <transition name="mobile-menu">
        <div v-if="showMobileMenu" class="md:hidden bg-background border-t border-border">
          <div class="px-4 py-4 space-y-3">
            <Link
              href="/"
              class="block text-foreground hover:text-primary transition-fast font-medium py-2"
              @click="showMobileMenu = false"
            >
              Home
            </Link>
            <Link
              href="/cars"
              class="block text-foreground hover:text-primary transition-fast font-medium py-2"
              @click="showMobileMenu = false"
            >
              Vehicles
            </Link>
            <template v-if="$page.props.auth?.user">
              <Link
                href="/my-bookings"
                class="block text-foreground hover:text-primary transition-fast font-medium py-2"
                @click="showMobileMenu = false"
              >
                My Bookings
              </Link>
              <Link
                href="/dashboard"
                class="block text-foreground hover:text-primary transition-fast font-medium py-2"
                @click="showMobileMenu = false"
              >
                Dashboard
              </Link>
              <a
                href="#contact"
                class="block text-foreground hover:text-primary transition-fast font-medium py-2"
                @click="showMobileMenu = false"
              >
                Contact
              </a>

              <div class="pt-3 border-t border-border space-y-3">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  <span>(555) 123-4567</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  <span>info@sentients.ai</span>
                </div>
                <Link
                  href="/logout"
                  method="post"
                  class="block w-full text-center py-2 text-destructive hover:bg-destructive/10 rounded-lg transition-colors duration-200"
                  @click="showMobileMenu = false"
                >
                  Sign Out
                </Link>
              </div>
            </template>
            <template v-else>
              <a
                href="#contact"
                class="block text-foreground hover:text-primary transition-fast font-medium py-2"
                @click="showMobileMenu = false"
              >
                Contact
              </a>

              <div class="pt-3 border-t border-border space-y-3">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  <span>(555) 123-4567</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  <span>info@sentients.ai</span>
                </div>
                <Link
                  href="/login"
                  class="block text-foreground hover:text-primary transition-fast font-medium py-2"
                  @click="showMobileMenu = false"
                >
                  Sign In
                </Link>
                <Link
                  href="/register"
                  class="block w-full text-center hero-button py-2 rounded-lg transition-colors duration-200"
                  @click="showMobileMenu = false"
                >
                  Get Started
                </Link>
              </div>
            </template>
          </div>
        </div>
      </transition>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
      <slot />
    </main>

    <!-- Footer -->
    <footer id="contact" class="bg-accent text-accent-foreground mt-20">
      <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Brand -->
          <div class="col-span-1 md:col-span-2">
            <div class="flex items-center space-x-3 mb-4">
              <img
                src="/images/logo.jpg"
                alt="SENTIENTS A.I Logo"
                class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg object-cover shadow-md"
                @error="handleImageError"
              />
              <span class="text-xl font-bold">SENTIENTS A.I</span>
            </div>
            <p class="text-muted-foreground max-w-md">
              AI-powered car rental platform delivering intelligent vehicle solutions with unmatched customer experience.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="font-semibold mb-4">Quick Links</h4>
            <ul class="space-y-2">
              <li><Link href="/" class="text-muted-foreground hover:text-accent-foreground transition-colors duration-200">Home</Link></li>
              <li><Link href="/cars" class="text-muted-foreground hover:text-accent-foreground transition-colors duration-200">Vehicles</Link></li>
              <li><a href="#locations" class="text-muted-foreground hover:text-accent-foreground transition-colors duration-200">Locations</a></li>
              <li><a href="#contact" class="text-muted-foreground hover:text-accent-foreground transition-colors duration-200">Contact</a></li>
            </ul>
          </div>

          <!-- Contact -->
          <div>
            <h4 class="font-semibold mb-4">Contact</h4>
            <ul class="space-y-2 text-muted-foreground">
              <li class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                (555) 123-4567
              </li>
              <li class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                info@rentlux.com
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>123 Rental Street<br>City, State 12345</span>
              </li>
            </ul>
          </div>
        </div>

        <div class="border-t border-border mt-8 pt-8 text-center text-muted-foreground">
          <p>&copy; 2025 SENTIENTS A.I. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <!-- Global Loading Overlay -->
    <div v-if="$page.props.loading" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white rounded-xl p-6 shadow-large">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          <span class="text-secondary-700">Loading...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const showUserMenu = ref(false)
const showMobileMenu = ref(false)
const userDropdown = ref(null)

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (userDropdown.value && !userDropdown.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

// Handle image loading errors
const handleImageError = (event) => {
  console.warn('Logo image failed to load:', event.target.src)
  // Fallback to a default SVG if image fails to load
  event.target.style.display = 'none'
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease-out;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: all 0.3s ease-out;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Navigation Styles */
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border-radius: 0.75rem;
  color: rgb(75, 85, 99);
  font-weight: 500;
  transition: all 0.2s ease;
  position: relative;
}

.nav-link:hover {
  color: rgb(37, 99, 235);
  background-color: rgba(59, 130, 246, 0.05);
}

.nav-link-active {
  color: rgb(37, 99, 235);
  background-color: rgb(239, 246, 255);
  font-weight: 600;
}

.nav-link-active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0.25rem;
  height: 0.25rem;
  background-color: rgb(37, 99, 235);
  border-radius: 9999px;
}

.action-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(to right, rgb(37, 99, 235), rgb(79, 70, 229));
  color: white;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.action-button:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transform: scale(1.05);
}

.h-18 {
  height: 4.5rem;
}
</style>