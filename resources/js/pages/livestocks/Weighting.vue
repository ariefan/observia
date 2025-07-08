<script setup lang="ts">
// Core Vue imports
import { ref } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";

// Layout and Components
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/InputError.vue";
import { Card, CardContent } from "@/components/ui/card";


// Icons
import { ArrowLeft } from "lucide-vue-next";

// Props
const props = defineProps<{
    livestock: any;
}>();

// Form state and handlers
const form = useForm({
    livestock_id: props.livestock.tag_id,
    weight: 0,
    date: new Date().toISOString().slice(0, 10),
});

const submit = () => {
    form.post(route('livestocks.weight.store', { livestock: props.livestock.id }));
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
                <Button @click="back" variant="outline" size="icon" class="h-10 w-10 shrink-0">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <Label for="livestock_id">ID Ternak</Label>
                        <Input id="livestock_id" type="text" v-model="form.livestock_id" disabled />
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
