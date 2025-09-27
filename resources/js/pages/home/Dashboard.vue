<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import { computed, ref } from 'vue';
import Rank from './Rank.vue';
import Tips from './Tips.vue';
import Guide from './Guide.vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';

import RealtimeClock from '@/components/ui/realtime-clock/RealtimeClock.vue';


const props = defineProps<{
    name?: string;
    totalLivestock?: number;
    todayMilkProduction?: number;
    totalMilkProduction?: number;
    averageWeight?: number;
    milkProductionTrend?: number | null;
    weightTrend?: number | null;
    todayFCR?: number;
    yesterdayFCR?: number;
    todayFeedAmount?: number;
    yesterdayFeedAmount?: number;
    yesterdayMilkProduction?: number;
    notification?: {
        emoji: string;
        message: string;
    };
    farmInvites?: Array<{
        id: number;
        farm_id: string;
        email: string;
        role: string;
        farm: {
            id: string;
            name: string;
        };
    }>;
}>();

const isMonthView = ref(false);

const acceptInvite = (inviteId: number) => {
    router.post(`/farm-invites/${inviteId}/accept`);
};

const rejectInvite = (inviteId: number) => {
    router.post(`/farm-invites/${inviteId}/reject`);
};

const page = usePage<SharedData>();
const auth = computed(() => page.props.auth);
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="w-full max-w-7xl mx-auto flex h-full flex-1 flex-col gap-4 p-2 md:p-4 overflow-x-hidden">
            <div class="min-h-screen pb-20 md:pb-0 w-full">
                <Card class="bg-primary text-white">
                    <CardHeader class="pt-3 pb-2 px-3 md:pt-4 md:px-6">
                        <CardTitle class="flex justify-between items-center">
                            <div class="text-base md:text-lg lg:text-xl">
                                Hi, {{ auth.user.name }}
                            </div>
                            <div class="flex items-center gap-3 md:gap-4">
                                <div class="text-xs md:text-sm lg:text-base xl:text-lg">
                                    <RealtimeClock class="text-white" />
                                </div>
                            </div>
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="px-3 md:px-6">
                        <!-- Notification Banner -->
                        <div
                            class="flex items-center space-x-2 md:space-x-4 bg-teal-800 rounded-xl p-2 md:p-3 mb-3 md:mb-4">
                            <div class="text-lg md:text-xl">{{ props.notification?.emoji || '😊' }}</div>
                            <p class="text-sm md:text-base">
                                {{ props.notification?.message || 'Selamat datang di dashboard peternakan Anda!' }}
                            </p>
                        </div>

                        <!-- Farm Invitation Alerts -->
                        <div v-if="props.farmInvites && props.farmInvites.length > 0" class="space-y-3 mb-4">
                            <Alert v-for="invite in props.farmInvites" :key="invite.id" variant="info"
                                class="bg-teal-50 dark:bg-teal-900/20">
                                <AlertTitle class="text-teal-800 dark:text-teal-200">
                                    Undangan Bergabung Peternakan
                                </AlertTitle>
                                <AlertDescription class="text-teal-700 dark:text-teal-300">
                                    <p class="mb-3">
                                        Anda diundang untuk bergabung dengan peternakan <strong>{{ invite.farm.name
                                        }}</strong>
                                        sebagai <strong>{{ invite.role }}</strong>.
                                    </p>
                                    <div class="flex gap-2">
                                        <Button @click="acceptInvite(invite.id)" size="sm">
                                            Terima
                                        </Button>
                                        <Button @click="rejectInvite(invite.id)" size="sm" variant="outline"
                                            class="text-red-600 border-red-300 hover:bg-red-50">
                                            Tolak
                                        </Button>
                                    </div>
                                </AlertDescription>
                            </Alert>
                        </div>

                        <!-- Goat and Milk Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 w-full">
                            <!-- Card 1: FCR -->
                            <Card class="rounded-xl md:rounded-2xl">
                                <CardHeader
                                    class="bg-orange-500 dark:bg-orange-200 text-white dark:text-black rounded-t-xl px-3 py-2 md:px-4">
                                    <CardTitle class="text-sm md:text-base font-medium">Feed Cost Ratio Seluruh Populasi
                                        hari ini:</CardTitle>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-orange-800 text-center py-4 md:py-6 rounded-b-xl">
                                    <div class="text-4xl md:text-6xl font-bold mb-2">
                                        {{ props.todayFCR || '-' }}
                                    </div>
                                    <div class="text-xs md:text-sm text-gray-600 dark:text-gray-300">
                                        FCR di Peternakanmu hari ini
                                        <span v-if="props.todayFCR && props.yesterdayFCR"
                                            :class="props.todayFCR < props.yesterdayFCR ? 'text-green-600' : 'text-red-600'">
                                            {{ props.todayFCR < props.yesterdayFCR ? 'turun' : 'naik' }} dari hari
                                                kemarin ({{ props.yesterdayFCR }}) </span>
                                                <span v-else-if="props.yesterdayFCR">
                                                    dari hari kemarin ({{ props.yesterdayFCR }})
                                                </span>
                                                <span v-else>
                                                    -
                                                </span>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Card 2: Milk Production -->
                            <Card class="rounded-xl md:rounded-2xl">
                                <CardHeader
                                    class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-xl px-3 py-2 md:px-4">
                                    <CardTitle class="text-sm md:text-base font-medium">Produksi Susu Seluruh Populasi
                                        hari ini:</CardTitle>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 md:py-6 rounded-b-xl">
                                    <div class="text-4xl md:text-6xl font-bold mb-2">
                                        {{ props.todayMilkProduction || '-' }} <span v-if="props.todayMilkProduction"
                                            class="text-lg font-normal">Liter</span>
                                    </div>
                                    <div class="text-xs md:text-sm text-gray-600 dark:text-gray-300">
                                        Produksi susu seluruh ternakmu hari ini:<br>
                                        <span v-if="props.todayMilkProduction && props.yesterdayMilkProduction"
                                            :class="props.todayMilkProduction > props.yesterdayMilkProduction ? 'text-green-600' : 'text-red-600'">
                                            {{ Math.round(((props.todayMilkProduction - props.yesterdayMilkProduction) /
                                            props.yesterdayMilkProduction) * 100) }}%
                                            {{ props.todayMilkProduction > props.yesterdayMilkProduction ? 'up' : 'down'
                                            }} dari hari kemarin
                                            (total : {{ props.yesterdayMilkProduction }} Liter)
                                        </span>
                                        <span v-else>
                                            -
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Card 3: Feed Amount -->
                            <Card class="rounded-xl md:rounded-2xl">
                                <CardHeader
                                    class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-xl px-3 py-2 md:px-4">
                                    <CardTitle class="text-sm md:text-base font-medium">Pemberian Pakan Seluruh Populasi
                                        hari ini:</CardTitle>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-4 md:py-6 rounded-b-xl">
                                    <div class="text-4xl md:text-6xl font-bold mb-2">
                                        {{ props.todayFeedAmount || '-' }} <span v-if="props.todayFeedAmount"
                                            class="text-lg font-normal">Kg</span>
                                    </div>
                                    <div class="text-xs md:text-sm text-gray-600 dark:text-gray-300">
                                        Pemberian Pakan seluruh Populasi hari ini:<br>
                                        <span v-if="props.todayFeedAmount && props.yesterdayFeedAmount"
                                            :class="props.todayFeedAmount > props.yesterdayFeedAmount ? 'text-green-600' : 'text-red-600'">
                                            {{ Math.round(((props.todayFeedAmount - props.yesterdayFeedAmount) /
                                            props.yesterdayFeedAmount) * 100) }}%
                                            {{ props.todayFeedAmount > props.yesterdayFeedAmount ? 'up' : 'down' }} dari
                                            hari kemarin
                                            (total : {{ props.yesterdayFeedAmount }} Kg)
                                        </span>
                                        <span v-else>
                                            -
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                    </CardContent>
                </Card>

                <div class="relative flex-1 mt-4 md:mt-6 w-full hidden">
                    <Guide />
                </div>

                <div class="relative flex-1 grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mt-4 md:mt-6 w-full">
                    <Rank />
                </div>

                <div class="relative flex-1 mt-4 md:mt-6 w-full">
                    <Tips />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
