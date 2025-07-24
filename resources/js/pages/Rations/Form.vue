<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { ref, watch, computed } from 'vue';
import { X, Plus } from 'lucide-vue-next';

const props = defineProps({
    ration: Object, // optional for edit
});

const isEditMode = computed(() => !!props.ration);

const form = useForm({
    name: props.ration?.name || '',
    items: props.ration?.ration_items?.map(item => ({
        feed: item.feed,
        quantity: item.quantity,
        price: item.price,
    })) || [],
});

const addItem = () => {
    form.items.push({
        feed: '',
        quantity: 0,
        price: 0,
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    if (isEditMode.value) {
        form.put(route('rations.update', props.ration.id));
    } else {
        form.post(route('rations.store'));
    }
};
</script>

<template>

    <Head :title="isEditMode ? 'Edit Ransum' : 'Buat Ransum'" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ isEditMode ? 'Edit Catatan Ransum' : 'Buat Catatan Ransum' }}
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
                            <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="mt-4">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                Komposisi
                            </h3>

                            <div class="mt-4 p-4 border rounded-md" v-if="form.items.length > 0">
                                <div
                                    class="grid grid-cols-5 gap-4 font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    <div>Jenis Pakan</div>
                                    <div>Jumlah (Kg)</div>
                                    <div>Harga Total</div>
                                    <div class="text-right">Harga / kg</div>
                                    <div class="text-right">Aksi</div>
                                </div>

                                <div v-for="(item, index) in form.items" :key="index"
                                    class="grid grid-cols-5 gap-4 items-start">
                                    <div>
                                        <Input :id="'feed_' + index" v-model="item.feed" type="text" size="sm"
                                            class="mt-1 block w-full" required />
                                        <InputError class="mt-1" :message="form.errors[`items.${index}.feed`]" />
                                    </div>

                                    <div>
                                        <Input :id="'quantity_' + index" v-model="item.quantity" type="number" size="sm"
                                            class="mt-1 block w-full" required min="0" step="any" />
                                        <InputError class="mt-1" :message="form.errors[`items.${index}.quantity`]" />
                                    </div>

                                    <div>
                                        <Input :id="'price_' + index" v-model="item.price" type="number" size="sm"
                                            class="mt-1 block w-full" required min="0" step="any" />
                                        <InputError class="mt-1" :message="form.errors[`items.${index}.price`]" />
                                    </div>

                                    <div class="flex justify-end">
                                        <span class="block w-full pt-2.5 rounded text-right">
                                            {{
                                                item.quantity > 0
                                                    ? (item.price / item.quantity).toLocaleString('id-ID', {
                                                        style: 'currency',
                                                        currency: 'IDR',
                                                        maximumFractionDigits: 0
                                                    }) + ' / kg'
                                                    : '-'
                                            }}
                                        </span>
                                    </div>

                                    <div class="flex justify-end mt-2">
                                        <Button variant="ghost" size="icon" @click="removeItem(index)">
                                            <X class="size-4 font-bold" />
                                        </Button>
                                    </div>
                                </div>
                            </div>


                            <div class="mt-4 flex justify-center">
                                <Button variant="ghost" @click="addItem"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                    <Plus class="size-4 font-bold" /> Tambah Komposisi
                                </Button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <Link :href="route('rations.index')" class="mr-4">Batal</Link>
                            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ isEditMode ? 'Update' : 'Simpan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
