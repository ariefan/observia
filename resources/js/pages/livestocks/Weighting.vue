<script setup lang="ts">
// Core Vue imports
import { ref, watch, onMounted } from "vue";
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


// Icons
import { ArrowLeft, Check, ChevronsUpDown } from "lucide-vue-next";

// Props
const props = defineProps<{
    livestock: any;
}>();

// Form state and handlers
const getDisplayValue = (livestock: any) => {
    if (!livestock) return '';
    return `${livestock.aifarm_id} - ${livestock.name}`;
};

const form = useForm({
    livestock_id: props.livestock.id,
    weight: 0,
    date: new Date().toISOString().slice(0, 10),
});

const selectedLivestock = ref<any>(props.livestock.id ? props.livestock : null);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);

// Load initial livestock data
const loadLivestock = async (query: string = '') => {
    try {
        const response = await axios.get(route('livestocks.search', { q: query }));
        searchResults.value = response.data;
    } catch (error) {
        console.error(error);
        searchResults.value = [];
    }
};

// Watch for search query changes
watch(searchQuery, async (newQuery) => {
    if (!newQuery || newQuery.length === 0) {
        // Load all livestock when search is empty
        await loadLivestock();
        return;
    }

    if (newQuery.length < 2) {
        return; // Don't search for very short queries
    }

    await loadLivestock(newQuery);
});

// Load initial data on component mount
onMounted(() => {
    loadLivestock();
});

watch(selectedLivestock, (newValue) => {
    if (newValue) {
        form.livestock_id = newValue.id;
    }
});

const submit = () => {
    form.post(route('livestocks.weight.store'));
};

// Navigation functions
const back = () => window.history.back();
</script>

<template>

    <Head title="Tambah Bobot Ternak" />

    <AppLayout>
        <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex items-center space-x-4">
                <Button @click="back" variant="ghost" size="icon" class="h-10 w-10 shrink-0">
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight">Tambah Bobot Ternak</h1>
                    <p class="text-muted-foreground">
                        Tambahkan informasi mengenai ternak Anda dengan lengkap, bantu kami untuk lebih mudah dalam
                        mengelola data ternak anda.
                    </p>
                </div>
            </div>

            <Card class="bg-cyan-50 border-cyan-200">
                <CardContent class="p-6 flex items-center justify-between">
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-cyan-800">Input bobot ternak lebih cepat</h2>
                        <p class="text-cyan-700 text-sm">Otomatiskan tugas-tugas manual seperti pengumpulan data dan
                            analisis, menghemat waktu dan tenaga kerja Anda.</p>
                    </div>
                    <Button variant="outline" class="border-cyan-600 text-cyan-600">Gunakan IOT</Button>
                </CardContent>
            </Card>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <Label for="livestock_id">ID Ternak</Label>
                        <Combobox v-model="selectedLivestock" v-model:search-term="searchQuery"
                            :display-value="getDisplayValue" class="w-full">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button variant="outline" class="justify-between w-full">
                                        {{ selectedLivestock ? getDisplayValue(selectedLivestock) : 'Pilih Ternak' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList>
                                <ComboboxInput v-model="searchQuery" placeholder="Cari ternak..." />
                                <ComboboxEmpty>Ternak tidak ditemukan.</ComboboxEmpty>
                                <ComboboxGroup>
                                    <ComboboxItem v-for="result in searchResults" :key="result.id" :value="result">
                                        {{ result.tag_id }} - {{ result.name }}
                                        <ComboboxItemIndicator>
                                            <Check class="ml-auto h-4 w-4" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="form.errors.livestock_id" />
                    </div>

                    <div>
                        <Label for="weight">Bobot Ternak</Label>
                        <div class="relative">
                            <Input id="weight" type="number" v-model="form.weight" class="pr-12" />
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-muted-foreground">
                                Kg
                            </div>
                        </div>
                        <InputError :message="form.errors.weight" />
                    </div>

                    <div>
                        <Label for="date">Tanggal Penimbangan</Label>
                        <Input type="date" v-model="form.date" />
                        <InputError :message="form.errors.date" />
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <Button type="submit" :disabled="form.processing">Simpan bobot</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
