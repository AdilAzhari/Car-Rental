<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md">
      <h1 class="text-2xl font-bold text-green-600 mb-4">✅ Inertia.js is Working!</h1>
      <p class="text-gray-700">{{ message }}</p>
      <p class="text-sm text-gray-500 mt-4">If you can see this page, your Inertia.js setup is correct.</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  message: String
})
</script>