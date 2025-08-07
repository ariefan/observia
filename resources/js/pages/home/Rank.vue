<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import LivestockDefault from "@/assets/livestock-default.png";

defineProps<{
    name?: string;
}>();

interface LivestockRanking {
    id: string;
    name: string;
    aifarm_id: string;
    photo: string | null;
    species: string;
    average_litre_per_day?: number;
    total_volume?: number;
    lactation_days?: number;
    current_weight?: number;
}

interface RankingData {
    milk_rankings: LivestockRanking[];
    weight_rankings: LivestockRanking[];
}

const rankings = ref<RankingData>({ milk_rankings: [], weight_rankings: [] });
const loading = ref(true);
const error = ref<string | null>(null);

// Get top 3 milk producers
const topMilkProducers = computed(() => {
    return rankings.value.milk_rankings.slice(0, 3);
});

// Get top 3 by weight
const topWeightLivestock = computed(() => {
    return rankings.value.weight_rankings.slice(0, 3);
});

// Get livestock photo URL
const getPhotoUrl = (livestock: LivestockRanking) => {
    if (livestock.photo) {
        return `/storage/${livestock.photo}`;
    }
    return LivestockDefault;
};

// Navigate to livestock detail
const viewLivestock = (id: string) => {
    window.location.href = `/livestocks/${id}`;
};

// Load rankings data
const loadRankings = async () => {
    try {
        console.log('Loading rankings data...');
        loading.value = true;
        const response = await axios.get('/api/livestocks/rankings');
        console.log('Rankings response:', response.data);
        rankings.value = response.data;
        error.value = null;
    } catch (err) {
        console.error('Failed to load rankings:', err);
        error.value = 'Gagal memuat data ranking';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    console.log('Rank component mounted!');
    loadRankings();
});
</script>

