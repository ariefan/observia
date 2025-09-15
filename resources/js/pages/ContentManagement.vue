<template>
  <Head title="Manajemen Konten" />

  <AppLayout>
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-start justify-between space-x-4">
        <div class="space-y-1">
          <h1 class="text-2xl font-bold tracking-tight">Manajemen Konten</h1>
          <p class="text-muted-foreground">
            Kelola video dan artikel untuk bagian "Temukan Tips dan Triknya di Sini"
          </p>
        </div>
      </div>

      <!-- Tabs for Videos and Articles -->
      <div class="w-full">
        <div class="flex space-x-1 bg-muted p-1 rounded-lg mb-6">
          <button
            @click="activeTab = 'videos'"
            :class="[
              'flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition-colors',
              activeTab === 'videos' 
                ? 'bg-background text-foreground shadow-sm' 
                : 'text-muted-foreground hover:text-foreground'
            ]"
          >
            Video
          </button>
          <button
            @click="activeTab = 'articles'"
            :class="[
              'flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition-colors',
              activeTab === 'articles' 
                ? 'bg-background text-foreground shadow-sm' 
                : 'text-muted-foreground hover:text-foreground'
            ]"
          >
            Artikel
          </button>
        </div>

        <!-- Videos Tab -->
        <div v-if="activeTab === 'videos'" class="space-y-6">
          <!-- Add Video Button -->
          <div class="flex justify-end">
            <Button @click="openVideoDialog()">
              <Plus class="h-4 w-4 mr-2" />
              Tambah Video
            </Button>
          </div>

          <!-- Videos List -->
          <Card>
            <CardHeader>
              <CardTitle>Daftar Video</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="videos.length === 0" class="text-center py-8 text-muted-foreground">
                Belum ada video. Tambahkan video pertama Anda!
              </div>
              <div v-else class="space-y-4">
                <div v-for="video in videos" :key="video.id" 
                  class="flex items-center justify-between p-4 border rounded-lg">
                  <div class="flex-1">
                    <h3 class="font-medium">{{ video.title }}</h3>
                    <p class="text-sm text-muted-foreground">{{ video.description || 'Tidak ada deskripsi' }}</p>
                    <div class="flex gap-2 mt-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ getCategoryLabel(video.category) }}
                      </span>
                      <span v-if="!video.is_active" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Tidak Aktif
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 ml-4">
                    <Button @click="openVideoDialog(video)" variant="outline" size="sm">
                      <Edit class="h-3 w-3 mr-1" />
                      Edit
                    </Button>
                    <Button @click="deleteVideo(video.id)" variant="destructive" size="sm">
                      <Trash2 class="h-3 w-3 mr-1" />
                      Hapus
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Articles Tab -->
        <div v-if="activeTab === 'articles'" class="space-y-6">
          <!-- Add Article Button -->
          <div class="flex justify-end">
            <Button @click="openArticleDialog()">
              <Plus class="h-4 w-4 mr-2" />
              Tambah Artikel
            </Button>
          </div>

          <!-- Articles List -->
          <Card>
            <CardHeader>
              <CardTitle>Daftar Artikel</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="articles.length === 0" class="text-center py-8 text-muted-foreground">
                Belum ada artikel. Tambahkan artikel pertama Anda!
              </div>
              <div v-else class="space-y-4">
                <div v-for="article in articles" :key="article.id" 
                  class="flex items-center justify-between p-4 border rounded-lg">
                  <div class="flex-1">
                    <h3 class="font-medium">{{ article.title }}</h3>
                    <p class="text-sm text-muted-foreground">{{ article.description || 'Tidak ada deskripsi' }}</p>
                    <div class="flex gap-2 mt-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ getCategoryLabel(article.category) }}
                      </span>
                      <span v-if="article.author" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ article.author }}
                      </span>
                      <span v-if="!article.is_active" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Tidak Aktif
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 ml-4">
                    <Button @click="openArticleDialog(article)" variant="outline" size="sm">
                      <Edit class="h-3 w-3 mr-1" />
                      Edit
                    </Button>
                    <Button @click="deleteArticle(article.id)" variant="destructive" size="sm">
                      <Trash2 class="h-3 w-3 mr-1" />
                      Hapus
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Video Dialog -->
    <Dialog v-model:open="videoDialogOpen">
      <DialogContent class="max-w-2xl" aria-describedby="video-dialog-description">
        <DialogHeader>
          <DialogTitle>{{ editingVideo ? 'Edit Video' : 'Tambah Video' }}</DialogTitle>
          <p id="video-dialog-description" class="sr-only">
            Form untuk {{ editingVideo ? 'mengedit' : 'menambahkan' }} video baru
          </p>
        </DialogHeader>
        <form @submit.prevent="saveVideo" class="space-y-4">
          <div class="space-y-2">
            <Label for="video-title">Judul</Label>
            <Input id="video-title" v-model="videoForm.title" required />
          </div>
          <div class="space-y-2">
            <Label for="video-description">Deskripsi (Opsional)</Label>
            <textarea
              id="video-description"
              v-model="videoForm.description"
              rows="3"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
              placeholder="Masukkan deskripsi video..."
            />
          </div>
          <div class="space-y-2">
            <Label for="video-youtube-id">YouTube ID</Label>
            <Input id="video-youtube-id" v-model="videoForm.youtube_id" placeholder="e.g. dQw4w9WgXcQ" required />
          </div>
          <div class="space-y-2">
            <Label for="video-category">Kategori</Label>
            <Select v-model="videoForm.category">
              <SelectTrigger>
                <SelectValue placeholder="Pilih kategori" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="manajemen">Manajemen</SelectItem>
                <SelectItem value="kesehatan">Kesehatan</SelectItem>
                <SelectItem value="breeding">Breeding</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-center space-x-2">
            <input type="checkbox" id="video-active" v-model="videoForm.is_active" class="rounded" />
            <Label for="video-active">Aktif</Label>
          </div>
          <div class="space-y-2">
            <Label for="video-sort">Urutan (0 = paling atas)</Label>
            <Input id="video-sort" v-model.number="videoForm.sort_order" type="number" min="0" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="videoDialogOpen = false">Batal</Button>
            <Button type="submit" :disabled="videoSaving">
              {{ videoSaving ? 'Menyimpan...' : 'Simpan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Article Dialog -->
    <Dialog v-model:open="articleDialogOpen">
      <DialogContent class="max-w-4xl max-h-[80vh] overflow-y-auto" aria-describedby="article-dialog-description">
        <DialogHeader>
          <DialogTitle>{{ editingArticle ? 'Edit Artikel' : 'Tambah Artikel' }}</DialogTitle>
          <p id="article-dialog-description" class="sr-only">
            Form untuk {{ editingArticle ? 'mengedit' : 'menambahkan' }} artikel baru
          </p>
        </DialogHeader>
        <form @submit.prevent="saveArticle" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="article-title">Judul</Label>
              <Input id="article-title" v-model="articleForm.title" required />
            </div>
            <div class="space-y-2">
              <Label for="article-author">Penulis (Opsional)</Label>
              <Input id="article-author" v-model="articleForm.author" />
            </div>
          </div>
          <div class="space-y-2">
            <Label for="article-description">Deskripsi (Opsional)</Label>
            <textarea
              id="article-description"
              v-model="articleForm.description"
              rows="3"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
              placeholder="Masukkan deskripsi artikel..."
            />
          </div>
          <div class="space-y-2">
            <Label for="article-image">URL Gambar (Opsional)</Label>
            <Input id="article-image" v-model="articleForm.image_url" type="url" />
          </div>
          <div class="space-y-2">
            <Label for="article-category">Kategori *</Label>
            <Select v-model="articleForm.category" required>
              <SelectTrigger>
                <SelectValue placeholder="Pilih kategori" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="manajemen">Manajemen</SelectItem>
                <SelectItem value="kesehatan">Kesehatan</SelectItem>
                <SelectItem value="breeding">Breeding</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label for="article-content">Konten</Label>
            <textarea
              id="article-content"
              v-model="articleForm.content"
              rows="8"
              required
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
              placeholder="Masukkan konten artikel..."
            />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-center space-x-2">
              <input type="checkbox" id="article-active" v-model="articleForm.is_active" class="rounded" />
              <Label for="article-active">Aktif</Label>
            </div>
            <div class="space-y-2">
              <Label for="article-sort">Urutan</Label>
              <Input id="article-sort" v-model.number="articleForm.sort_order" type="number" min="0" />
            </div>
            <div class="space-y-2">
              <Label for="article-published">Tanggal Publikasi</Label>
              <Input id="article-published" v-model="articleForm.published_at" type="datetime-local" />
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="articleDialogOpen = false">Batal</Button>
            <Button type="button" variant="outline" @click="console.log('Debug form data:', articleForm)">Debug</Button>
            <Button type="submit" :disabled="articleSaving">
              {{ articleSaving ? 'Menyimpan...' : 'Simpan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import axios from 'axios';

// Configure axios to include CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const props = defineProps<{
  videos: Array<any>;
  articles: Array<any>;
}>();

// Tab state
const activeTab = ref('videos');

// Data
const videos = ref(props.videos || []);
const articles = ref(props.articles || []);

// Video management
const videoDialogOpen = ref(false);
const videoSaving = ref(false);
const editingVideo = ref(null);
const videoForm = ref({
  title: '',
  description: '',
  youtube_id: '',
  category: '',
  is_active: true,
  sort_order: 0,
});

// Article management
const articleDialogOpen = ref(false);
const articleSaving = ref(false);
const editingArticle = ref(null);
const articleForm = ref({
  title: '',
  description: '',
  content: '',
  author: '',
  image_url: '',
  category: '',
  is_active: true,
  sort_order: 0,
  published_at: '',
});

// Helper functions
const getCategoryLabel = (category: string) => {
  const labels = {
    'manajemen': 'Manajemen',
    'kesehatan': 'Kesehatan',
    'breeding': 'Breeding'
  };
  return labels[category] || category;
};

const resetVideoForm = () => {
  videoForm.value = {
    title: '',
    description: '',
    youtube_id: '',
    category: '',
    is_active: true,
    sort_order: 0,
  };
};

const resetArticleForm = () => {
  articleForm.value = {
    title: '',
    description: '',
    content: '',
    author: '',
    image_url: '',
    category: 'manajemen',
    is_active: true,
    sort_order: 0,
    published_at: '',
  };
};

// Video functions
const openVideoDialog = (video = null) => {
  editingVideo.value = video;
  if (video) {
    videoForm.value = { ...video };
  } else {
    resetVideoForm();
  }
  videoDialogOpen.value = true;
};

const saveVideo = async () => {
  videoSaving.value = true;
  try {
    if (editingVideo.value) {
      await axios.put(`/api/videos/${editingVideo.value.id}`, videoForm.value);
      const index = videos.value.findIndex(v => v.id === editingVideo.value.id);
      if (index !== -1) {
        videos.value[index] = { ...editingVideo.value, ...videoForm.value };
      }
    } else {
      const response = await axios.post('/api/videos', videoForm.value);
      videos.value.push(response.data.video);
    }
    videoDialogOpen.value = false;
    resetVideoForm();
  } catch (error) {
    console.error('Error saving video:', error);
    alert('Gagal menyimpan video. Silakan coba lagi.');
  } finally {
    videoSaving.value = false;
  }
};

const deleteVideo = async (videoId: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus video ini?')) return;
  
  try {
    await axios.delete(`/api/videos/${videoId}`);
    videos.value = videos.value.filter(v => v.id !== videoId);
  } catch (error) {
    console.error('Error deleting video:', error);
    alert('Gagal menghapus video. Silakan coba lagi.');
  }
};

// Article functions
const openArticleDialog = (article = null) => {
  editingArticle.value = article;
  if (article) {
    articleForm.value = { 
      ...article,
      published_at: article.published_at ? new Date(article.published_at).toISOString().slice(0, 16) : ''
    };
  } else {
    resetArticleForm();
  }
  articleDialogOpen.value = true;
};

const saveArticle = async () => {
  articleSaving.value = true;
  try {
    console.log('Sending article data:', articleForm.value);
    if (editingArticle.value) {
      await axios.put(`/api/articles/${editingArticle.value.id}`, articleForm.value);
      const index = articles.value.findIndex(a => a.id === editingArticle.value.id);
      if (index !== -1) {
        articles.value[index] = { ...editingArticle.value, ...articleForm.value };
      }
    } else {
      const response = await axios.post('/api/articles', articleForm.value);
      articles.value.push(response.data.article);
    }
    articleDialogOpen.value = false;
    resetArticleForm();
  } catch (error) {
    console.error('Error saving article:', error);
    console.log('Article form data was:', articleForm.value);
    if (error.response && error.response.status === 422) {
      console.log('Validation errors:', error.response.data);
      const errors = error.response.data.errors;
      const errorMessages = Object.values(errors).flat();
      alert('Validasi gagal:\n' + errorMessages.join('\n'));
    } else {
      alert('Gagal menyimpan artikel. Silakan coba lagi.');
    }
  } finally {
    articleSaving.value = false;
  }
};

const deleteArticle = async (articleId: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus artikel ini?')) return;
  
  try {
    await axios.delete(`/api/articles/${articleId}`);
    articles.value = articles.value.filter(a => a.id !== articleId);
  } catch (error) {
    console.error('Error deleting article:', error);
    alert('Gagal menghapus artikel. Silakan coba lagi.');
  }
};

onMounted(() => {
  // Component is ready
});
</script>