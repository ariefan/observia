<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';

defineProps({
    feeds: Object,
});
</script>

<template>

    <Head title="Pakan" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Pakan
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <Link :href="route('feeds.create')">
                        <Button type="button">Tambah Pakan</Button>
                        </Link>
                    </div>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Pakan</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-for="feed in feeds" :key="feed.id">
                                    <TableRow>
                                        <TableCell class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ feed.name }}
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Link :href="route('feeds.destroy', feed.id)" method="delete" as="button"
                                                class="font-medium text-red-600 dark:text-red-500 hover:underline ml-4">
                                            Hapus
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-if="feeds.length === 0">
                                    <TableCell colspan="2" class="px-6 py-4 text-center">
                                        Belum ada data pakan.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
