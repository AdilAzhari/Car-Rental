<template>
    <div>
        <CarDetailsPage :id="id" />
    </div>
</template>

<script setup>
import CarDetailsPage from '../CarDetailsPage.vue'

defineProps({
    id: [String, Number]
})
</script>