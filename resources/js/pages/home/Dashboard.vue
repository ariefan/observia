<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import { computed } from 'vue';
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

import RealtimeClock from '@/components/ui/realtime-clock/RealtimeClock.vue';


const props = defineProps<{
    name?: string;
    totalLivestock?: number;
    todayMilkProduction?: number;
    totalMilkProduction?: number;
    averageWeight?: number;
    milkProductionTrend?: number | null;
    weightTrend?: number | null;
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
                            <div class="text-xs md:text-sm lg:text-base xl:text-lg">
                                <RealtimeClock class="text-white" />
                            </div>
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="px-3 md:px-6">
                        <!-- Notification Banner -->
                        <div class="flex items-center space-x-2 md:space-x-4 bg-teal-800 rounded-xl p-2 md:p-3 mb-3 md:mb-4">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4 w-full">
                            <!-- Milk Section -->
                            <Card class="rounded-xl md:rounded-2xl">
                                <CardHeader
                                    class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-xl px-3 py-2 md:px-4 flex items-left gap-2">
                                    <span class="text-sm md:text-base">Susu</span>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-4 md:py-6 rounded-b-xl text-3xl md:text-5xl font-semibold">
                                    {{ props.todayMilkProduction || 0 }} <span class="text-xs md:text-sm font-normal">Liter</span>
                                </CardContent>
                            </Card>

                            <!-- Goat Section -->
                            <Card class="rounded-xl md:rounded-2xl">
                                <CardHeader
                                    class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-xl px-3 py-2 md:px-4 flex items-left gap-2">
                                    <span class="text-sm md:text-base">Kambing</span>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-4 md:py-6 rounded-b-xl text-3xl md:text-5xl font-semibold">
                                    {{ props.totalLivestock || 0 }} <span class="text-xs md:text-sm font-normal">Ekor</span>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Footer Stats -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4 mt-3 md:mt-4 w-full">
                            <!-- Milk Production -->
                            <div class="text-xs md:text-sm px-1 md:px-2 items-center text-left w-full min-w-0">
                                <span class="text-left break-words">
                                    Produksi susu seluruh ternakmu hari ini: <br />
                                    <span
                                        v-if="props.milkProductionTrend !== undefined && props.milkProductionTrend !== null && props.milkProductionTrend !== 0"
                                        :class="props.milkProductionTrend > 0 ? 'text-green-300 dark:text-green-700' : 'text-red-300 dark:text-red-700'"
                                        class="text-lg md:text-xl font-semibold me-1 md:me-2">
                                        {{ props.milkProductionTrend > 0 ? '↑' : '↓' }} {{
                                        Math.abs(props.milkProductionTrend) }}%
                                    </span>
                                    <span v-else-if="props.milkProductionTrend === 0"
                                        class="text-blue-300 dark:text-blue-500 text-lg md:text-xl font-semibold me-1 md:me-2">
                                        = 0%
                                    </span>
                                    <span v-else class="text-gray-300 dark:text-gray-500 text-lg md:text-xl font-semibold me-1 md:me-2">
                                        0%
                                    </span>
                                    dari {{ props.totalLivestock || 0 }} ekor
                                </span>
                            </div>

                            <!-- Weight Development -->
                            <div class="text-xs md:text-sm px-1 md:px-2 items-center text-left w-full min-w-0">
                                <span class="text-left break-words">
                                    Perkembangan bobot seluruh ternakmu minggu ini: <br />
                                    <span
                                        v-if="props.weightTrend !== undefined && props.weightTrend !== null && props.weightTrend !== 0"
                                        :class="props.weightTrend > 0 ? 'text-green-300 dark:text-green-700' : 'text-red-300 dark:text-red-700'"
                                        class="text-lg md:text-xl font-semibold me-1 md:me-2">
                                        {{ props.weightTrend > 0 ? '↑' : '↓' }} {{ Math.abs(props.weightTrend) }}%
                                    </span>
                                    <span v-else-if="props.weightTrend === 0"
                                        class="text-blue-300 dark:text-blue-500 text-lg md:text-xl font-semibold me-1 md:me-2">
                                        = 0%
                                    </span>
                                    <span v-else class="text-gray-300 dark:text-gray-500 text-lg md:text-xl font-semibold me-1 md:me-2">
                                        0%
                                    </span>
                                    rata-rata {{ props.averageWeight || 0 }}kg
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="relative flex-1 mt-4 md:mt-6 w-full">
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
