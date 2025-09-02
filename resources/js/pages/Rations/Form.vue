<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { computed, watch } from 'vue';
import { X, Plus, Check, ChevronsUpDown } from 'lucide-vue-next';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';

const props = defineProps({
    ration: Object,
    restock: Boolean,
    feedItems: Array,
});

const isEditMode = computed(() => !!props.ration);

const form = useForm({
    name: props.ration?.name || '',
    items: props.ration?.ration_items?.map(item => ({
        inventory_item_id: null, // New field for inventory linking
        feed: item.feed,
        quantity: props.restock ? 0 : item.quantity,
        price: props.restock ? 0 : item.price,
        current_stock: 0,
        unit_cost: 0,
        unit: null,
    })) || [],
    restock: props.restock ? '1' : '0',
});

const addItem = () => {
    form.items.push({
        inventory_item_id: null,
        feed: '',
        quantity: 0,
        price: 0,
        current_stock: 0,
        unit_cost: 0,
        unit: null,
    });
};

const selectFeedItem = (index, item) => {
    if (form.items[index]) {
        form.items[index].inventory_item_id = item.id;
        form.items[index].feed = item.label;
        form.items[index].current_stock = item.current_stock;
        form.items[index].unit_cost = item.unit_cost || 0;
        form.items[index].unit = item.unit;
        // Auto-calculate price based on quantity and unit cost
        if (form.items[index].quantity > 0 && item.unit_cost) {
            form.items[index].price = form.items[index].quantity * item.unit_cost;
        }
    }
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

// Watch for quantity changes to auto-calculate price
watch(() => form.items, (newItems, oldItems) => {
    newItems.forEach((item, index) => {
        if (item.unit_cost > 0 && item.quantity > 0) {
            const newPrice = item.quantity * item.unit_cost;
            if (newPrice !== item.price) {
                item.price = newPrice;
            }
        }
    });
}, { deep: true });

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
        <div class="flex min-h-screen">
            <SecondSidebar current-route="rations.create" />
            <div class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="mb-6">
                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                            {{ isEditMode ? 'Edit Catatan Ransum' : 'Buat Catatan Ransum' }}
                        </h2>
                    </div>

                    <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-md font-semibold mb-2">{{ restock ? 'Restock' : 'Buat' }} Ransum</h3>
                    <form @submit.prevent="submit">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            Nama Ransum
                        </h3>
                        <div>
                            <Label for="name" value="Nama Ransum" />
                            <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                                :disabled="restock" />
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
                                    <div class="text-right" v-if="!restock">Aksi</div>
                                </div>

                                <div v-for="(item, index) in form.items" :key="index"
                                    class="grid grid-cols-5 gap-4 items-start">
                                    <div>
                                        <Combobox v-if="!restock">
                                            <ComboboxAnchor as="div" class="relative">
                                                <ComboboxInput 
                                                    :id="'feed_' + index"
                                                    v-model:search-term="item.feed"
                                                    :display-value="() => item.feed"
                                                    class="mt-1 block w-full h-8 pr-8"
                                                    placeholder="Pilih pakan dari inventory..."
                                                    required
                                                />
                                                <ComboboxTrigger class="absolute inset-y-0 right-0 flex items-center pr-2">
                                                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                                                </ComboboxTrigger>
                                            </ComboboxAnchor>
                                            <ComboboxList class="max-h-48 overflow-y-auto">
                                                <ComboboxEmpty class="py-2 px-3 text-sm text-gray-500">
                                                    Tidak ada pakan ditemukan
                                                </ComboboxEmpty>
                                                <ComboboxGroup>
                                                    <ComboboxItem 
                                                        v-for="feedOption in feedItems" 
                                                        :key="feedOption.id" 
                                                        :value="feedOption"
                                                        @click="selectFeedItem(index, feedOption)"
                                                        class="cursor-pointer"
                                                    >
                                                        <div class="flex items-center justify-between w-full">
                                                            <div>
                                                                <div class="font-medium text-sm">{{ feedOption.label }}</div>
                                                                <div class="text-xs text-gray-500">
                                                                    Stok: {{ feedOption.current_stock }} {{ feedOption.unit?.symbol || 'kg' }}
                                                                    <span v-if="feedOption.unit_cost" class="ml-2">
                                                                        | {{ feedOption.unit_cost.toLocaleString('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0}) }}/{{ feedOption.unit?.symbol || 'kg' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <ComboboxItemIndicator>
                                                                <Check class="h-4 w-4" />
                                                            </ComboboxItemIndicator>
                                                        </div>
                                                    </ComboboxItem>
                                                </ComboboxGroup>
                                            </ComboboxList>
                                        </Combobox>
                                        <Input v-else :id="'feed_' + index" v-model="item.feed" type="text"
                                            class="mt-1 block w-full h-8" required disabled />
                                        <InputError class="mt-1" :message="form.errors[`items.${index}.feed`]" />
                                        <div v-if="item.current_stock > 0" class="text-xs text-green-600 mt-1">
                                            Stok tersedia: {{ item.current_stock }} {{ item.unit?.symbol || 'kg' }}
                                        </div>
                                    </div>

                                    <div>
                                        <Input :id="'quantity_' + index" v-model="item.quantity" type="number"
                                            class="mt-1 block w-full h-8" required min="0" step="any" />
                                        <InputError class="mt-1" :message="form.errors[`items.${index}.quantity`]" />
                                    </div>

                                    <div>
                                        <Input :id="'price_' + index" v-model="item.price" type="number"
                                            class="mt-1 block w-full h-8" required min="0" step="any" />
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

                                    <div class="flex justify-end mt-2" v-if="!restock">
                                        <Button variant="ghost" size="icon" @click="removeItem(index)">
                                            <X class="size-4 font-bold" />
                                        </Button>
                                    </div>
                                </div>
                            </div>


                            <div class="mt-4 flex justify-center" v-if="!restock && !isEditMode">
                                <Button variant="ghost" @click="addItem"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                    <Plus class="size-4 font-bold" /> Tambah Komposisi
                                </Button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <Link :href="route('rations.index')" class="mr-4">Batal</Link>
                            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ isEditMode ? (restock ? 'Restock' : 'Update') : 'Simpan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
