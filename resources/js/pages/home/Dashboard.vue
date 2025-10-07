<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import { computed, ref, watch } from 'vue';
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
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

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

const isMonthView = ref(true);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedMonth = ref(new Date().toISOString().slice(0, 7));
const viewType = ref<'date' | 'month'>('month');

const fetchStatsForDate = () => {
    router.get('/dashboard', {
        date: selectedDate.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const fetchStatsForMonth = () => {
    router.get('/dashboard', {
        month: selectedMonth.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

// Watch for viewType changes and fetch data accordingly
watch(viewType, (newType) => {
    if (newType === 'date') {
        fetchStatsForDate();
    } else if (newType === 'month') {
        fetchStatsForMonth();
    }
});

const acceptInvite = (inviteId: number) => {
    router.post(`/farm-invites/${inviteId}/accept`);
};

const rejectInvite = (inviteId: number) => {
    router.post(`/farm-invites/${inviteId}/reject`);
};

const page = usePage<SharedData>();
const auth = computed(() => page.props.auth);

// Compute whether the selected date is today
const isToday = computed(() => {
    if (viewType.value === 'month') {
        const today = new Date();
        const currentMonth = today.toISOString().slice(0, 7);
        return selectedMonth.value === currentMonth;
    } else {
        const today = new Date().toISOString().split('T')[0];
        return selectedDate.value === today;
    }
});

// Compute the display text based on view type and selection
const dateDisplayText = computed(() => {
    if (isToday.value) {
        return 'hari ini';
    }

    if (viewType.value === 'month') {
        const [year, month] = selectedMonth.value.split('-');
        const date = new Date(parseInt(year), parseInt(month) - 1);
        return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    } else {
        const date = new Date(selectedDate.value + 'T00:00:00');
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <main
            class="mx-auto flex min-h-full w-full max-w-7xl flex-1 flex-col gap-4 overflow-x-hidden p-4 pb-24 sm:pb-12 lg:pb-8">
                <Card class="bg-primary text-white shadow-lg">
                    <CardHeader class="space-y-3 px-3 pt-4 pb-2 sm:px-6 sm:pb-3">
                        <CardTitle class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-base font-semibold md:text-lg lg:text-xl">
                                Hi, {{ auth.user.name }}
                            </div>
                            <div
                                class="flex w-full items-center justify-between gap-3 md:gap-4 sm:w-auto sm:justify-end">
                                <div class="text-xs md:text-sm lg:text-base xl:text-lg">
                                    <RealtimeClock class="text-white" />
                                </div>
                            </div>
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="space-y-4 px-3 pb-4 sm:px-6">
                        <!-- Notification Banner -->
                        <div
                            class="flex flex-col gap-2 rounded-xl bg-teal-800 p-3 text-left sm:flex-row sm:items-center sm:gap-4 md:p-4">
                            <div class="text-xl md:text-2xl">{{ props.notification?.emoji || '😊' }}</div>
                            <p class="text-sm leading-relaxed md:text-base">
                                {{ props.notification?.message || 'Selamat datang di dashboard peternakan Anda!' }}
                            </p>
                        </div>

                        <!-- Farm Invitation Alerts -->
                        <div v-if="props.farmInvites && props.farmInvites.length > 0" class="space-y-3">
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
                                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                                        <Button @click="acceptInvite(invite.id)" size="sm" class="w-full sm:w-auto">
                                            Terima
                                        </Button>
                                        <Button @click="rejectInvite(invite.id)" size="sm" variant="outline"
                                            class="w-full sm:w-auto border-red-300 text-red-600 hover:bg-red-50">
                                            Tolak
                                        </Button>
                                    </div>
                                </AlertDescription>
                            </Alert>
                        </div>

                        <!-- Date/Month Selection -->
                        <div class="flex flex-col items-start gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            <div class="flex w-full items-center gap-2 sm:w-auto">
                                <Select v-model="viewType">
                                    <SelectTrigger
                                        class="w-full border-white/30 bg-white/90 text-gray-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:w-[160px]">
                                        <SelectValue placeholder="Select view" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="date">Per Tanggal</SelectItem>
                                        <SelectItem value="month">Per Bulan</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div v-if="viewType === 'date'" class="flex w-full items-center gap-2 sm:w-auto">
                                <Input
                                    type="date"
                                    v-model="selectedDate"
                                    @change="fetchStatsForDate"
                                    class="w-full border-white/30 bg-white/90 text-gray-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                />
                            </div>

                            <div v-if="viewType === 'month'" class="flex w-full items-center gap-2 sm:w-auto">
                                <Input
                                    type="month"
                                    v-model="selectedMonth"
                                    @change="fetchStatsForMonth"
                                    class="w-full border-white/30 bg-white/90 text-gray-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Goat and Milk Stats -->
                        <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 md:gap-4">
                            <!-- Card 1: FCR -->
                            <Card class="rounded-xl md:rounded-2xl">
                                <CardHeader
                                    class="rounded-t-xl bg-orange-500 px-3 py-2 text-white dark:bg-orange-200 dark:text-black md:px-4">
                                    <CardTitle class="text-sm font-medium md:text-base">
                                        Feed Cost Ratio Seluruh Populasi {{ dateDisplayText }}:
                                    </CardTitle>
                                </CardHeader>
                                <CardContent
                                    class="rounded-b-xl bg-white py-4 text-center text-orange-800 dark:bg-zinc-800 dark:text-white md:py-6">
                                    <div class="mb-2 text-3xl font-bold md:text-5xl">
                                        {{ props.todayFCR || '-' }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 md:text-sm">
                                        FCR di Peternakanmu {{ dateDisplayText }}
                                        <span v-if="props.todayFCR && props.yesterdayFCR"
                                            :class="props.todayFCR < props.yesterdayFCR ? 'text-green-600' : 'text-red-600'">
                                            {{ props.todayFCR < props.yesterdayFCR ? 'turun' : 'naik' }} dari hari
                                            kemarin
                                            ({{ props.yesterdayFCR }})
                                        </span>
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
                                    class="rounded-t-xl bg-cyan-500 px-3 py-2 text-white dark:bg-cyan-200 dark:text-black md:px-4">
                                    <CardTitle class="text-sm font-medium md:text-base">
                                        Produksi Susu Seluruh Populasi {{ dateDisplayText }}:
                                    </CardTitle>
                                </CardHeader>
                                <CardContent
                                    class="rounded-b-xl bg-white py-4 text-center text-cyan-800 dark:bg-zinc-800 dark:text-white md:py-6">
                                    <div class="mb-2 text-3xl font-bold md:text-5xl">
                                        {{ props.todayMilkProduction || '-' }}
                                        <span v-if="props.todayMilkProduction" class="text-base font-normal md:text-lg">
                                            Liter
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 md:text-sm">
                                        Produksi susu seluruh ternakmu {{ dateDisplayText }}:<br>
                                        <span v-if="props.todayMilkProduction && props.yesterdayMilkProduction"
                                            :class="props.todayMilkProduction > props.yesterdayMilkProduction ? 'text-green-600' : 'text-red-600'">
                                            {{ Math.round(((props.todayMilkProduction - props.yesterdayMilkProduction) /
                                                props.yesterdayMilkProduction) * 100) }}%
                                            {{ props.todayMilkProduction > props.yesterdayMilkProduction ? '↑' : '↓'
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
                                    class="rounded-t-xl bg-teal-500 px-3 py-2 text-white dark:bg-teal-200 dark:text-black md:px-4">
                                    <CardTitle class="text-sm font-medium md:text-base">
                                        Pemberian Pakan Seluruh Populasi {{ dateDisplayText }}:
                                    </CardTitle>
                                </CardHeader>
                                <CardContent
                                    class="rounded-b-xl bg-white py-4 text-center text-teal-800 dark:bg-zinc-800 dark:text-white md:py-6">
                                    <div class="mb-2 text-3xl font-bold md:text-5xl">
                                        {{ props.todayFeedAmount || '-' }}
                                        <span v-if="props.todayFeedAmount" class="text-base font-normal md:text-lg">
                                            Kg
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 md:text-sm">
                                        Pemberian Pakan seluruh Populasi {{ dateDisplayText }}:<br>
                                        <span v-if="props.todayFeedAmount && props.yesterdayFeedAmount"
                                            :class="props.todayFeedAmount > props.yesterdayFeedAmount ? 'text-green-600' : 'text-red-600'">
                                            {{ Math.round(((props.todayFeedAmount - props.yesterdayFeedAmount) /
                                                props.yesterdayFeedAmount) * 100) }}%
                                            {{ props.todayFeedAmount > props.yesterdayFeedAmount ? '↑' : '↓' }} dari
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

                <section class="relative hidden w-full flex-1 sm:block">
                    <Guide />
                </section>

                <section class="relative grid w-full flex-1 grid-cols-1 gap-4 md:grid-cols-2">
                    <Rank />
                </section>

                <section class="relative w-full flex-1">
                    <Tips />
                </section>
        </main>
    </AppLayout>
</template>
