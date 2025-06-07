<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
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
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                v-if="!($page.props.auth.user.farms && $page.props.auth.user.farms.length > 0)">
                <Card class="!bg-primary">
                    <template #header />

                    <template #title>
                        <div class="flex justify-between items-center text-white md:text-lg text-base lg:text-xl">
                            <span>Hi, {{ $page.props.auth.user.name }}</span>
                            <span>Selamat Datang</span>
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
                                <Link :href="route('farms.create')">
                                <Button size="small" rounded variant="outlined"
                                    class="!bg-white dark:!bg-black hover:!bg-zinc-200 dark:hover:!bg-zinc-800 font-bold w-full sm:w-64">
                                    Mulai kelola peternakan Anda
                                </Button>
                                </Link>
                            </div>
                        </div>
                    </template>
                </Card>
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
