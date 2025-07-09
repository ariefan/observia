<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('feeds.store'));
};
</script>

<template>

    <Head title="Tambah Pakan" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tambah Pakan
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div>
                            <Label for="name">Nama Pakan</Label>
                            <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                                autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
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
