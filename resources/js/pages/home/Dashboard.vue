<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Rank from './Rank.vue';
import Tips from './Tips.vue';
import Guide from './Guide.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import RealtimeClock from '@/components/ui/realtime-clock/RealtimeClock.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

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
}>();
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="max-w-7xl mx-auto flex h-full flex-1 flex-col gap-4 p-4">
            <div class="min-h-screen">
                <Card class="bg-primary text-white">
                    <CardHeader class="pt-4 pb-2">
                        <CardTitle class="flex justify-between items-center">
                            <div class="text-lg md:text-xl">
                                Hi, {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm md:text-base lg:text-lg">
                                <RealtimeClock class="text-white" />
                            </div>
                        </CardTitle>
                    </CardHeader>

                    <CardContent>
                        <!-- Notification Banner -->
                        <div class="flex items-center space-x-4 bg-teal-800 rounded-xl p-3 mb-4">
                            <div>{{ props.notification?.emoji || '😊' }}</div>
                            <p>
                                {{ props.notification?.message || 'Selamat datang di dashboard peternakan Anda!' }}
                            </p>
                        </div>

                        <!-- Goat and Milk Stats -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Milk Section -->
                            <Card class="rounded-2xl">
                                <CardHeader
                                    class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-xl px-4 py-2 flex items-left gap-2">
                                    <span>Susu</span>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-6 rounded-b-xl text-5xl font-semibold">
                                    {{ props.todayMilkProduction || 0 }} <span class="text-sm font-normal">Liter</span>
                                </CardContent>
                            </Card>

                            <!-- Goat Section -->
                            <Card class="rounded-2xl">
                                <CardHeader
                                    class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-xl px-4 py-2 flex items-left gap-2">
                                    <span>Kambing</span>
                                </CardHeader>
                                <CardContent
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-6 rounded-b-xl text-5xl font-semibold">
                                    {{ props.totalLivestock || 0 }} <span class="text-sm font-normal">Ekor</span>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Footer Stats -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <!-- Milk Production -->
                            <div class="text-sm px-2 items-center text-left">
                                <span class="text-left">
                                    Produksi susu seluruh ternakmu hari ini: <br />
                                    <span v-if="props.milkProductionTrend !== undefined && props.milkProductionTrend !== null && props.milkProductionTrend !== 0"
                                          :class="props.milkProductionTrend > 0 ? 'text-green-300 dark:text-green-700' : 'text-red-300 dark:text-red-700'"
                                          class="text-xl font-semibold me-2">
                                        {{ props.milkProductionTrend > 0 ? '↑' : '↓' }} {{ Math.abs(props.milkProductionTrend) }}%
                                    </span>
                                    <span v-else-if="props.milkProductionTrend === 0" class="text-blue-300 dark:text-blue-500 text-xl font-semibold me-2">
                                        = 0%
                                    </span>
                                    <span v-else class="text-gray-300 dark:text-gray-500 text-xl font-semibold me-2">
                                        0%
                                    </span>
                                    dari {{ props.totalLivestock || 0 }} ekor
                                </span>
                            </div>

                            <!-- Weight Development -->
                            <div class="text-sm px-2 items-center text-left">
                                <span class="text-left">
                                    Perkembangan bobot seluruh ternakmu minggu ini: <br />
                                    <span v-if="props.weightTrend !== undefined && props.weightTrend !== null && props.weightTrend !== 0"
                                          :class="props.weightTrend > 0 ? 'text-green-300 dark:text-green-700' : 'text-red-300 dark:text-red-700'"
                                          class="text-xl font-semibold me-2">
                                        {{ props.weightTrend > 0 ? '↑' : '↓' }} {{ Math.abs(props.weightTrend) }}%
                                    </span>
                                    <span v-else-if="props.weightTrend === 0" class="text-blue-300 dark:text-blue-500 text-xl font-semibold me-2">
                                        = 0%
                                    </span>
                                    <span v-else class="text-gray-300 dark:text-gray-500 text-xl font-semibold me-2">
                                        0%
                                    </span>
                                    rata-rata {{ props.averageWeight || 0 }}kg
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="relative flex-1 mt-6">
                    <Guide />
                </div>

                <div class="relative flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <Rank />
                </div>

                <div class="relative flex-1 mt-6">
                    <Tips />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
