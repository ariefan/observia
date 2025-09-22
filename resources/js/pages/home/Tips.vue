<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import YoutubePlayer from '@/components/YoutubePlayer.vue';
import Example1 from '@/assets/example-1.jpg';
import axios from 'axios';

const videos = ref({
    manajemen: [],
    kesehatan: [],
    breeding: [],
});

const articles = ref({
    manajemen: [],
    kesehatan: [],
    breeding: [],
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


const value = ref('0');
const articleValue = ref('0');

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
            breeding: [],
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
            breeding: [],
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
                    <Button @click="value = '0'" size="small" rounded :outlined="value !== '0'">
                        <p class="text-xs px-2">Manajemen</p>
                    </Button>
                    <Button @click="value = '1'" size="small" rounded :outlined="value !== '1'">
                        <p class="text-xs px-2">Kesehatan</p>
                    </Button>
                    <Button @click="value = '2'" size="small" rounded :outlined="value !== '2'">
                        <p class="text-xs px-2">Breeding</p>
                    </Button>
                </div>
                <a href="#" class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua
                </a>
            </div>

            <Tabs v-model:value="value">
                <TabPanels>
                    <TabPanel value="0">
                        <Carousel v-if="videos.manajemen.length > 0" :value="videos.manajemen" :numVisible="3" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
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
                        <Carousel v-if="videos.kesehatan.length > 0" :value="videos.kesehatan" :numVisible="3" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
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
                        <Carousel v-if="videos.breeding.length > 0" :value="videos.breeding" :numVisible="3" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <YoutubePlayer :videoId="slotProps.data.youtube_id" :title="slotProps.data.title" />
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada video untuk kategori Breeding
                        </div>
                    </TabPanel>
                </TabPanels>
            </Tabs>





            <div class="flex justify-between px-2 mt-8">
                <div class="flex mb-2 gap-4 justify-start">
                    <h2 class="text-lg font-semibold">Artikel</h2>
                    <Button @click="articleValue = '0'" size="small" rounded :outlined="articleValue !== '0'">
                        <p class="text-xs px-2">Manajemen</p>
                    </Button>
                    <Button @click="articleValue = '1'" size="small" rounded :outlined="articleValue !== '1'">
                        <p class="text-xs px-2">Kesehatan</p>
                    </Button>
                    <Button @click="articleValue = '2'" size="small" rounded :outlined="articleValue !== '2'">
                        <p class="text-xs px-2">Breeding</p>
                    </Button>
                </div>
                <a href="#" class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua
                </a>
            </div>

            <Tabs v-model:value="articleValue">
                <TabPanels>
                    <TabPanel value="0">
                        <Carousel v-if="articles.manajemen.length > 0" :value="articles.manajemen" :numVisible="4" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden" class="mb-1 cursor-pointer hover:shadow-lg transition-shadow" @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <img alt="article header" :src="slotProps.data.image_url || Example1" />
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{ slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0, 100) + '...' }}
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
                        <Carousel v-if="articles.kesehatan.length > 0" :value="articles.kesehatan" :numVisible="4" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden" class="mb-1 cursor-pointer hover:shadow-lg transition-shadow" @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <img alt="article header" :src="slotProps.data.image_url || Example1" />
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{ slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0, 100) + '...' }}
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
                        <Carousel v-if="articles.breeding.length > 0" :value="articles.breeding" :numVisible="4" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden" class="mb-1 cursor-pointer hover:shadow-lg transition-shadow" @click="openArticle(slotProps.data)">
                                        <template #header>
                                            <img alt="article header" :src="slotProps.data.image_url || Example1" />
                                        </template>
                                        <template #title>
                                            <p class="text-sm hover:text-blue-600 transition-colors">{{ slotProps.data.title }}</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">{{ slotProps.data.author || 'Anonim' }}</p>
                                            <p class="text-xs">{{ formatDate(slotProps.data.published_at) }}</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                {{ slotProps.data.description || slotProps.data.content.substring(0, 100) + '...' }}
                                            </p>
                                        </template>
                                    </Card>
                                </div>
                            </template>
                        </Carousel>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            Belum ada artikel untuk kategori Breeding
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
</style>
