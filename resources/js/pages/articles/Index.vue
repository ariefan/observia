<template>

  <Head title="Semua Artikel" />

  <AppLayout>
    <div class="max-w-7xl mx-auto px-6 py-4 space-y-6">
      <!-- Header Section -->
      <div class="bg-white rounded-lg shadow-sm p-8">
        <!-- Back Button and Title -->
        <div class="flex items-center gap-4 mb-6">
          <Button variant="outline" @click="goBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Kembali
          </Button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Semua Artikel</h1>
            <p class="text-gray-600 mt-1">Baca artikel lengkap seputar peternakan dan pertanian</p>
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
          <Input v-model="searchQuery" placeholder="Cari artikel berdasarkan judul, penulis, atau konten..."
            class="pl-10" />
        </div>

        <!-- Results Info -->
        <div class="text-sm text-gray-600 mb-4">
          Menampilkan {{ filteredArticles.length }} dari {{ totalArticles }} artikel
        </div>
      </div>

      <!-- Articles Grid -->
      <div v-if="filteredArticles.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article v-for="article in filteredArticles" :key="article.id" @click="openArticle(article)"
          class="bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-200 group">
          <!-- Article Image -->
          <div class="aspect-video bg-gray-200 overflow-hidden">
            <img v-if="article.image_url" :src="article.image_url" :alt="article.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
              @error="handleImageError" />
            <div v-else
              class="w-full h-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
              <FileText class="h-12 w-12 text-blue-400" />
            </div>
          </div>

          <!-- Article Content -->
          <div class="p-6 space-y-3">
            <!-- Category and Date -->
            <div class="flex items-center justify-between text-xs">
              <span class="inline-flex items-center px-2 py-1 rounded-full font-medium bg-blue-100 text-blue-800">
                {{ getCategoryLabel(article.category) }}
              </span>
              <span v-if="article.published_at" class="text-gray-500">
                {{ formatDate(article.published_at) }}
              </span>
            </div>

            <!-- Title -->
            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
              {{ article.title }}
            </h3>

            <!-- Description -->
            <p v-if="article.description" class="text-sm text-gray-600 line-clamp-3">
              {{ stripMarkdown(article.description) }}
            </p>
            <p v-else class="text-sm text-gray-600 line-clamp-3">
              {{ stripMarkdown(article.content).substring(0, 150) }}...
            </p>

            <!-- Author and Read More -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
              <div v-if="article.author" class="flex items-center text-xs text-gray-500">
                <User class="h-3 w-3 mr-1" />
                {{ article.author }}
              </div>
              <div v-else class="flex items-center text-xs text-gray-500">
                <User class="h-3 w-3 mr-1" />
                Admin
              </div>
              <span class="text-xs text-blue-600 font-medium group-hover:text-blue-700">
                Baca Selengkapnya →
              </span>
            </div>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
        <div class="max-w-md mx-auto">
          <FileText class="h-16 w-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada artikel ditemukan</h3>
          <p class="text-gray-600 mb-4">
            {{ searchQuery ? 'Coba ubah kata kunci pencarian Anda' : 'Belum ada artikel untuk kategori ini' }}
          </p>
          <Button @click="clearFilters" variant="outline">
            <RotateCcw class="h-4 w-4 mr-2" />
            Reset Filter
          </Button>
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
import { ArrowLeft, Search, FileText, RotateCcw, User } from 'lucide-vue-next';
import axios from 'axios';

// State
const articles = ref({
  manajemen: [],
  kesehatan: [],
  budidaya: [],
});
const selectedCategory = ref('all');
const searchQuery = ref('');

// Helper functions
const getCategoryLabel = (category: string) => {
  const labels = {
    'manajemen': 'Manajemen',
    'kesehatan': 'Kesehatan',
    'budidaya': 'Budi Daya'
  };
  return labels[category] || category;
};

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const stripMarkdown = (text: string) => {
  if (!text) return '';
  return text
    .replace(/[#*_\[\]()]/g, '') // Remove markdown symbols
    .replace(/\n/g, ' ') // Replace newlines with spaces
    .trim();
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.style.display = 'none';
};

// Computed properties
const allArticles = computed(() => {
  return [
    ...articles.value.manajemen,
    ...articles.value.kesehatan,
    ...articles.value.budidaya,
  ];
});

const totalArticles = computed(() => allArticles.value.length);

const filteredArticles = computed(() => {
  let filtered = allArticles.value;

  // Filter by category
  if (selectedCategory.value !== 'all') {
    filtered = articles.value[selectedCategory.value] || [];
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(article =>
      article.title.toLowerCase().includes(query) ||
      (article.description && article.description.toLowerCase().includes(query)) ||
      (article.content && article.content.toLowerCase().includes(query)) ||
      (article.author && article.author.toLowerCase().includes(query))
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

const openArticle = (article: any) => {
  router.visit(`/articles/${article.id}`);
};

// Fetch articles data
const fetchArticles = async () => {
  try {
    const response = await axios.get('/api/content');
    articles.value = response.data.articles;
  } catch (error) {
    console.error('Gagal memuat artikel:', error);
    // Fallback data jika API gagal
    articles.value = {
      manajemen: [],
      kesehatan: [],
      budidaya: [],
    };
  }
};

onMounted(() => {
  fetchArticles();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>