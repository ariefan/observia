<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    herd: Object,
});

const form = useForm({
    name: props.herd?.name || '',
    description: props.herd?.description || '',
    status: props.herd?.status || '',
    type: props.herd?.type || '',
    capacity: props.herd?.capacity || 0,
    farm_id: props.herd?.farm_id || '', // You may want to set this dynamically
});

const isEdit = !!props.herd;

const submit = () => {
    if (isEdit) {
        form.put(route('herds.update', props.herd.id));
    } else {
        form.post(route('herds.store'));
    }
};
</script>

<template>

    <Head :title="isEdit ? 'Edit Kandang' : 'Tambah Kandang'" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ isEdit ? 'Edit Kandang' : 'Tambah Kandang' }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
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
                        <div class="mb-4">
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
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
