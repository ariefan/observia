<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface HealthRecord {
  id: string;
  livestock_id: string;
  health_status: 'sehat' | 'sakit';
  diagnosis: string | null;
  treatment: string | null;
  notes: string | null;
  medicine_name: string | null;
  medicine_type: string | null;
  medicine_quantity: number | null;
  record_date: string;
  livestock: {
    id: string;
    name: string;
    tag_id: string;
    breed: {
      name: string;
    };
  };
}

const props = defineProps<{
  healthRecord: HealthRecord;
  livestocks: Array<{
    id: string;
    name: string;
    tag_id: string;
    breed_name: string;
  }>;
}>();

const form = useForm({
  livestock_id: props.healthRecord.livestock_id,
  health_status: props.healthRecord.health_status,
  diagnosis: props.healthRecord.diagnosis || '',
  treatment: props.healthRecord.treatment || '',
  notes: props.healthRecord.notes || '',
  medicine_name: props.healthRecord.medicine_name || '',
  medicine_type: props.healthRecord.medicine_type || '',
  medicine_quantity: props.healthRecord.medicine_quantity?.toString() || '',
  record_date: props.healthRecord.record_date,
});

const medicineTypes = [
  { value: 'tablet', label: 'Tablet' },
  { value: 'kapsul', label: 'Kapsul' },
  { value: 'cair', label: 'Cair/Inject' },
  { value: 'salep', label: 'Salep' },
  { value: 'serbuk', label: 'Serbuk' },
];

const medicineQuantities = [
  { value: '1', label: '1' },
  { value: '2', label: '2' },
  { value: '3', label: '3' },
  { value: '5', label: '5' },
  { value: '10', label: '10' },
  { value: '15', label: '15' },
  { value: '20', label: '20' },
];


const submit = () => {
  form.put(route('health-records.update', props.healthRecord.id));
};

const goBack = () => {
  router.visit(route('health-records.index'));
};
</script>

<template>
  <Head title="Edit Catatan Kesehatan" />

  <AppLayout>
    <div class="max-w-6xl mx-auto p-6">
      <Card>
        <CardHeader>
          <div class="flex items-center gap-4 mb-4">
            <Button
              variant="ghost"
              size="sm"
              @click="goBack"
              class="p-2"
            >
              <ArrowLeft class="h-4 w-4" />
            </Button>
            <div>
              <CardTitle class="text-2xl">Edit Catatan Kesehatan</CardTitle>
              <CardDescription>Perbarui informasi kesehatan ternak Anda.</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left column -->
          <div class="lg:col-span-4 space-y-6">
            <!-- ID Ternak -->
            <div>
              <Label for="livestock_id" class="block text-sm font-medium mb-2">ID Ternak</Label>
              <Select v-model="form.livestock_id">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih ternak" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="livestock in livestocks" 
                    :key="livestock.id" 
                    :value="livestock.id"
                  >
                    {{ livestock.tag_id }} - {{ livestock.name }} ({{ livestock.breed_name }})
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="form.errors.livestock_id" class="mt-1" />
            </div>

            <!-- Status Kesehatan -->
            <fieldset>
              <legend class="block text-sm font-medium mb-2">Status Kesehatan</legend>
              <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                  <input 
                    type="radio" 
                    v-model="form.health_status" 
                    value="sehat" 
                    class="h-4 w-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:focus:ring-emerald-400 dark:bg-gray-700"
                  />
                  <span class="text-gray-700 dark:text-gray-300">Sehat</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                  <input 
                    type="radio" 
                    v-model="form.health_status" 
                    value="sakit" 
                    class="h-4 w-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:focus:ring-emerald-400 dark:bg-gray-700"
                  />
                  <span class="text-gray-700 dark:text-gray-300">Sakit</span>
                </label>
              </div>
              <InputError :message="form.errors.health_status" class="mt-1" />
            </fieldset>

            <!-- Tanggal Pencatatan -->
            <div>
              <Label for="record_date" class="block text-sm font-medium mb-2">Tanggal Pencatatan</Label>
              <Input
                id="record_date"
                v-model="form.record_date"
                type="date"
                class="w-full"
              />
              <InputError :message="form.errors.record_date" class="mt-1" />
            </div>
          </div>

          <!-- Right column -->
          <div class="lg:col-span-8 space-y-6">
            <!-- Diagnosa & Treatment -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="diagnosis" class="block text-sm font-medium mb-2">Diagnosa</Label>
                <Input
                  id="diagnosis"
                  v-model="form.diagnosis"
                  type="text"
                  placeholder="Contoh: Mastitis, Cacingan"
                  class="w-full"
                />
                <InputError :message="form.errors.diagnosis" class="mt-1" />
              </div>

              <div>
                <Label for="treatment" class="block text-sm font-medium mb-2">Treatment</Label>
                <Input
                  id="treatment"
                  v-model="form.treatment"
                  type="text"
                  placeholder="Pemberian obat, vitamin, rawat dokter"
                  class="w-full"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Contoh: pemberian obat, vitamin, atau rawat dokter.</p>
                <InputError :message="form.errors.treatment" class="mt-1" />
              </div>
            </div>

            <!-- Keterangan -->
            <div>
              <Label for="notes" class="block text-sm font-medium mb-2">Keterangan</Label>
              <Textarea
                id="notes"
                v-model="form.notes"
                rows="4"
                placeholder="Ceritakan kondisi ternak. Jika sakit, tulis ciri-cirinya."
                class="w-full"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ceritakan kondisi ternak Anda. Jika sakit, deskripsikan ciri-ciri penyakit.</p>
              <InputError :message="form.errors.notes" class="mt-1" />
            </div>

            <!-- Obat / Jenis / Jumlah -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <Label for="medicine_name" class="block text-sm font-medium mb-2">Nama Obat</Label>
                <Input
                  id="medicine_name"
                  v-model="form.medicine_name"
                  type="text"
                  placeholder="Amoxicillin"
                  class="w-full"
                />
                <InputError :message="form.errors.medicine_name" class="mt-1" />
              </div>

              <div>
                <Label for="medicine_type" class="block text-sm font-medium mb-2">Jenis</Label>
                <Select v-model="form.medicine_type">
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih jenis" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="type in medicineTypes" 
                      :key="type.value" 
                      :value="type.value"
                    >
                      {{ type.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.medicine_type" class="mt-1" />
              </div>

              <div>
                <Label for="medicine_quantity" class="block text-sm font-medium mb-2">Jumlah</Label>
                <Select v-model="form.medicine_quantity">
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih jumlah" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="qty in medicineQuantities" 
                      :key="qty.value" 
                      :value="qty.value"
                    >
                      {{ qty.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.medicine_quantity" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="lg:col-span-12 flex justify-end">
            <Button
              type="submit"
              :disabled="form.processing"
              class="bg-emerald-600 text-white px-5 py-3 font-medium hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-800"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Perbarui Catatan</span>
            </Button>
          </div>
        </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>