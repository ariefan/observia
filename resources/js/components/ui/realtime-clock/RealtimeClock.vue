<template>
    <div :class="fontClass" class="text-xl">
        {{ formattedTime }}
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    fontClass: {
        type: String,
        default: 'font-ibmplex', // ← SET DEFAULT HERE
    }
})

const formattedTime = ref('')

function updateClock() {
    const now = new Date()
    const day = String(now.getDate()).padStart(2, '0')
    const month = now.toLocaleString('id-ID', { month: 'short' })
    const year = now.getFullYear()
    const hour = String(now.getHours()).padStart(2, '0')
    const minute = String(now.getMinutes()).padStart(2, '0')
    const second = String(now.getSeconds()).padStart(2, '0')

    formattedTime.value = `${day} ${month} ${year}, ${hour}:${minute}:${second}`
}

let interval

onMounted(() => {
    updateClock()
    interval = setInterval(updateClock, 1000)
})

onUnmounted(() => {
    clearInterval(interval)
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono&display=swap');

.font-ibmplex {
    font-family: 'IBM Plex Mono', monospace !important;
}
</style>