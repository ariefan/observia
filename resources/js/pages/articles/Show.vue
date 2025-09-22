<template>

  <Head :title="article.title" />

  <AppLayout>
    <div class="min-w-7xl mx-auto px-6 py-4 space-y-6">
      <!-- Article Header -->
      <div class="bg-white rounded-lg shadow-sm p-6 space-y-2">
        <!-- Back Button and Category Badge -->
        <div class="flex items-center justify-between mb-4">
          <Button variant="outline" @click="goBack">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Kembali
          </Button>

          <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
              {{ getCategoryLabel(article.category) }}
            </span>
            <span v-if="!article.is_active"
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
              Tidak Aktif
            </span>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-gray-900">{{ article.title }}</h1>

        <!-- Meta Information -->
        <div class="flex items-center gap-4 text-sm text-gray-600">
          <div v-if="article.author" class="flex items-center gap-1">
            <User class="h-4 w-4" />
            <span>{{ article.author }}</span>
          </div>
          <div v-if="article.published_at" class="flex items-center gap-1">
            <Calendar class="h-4 w-4" />
            <span>{{ formatDate(article.published_at) }}</span>
          </div>
        </div>

        <!-- Description -->
        <div v-if="article.description" class="text-lg text-gray-700 leading-relaxed prose prose-lg max-w-none">
          <div v-html="renderedDescription"></div>
        </div>

        <!-- Featured Image -->
        <div v-if="article.image_url" class="rounded-lg overflow-hidden">
          <img :src="article.image_url" :alt="article.title" class="w-full h-64 object-cover"
            @error="imageError = true" />
        </div>

        <!-- Article Content -->
        <div class="prose prose-xl max-w-none">
          <div v-html="renderedContent" class="leading-relaxed text-gray-800"></div>
        </div>

        <!-- Share Section -->
        <h3 class="text-lg font-semibold mb-4">Bagikan Artikel</h3>
        <div class="flex gap-3">
          <Button variant="outline" size="sm" @click="shareArticle">
            <Share class="h-4 w-4 mr-2" />
            Salin Link
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft, User, Calendar, Share } from 'lucide-vue-next';

const props = defineProps<{
  article: {
    id: number;
    title: string;
    description?: string;
    content: string;
    author?: string;
    image_url?: string;
    category: string;
    is_active: boolean;
    published_at?: string;
  };
}>();

const imageError = ref(false);

// Helper function to get category label
const getCategoryLabel = (category: string) => {
  const labels = {
    'manajemen': 'Manajemen',
    'kesehatan': 'Kesehatan',
    'breeding': 'Breeding'
  };
  return labels[category] || category;
};

// Helper function to format date
const formatDate = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

// Simple markdown rendering function
const renderMarkdown = (text: string) => {
  if (!text) return '';

  let content = text;

  // Convert markdown-like syntax to HTML
  content = content
    // Headers
    .replace(/^### (.*$)/gim, '<h3 class="text-xl font-semibold mt-6 mb-3">$1</h3>')
    .replace(/^## (.*$)/gim, '<h2 class="text-2xl font-semibold mt-8 mb-4">$1</h2>')
    .replace(/^# (.*$)/gim, '<h1 class="text-3xl font-bold mt-8 mb-4">$1</h1>')
    // Bold and italic
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    // Links
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:underline">$1</a>')
    // Lists
    .replace(/^\* (.*$)/gim, '<li class="ml-4">$1</li>')
    .replace(/^- (.*$)/gim, '<li class="ml-4">$1</li>')
    .replace(/^\d+\. (.*$)/gim, '<li class="ml-4">$1</li>')
    // Line breaks
    .replace(/\n\n/g, '</p><p class="mb-4">')
    .replace(/\n/g, '<br>');

  // Wrap in paragraphs
  if (!content.startsWith('<')) {
    content = '<p class="mb-4">' + content + '</p>';
  }

  // Wrap lists in ul tags
  content = content.replace(/(<li.*?>.*?<\/li>)/g, (match) => {
    if (!match.includes('<ul>')) {
      return '<ul class="list-disc list-inside mb-4">' + match + '</ul>';
    }
    return match;
  });

  return content;
};

// Simple markdown-like rendering
const renderedContent = computed(() => renderMarkdown(props.article.content));
const renderedDescription = computed(() => renderMarkdown(props.article.description || ''));

// Navigation
const goBack = () => {
  // Try to go back in history, fallback to home page
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit('/home');
  }
};

// Share functionality
const shareArticle = async () => {
  try {
    if (navigator.share) {
      await navigator.share({
        title: props.article.title,
        text: props.article.description || '',
        url: window.location.href,
      });
    } else {
      // Fallback: copy to clipboard
      await navigator.clipboard.writeText(window.location.href);
      alert('Link artikel telah disalin ke clipboard!');
    }
  } catch (error) {
    console.error('Error sharing:', error);
  }
};
</script>

<style scoped>
.prose {
  color: #374151;
}

.prose h1,
.prose h2,
.prose h3 {
  color: #111827;
}

.prose strong {
  font-weight: 600;
}

.prose em {
  font-style: italic;
}

.prose ul {
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.prose li {
  margin-bottom: 0.5rem;
}
</style>