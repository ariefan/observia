<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ProductivitySidebar from '@/components/ProductivitySidebar.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { CalendarDays, Weight, TrendingUp, Trophy, Award, Medal, Droplets } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';

const page = usePage<SharedData>();

defineOptions({
    layout: AppLayout,
});

interface WeightRanking {
    id: string;
    name: string;
    tag_id: string;
    current_weight: number;
    avatar?: string;
    rank: number;
    herd_name?: string;
    weight_unit?: string;
}

interface Props {
    rankings: WeightRanking[];
    date: string;
}

const props = defineProps<Props>();


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
</script>

<template>
    <div class="flex">
        <ProductivitySidebar current-route="productivity.weight" />
        
        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Ranking Bobot Ternak</h1>
                        <p class="text-muted-foreground">
                            Ranking ternak berdasarkan bobot terberat
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                            <CalendarDays class="h-4 w-4" />
                            <span>Ranking berdasarkan tanggal terbaru & bobot tertinggi</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div v-if="rankings.length === 0" class="text-center py-12">
                        <Weight class="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                        <h3 class="text-lg font-semibold mb-2">Belum ada data</h3>
                        <p class="text-muted-foreground">
                            Belum ada data bobot ternak yang tersedia.
                        </p>
                    </div>

                    <Card v-for="livestock in rankings" :key="livestock.id" class="overflow-hidden">
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="relative">
                                        <Avatar class="h-16 w-16">
                                            <AvatarImage 
                                                v-if="livestock.avatar" 
                                                :src="livestock.avatar" 
                                                :alt="livestock.name" 
                                            />
                                            <AvatarFallback class="text-lg font-semibold">
                                                {{ getAvatarFallback(livestock.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        
                                        <div v-if="livestock.rank <= 3" 
                                             class="absolute -top-2 -right-2 p-1 rounded-full bg-background border">
                                            <component 
                                                :is="getRankIcon(livestock.rank)" 
                                                :class="['h-4 w-4', getRankColor(livestock.rank)]"
                                            />
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-lg font-semibold">{{ livestock.name || 'Tanpa Nama' }}</h3>
                                            <Badge variant="outline">{{ livestock.tag_id }}</Badge>
                                            <span v-if="livestock.record_date" class="text-xs text-muted-foreground">
                                                {{ new Date(livestock.record_date).toLocaleDateString('id-ID', { 
                                                    day: '2-digit', 
                                                    month: 'short', 
                                                    year: 'numeric'
                                                }) }}
                                            </span>
                                        </div>
                                        <p v-if="livestock.herd_name" class="text-sm text-muted-foreground">
                                            Kandang: {{ livestock.herd_name }}
                                        </p>
                                        <div class="flex items-center space-x-2">
                                            <Weight class="h-4 w-4 text-green-500" />
                                            <span class="text-lg font-bold">
                                                {{ parseFloat(livestock.current_weight).toFixed(2) }} {{ livestock.weight_unit || 'kg' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <div class="text-3xl font-bold" :class="getRankColor(livestock.rank)">
                                        #{{ livestock.rank }}
                                    </div>
                                    <p class="text-sm text-muted-foreground">Peringkat</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>