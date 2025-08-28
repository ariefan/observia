<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxOption,
  ComboboxOptions,
} from '@headlessui/vue';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { ArrowLeft, Check, ChevronsUpDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';

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
  medicine_quantity: props.healthRecord.medicine_quantity,
  record_date: props.healthRecord.record_date,
});

// Search states
const livestockSearch = ref('');
const diagnosisSearch = ref('');
const medicineSearch = ref('');

// Filtered options
const filteredLivestocks = computed(() => {
  if (!livestockSearch.value) return props.livestocks;
  return props.livestocks.filter(livestock => 
    livestock.tag_id.toLowerCase().includes(livestockSearch.value.toLowerCase()) ||
    livestock.name.toLowerCase().includes(livestockSearch.value.toLowerCase()) ||
    livestock.breed_name.toLowerCase().includes(livestockSearch.value.toLowerCase())
  );
});

// VeNom Codes (Veterinary Nomenclature) - Common veterinary conditions
const venomCodes = [
  { code: 'V001', label: 'Mastitis', description: 'Peradangan pada kelenjar susu' },
  { code: 'V002', label: 'Foot Rot', description: 'Pembusukan kuku/teracak' },
  { code: 'V003', label: 'Pneumonia', description: 'Peradangan paru-paru' },
  { code: 'V004', label: 'Diarrhea', description: 'Diare/mencret' },
  { code: 'V005', label: 'Bloat', description: 'Perut kembung/timpani' },
  { code: 'V006', label: 'Milk Fever', description: 'Demam susu/hipokalsemia' },
  { code: 'V007', label: 'Ketosis', description: 'Ketonikosis' },
  { code: 'V008', label: 'Retained Placenta', description: 'Tertinggalnya ari-ari' },
  { code: 'V009', label: 'Endometritis', description: 'Peradangan rahim' },
  { code: 'V010', label: 'Lameness', description: 'Pincang/kepincangan' },
  { code: 'V011', label: 'Parasitic Infection', description: 'Infeksi parasit/cacingan' },
  { code: 'V012', label: 'Skin Disease', description: 'Penyakit kulit' },
  { code: 'V013', label: 'Eye Infection', description: 'Infeksi mata' },
  { code: 'V014', label: 'Respiratory Disease', description: 'Penyakit saluran pernapasan' },
  { code: 'V015', label: 'Digestive Disorder', description: 'Gangguan pencernaan' },
];

// ATCvet Codes (WHO) - Common veterinary medicines
const atcvetCodes = [
  { code: 'QA07AA02', label: 'Neomycin', description: 'Antibiotik untuk infeksi usus' },
  { code: 'QJ01AA02', label: 'Doxycycline', description: 'Antibiotik spektrum luas' },
  { code: 'QJ01CA01', label: 'Ampicillin', description: 'Antibiotik penisilin' },
  { code: 'QJ01CE02', label: 'Phenoxymethylpenicillin', description: 'Penisilin oral' },
  { code: 'QJ01DA02', label: 'Erythromycin', description: 'Antibiotik makrolida' },
  { code: 'QJ01EA01', label: 'Trimethoprim', description: 'Antibiotik sulfonamida' },
  { code: 'QJ01FF01', label: 'Clindamycin', description: 'Antibiotik lincosamida' },
  { code: 'QA02AD01', label: 'Ordinary Salt Combinations', description: 'Elektrolit untuk dehidrasi' },
  { code: 'QA03AA04', label: 'Atropine', description: 'Antispasmodik' },
  { code: 'QA06AB58', label: 'Bisacodyl', description: 'Pencahar stimulan' },
  { code: 'QG04BD04', label: 'Dinoprost', description: 'Prostaglandin untuk reproduksi' },
  { code: 'QH01CB02', label: 'Octreotide', description: 'Analog somatostatin' },
  { code: 'QJ01BA01', label: 'Chloramphenicol', description: 'Antibiotik kloramfenikol' },
  { code: 'QM01AE03', label: 'Ketoprofen', description: 'Anti-inflamasi' },
  { code: 'QN01AF01', label: 'Halothane', description: 'Anestesi umum' },
  { code: 'QP51AG02', label: 'Ivermectin', description: 'Antiparasit' },
  { code: 'QP52AC11', label: 'Albendazole', description: 'Antelmintik' },
];

// Filtered diagnosis options
const filteredDiagnosis = computed(() => {
  if (!diagnosisSearch.value) return venomCodes;
  return venomCodes.filter(code => 
    code.label.toLowerCase().includes(diagnosisSearch.value.toLowerCase()) ||
    code.description.toLowerCase().includes(diagnosisSearch.value.toLowerCase()) ||
    code.code.toLowerCase().includes(diagnosisSearch.value.toLowerCase())
  );
});

// Filtered medicine options
const filteredMedicines = computed(() => {
  if (!medicineSearch.value) return atcvetCodes;
  return atcvetCodes.filter(code => 
    code.label.toLowerCase().includes(medicineSearch.value.toLowerCase()) ||
    code.description.toLowerCase().includes(medicineSearch.value.toLowerCase()) ||
    code.code.toLowerCase().includes(medicineSearch.value.toLowerCase())
  );
});

