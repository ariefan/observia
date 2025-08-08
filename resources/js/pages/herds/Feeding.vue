<script setup lang="ts">
// Core Vue imports
import { ref, watch, onMounted, nextTick, computed } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import axios from "axios";

// Layout and Components
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/InputError.vue";
import { Card, CardContent } from "@/components/ui/card";
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

// Icons
import { ArrowLeft, Check, ChevronsUpDown, Milk } from "lucide-vue-next";

// Props
const props = defineProps<{
    herd: any;
    ration: any;
}>();

// Form state and handlers
const getDisplayValue = (herd: any) => {
    if (!herd) return '';
    return `${herd.name}`;
};

const getRationDisplayValue = (ration: any) => {
    if (!ration) return '';
    const quantityText = ration.total_quantity ? ` (${ration.total_quantity} kg tersedia)` : '';
    return `${ration.name}${quantityText}`;
};

// Get current date and time
const now = new Date();
const currentHour = now.getHours();
const currentSession = (() => {
    if (currentHour >= 5 && currentHour < 12) return 'morning';
    if (currentHour >= 12 && currentHour < 15) return 'afternoon';
    if (currentHour >= 15 && currentHour < 19) return 'evening';
    return 'night';
})();

const form = useForm({
    herd_id: props.herd.id,
    ration_id: null,
    quantity: 0,
    date: now.toISOString().slice(0, 10),
    time: now.toTimeString().slice(0, 5),
    session: currentSession,
    notes: '',
});

const selectedHerd = ref<any>(props.herd.id ? props.herd : null);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isUpdatingAutomatically = ref(false);

// Rations combobox state
const selectedRation = ref<any>(null);
const rationQuery = ref('');
const rationResults = ref<any[]>([]);

// Load initial herd data
const loadHerd = async (query: string = '') => {
    try {
        const response = await axios.get(route('herds.search', { q: query, sex: 'F' }));
        searchResults.value = response.data;
        console.log(response.data)
    } catch (error) {
        console.error(error);
        searchResults.value = [];
    }
};

// Load rations data
const loadRations = async (query: string = '') => {
    try {
        const response = await axios.get(route('rations.search', { q: query }));
        rationResults.value = response.data;
        console.log(response.data)
    } catch (error) {
        console.error(error);
        rationResults.value = [];
    }
};

// Watch for search query changes
watch(searchQuery, async (newQuery) => {
    if (!newQuery || newQuery.length === 0) {
        // Load all herd when search is empty
        await loadHerd();
        return;
    }

    if (newQuery.length < 2) {
        return; // Don't search for very short queries
    }

    await loadHerd(newQuery);
});

// Load initial data on component mount
onMounted(() => {
    loadHerd();
    loadRations();
});
// Watch for ration combobox search
watch(rationQuery, async (newQuery) => {
    if (!newQuery || newQuery.length === 0) {
        await loadRations();
        return;
    }
    if (newQuery.length < 2) {
        return;
    }
    await loadRations(newQuery);
});

// Watch for ration selection
watch(selectedRation, (newValue) => {
    if (newValue) {
        form.ration_id = newValue.id;
    }
});

// Computed property for available quantity
const availableQuantity = computed(() => {
    return selectedRation.value?.total_quantity || 0;
});

// Computed property for quantity validation
const quantityExceedsAvailable = computed(() => {
    return form.quantity > availableQuantity.value;
});

watch(selectedHerd, (newValue) => {
    if (newValue) {
        form.herd_id = newValue.id;
    }
});

// Watch for session changes and update time accordingly
watch(() => form.session, async (newSession) => {
    if (isUpdatingAutomatically.value) return;

    isUpdatingAutomatically.value = true;
    if (newSession === 'morning') {
        form.time = '08:00';
    } else if (newSession === 'afternoon') {
        form.time = '13:00';
    } else if (newSession === 'evening') {
        form.time = '16:00';
    } else if (newSession === 'night') {
        form.time = '19:00';
    }
    await nextTick();
    isUpdatingAutomatically.value = false;
});

// Watch for time changes and update session accordingly
watch(() => form.time, async (newTime) => {
    if (isUpdatingAutomatically.value) return;

    if (newTime) {
        isUpdatingAutomatically.value = true;
        const [hours] = newTime.split(':').map(Number);
        if (hours >= 5 && hours < 12) {
            form.session = 'morning';
        } else if (hours >= 12 && hours < 15) {
            form.session = 'afternoon';
        } else if (hours >= 15 && hours < 19) {
            form.session = 'evening';
        } else {
            form.session = 'night';
        }
        await nextTick();
        isUpdatingAutomatically.value = false;
    }
});

const submit = () => {
    if (quantityExceedsAvailable.value) {
        alert(`Tidak dapat menyimpan: Jumlah pakan (${form.quantity} kg) melebihi stok tersedia (${availableQuantity.value} kg).`);
        return;
    }
    form.post(route('herds.feeding.store'));
};

