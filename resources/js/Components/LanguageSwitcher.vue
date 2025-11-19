<template>
  <div class="relative">
    <button
      @click="dropdownOpen = !dropdownOpen"
      class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md transition-colors duration-200"
      :class="{
        'bg-gray-100': dropdownOpen
      }"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
      </svg>
      <span>{{ currentLanguageName }}</span>
      <svg
        class="w-4 h-4 transition-transform duration-200"
        :class="{ 'transform rotate-180': dropdownOpen }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-show="dropdownOpen"
        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        @click.outside="dropdownOpen = false"
      >
        <div class="py-1">
          <button
            v-for="language in languages"
            :key="language.code"
            @click="changeLanguage(language.code)"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition-colors duration-200"
            :class="{
              'bg-blue-50 text-blue-700': language.code === currentLocale,
              'text-right': language.code === 'ar'
            }"
            :dir="language.code === 'ar' ? 'rtl' : 'ltr'"
          >
            <img
              :src="language.flag"
              :alt="language.name"
              class="w-5 h-5 mr-3 ml-0 rounded-sm"
              :class="{ 'mr-0 ml-3': language.code === 'ar' }"
            >
            <span>{{ language.name }}</span>
            <svg
              v-if="language.code === currentLocale"
              class="w-4 h-4 ml-auto text-blue-600"
              :class="{ 'ml-0 mr-auto': language.code === 'ar' }"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { locale } = useI18n()
const dropdownOpen = ref(false)

const languages = [
  {
    code: 'en',
    name: 'English',
    flag: '/images/flags/us.svg'
  },
  {
    code: 'ar',
    name: 'العربية',
    flag: '/images/flags/sa.svg'
  }
]

const currentLocale = computed(() => locale.value)

const currentLanguageName = computed(() => {
  const current = languages.find(lang => lang.code === currentLocale.value)
  return current ? current.name : 'English'
})

const changeLanguage = (newLocale) => {
  if (newLocale === currentLocale.value) {
    dropdownOpen.value = false
    return
  }

  // Update the i18n locale
  locale.value = newLocale

  // Update document direction for RTL languages
  document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = newLocale

  // Store the preference
  localStorage.setItem('locale', newLocale)

  // Make a request to update the session locale
  router.get(window.location.pathname, { locale: newLocale }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      dropdownOpen.value = false
    }
  })
}

// Close dropdown when clicking outside
const closeDropdown = (event) => {
  if (!event.target.closest('.relative')) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)

  // Set initial document direction
  document.documentElement.dir = currentLocale.value === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = currentLocale.value
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<style scoped>
/* RTL specific styles */
[dir="rtl"] .mr-3 {
  margin-right: 0;
  margin-left: 0.75rem;
}

[dir="rtl"] .ml-auto {
  margin-left: 0;
  margin-right: auto;
}

/* Ensure proper flag spacing in RTL */
[dir="rtl"] img {
  margin-left: 0.75rem;
  margin-right: 0;
}
</style>