const medicineTypes = [
  { value: 'tablet', label: 'Tablet' },
  { value: 'kapsul', label: 'Kapsul' },
  { value: 'cair', label: 'Cair/Inject' },
  { value: 'salep', label: 'Salep' },
  { value: 'serbuk', label: 'Serbuk' },
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
              <Combobox v-model="form.livestock_id">
                <div class="relative">
                  <ComboboxButton
                    class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                    <span class="block truncate text-foreground">{{ 
                      filteredLivestocks.find(l => l.id === form.livestock_id)?.tag_id 
                      ? `${filteredLivestocks.find(l => l.id === form.livestock_id)?.tag_id} - ${filteredLivestocks.find(l => l.id === form.livestock_id)?.name} (${filteredLivestocks.find(l => l.id === form.livestock_id)?.breed_name})`
                      : 'Pilih ternak...'
                    }}</span>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                      <ChevronsUpDown class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                    </span>
                  </ComboboxButton>
                  <ComboboxOptions
                    class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-popover border border-border py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                    <div class="relative">
                      <ComboboxInput
                        class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                        :display-value="() => ''"
                        @change="livestockSearch = $event.target.value"
                        placeholder="Cari ID ternak, nama, atau ras..." />
                    </div>
                    <div v-if="filteredLivestocks.length === 0 && livestockSearch !== ''"
                      class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                      Tidak ada ternak ditemukan.
                    </div>
                    <ComboboxOption v-for="livestock in filteredLivestocks" :key="livestock.id" v-slot="{ selected, active }"
                      :value="livestock.id" as="template">
                      <li :class="[
                        active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                        'relative cursor-default select-none py-2 pl-10 pr-4',
                      ]">
                        <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                          {{ livestock.tag_id }} - {{ livestock.name }} ({{ livestock.breed_name }})
                        </span>
                        <span v-if="selected" :class="[
                          active ? 'text-accent-foreground' : 'text-primary',
                          'absolute inset-y-0 left-0 flex items-center pl-3',
                        ]">
                          <Check class="h-4 w-4" aria-hidden="true" />
                        </span>
                      </li>
                    </ComboboxOption>
                  </ComboboxOptions>
                </div>
              </Combobox>
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
                <Combobox v-model="form.diagnosis">
                  <div class="relative">
                    <ComboboxButton
                      class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                      <span class="block truncate text-foreground">{{ 
                        form.diagnosis || 'Pilih diagnosa...'
                      }}</span>
                      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                        <ChevronsUpDown class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                      </span>
                    </ComboboxButton>
                    <ComboboxOptions
                      class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-popover border border-border py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                      <div class="relative">
                        <ComboboxInput
                          class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                          :display-value="() => ''"
                          @change="diagnosisSearch = $event.target.value"
                          placeholder="Cari kode diagnosa atau deskripsi..." />
                      </div>
                      <div v-if="filteredDiagnosis.length === 0 && diagnosisSearch !== ''"
                        class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                        Tidak ada diagnosa ditemukan.
                      </div>
                      <ComboboxOption v-for="diagnosis in filteredDiagnosis" :key="diagnosis.code" v-slot="{ selected, active }"
                        :value="diagnosis.label" as="template">
                        <li :class="[
                          active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                          'relative cursor-default select-none py-2 pl-10 pr-4',
                        ]">
                          <div class="flex flex-col">
                            <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                              {{ diagnosis.label }}
                            </span>
                            <span :class="[active ? 'text-accent-foreground/70' : 'text-muted-foreground', 'block text-xs truncate']">
                              {{ diagnosis.code }} - {{ diagnosis.description }}
                            </span>
                          </div>
                          <span v-if="selected" :class="[
                            active ? 'text-accent-foreground' : 'text-primary',
                            'absolute inset-y-0 left-0 flex items-center pl-3',
                          ]">
                            <Check class="h-4 w-4" aria-hidden="true" />
                          </span>
                        </li>
                      </ComboboxOption>
                    </ComboboxOptions>
                  </div>
                </Combobox>
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
                <Combobox v-model="form.medicine_name">
                  <div class="relative">
                    <ComboboxButton
                      class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                      <span class="block truncate text-foreground">{{ 
                        form.medicine_name || 'Pilih obat...'
                      }}</span>
                      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                        <ChevronsUpDown class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                      </span>
                    </ComboboxButton>
                    <ComboboxOptions
                      class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-popover border border-border py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                      <div class="relative">
                        <ComboboxInput
                          class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                          :display-value="() => ''"
                          @change="medicineSearch = $event.target.value"
                          placeholder="Cari kode obat atau nama..." />
                      </div>
                      <div v-if="filteredMedicines.length === 0 && medicineSearch !== ''"
                        class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                        Tidak ada obat ditemukan.
                      </div>
                      <ComboboxOption v-for="medicine in filteredMedicines" :key="medicine.code" v-slot="{ selected, active }"
                        :value="medicine.label" as="template">
                        <li :class="[
                          active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                          'relative cursor-default select-none py-2 pl-10 pr-4',
                        ]">
                          <div class="flex flex-col">
                            <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                              {{ medicine.label }}
                            </span>
                            <span :class="[active ? 'text-accent-foreground/70' : 'text-muted-foreground', 'block text-xs truncate']">
                              {{ medicine.code }} - {{ medicine.description }}
                            </span>
                          </div>
                          <span v-if="selected" :class="[
                            active ? 'text-accent-foreground' : 'text-primary',
                            'absolute inset-y-0 left-0 flex items-center pl-3',
                          ]">
                            <Check class="h-4 w-4" aria-hidden="true" />
                          </span>
                        </li>
                      </ComboboxOption>
                    </ComboboxOptions>
                  </div>
                </Combobox>
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
                <Input 
                  id="medicine_quantity" 
                  v-model.number="form.medicine_quantity" 
                  type="number" 
                  min="1"
                  placeholder="Masukkan jumlah" 
                  class="w-full" 
                />
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