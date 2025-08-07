<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Save, AlertCircle } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { computed, watch } from 'vue';

const props = defineProps({
    availableFeedings: Array,
});

const form = useForm({
    feeding_id: '',
    leftover_quantity: '',
    date: new Date().toISOString().split('T')[0],
    time: new Date().toTimeString().split(' ')[0].substring(0, 5),
    notes: '',
});

const selectedFeeding = computed(() => {
    return props.availableFeedings.find(feeding => feeding.id == form.feeding_id);
});

// Watch for feeding selection changes to auto-fill date and time
watch(() => form.feeding_id, (newValue) => {
    if (newValue) {
        const feeding = props.availableFeedings.find(f => f.id == newValue);
        if (feeding) {
            form.date = feeding.date;
            if (feeding.time) {
                form.time = feeding.time;
            }
        }
    }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatSession = (session) => {
    const sessions = {
        'morning': 'Pagi',
        'afternoon': 'Sore'
    };
    return sessions[session] || session;
};

const submit = () => {
    form.post(route('rations.leftover.store'));
};
</script>

<template>

    <Head title="Catat Sisa Pakan" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('rations.index')" class="text-gray-500 hover:text-gray-700">
                <ArrowLeft class="w-6 h-6" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Catat Sisa Pakan
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Catat Sisa Pakan</CardTitle>
                        <CardDescription>
                            Pilih pemberian pakan yang ingin dicatat sisa pakannya untuk membantu mengoptimalkan
                            pemberian pakan
                            di masa mendatang.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="availableFeedings.length === 0" class="text-center py-8">
                            <Alert>
                                <AlertCircle class="h-4 w-4" />
                                <AlertDescription>
                                    Tidak ada pemberian pakan dalam 7 hari terakhir yang belum dicatat sisa pakannya.
                                </AlertDescription>
                            </Alert>
                            <div class="mt-4">
                                <Link :href="route('rations.index')">
                                <Button variant="outline">Kembali ke Daftar Pakan</Button>
                                </Link>
                            </div>
                        </div>

                        <form v-else @submit.prevent="submit" class="space-y-6">
                            <!-- Feeding Selection -->
                            <div class="space-y-2">
                                <Label for="feeding_id">Pilih Pemberian Pakan *</Label>
                                <Select v-model="form.feeding_id" required>
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Pilih pemberian pakan yang ingin dicatat sisa pakannya..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="feeding in availableFeedings" :key="feeding.id"
                                            :value="feeding.id.toString()">
                                            <div class="flex justify-between items-center w-full">
                                                <div>
                                                    <div class="font-medium">{{ feeding.herd?.name || 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ feeding.ration?.name || 'N/A' }} - {{ feeding.quantity }} kg
                                                    </div>
                                                </div>
                                                <div class="text-right text-sm text-gray-500">
                                                    <div>{{ formatDate(feeding.date) }}</div>
                                                    <div>{{ formatSession(feeding.session) }}
                                                        <span v-if="feeding.time"> - {{ feeding.time }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.feeding_id" class="text-sm text-red-600">
                                    {{ form.errors.feeding_id }}
                                </div>
                            </div>

                            <!-- Selected Feeding Info -->
                            <div v-if="selectedFeeding" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-medium text-blue-900 mb-2">Detail Pemberian Pakan Terpilih:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-700 font-medium">Kandang:</span>
                                        <div>{{ selectedFeeding.herd?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 font-medium">Ransum:</span>
                                        <div>{{ selectedFeeding.ration?.name || 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 font-medium">Jumlah Diberikan:</span>
                                        <div>{{ selectedFeeding.quantity }} kg</div>
                                    </div>
                                    <div>
                                        <span class="text-blue-700 font-medium">Sesi:</span>
                                        <div>{{ formatSession(selectedFeeding.session) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Leftover Quantity -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="leftover_quantity">Jumlah Sisa Pakan (kg) *</Label>
                                    <Input id="leftover_quantity" v-model="form.leftover_quantity" type="number"
                                        step="0.01" min="0" :max="selectedFeeding?.quantity || undefined"
                                        placeholder="0.00" required class="text-lg" />
                                    <div v-if="form.errors.leftover_quantity" class="text-sm text-red-600">
                                        {{ form.errors.leftover_quantity }}
                                    </div>
                                    <div v-if="selectedFeeding" class="text-sm text-gray-500">
                                        Maksimal: {{ selectedFeeding.quantity }} kg
                                    </div>
                                </div>

                                <div v-if="selectedFeeding && form.leftover_quantity" class="space-y-4">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <h5 class="font-medium text-green-900 mb-2">Efisiensi Pakan:</h5>
                                        <div class="space-y-1 text-sm">
                                            <div class="flex justify-between">
                                                <span>Diberikan:</span>
                                                <span class="font-medium">{{ selectedFeeding.quantity }} kg</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Sisa:</span>
                                                <span class="font-medium text-orange-600">{{ form.leftover_quantity }}
                                                    kg</span>
                                            </div>
                                            <div class="flex justify-between border-t pt-1">
                                                <span>Dimakan:</span>
                                                <span class="font-medium text-green-600">
                                                    {{ (selectedFeeding.quantity - form.leftover_quantity).toFixed(2) }}
                                                    kg
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Efisiensi:</span>
                                                <span class="font-bold text-green-600">
                                                    {{ Math.round(((selectedFeeding.quantity - form.leftover_quantity) /
                                                    selectedFeeding.quantity) * 100) }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date and Time -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="date">Tanggal Pencatatan *</Label>
                                    <Input id="date" v-model="form.date" type="date" required />
                                    <div v-if="form.errors.date" class="text-sm text-red-600">
                                        {{ form.errors.date }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="time">Waktu Pencatatan</Label>
                                    <Input id="time" v-model="form.time" type="time" />
                                    <div v-if="form.errors.time" class="text-sm text-red-600">
                                        {{ form.errors.time }}
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <Label for="notes">Catatan (Opsional)</Label>
                                <Textarea id="notes" v-model="form.notes"
                                    placeholder="Catatan tambahan tentang sisa pakan (kondisi, penyebab, dll.)"
                                    rows="3" />
                                <div v-if="form.errors.notes" class="text-sm text-red-600">
                                    {{ form.errors.notes }}
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-4 pt-6 border-t">
                                <Link :href="route('rations.index')">
                                <Button variant="outline">Batal</Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing" class="flex items-center gap-2">
                                    <Save class="w-4 h-4" />
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Sisa Pakan' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
