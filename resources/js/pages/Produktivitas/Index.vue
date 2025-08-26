<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { TrendingUp, Droplets, Weight } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import { IconMilk, IconScaleOutline } from '@tabler/icons-vue';

const page = usePage<SharedData>();

defineOptions({
    layout: AppLayout,
});

const sidebarItems = [
    {
        title: 'Susu',
        href: '/produktivitas/susu',
        icon: IconMilk,
        description: 'Ranking produksi susu harian',
    },
    {
        title: 'Bobot',
        href: '/produktivitas/bobot',
        icon: IconScaleOutline,
        description: 'Ranking bobot harian',
    },
];
</script>

<template>
    <div class="flex">
        <SecondSidebar title="Produktivitas" :icon="TrendingUp" :items="sidebarItems" />

        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center py-12">
                    <TrendingUp class="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                    <h2 class="text-2xl font-bold tracking-tight mb-2">Produktivitas</h2>
                    <p class="text-muted-foreground mb-8">
                        Pilih kategori produktivitas yang ingin Anda lihat dari sidebar
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                        <a href="/productivity/milk"
                            class="group relative rounded-lg border p-6 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                    <IconMilk class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="text-left">
                                    <h3 class="font-semibold">Produksi Susu</h3>
                                    <p class="text-sm text-muted-foreground">Ranking harian ternak berdasarkan produksi
                                        susu</p>
                                </div>
                            </div>
                        </a>

                        <a href="/productivity/weight"
                            class="group relative rounded-lg border p-6 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                                    <IconScaleOutline class="h-6 w-6 text-green-600 dark:text-green-400" />
                                </div>
                                <div class="text-left">
                                    <h3 class="font-semibold">Bobot Ternak</h3>
                                    <p class="text-sm text-muted-foreground">Ranking harian ternak berdasarkan bobot</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>