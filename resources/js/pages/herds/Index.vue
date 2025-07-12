<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
defineProps({
    herds: Array,
});
</script>

<template>

    <Head title="Kandang" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Kandang
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <Link :href="route('herds.create')">
                        <Button type="button">Tambah Kandang</Button>
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template v-for="herd in herds" :key="herd.id">
                            <div
                                class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow p-4 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-lg font-bold mb-2">{{ herd.name }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ herd.description }}</p>
                                    <p class="text-xs text-gray-500">Status: {{ herd.status || '-' }}</p>
                                    <p class="text-xs text-gray-500">Tipe: {{ herd.type || '-' }}</p>
                                    <p class="text-xs text-gray-500">Kapasitas: {{ herd.capacity }}</p>
                                </div>
                                <div class="flex gap-2 mt-4">
                                    <Link :href="route('herds.edit', herd.id)">
                                    <Button size="sm">Edit</Button>
                                    </Link>
                                    <Button size="sm" variant="destructive"
                                        @click="() => router.delete(route('herds.destroy', herd.id))">
                                        Hapus
                                    </Button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
