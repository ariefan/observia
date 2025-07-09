<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

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
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        Nama Pakan
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="feed in feeds" :key="feed.id"
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ feed.name }}
                                    </th>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('feeds.destroy', feed.id)" method="delete" as="button"
                                            class="font-medium text-red-600 dark:text-red-500 hover:underline ml-4">
                                        Hapus</Link>
                                    </td>
                                </tr>
                                <tr v-if="feeds.length === 0">
                                    <td colspan="2" class="px-6 py-4 text-center">
                                        Belum ada data pakan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
