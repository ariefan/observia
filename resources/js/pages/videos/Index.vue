<template>

  <Head title="Semua Video" />

  <AppLayout>
    <div class="w-full max-w-7xl mx-auto px-6 py-4 space-y-6">
      <!-- Header Section -->
      <div class="bg-white rounded-lg shadow-sm p-8">
        <!-- Back Button and Title -->
        <div class="flex items-center gap-4 mb-6">
          <Button variant="outline" @click="goBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Kembali
          </Button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Semua Video</h1>
            <p class="text-gray-600 mt-1">Jelajahi koleksi lengkap video edukasi peternakan</p>
          </div>
        </div>

        <!-- Category Filter -->
        <div class="flex flex-wrap gap-3 mb-6">
          <Button @click="selectedCategory = 'all'" :variant="selectedCategory === 'all' ? 'default' : 'outline'"
            size="sm">
            Semua Kategori
          </Button>
          <Button @click="selectedCategory = 'manajemen'"
            :variant="selectedCategory === 'manajemen' ? 'default' : 'outline'" size="sm">
            Manajemen
          </Button>
          <Button @click="selectedCategory = 'kesehatan'"
            :variant="selectedCategory === 'kesehatan' ? 'default' : 'outline'" size="sm">
            Kesehatan
          </Button>
          <Button @click="selectedCategory = 'budidaya'"
            :variant="selectedCategory === 'budidaya' ? 'default' : 'outline'" size="sm">
            Budi Daya
          </Button>
        </div>

        <!-- Search Bar -->
        <div class="relative mb-6">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
          <Input v-model="searchQuery" placeholder="Cari video berdasarkan judul atau deskripsi..." class="pl-10" />
        </div>

        <!-- Results Info -->
        <div class="text-sm text-gray-600 mb-4">
          Menampilkan {{ filteredVideos.length }} dari {{ totalVideos }} video
        </div>

        <!-- Videos Grid -->
        <div v-if="filteredVideos.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
          <div v-for="video in filteredVideos" :key="video.id" class="group">
            <YoutubePlayer :videoId="video.youtube_id" :title="video.title" class="w-full" />
            <div class="mt-3 space-y-2">
              <h3 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                {{ video.title }}
              </h3>
              <p v-if="video.description" class="text-sm text-gray-600 line-clamp-2">
                {{ video.description }}
              </p>
              <div class="flex items-center gap-2">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ getCategoryLabel(video.category) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
          <div class="max-w-md mx-auto">
            <Video class="h-16 w-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada video ditemukan</h3>
            <p class="text-gray-600 mb-4">
              {{ searchQuery ? 'Coba ubah kata kunci pencarian Anda' : 'Belum ada video untuk kategori ini' }}
            </p>
            <Button @click="clearFilters" variant="outline">
              <RotateCcw class="h-4 w-4 mr-2" />
              Reset Filter
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import YoutubePlayer from '@/components/YoutubePlayer.vue';
import { ArrowLeft, Search, Video, RotateCcw } from 'lucide-vue-next';
import axios from 'axios';

// State
const videos = ref({
  manajemen: [],
  kesehatan: [],
  budidaya: [],
});
const selectedCategory = ref('all');
const searchQuery = ref('');

// Helper function to get category label
const getCategoryLabel = (category: string) => {
  const labels = {
    'manajemen': 'Manajemen',
    'kesehatan': 'Kesehatan',
    'budidaya': 'Budi Daya'
  };
  return labels[category] || category;
};

// Computed properties
const allVideos = computed(() => {
  return [
    ...videos.value.manajemen,
    ...videos.value.kesehatan,
    ...videos.value.budidaya,
  ];
});

const totalVideos = computed(() => allVideos.value.length);

const filteredVideos = computed(() => {
  let filtered = allVideos.value;

  // Filter by category
  if (selectedCategory.value !== 'all') {
    filtered = videos.value[selectedCategory.value] || [];
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(video =>
      video.title.toLowerCase().includes(query) ||
      (video.description && video.description.toLowerCase().includes(query))
    );
  }

  return filtered;
});

// Functions
const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit('/home');
  }
};

const clearFilters = () => {
  selectedCategory.value = 'all';
  searchQuery.value = '';
};

// Fetch videos data
const fetchVideos = async () => {
  try {
    const response = await axios.get('/api/content');
    videos.value = response.data.videos;
  } catch (error) {
    console.error('Gagal memuat video:', error);
    // Fallback data jika API gagal
    videos.value = {
      manajemen: [],
      kesehatan: [],
      budidaya: [],
    };
  }
};

onMounted(() => {
  fetchVideos();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>