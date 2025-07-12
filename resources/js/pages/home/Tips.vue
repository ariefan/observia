<script setup lang="ts">
import { ref, onMounted, warn } from 'vue';
import YoutubePlayer from '@/components/YoutubePlayer.vue';
import Example1 from '@/assets/example-1.jpg';

const products = ref([
    { id: '2L4dQcAhHOw' },
    { id: 'poD6VPc1JmQ' },
    { id: 'o8uRaaZR1Ew' },
]);

const articles = ref([
    { id: 'dQw4w9WgXcQ' },
    { id: '9bZkp7q19f0' },
    { id: '3JZ_D3ELwOQ' },
    { id: 'hTWKbfoikeg' },
    { id: 'e-ORhEE9VVg' },
    { id: 'fLexgOxsZu0' },
    { id: 'L_jWHffIx5E' },
    { id: 'RgKAFK5djSk' },
    { id: 'YykjpeuMNEk' },
    { id: 'uelHwf8o7_U' }
]);

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

defineProps<{
    name?: string;
}>();
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
                        <Carousel :value="products" :numVisible="3" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <YoutubePlayer :videoId="slotProps.data.id" />
                                </div>
                            </template>
                        </Carousel>
                    </TabPanel>
                    <TabPanel value="1">
                        <p class="m-0">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
                            laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi
                            architecto beatae vitae dicta sunt explicabo. Nemo enim
                            ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur
                            magni dolores eos qui ratione voluptatem sequi nesciunt. Consectetur, adipisci velit, sed
                            quia non numquam eius modi.
                        </p>
                    </TabPanel>
                    <TabPanel value="2">
                        <p class="m-0">
                            At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium
                            voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati
                            cupiditate non provident, similique sunt in culpa
                            qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum
                            facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio
                            cumque nihil impedit quo minus.
                        </p>
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
                        <Carousel :value="articles" :numVisible="4" :numScroll="1" :circular="true"
                            :responsiveOptions="responsiveOptions">
                            <template #item="slotProps">
                                <div class="mx-1">
                                    <Card style="overflow: hidden" class="mb-1">
                                        <template #header>
                                            <img alt="user header" :src="Example1" />
                                        </template>
                                        <template #title>
                                            <p class="text-sm">Mengenal indukan unggul</p>
                                        </template>
                                        <template #subtitle>
                                            <p class="text-xs">Budi Setiawan</p>
                                            <p class="text-xs">2 April 2025</p>
                                        </template>
                                        <template #content>
                                            <p class="m-0 text-sm">
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Inventore sed
                                                consequuntur...
                                            </p>
                                        </template>
                                    </Card>
                                </div>
                            </template>
                        </Carousel>
                    </TabPanel>
                    <TabPanel value="1">
                        <p class="m-0">
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
                            laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi
                            architecto beatae vitae dicta sunt explicabo. Nemo enim
                            ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur
                            magni dolores eos qui ratione voluptatem sequi nesciunt. Consectetur, adipisci velit, sed
                            quia non numquam eius modi.
                        </p>
                    </TabPanel>
                    <TabPanel value="2">
                        <p class="m-0">
                            At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium
                            voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati
                            cupiditate non provident, similique sunt in culpa
                            qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum
                            facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio
                            cumque nihil impedit quo minus.
                        </p>
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
