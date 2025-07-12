<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    feeds: Object,
});

const form = useForm({
    name: '',
    items: [],
});

const addItem = () => {
    form.items.push({
        feed_id: '',
        quantity: 0,
        price: 0,
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.post(route('rations.store'));
};

</script>

<template>

    <Head title="Buat Ransum" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Buat Catatan Ransum
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Nama Ransum
                        </h3>
                        <div>
                            <Label for="name" value="Nama Ransum" />
                            <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                                autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="mt-4">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                Komposisi
                            </h3>

                            <div v-for="(item, index) in form.items" :key="index" class="mt-4 p-4 border rounded-md">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <Label :for="'feed_id_' + index">Jenis Pakan</Label>
                                        <Input :id="'feed_id_' + index" v-model="item.feed_id_" type="number"
                                            class="mt-1 block w-full" required />
                                    </div>
                                    <div>
                                        <Label :for="'quantity_' + index">Jumlah (Kg)</Label>
                                        <Input :id="'quantity_' + index" v-model="item.quantity" type="number"
                                            class="mt-1 block w-full" required />
                                    </div>
                                    <div>
                                        <Label :for="'price_' + index">Harga</Label>
                                        <Input :id="'price_' + index" v-model="item.price" type="number"
                                            class="mt-1 block w-full" required />
                                    </div>
                                </div>
                                <div class="flex justify-end mt-4">
                                    <button type="button" @click="removeItem(index)"
                                        class="text-red-600 dark:text-red-500 hover:underline">Hapus</button>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" @click="addItem"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                    + Tambah Komposisi
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <Link :href="route('rations.index')" class="mr-4">Batal</Link>
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
