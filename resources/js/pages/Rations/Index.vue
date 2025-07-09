<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    rations: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const totalFeeds = (rationItems) => {
    return rationItems.length;
};

const totalCost = (rationItems) => {
    return rationItems.reduce((acc, item) => acc + (item.quantity * item.price), 0);
};

</script>

<template>

    <Head title="Ransum" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Ransum
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <Link :href="route('rations.create')"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Tambah Ransum
                        </Link>
                    </div>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        Nama Ransum
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Total Pakan
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Total Biaya
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ration in rations" :key="ration.id"
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ ration.name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ totalFeeds(ration.ration_items) }} Jenis Pakan
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ formatCurrency(totalCost(ration.ration_items)) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('rations.edit', ration.id)"
                                            class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit
                                        </Link>
                                        <Link :href="route('rations.destroy', ration.id)" method="delete" as="button"
                                            class="font-medium text-red-600 dark:text-red-500 hover:underline ml-4">
                                        Hapus</Link>
                                    </td>
                                </tr>
                                <tr v-if="rations.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center">
                                        Belum ada data ransum.
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
