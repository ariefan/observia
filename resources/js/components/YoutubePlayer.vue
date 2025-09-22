<script setup lang="ts">
import { ref, computed } from 'vue'
import { PlayIcon } from 'lucide-vue-next'
import Dialog from 'primevue/dialog';

const props = defineProps({
    videoId: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: 'Video'
    }
})

const showModal = ref(false)
const thumbnailError = ref(false)

// Validate YouTube video ID
const isValidYouTubeId = computed(() => {
    return props.videoId && /^[a-zA-Z0-9_-]{11}$/.test(props.videoId)
})

const thumbnailUrl = computed(() => `https://img.youtube.com/vi/${props.videoId}/hqdefault.jpg`)
const youtubeEmbedUrl = computed(() => `https://www.youtube.com/embed/${props.videoId}?autoplay=1&rel=0&modestbranding=1&fs=1&cc_load_policy=1&iv_load_policy=3&autohide=0&controls=1`)
const youtubeWatchUrl = computed(() => `https://www.youtube.com/watch?v=${props.videoId}`)

const handleThumbnailError = () => {
    thumbnailError.value = true
}
</script>

<template>
    <div
        class="w-full max-w-xl rounded-xl overflow-hidden shadow-xl bg-white dark:bg-zinc-900 border dark:border-zinc-700">

        <!-- Thumbnail & Overlay -->
        <div class="relative w-full aspect-video group">
            <!-- Thumbnail -->
            <img v-if="!thumbnailError && isValidYouTubeId"
                :src="thumbnailUrl"
                :alt="title"
                @error="handleThumbnailError"
                class="w-full h-full object-cover absolute top-0 left-0 z-0" />

            <!-- Fallback for invalid/unavailable videos -->
            <div v-if="thumbnailError || !isValidYouTubeId"
                class="w-full h-full bg-gray-800 flex items-center justify-center absolute top-0 left-0 z-0">
                <div class="text-center text-white p-4">
                    <PlayIcon class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p class="text-sm">{{ !isValidYouTubeId ? 'Invalid Video ID' : 'Video Unavailable' }}</p>
                    <p class="text-xs opacity-75">{{ title }}</p>
                </div>
            </div>

            <!-- Centered Play Button with Black Circle -->
            <button @click="isValidYouTubeId ? showModal = true : window.open(youtubeWatchUrl, '_blank')"
                class="absolute inset-0 flex items-center justify-center z-20">
                <div
                    class="w-16 h-16 bg-black/70 hover:bg-black rounded-full flex items-center justify-center transition-all duration-200">
                    <PlayIcon class="w-8 h-8 text-white/50 hover:text-white" />
                </div>
            </button>

            <!-- Gradient Bottom Text -->
            <div
                class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 via-black/70 to-transparent text-white z-10 p-4">
                <h2 class="text-sm font-bold">{{ title }}</h2>
                <p v-if="!isValidYouTubeId" class="text-xs text-red-300">Click to watch on YouTube</p>
            </div>
        </div>
    </div>

    <Dialog v-if="isValidYouTubeId" v-model:visible="showModal" modal
        class="p-0 m-0 overflow-hidden w-screen max-w-5xl !bg-transparent !border-0 -mt-10">
        <template #container="{ closeCallback }">
            <!-- Close Button -->
            <button @click="closeCallback"
                class="absolute top-0 right-0 z-50 bg-black/70 hover:bg-black/50 text-white rounded-full p-2 shadow-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="relative w-full aspect-video bg-black rounded-xl overflow-hidden mt-10">
                <!-- YouTube Video Embed -->
                <iframe :src="youtubeEmbedUrl" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                    class="absolute top-0 left-0 w-full h-full"></iframe>
            </div>
        </template>
    </Dialog>

</template>
