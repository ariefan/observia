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

import { ref, reactive } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const showDialog = ref(false);
const isEdit = ref(false);
const form = reactive({
    id: null,
    name: '',
    description: '',
    status: '',
    type: '',
    capacity: 0,
    farm_id: typeof window !== 'undefined' && window.currentFarmId ? window.currentFarmId : '',
    errors: {},
    processing: false,
});

function openCreateDialog() {
    isEdit.value = false;
    form.id = null;
    form.name = '';
    form.description = '';
    form.status = '';
    form.type = '';
    form.capacity = 0;
    form.errors = {};
    showDialog.value = true;
}

function openEditDialog(herd) {
    isEdit.value = true;
    form.id = herd.id;
    form.name = herd.name || '';
    form.description = herd.description || '';
    form.status = herd.status || '';
    form.type = herd.type || '';
    form.capacity = herd.capacity || 0;
    form.farm_id = herd.farm_id || (typeof window !== 'undefined' && window.currentFarmId ? window.currentFarmId : '');
    form.errors = {};
    showDialog.value = true;
}

function closeDialog() {
    showDialog.value = false;
}

function submitForm() {
    form.processing = true;
    const payload = {
        name: form.name,
        description: form.description,
        status: form.status,
        type: form.type,
        capacity: form.capacity,
        farm_id: form.farm_id,
    };
    if (isEdit.value && form.id) {
        router.put(route('herds.update', form.id), payload, {
            onError: (errors) => {
                form.errors = errors;
                form.processing = false;
            },
            onSuccess: () => {
                closeDialog();
                form.processing = false;
            },
        });
    } else {
        router.post(route('herds.store'), payload, {
            onError: (errors) => {
                form.errors = errors;
                form.processing = false;
            },
            onSuccess: () => {
                closeDialog();
                form.processing = false;
            },
        });
    }
}
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
                    Pakan
                    </Link>
                    <Link :href="route('herds.index')"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    Kandang
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
                                <!-- <p class="text-xs text-gray-500">Status: {{ herd.status || '-' }}</p> -->
                                <!-- <p class="text-xs text-gray-500">Tipe: {{ herd.type || '-' }}</p> -->
                                <!-- <p class="text-xs text-gray-500">Kapasitas: {{ herd.capacity }}</p> -->
                            </div>
                            <div class="flex gap-1 justify-end">
                                <Button size="icon" variant="ghost" @click="openEditDialog(herd)">
                                    <Edit class="size-4" /> <!-- Edit icon -->
                                </Button>
                                <Button size="icon" variant="ghost"
                                    @click="() => router.delete(route('herds.destroy', herd.id))">
                                    <Trash2 class="size-4" /> <!-- Delete icon -->
                                </Button>
                            </div>
                        </div>
                    </template>
                    <div @click="openCreateDialog"
                        class="cursor-pointer h-40 hover:bg-card border border-primary hover:border-primary/50 dark:hover:border-primary/50 rounded-lg shadow p-4 flex flex-col justify-center items-center gap-2"
                        style="width: 50%;">
                        <div class="text-center font-semibold text-primary text-sm">Tambah Kandang</div>
                        <PlusCircle class="size-10 text-primary" />
                    </div>
                </div>
                <!-- Dialog/Modal for Herd Form -->
                <div v-if="showDialog"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg relative">
                        <button @click="closeDialog"
                            class="absolute top-2 right-2 text-gray-500 hover:text-primary">&times;</button>
                        <h3 class="text-lg font-semibold mb-4">{{ isEdit ? 'Edit Kandang' : 'Tambah Kandang' }}</h3>
                        <form @submit.prevent="submitForm">
                            <input type="hidden" v-model="form.farm_id" />
                            <div class="mb-4">
                                <Label for="name">Nama Kandang</Label>
                                <Input id="name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="mb-4">
                                <Label for="description">Deskripsi</Label>
                                <Input id="description" v-model="form.description" />
                                <InputError :message="form.errors.description" />
                            </div>
                            <!-- <div class="mb-4">
                                <Label for="status">Status</Label>
                                <Input id="status" v-model="form.status" />
                                <InputError :message="form.errors.status" />
                            </div>
                            <div class="mb-4">
                                <Label for="type">Tipe</Label>
                                <Input id="type" v-model="form.type" />
                                <InputError :message="form.errors.type" />
                            </div>
                            <div class="mb-4">
                                <Label for="capacity">Kapasitas</Label>
                                <Input id="capacity" type="number" v-model="form.capacity" />
                                <InputError :message="form.errors.capacity" />
                            </div> -->
                            <div class="flex items-center justify-end mt-4">
                                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
