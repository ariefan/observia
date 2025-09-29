<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ProductivitySidebar from '@/components/ProductivitySidebar.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { CalendarDays, Droplets, Weight, TrendingUp, Trophy, Award, Medal } from 'lucide-vue-next';
import { IconMilk, IconScaleOutline } from '@tabler/icons-vue';
import { usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import LivestockDefault from "@/assets/livestock-default.png";
import LivestockDetailDialog from '@/components/LivestockDetailDialog.vue';

const page = usePage<SharedData>();

defineOptions({
    layout: AppLayout,
});

interface MilkRanking {
    id: string;
    name: string;
    tag_id: string;
    daily_milk_production: number;
    avatar?: string;
    rank: number;
    herd_name?: string;
}

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

interface Props {
    rankings: MilkRanking[];
    date: string;
}

const props = defineProps<Props>();

// Data for best milk production component
const topRankings = ref<RankingData>({ milk_rankings: [], weight_rankings: [] });
const loading = ref(true);
const error = ref<string | null>(null);

// Dialog state
const showDialog = ref(false);
const selectedLivestock = ref<LivestockRanking | null>(null);


const getRankIcon = (rank: number) => {
    switch (rank) {
        case 1: return Trophy;
        case 2: return Award;
        case 3: return Medal;
        default: return null;
    }
};

const getRankColor = (rank: number) => {
    switch (rank) {
        case 1: return 'text-yellow-500';
        case 2: return 'text-gray-400';
        case 3: return 'text-amber-600';
        default: return 'text-muted-foreground';
    }
};

const getAvatarFallback = (name: string) => {
    return name ? name.substring(0, 2).toUpperCase() : 'UN';
};

// Functions for best milk production component
const topMilkProducers = computed(() => {
    return topRankings.value.milk_rankings.slice(0, 3);
});

const getPhotoUrl = (livestock: LivestockRanking) => {
    if (livestock.photo) {
        return `/storage/${livestock.photo}`;
    }
    return LivestockDefault;
};

const viewLivestock = (livestock: LivestockRanking) => {
    selectedLivestock.value = {
        ...livestock,
        national_rank: Math.floor(Math.random() * 1000) + 1, // Mock data
        barn_rank: Math.floor(Math.random() * 50) + 1, // Mock data
        total_national_livestock: 10000
    };
    showDialog.value = true;
};

const viewMilkRanking = (milkRanking: MilkRanking) => {
    selectedLivestock.value = {
        id: milkRanking.id,
        name: milkRanking.name,
        aifarm_id: milkRanking.tag_id,
        photo: milkRanking.avatar || null,
        species: 'Kambing Etawa', // Default species for milk rankings
        average_litre_per_day: milkRanking.daily_milk_production,
        national_rank: Math.floor(Math.random() * 1000) + 1, // Mock data
        barn_rank: milkRanking.rank,
        total_national_livestock: 10000
    };
    showDialog.value = true;
};

const viewLivestockById = (id: string) => {
    window.location.href = route('livestocks.show', { livestock: id });
};

const loadRankings = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/livestocks/rankings');
        topRankings.value = response.data;
        error.value = null;
    } catch (err) {
        console.error('Failed to load rankings:', err);
        error.value = 'Gagal memuat data ranking';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadRankings();
});
</script>

