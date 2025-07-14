<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import { Building2, Users, Edit, Trash2, Wheat, PlusCircle } from 'lucide-vue-next';
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

        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
                <nav class="space-y-2">
                    <Link :href="route('rations.index')"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    <Building2 class="size-4" /> Pakan
                    </Link>
                    <Link :href="route('herds.index')"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    <Users class="size-4" /> Kandang
                    </Link>
                </nav>
            </aside>

            <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
                <div class="sm:rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-primary">Kandang</h3>
                            <p class="text-sm">Temukan dan kelola informasi lengkap menegnai semua kandang yang ada
                                dalam
                                peternakan Anda.</p>
                        </div>
                        <!-- <Link :href="route('herds.create')">
                        <Button type="button">Tambah Kandang</Button>
                        </Link> -->
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <template v-for="herd in herds" :key="herd.id">
                        <div
                            class="bg-card h-40 hover:border hover:border-primary/50 dark:hover:border-primary/50 rounded-lg shadow p-4 flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold">{{ herd.name }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ herd.description }}</p>
                                <p class="text-xs text-gray-500">Status: {{ herd.status || '-' }}</p>
                                <p class="text-xs text-gray-500">Tipe: {{ herd.type || '-' }}</p>
                                <!-- <p class="text-xs text-gray-500">Kapasitas: {{ herd.capacity }}</p> -->
                            </div>
                            <div class="flex gap-1 justify-end">
                                <Link :href="route('herds.edit', herd.id)">
                                <Button size="icon" variant="ghost">
                                    <Wheat class="size-4" /> <!-- Feed icon -->
                                </Button>
                                </Link>
                                <Link :href="route('herds.edit', herd.id)">
                                <Button size="icon" variant="ghost">
                                    <Edit class="size-4" /> <!-- Edit icon -->
                                </Button>
                                </Link>
                                <Button size="icon" variant="ghost"
                                    @click="() => router.delete(route('herds.destroy', herd.id))">
                                    <Trash2 class="size-4" /> <!-- Delete icon -->
                                </Button>
                            </div>
                        </div>
                    </template>
                    <Link :href="route('herds.create')">
                    <div class="h-40 hover:bg-card border border-primary hover:border-primary/50 dark:hover:border-primary/50 rounded-lg shadow p-4 flex flex-col justify-center items-center gap-2"
                        style="width: 50%;">
                        <div class="text-center font-semibold text-primary text-sm">Tambah Kandang</div>
                        <PlusCircle class="size-10 text-primary" />
                    </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
