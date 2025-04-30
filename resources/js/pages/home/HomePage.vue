<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Guide from './Guide.vue';
import Rank from './Rank.vue';
import Tips from './Tips.vue';
import Welcome1 from '@/assets/welcome-1.jpg';

defineProps<{
    name?: string;
}>();
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="max-w-7xl mx-auto flex flex-col gap-4 p-4">
            <!-- <div class="grid grid-cols-1 auto-rows-min gap-4 md:grid-cols-3"> -->
            <!-- FAT CHONKY BOI SPANNING 2 COLUMNS -->
            <!-- <div class="md:col-span-2">
            </div> -->
            <!-- LONELY THIRD COLUMN -->
            <!-- <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                </div>
            </div> -->
            <!-- MIDDLE BIG CHONK -->
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Card class="!bg-primary">
                    <template #header />

                    <template #title>
                        <div class="flex justify-between items-center text-white md:text-lg text-base lg:text-xl">
                            <span>Hi, {{ $page.props.auth.user.name }}</span>
                            <span>Dashboard Farm</span>
                        </div>
                    </template>

                    <template #footer>
                        <div class="relative w-full h-[150px] lg:h-[180px] rounded-xl overflow-hidden">
                            <!-- The sacred image, now behaving like a good zoomed boy -->
                            <img :src="Welcome1" alt="Welcome 1"
                                class="absolute inset-0 w-full h-full object-cover object-center z-0" />

                            <!-- Overlay time -->
                            <div class="absolute inset-0 flex flex-col justify-between text-white p-2 lg:p-4 z-10">
                                <div>
                                    <h2 class="lg:text-xl text-black font-bold leading-tight">
                                        Apakah Ternakmu Menguntungkan?
                                    </h2>
                                    <p class="text-sm text-black font-light mt-1">
                                        Daftarkan ternak mu sekarang untuk memantau produktifitasnya.
                                    </p>
                                </div>

                                <!-- Bottom Button of Glory -->
                                <Button size="small" rounded variant="outlined"
                                    class="!bg-white dark:!bg-black hover:!bg-zinc-200 dark:hover:!bg-zinc-800 font-bold w-full sm:w-64">
                                    Mulai kelola peternakan Anda
                                </Button>
                            </div>
                        </div>
                    </template>
                </Card>

            </div>
            <!-- BOTTOM BIG CHONK -->
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Card class="!bg-primary">
                    <template #title>
                        <div class="flex justify-between items-center text-white md:text-lg text-base lg:text-xl">
                            <span>Hi, {{ $page.props.auth.user.name }}</span>
                            <span>Dashboard Farm</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="flex space-x-4 bg-teal-800 rounded-xl p-3 mb-4">
                            <div>😊</div>
                            <p class="text-white">
                                Perkembangan populasi ternak dan susu seluruh peternakanmu hari ini
                                untung Rp300.000 dari pengelolaan pakan dan peningkatan jumlah susu sebanyak 10%.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-white font-sans">
                            <!-- GOAT SECTION -->
                            <div class="rounded-2xl flex flex-col">
                                <!-- Header -->
                                <div
                                    class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-xl px-4 py-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <span>Kambing</span>
                                </div>

                                <!-- Big fat number -->
                                <div
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-6 rounded-b-xl text-5xl font-semibold">
                                    00 <span class="text-sm font-normal">Ekor</span>
                                </div>
                            </div>

                            <!-- MILK SECTION -->
                            <div class="rounded-2xl flex flex-col">
                                <!-- Header -->
                                <div
                                    class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-xl px-4 py-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <span>Susu</span>
                                </div>

                                <!-- Big fat number -->
                                <div
                                    class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-6 rounded-b-xl text-5xl font-semibold">
                                    00 <span class="text-sm font-normal">Liter</span>
                                </div>
                            </div>
                        </div>


                        <!-- Footer -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-white font-sans">
                            <div class="text-sm mt-2 px-2 text-white grid grid-cols-3 items-center text-center">
                                <!-- Left: Description -->
                                <span class="text-left">
                                    Perkembangan bobot seluruh ternakmu hari ini:
                                </span>

                                <!-- Center: The sexy stats -->
                                <div class="flex justify-center gap-2 text-xl font-semibold">
                                    <span class="text-green-300 dark:text-green-700">↑ 0%</span>
                                    <span class="text-red-300 dark:text-red-700">↓ 0%</span>
                                </div>

                                <!-- Right: Total -->
                                <span class="text-right">
                                    Dari 10000 ternak perah
                                </span>
                            </div>


                            <div class="text-sm mt-2 px-2 text-white grid grid-cols-3 items-center text-center">
                                <!-- Left: Description -->
                                <span class="text-left">
                                    Produksi susu seluruh ternakmu hari ini:
                                </span>

                                <!-- Center: The sexy stats -->
                                <div class="flex justify-center gap-2 text-xl font-semibold">
                                    <span class="text-green-300 dark:text-green-700">↑ 0%</span>
                                    <span class="text-red-300 dark:text-red-700">↓ 0%</span>
                                </div>

                                <!-- Right: Total -->
                                <span class="text-right">
                                    Dari 10000 ternak perah
                                </span>
                            </div>

                        </div>

                    </template>
                </Card>
            </div>

            <div class="relative flex-1">
                <Guide />
            </div>

            <div class="relative flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <Rank />
            </div>

            <div class="relative flex-1">
                <Tips />
            </div>
        </div>
    </AppLayout>
</template>