<template>
    <div class="flex">
        <ProductivitySidebar current-route="productivity.milk" />

        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Ranking Produksi Susu</h1>
                        <p class="text-muted-foreground">
                            Ranking ternak berdasarkan produksi susu harian
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                            <CalendarDays class="h-4 w-4" />
                            <span>Ranking berdasarkan tanggal terbaru & volume tertinggi</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Left side - Best milk production component -->
                    <div class="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <CardTitle class="md:text-lg text-base lg:text-xl">Produksi Susu Terbaik
                                        </CardTitle>
                                        <CardDescription>Rank Ternak</CardDescription>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-muted-foreground">Perah pagi & sore</span>
                                        <button @click="loadRankings" :disabled="loading"
                                            class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                                            <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div v-if="loading" class="flex justify-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                                </div>

                                <div v-else-if="error" class="text-center py-8 text-red-500">
                                    {{ error }}
                                </div>

                                <div v-else-if="topMilkProducers.length === 0"
                                    class="text-center py-8 text-muted-foreground">
                                    Belum ada data produksi susu
                                </div>

                                <div v-else class="flex flex-col items-center gap-6">
                                    <!-- Ranking Cards -->
                                    <div class="flex justify-center gap-12 items-end">
                                        <!-- 2nd Place -->
                                        <div v-if="topMilkProducers[1]"
                                            class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                                            @click="viewLivestock(topMilkProducers[1])">
                                            <Avatar class="border-2 border-primary" shape="circle">
                                                <AvatarImage :src="getPhotoUrl(topMilkProducers[1])"
                                                    :alt="topMilkProducers[1].name" />
                                                <AvatarFallback>{{ topMilkProducers[1].name.charAt(0).toUpperCase() }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <Badge class="!rounded-full">{{ topMilkProducers[1].aifarm_id }}</Badge>
                                            <div class="text-xs">{{ topMilkProducers[1].name }}</div>
                                            <div
                                                class="flex flex-col text-white text-shadow items-center rank-silver-bg rounded-t-xl w-16 h-28 justify-center pb-2 shadow-lg">
                                                <div class="text-2xl font-semibold">2</div>
                                                <div class="text-xs">{{ topMilkProducers[1].average_litre_per_day }}
                                                    L/hari</div>
                                            </div>
                                        </div>

                                        <!-- 1st Place -->
                                        <div v-if="topMilkProducers[0]"
                                            class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                                            @click="viewLivestock(topMilkProducers[0])">
                                            <Avatar class="border-2 border-primary" shape="circle">
                                                <AvatarImage :src="getPhotoUrl(topMilkProducers[0])"
                                                    :alt="topMilkProducers[0].name" />
                                                <AvatarFallback>{{ topMilkProducers[0].name.charAt(0).toUpperCase() }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <Badge class="!rounded-full">{{ topMilkProducers[0].aifarm_id }}</Badge>
                                            <div class="text-xs">{{ topMilkProducers[0].name }}</div>
                                            <div
                                                class="flex flex-col text-white text-shadow items-center rank-gold-bg bg-gradient-to-t rounded-t-xl w-16 h-36 justify-center pb-2">
                                                <div class="text-2xl font-semibold">1</div>
                                                <div class="text-xs">{{ topMilkProducers[0].average_litre_per_day }}
                                                    L/hari</div>
                                            </div>
                                        </div>

                                        <!-- 3rd Place -->
                                        <div v-if="topMilkProducers[2]"
                                            class="flex flex-col items-center gap-2 cursor-pointer hover:scale-105 transition-transform"
                                            @click="viewLivestock(topMilkProducers[2])">
                                            <Avatar class="border-2 border-primary" shape="circle">
                                                <AvatarImage :src="getPhotoUrl(topMilkProducers[2])"
                                                    :alt="topMilkProducers[2].name" />
                                                <AvatarFallback>{{ topMilkProducers[2].name.charAt(0).toUpperCase() }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <Badge class="!rounded-full">{{ topMilkProducers[2].aifarm_id }}</Badge>
                                            <div class="text-xs">{{ topMilkProducers[2].name }}</div>
                                            <div
                                                class="flex flex-col text-white text-shadow items-center rank-bronze-bg bg-gradient-to-t rounded-t-xl w-16 h-24 justify-center pb-2">
                                                <div class="text-2xl font-semibold">3</div>
                                                <div class="text-xs">{{ topMilkProducers[2].average_litre_per_day }}
                                                    L/hari</div>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-sm">Ternak dengan produksi susu terbaik ini memiliki tingkat
                                        produktivitas
                                        yang tinggi dari seluruh ternak, memberikan susu berkualitas tinggi untuk bisnis
                                        Anda.
                                    </p>

                                    <!-- View All Link - Hidden on milk page since we're already there -->
                                    <!-- This link would normally go to /productivity/milk but we're already on that page -->
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Info Card -->
                        <div class="mx-auto mt-4">
                            <div
                                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-cyan-50 to-blue-50 shadow-sm">
                                <div class="flex flex-col md:flex-row gap-6 md:gap-10 p-6 md:p-8">
                                    <!-- Left: Copy -->
                                    <div class="md:w-[60%]">
                                        <h3 class="text-slate-900 text-xl md:text-2xl font-semibold">
                                            Info penting untuk Anda!
                                        </h3>

                                        <p class="mt-3 text-slate-700 leading-relaxed">
                                            Perhatikan kambing-kambing ini untuk melihat apakah ada faktor yang
                                            mempengaruhi produktivitas mereka, seperti kesehatan, pakan,
                                            atau waktu pemerahan.
                                        </p>

                                        <p class="mt-3 text-slate-700 leading-relaxed">
                                            Gunakan informasi ini untuk membantu Anda mengoptimalkan produksi
                                            susu di peternakan Anda.
                                        </p>

                                        <p class="mt-4 text-sm text-slate-600 italic">
                                            Catatan: data ini hanya untuk hari ini.
                                        </p>
                                    </div>

                                    <!-- Right: Illustration -->
                                    <div class="md:w-[40%] relative flex items-center justify-center">
                                        <!-- soft blob glow -->
                                        <div class="absolute inset-0">
                                            <div
                                                class="absolute -top-6 -right-6 h-40 w-40 rounded-full bg-cyan-200/50 blur-2xl">
                                            </div>
                                            <div
                                                class="absolute bottom-0 left-2 h-28 w-28 rounded-full bg-sky-200/50 blur-2xl">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right side - Ranking List -->
                    <div class="lg:col-span-2">
                        <div class="space-y-4">
                            <div v-if="rankings.length === 0" class="text-center py-12">
                                <IconMilk class="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                                <h3 class="text-lg font-semibold mb-2">Belum ada data</h3>
                                <p class="text-muted-foreground">
                                    Belum ada data produksi susu yang tersedia.
                                </p>
                            </div>

                            <Card v-for="livestock in rankings" :key="livestock.id" class="overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-200" @click="viewMilkRanking(livestock)">
                                <CardContent class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="text-center">
                                                <div class="text-3xl font-bold" :class="getRankColor(livestock.rank)">
                                                    {{ livestock.rank }}
                                                </div>
                                            </div>

                                            <div class="relative">
                                                <Avatar class="h-16 w-16">
                                                    <AvatarImage v-if="livestock.avatar" :src="livestock.avatar"
                                                        :alt="livestock.name" />
                                                    <AvatarFallback class="text-lg font-semibold">
                                                        {{ getAvatarFallback(livestock.name) }}
                                                    </AvatarFallback>
                                                </Avatar>

                                                <div v-if="livestock.rank <= 3"
                                                    class="absolute -top-2 -right-2 p-1 rounded-full bg-background border">
                                                    <component :is="getRankIcon(livestock.rank)"
                                                        :class="['h-4 w-4', getRankColor(livestock.rank)]" />
                                                </div>
                                            </div>

                                            <div class="space-y-1">
                                                <div class="flex flex-col space-y-1">
                                                    <Badge variant="outline" class="w-fit">{{ livestock.tag_id }}
                                                    </Badge>
                                                    <h3 class="text-lg font-semibold">{{ livestock.name || 'Tanpa Nama'
                                                        }}</h3>
                                                </div>
                                            </div>
                                        </div>

                                        <Badge variant="secondary" class="flex items-center space-x-2 px-3 py-2">
                                            <IconMilk class="h-4 w-4 text-blue-500" />
                                            <span class="text-lg font-bold">{{
                                                parseFloat(livestock.daily_milk_production).toFixed(2) }}
                                                L</span>
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Livestock Detail Dialog -->
        <LivestockDetailDialog
            v-model:open="showDialog"
            :livestock="selectedLivestock"
        />
    </div>
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