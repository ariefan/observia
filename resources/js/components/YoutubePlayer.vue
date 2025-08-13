<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { PlayIcon } from 'lucide-vue-next' // ← install this package if you haven't!
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';

const props = defineProps({
    videoId: {
        type: String,
        required: true
    }
})

const API_KEY = 'AIzaSyCqRxXiCLVm2gpk_jcyr1IgyuNPnZS0CfM' // Replace with your key or env
const loading = ref(true)
const error = ref(null)
const video = ref(null)
const showModal = ref(false)

const fetchVideoData = async () => {
    loading.value = true
    error.value = null

    try {
        const res = await fetch(
            `https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=${props.videoId}&key=${API_KEY}`
        )
        const data = await res.json()

        if (data.error) throw new Error(data.error.message)
        if (!data.items || data.items.length === 0) throw new Error("No video found, bro.")

        video.value = data.items[0]
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
}

watch(() => props.videoId, fetchVideoData, { immediate: true })

const thumbnailUrl = computed(() => video.value?.snippet?.thumbnails?.high?.url || '')
const formattedDate = computed(() => {
    const date = new Date(video.value?.snippet?.publishedAt)
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(date)
})
const formattedDuration = computed(() => {
    const isoDuration = video.value?.contentDetails?.duration || ''
    const match = isoDuration.match(/PT(?:(\d+)M)?(?:(\d+)S)?/)
    const minutes = match?.[1] || '0'
    const seconds = match?.[2] || '00'
    return `${minutes}:${seconds.padStart(2, '0')}`
})
const youtubeEmbedUrl = computed(() => `https://www.youtube.com/embed/${props.videoId}?autoplay=1`)
</script>

<template>
    <div
        class="w-full max-w-xl rounded-xl overflow-hidden shadow-xl bg-white dark:bg-zinc-900 border dark:border-zinc-700">

        <!-- Loading -->
        <div v-if="loading" class="p-6 text-zinc-500 dark:text-zinc-300">Fetching YouTube magic...</div>

        <!-- Error -->
        <div v-else-if="error" class="p-6 text-red-600 font-semibold">Error: {{ error }}</div>

        <!-- Thumbnail & Overlay -->
        <div v-else class="relative w-full h-56 group">
            <!-- Thumbnail -->
            <img :src="thumbnailUrl" :alt="video.snippet.title"
                class="w-full h-full object-cover absolute top-0 left-0 z-0" />

            <!-- Centered Play Button with Black Circle -->
            <button @click="showModal = true" class="absolute inset-0 flex items-center justify-center z-20">
                <div
                    class="w-16 h-16 bg-black/70 hover:bg-black rounded-full flex items-center justify-center transition-all duration-200">
                    <PlayIcon class="w-8 h-8 text-white/50 hover:text-white" />
                </div>
            </button>

            <!-- Gradient Bottom Text -->
            <div
                class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 via-black/70 to-transparent text-white z-10 p-4">
                <p class="text-sm text-teal-200">{{ video.snippet.channelTitle }}</p>
                <h2 class="text-sm font-bold">{{ video.snippet.title }}</h2>
                <div class="flex justify-between text-xs text-zinc-300">
                    <p class="mt-2">{{ formattedDate }}</p>
                    <p>
                        <Tag :value="formattedDuration" severity="secondary" rounded
                            class="!bg-white/20 !text-white !px-3 !text-xs" />
                    </p>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showModal" modal
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
                <!-- The Iframe of YouTube Doom -->
                <iframe :src="youtubeEmbedUrl" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen
                    class="absolute top-0 left-0 w-full h-full"></iframe>
            </div>
        </template>
    </Dialog>

</template>
