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

defineProps<{
    name?: string;
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
                            <div>😊</div>
                            <p>
                                Perkembangan populasi ternak dan susu seluruh peternakanmu hari ini
                                untung Rp300.000 dari pengelolaan pakan dan peningkatan jumlah susu sebanyak 10%.
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
                                    00 <span class="text-sm font-normal">Liter</span>
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
                                    00 <span class="text-sm font-normal">Ekor</span>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Footer Stats -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <!-- Milk Production -->
                            <div class="text-sm px-2 items-center text-left">
                                <span class="text-left">
                                    Produksi susu seluruh ternakmu hari ini: <br />
                                    <span class="text-red-300 dark:text-red-700 text-xl font-semibold me-2">↓ 69%</span>
                                    dari 10000 ekor
                                </span>
                                <!-- <div class="flex justify-center gap-2 text-xl font-semibold">
                                    <span class="text-green-300 dark:text-green-700">↑ 0%</span>
                                    <span class="text-red-300 dark:text-red-700">↓ 0%</span>
                                </div>
                                <span class="text-right">
                                    dari 10000 ekor
                                </span> -->
                            </div>

                            <!-- Weight Development -->
                            <div class="text-sm px-2 items-center text-left">
                                <span class="text-left">
                                    Perkembangan bobot seluruh ternakmu hari ini <br />
                                    <span class="text-green-300 dark:text-green-700 text-xl font-semibold me-2">↑
                                        69%</span>
                                    dari 10000 ekor
                                </span>
                                <!-- <div class="flex justify-center gap-2 text-xl font-semibold">
                                    <span class="text-green-300 dark:text-green-700">↑ 69%</span>
                                    <span class="text-red-300 dark:text-red-700">↓ 0%</span>
                                </div> -->
                                <!-- <span class="text-right">
                                    dari 10000 ekor
                                </span> -->
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