// Navigation functions
const back = () => window.history.back();
</script>

<template>

    <Head title="Tambah Data Pakan" />

    <AppLayout>
        <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex items-center space-x-4">
                <Button @click="back" variant="ghost" size="icon" class="h-10 w-10 shrink-0">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div class="space-y-1">
                    <h1 class="text-xl font-bold tracking-tight">Tambah Data Pakan</h1>
                    <p class="text-muted-foreground text-sm">
                        Catat hasil perahan pakan harian untuk memantau produktivitas ternak Anda dengan akurat.
                    </p>
                </div>
            </div>

            <Card class="bg-teal-500/10 border-primary-200">
                <CardContent class="p-6 flex items-center justify-between">
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-primary">Monitoring Produktivitas Pakan</h2>
                        <p class="text-primary text-sm">Pantau dan analisis produksi pakan untuk mengoptimalkan
                            manajemen peternakan dan kesehatan ternak Anda.</p>
                    </div>
                    <!-- <Button variant="outline" class="border-primary-600 text-primary-600">
                        <Milk class="h-4 w-4 mr-2" />
                        Gunakan IOT
                    </Button> -->
                </CardContent>
            </Card>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <Label for="ration_id">Ransum</Label>
                        <Combobox v-model="selectedRation" v-model:search-term="rationQuery"
                            :display-value="getRationDisplayValue">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedRation ? getRationDisplayValue(selectedRation) : 'Pilih Ransum' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>
                            <ComboboxList class="w-full">
                                <ComboboxInput v-model="rationQuery" placeholder="Cari ransum..." />
                                <ComboboxEmpty>Ransum tidak ditemukan.</ComboboxEmpty>
                                <ComboboxGroup>
                                    <ComboboxItem v-for="ration in rationResults" :key="ration.id" :value="ration">
                                        <div class="flex items-center">
                                            <span>{{ ration.name }}</span>
                                            <ComboboxItemIndicator>
                                                <Check class="ml-auto h-4 w-4" />
                                            </ComboboxItemIndicator>
                                        </div>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.ration_id" />
                    </div>
                    <div>
                        <Label for="herd_id">Kandang</Label>
                        <Combobox v-model="selectedHerd" v-model:search-term="searchQuery"
                            :display-value="getDisplayValue">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedHerd ? getDisplayValue(selectedHerd) : 'Pilih Ternak' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="w-full">
                                <ComboboxInput v-model="searchQuery" placeholder="Cari kandang..." />
                                <ComboboxEmpty>Kandang tidak ditemukan.</ComboboxEmpty>
                                <ComboboxGroup>
                                    <ComboboxItem v-for="result in searchResults" :key="result.id" :value="result">
                                        {{ result.name }}
                                        <ComboboxItemIndicator>
                                            <Check class="ml-auto h-4 w-4" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.herd_id" />
                    </div>

                    <div>
                        <Label for="quantity">Volume Pakan</Label>
                        <div class="relative">
                            <Input id="quantity" type="number" step="0.1" v-model="form.quantity" :class="[
                                'pr-16',
                                quantityExceedsAvailable ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
                            ]" />
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-muted-foreground">
                                kg
                            </div>
                        </div>
                        <div v-if="selectedRation && availableQuantity > 0" class="text-xs text-muted-foreground mt-1">
                            Stok tersedia: {{ availableQuantity }} kg
                        </div>
                        <div v-if="quantityExceedsAvailable" class="text-xs text-red-500 mt-1">
                            Jumlah melebihi stok tersedia ({{ availableQuantity }} kg)
                        </div>
                        <InputError :message="form.errors.quantity" />
                    </div>

                    <div>
                        <Label for="session">Sesi Pakan</Label>
                        <Select v-model="form.session">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih sesi pakan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="morning">Pagi</SelectItem>
                                <SelectItem value="afternoon">Siang</SelectItem>
                                <SelectItem value="evening">Sore</SelectItem>
                                <SelectItem value="night">Malam</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.session" />
                    </div>

                    <div>
                        <Label for="date">Tanggal Pakan</Label>
                        <Input type="date" v-model="form.date" />
                        <InputError :message="form.errors.date" />
                    </div>

                    <div class="hidden">
                        <Label for="time">Waktu Pakan</Label>
                        <Input type="time" v-model="form.time" />
                        <InputError :message="form.errors.time" />
                    </div>

                    <div class="md:col-span-2 hidden">
                        <Label for="notes">Catatan (Opsional)</Label>
                        <Input id="notes" v-model="form.notes"
                            placeholder="Tambahkan catatan tentang kondisi perah..." />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <Button type="submit" :disabled="form.processing || quantityExceedsAvailable"
                        :class="quantityExceedsAvailable ? 'opacity-50 cursor-not-allowed' : ''">
                        <Milk class="h-4 w-4 mr-2" />
                        Simpan Data Pakan
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