<template>
    <Card>
        <template #title>
            <div class="flex justify-between items-center md:text-lg text-base lg:text-xl">
                <span>Produksi Susu Terbaik</span>
                <div class="flex items-center gap-2">
                    <span class="text-sm">Perah pagi & sore</span>
                    <button @click="loadRankings" :disabled="loading"
                        class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
        <template #subtitle>Rank Ternak</template>
        <template #content>
            <div v-if="loading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>

            <div v-else-if="error" class="text-center py-8 text-red-500">
                {{ error }}
            </div>

            <div v-else-if="topMilkProducers.length === 0" class="text-center py-8 text-muted-foreground">
                Belum ada data produksi susu
            </div>

            <div v-else class="flex flex-col items-center gap-6">
                <!-- Ranking Cards -->
                <div class="flex justify-center gap-12 items-end">
                    <!-- 2nd Place -->
                    <div v-if="topMilkProducers[1]"
                        class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                        @click="viewLivestock(topMilkProducers[1].id)">
                        <Avatar class="border-2 border-primary" shape="circle">
                            <AvatarImage :src="getPhotoUrl(topMilkProducers[1])" :alt="topMilkProducers[1].name" />
                            <AvatarFallback>{{ topMilkProducers[1].name.charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <Badge class="!rounded-full">{{ topMilkProducers[1].aifarm_id }}</Badge>
                        <div class="text-xs">{{ topMilkProducers[1].name }}</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-silver-bg rounded-t-xl w-16 h-28 justify-center pb-2 shadow-lg">
                            <div class="text-2xl font-semibold">2</div>
                            <div class="text-xs">{{ topMilkProducers[1].average_litre_per_day }} L/hari</div>
                        </div>
                    </div>

                    <!-- 1st Place -->
                    <div v-if="topMilkProducers[0]"
                        class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                        @click="viewLivestock(topMilkProducers[0].id)">
                        <Avatar class="border-2 border-primary" shape="circle">
                            <AvatarImage :src="getPhotoUrl(topMilkProducers[0])" :alt="topMilkProducers[0].name" />
                            <AvatarFallback>{{ topMilkProducers[0].name.charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <Badge class="!rounded-full">{{ topMilkProducers[0].aifarm_id }}</Badge>
                        <div class="text-xs">{{ topMilkProducers[0].name }}</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-gold-bg bg-gradient-to-t rounded-t-xl w-16 h-36 justify-center pb-2">
                            <div class="text-2xl font-semibold">1</div>
                            <div class="text-xs">{{ topMilkProducers[0].average_litre_per_day }} L/hari</div>
                        </div>
                    </div>

                    <!-- 3rd Place -->
                    <div v-if="topMilkProducers[2]"
                        class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                        @click="viewLivestock(topMilkProducers[2].id)">
                        <Avatar class="border-2 border-primary" shape="circle">
                            <AvatarImage :src="getPhotoUrl(topMilkProducers[2])" :alt="topMilkProducers[2].name" />
                            <AvatarFallback>{{ topMilkProducers[2].name.charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <Badge class="!rounded-full">{{ topMilkProducers[2].aifarm_id }}</Badge>
                        <div class="text-xs">{{ topMilkProducers[2].name }}</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-bronze-bg bg-gradient-to-t rounded-t-xl w-16 h-24 justify-center pb-2">
                            <div class="text-2xl font-semibold">3</div>
                            <div class="text-xs">{{ topMilkProducers[2].average_litre_per_day }} L/hari</div>
                        </div>
                    </div>
                </div>

                <p class="text-sm">Ternak dengan produksi susu terbaik ini memiliki tingkat
                    produktivitas
                    yang tinggi dari seluruh ternak, memberikan susu berkualitas tinggi untuk bisnis Anda.
                </p>

                <!-- View All Link -->
                <a href="/livestocks"
                    class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua ternak produksi susu terbaik
                </a>
            </div>

        </template>
    </Card>

    <Card>
        <template #title>
            <div class="flex justify-between items-center md:text-lg text-base lg:text-xl">
                <span>Bobot terbaik</span>
                <div class="flex items-center gap-2">
                    <span class="text-sm">Pengecekan hari ini</span>
                    <button @click="loadRankings" :disabled="loading"
                        class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
        <template #subtitle>Rank Ternak</template>

        <template #content>
            <div v-if="loading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>

            <div v-else-if="error" class="text-center py-8 text-red-500">
                {{ error }}
            </div>

            <div v-else-if="topWeightLivestock.length === 0" class="text-center py-8 text-muted-foreground">
                Belum ada data bobot ternak
            </div>

            <div v-else class="flex flex-col items-center gap-6">
                <!-- Ranking Cards -->
                <div class="flex justify-center gap-12 items-end">
                    <!-- 2nd Place -->
                    <div v-if="topWeightLivestock[1]"
                        class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                        @click="viewLivestock(topWeightLivestock[1].id)">
                        <Avatar class="border-2 border-primary mr-2" shape="circle">
                            <AvatarImage :src="getPhotoUrl(topWeightLivestock[1])" :alt="topWeightLivestock[1].name" />
                            <AvatarFallback>{{ topWeightLivestock[1].name.charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <Badge class="!rounded-full">{{ topWeightLivestock[1].aifarm_id }}</Badge>
                        <div class="text-xs">{{ topWeightLivestock[1].name }}</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-silver-bg rounded-t-xl w-16 h-28 justify-center pb-2 shadow-lg">
                            <div class="text-2xl font-semibold">2</div>
                            <div class="text-xs">{{ topWeightLivestock[1].current_weight }} Kg</div>
                        </div>
                    </div>

                    <!-- 1st Place -->
                    <div v-if="topWeightLivestock[0]"
                        class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                        @click="viewLivestock(topWeightLivestock[0].id)">
                        <Avatar class="border-2 border-primary mr-2" shape="circle">
                            <AvatarImage :src="getPhotoUrl(topWeightLivestock[0])" :alt="topWeightLivestock[0].name" />
                            <AvatarFallback>{{ topWeightLivestock[0].name.charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <Badge class="!rounded-full">{{ topWeightLivestock[0].aifarm_id }}</Badge>
                        <div class="text-xs">{{ topWeightLivestock[0].name }}</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-gold-bg bg-gradient-to-t rounded-t-xl w-16 h-36 justify-center pb-2">
                            <div class="text-2xl font-semibold">1</div>
                            <div class="text-xs">{{ topWeightLivestock[0].current_weight }} Kg</div>
                        </div>
                    </div>

                    <!-- 3rd Place -->
                    <div v-if="topWeightLivestock[2]"
                        class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                        @click="viewLivestock(topWeightLivestock[2].id)">
                        <Avatar class="border-2 border-primary mr-2" shape="circle">
                            <AvatarImage :src="getPhotoUrl(topWeightLivestock[2])" :alt="topWeightLivestock[2].name" />
                            <AvatarFallback>{{ topWeightLivestock[2].name.charAt(0).toUpperCase() }}</AvatarFallback>
                        </Avatar>
                        <Badge class="!rounded-full">{{ topWeightLivestock[2].aifarm_id }}</Badge>
                        <div class="text-xs">{{ topWeightLivestock[2].name }}</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-bronze-bg bg-gradient-to-t rounded-t-xl w-16 h-24 justify-center pb-2">
                            <div class="text-2xl font-semibold">3</div>
                            <div class="text-xs">{{ topWeightLivestock[2].current_weight }} Kg</div>
                        </div>
                    </div>
                </div>

                <p class="text-sm">Ternak dengan bobot terbaik ini menunjukkan pertumbuhan dan kesehatan yang optimal,
                    menjadi indikator kualitas manajemen peternakan yang baik.
                </p>

                <!-- View All Link -->
                <a href="/livestocks"
                    class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua bobot ternak terbaik
                </a>
            </div>

        </template>
    </Card>
</template>


<style scoped>
.rank-gold-bg {
    background: linear-gradient(158.09deg,
            #ae9614 -17.83%,
            #ffe81c 23.34%,
            #9e5908 136.37%);
    height: 150px;
}

.rank-silver-bg {
    background: linear-gradient(145.86deg,
            #a3aeaf -1.81%,
            #eceeee 25.28%,
            #888e8e 95.36%);
    height: 125px;
}

.rank-bronze-bg {
    background: linear-gradient(151.76deg,
            #9f7156 -9.52%,
            #f7bf6c 26.03%,
            #7e5137 115.1%);
    height: 100px;
}

.text-shadow {
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
}
</style>
