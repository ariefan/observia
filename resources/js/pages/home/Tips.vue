<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import YoutubePlayer from '@/components/YoutubePlayer.vue';
import Example1 from '@/assets/example-1.jpg';
import { ImageOff } from 'lucide-vue-next';
import axios from 'axios';

const videos = ref({
    manajemen: [],
    kesehatan: [],
    budidaya: [],
});

const articles = ref({
    manajemen: [],
    kesehatan: [],
    budidaya: [],
});

const responsiveOptions = ref([
    {
        breakpoint: '1400px',
        numVisible: 2,
        numScroll: 1
    },
    {
        breakpoint: '1199px',
        numVisible: 3,
        numScroll: 1
    },
    {
        breakpoint: '767px',
        numVisible: 2,
        numScroll: 1
    },
    {
        breakpoint: '575px',
        numVisible: 1,
        numScroll: 1
    }
]);


const value = ref('all');
const articleValue = ref('all');

// Fetch content from API
const fetchContent = async () => {
    try {
        const response = await axios.get('/api/content');
        videos.value = response.data.videos;
        articles.value = response.data.articles;
    } catch (error) {
        console.error('Error fetching content:', error);
        // Fallback to default data if API fails
        videos.value = {
            manajemen: [
                { id: 1, title: 'Manajemen Peternakan', youtube_id: '2L4dQcAhHOw' },
                { id: 2, title: 'Teknik Pemberian Pakan', youtube_id: 'poD6VPc1JmQ' },
                { id: 3, title: 'Manajemen Kandang', youtube_id: 'o8uRaaZR1Ew' },
            ],
            kesehatan: [],
            budidaya: [],
        };
        articles.value = {
            manajemen: Array(10).fill(null).map((_, index) => ({
                id: index + 1,
                title: 'Mengenal indukan unggul',
                description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed consequuntur...',
                author: 'Budi Setiawan',
                published_at: '2024-04-02',
                image_url: null,
            })),
            kesehatan: [],
            budidaya: [],
        };
    }
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

// Function to open article details
const openArticle = (article: any) => {
    router.visit(`/articles/${article.id}`);
};

onMounted(() => {
    fetchContent();
});
</script>

<template>
    <Card>
        <template #title>
            <div class="flex justify-between items-center md:text-lg text-base lg:text-xl">
                Temukan Tips dan Triknya di Sini.
            </div>
        </template>
        <template #content>
            <p class="mb-8">Akses kumpulan video dan artikel informatif untuk membantu meningkatkan peternakan Anda.</p>
            <div class="flex justify-between px-2">
                <div class="flex mb-2 gap-4 justify-start">
                    <h2 class="text-lg font-semibold">Video</h2>
                    <Button @click="value = 'all'" size="small" rounded :outlined="value !== 'all'">
                        <p class="text-xs px-2">Semua</p>
                    </Button>
                    <Button @click="value = '0'" size="small" rounded :outlined="value !== '0'">
                        <p class="text-xs px-2">Manajemen</p>
                    </Button>
                    <Button @click="value = '1'" size="small" rounded :outlined="value !== '1'">
                        <p class="text-xs px-2">Kesehatan</p>
                    </Button>
                    <Button @click="value = '2'" size="small" rounded :outlined="value !== '2'">
                        <p class="text-xs px-2">Budi Daya</p>
                    </Button>
                </div>
                <button @click="router.visit('/videos')"
                    class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline mr-4 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua
                </button>
            </div>

            <Tabs v-model:value="value">
                <TabPanels>
                    <TabPanel value="all">
                        <Carousel
                            v-if="[...(videos.manajemen || []), ...(videos.kesehatan || []), ...(videos.budidaya || [])].length > 0"
                            :value="[...(videos.manajemen || []), ...(videos.kesehatan || []), ...(videos.budidaya || [])]"
                            :numVisible="3" :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <YoutubePlayer :videoId="slotProps.data.youtube_id" :title="slotProps.data.title" />
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada video tersedia
                        </div>
                    </TabPanel>
                    <TabPanel value="0">
                        <Carousel v-if="videos.manajemen.length > 0" :value="videos.manajemen" :numVisible="3"
                            :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <YoutubePlayer :videoId="slotProps.data.youtube_id" :title="slotProps.data.title" />
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada video untuk kategori Manajemen
                        </div>
                    </TabPanel>
                    <TabPanel value="1">
                        <Carousel v-if="videos.kesehatan.length > 0" :value="videos.kesehatan" :numVisible="3"
                            :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <YoutubePlayer :videoId="slotProps.data.youtube_id" :title="slotProps.data.title" />
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada video untuk kategori Kesehatan
                        </div>
                    </TabPanel>
                    <TabPanel value="2">
                        <Carousel v-if="(videos.budidaya || []).length > 0" :value="videos.budidaya || []"
                            :numVisible="3" :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <YoutubePlayer :videoId="slotProps.data.youtube_id" :title="slotProps.data.title" />
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada video untuk kategori Budi Daya
                        </div>
                    </TabPanel>
                </TabPanels>
            </Tabs>





            <div class="flex justify-between px-2 mt-8">
                <div class="flex mb-2 gap-4 justify-start">
                    <h2 class="text-lg font-semibold">Artikel</h2>
                    <Button @click="articleValue = 'all'" size="small" rounded :outlined="articleValue !== 'all'">
                        <p class="text-xs px-2">Semua</p>
                    </Button>
                    <Button @click="articleValue = '0'" size="small" rounded :outlined="articleValue !== '0'">
                        <p class="text-xs px-2">Manajemen</p>
                    </Button>
                    <Button @click="articleValue = '1'" size="small" rounded :outlined="articleValue !== '1'">
                        <p class="text-xs px-2">Kesehatan</p>
                    </Button>
                    <Button @click="articleValue = '2'" size="small" rounded :outlined="articleValue !== '2'">
                        <p class="text-xs px-2">Budi Daya</p>
                    </Button>
                </div>
                <button @click="router.visit('/articles')"
                    class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline mr-4 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua
                </button>
            </div>

            <Tabs v-model:value="articleValue">
                <TabPanels>
                    <TabPanel value="all">
                        <Carousel
                            v-if="[...(articles.manajemen || []), ...(articles.kesehatan || []), ...(articles.budidaya || [])].length > 0"
                            :value="[...(articles.manajemen || []), ...(articles.kesehatan || []), ...(articles.budidaya || [])]"
                            :numVisible="4" :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden; height: 400px;"
                                        class="mb-1 cursor-pointer hover:shadow-lg transition-shadow flex flex-col w-full"
                                        @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <div
                                                style="height: 200px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                                <img v-if="slotProps.data.image_url" alt="article header"
                                                    :src="slotProps.data.image_url"
                                                    style="height: 100%; width: 100%; object-fit: cover;" />
                                                <ImageOff v-else style="width: 48px; height: 48px; color: #9ca3af;" />
                                            </div>
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{
                                                slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0,
                                                100) + '...' }}
                                            </p>
                                        </template>
                                    </Card>
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada artikel tersedia
                        </div>
                    </TabPanel>
                    <TabPanel value="0">
                        <Carousel v-if="articles.manajemen.length > 0" :value="articles.manajemen" :numVisible="4"
                            :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden; height: 400px;"
                                        class="mb-1 cursor-pointer hover:shadow-lg transition-shadow flex flex-col w-full"
                                        @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <div
                                                style="height: 200px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                                <img v-if="slotProps.data.image_url" alt="article header"
                                                    :src="slotProps.data.image_url"
                                                    style="height: 100%; width: 100%; object-fit: cover;" />
                                                <ImageOff v-else style="width: 48px; height: 48px; color: #9ca3af;" />
                                            </div>
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{
                                                slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0,
                                                100) + '...' }}
                                            </p>
                                        </template>
                                    </Card>
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada artikel untuk kategori Manajemen
                        </div>
                    </TabPanel>
                    <TabPanel value="1">
                        <Carousel v-if="articles.kesehatan.length > 0" :value="articles.kesehatan" :numVisible="4"
                            :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden; height: 400px;"
                                        class="mb-1 cursor-pointer hover:shadow-lg transition-shadow flex flex-col w-full"
                                        @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <div
                                                style="height: 200px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                                <img v-if="slotProps.data.image_url" alt="article header"
                                                    :src="slotProps.data.image_url"
                                                    style="height: 100%; width: 100%; object-fit: cover;" />
                                                <ImageOff v-else style="width: 48px; height: 48px; color: #9ca3af;" />
                                            </div>
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{
                                                slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0,
                                                100) + '...' }}
                                            </p>
                                        </template>
                                    </Card>
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada artikel untuk kategori Kesehatan
                        </div>
                    </TabPanel>
                    <TabPanel value="2">
                        <Carousel v-if="(articles.budidaya || []).length > 0" :value="articles.budidaya || []"
                            :numVisible="4" :numScroll="1" :circular="true" :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden; height: 400px;"
                                        class="mb-1 cursor-pointer hover:shadow-lg transition-shadow flex flex-col w-full"
                                        @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <div
                                                style="height: 200px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                                <img v-if="slotProps.data.image_url" alt="article header"
                                                    :src="slotProps.data.image_url"
                                                    style="height: 100%; width: 100%; object-fit: cover;" />
                                                <ImageOff v-else style="width: 48px; height: 48px; color: #9ca3af;" />
                                            </div>
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{
                                                slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0,
                                                100) + '...' }}
                                            </p>
                                        </template>
                                    </Card>
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada artikel untuk kategori Budi Daya
                        </div>
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </template>
    </Card>
</template>

<style>
.p-carousel-prev-button,
.p-carousel-next-button {
    background-color: #0d9488 !important;
    color: white !important;
}

.p-carousel-prev-button:hover,
.p-carousel-next-button:hover {
    background-color: #2dd4bf !important;
    border-radius: 50% !important;
}

/* Ensure consistent card heights and proper content distribution */
.p-card {
    display: flex !important;
    flex-direction: column !important;
    width: 100% !important;
}

.p-card .p-card-content {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
}

.p-card .p-card-body {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Carousel item styling for full width */
.p-carousel .p-carousel-content .p-carousel-container .p-carousel-items-content .p-carousel-items-container .p-carousel-item {
    display: flex !important;
    width: 100% !important;
}

.p-carousel .p-carousel-content .p-carousel-container .p-carousel-items-content .p-carousel-items-container .p-carousel-item>div {
    width: 100% !important;
}
</style>
