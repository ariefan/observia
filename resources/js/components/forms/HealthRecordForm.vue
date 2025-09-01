<script setup lang="ts">
import { Button } from '@/components/ui/button';
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
import { Check, ChevronsUpDown, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

interface Livestock {
  id: string;
  name: string;
  tag_id: string;
  breed_name: string;
}

interface Medicine {
  inventory_item_id?: number;
  name: string;
  type: string;
  quantity: number | undefined;
  dosage: string;
  current_stock?: number;
  template?: any; // Optional template reference for display
}

interface FormData {
  livestock_id: string;
  health_status: string;
  diagnosis: string[];
  treatment: string[];
  notes: string;
  medicines: Medicine[];
  record_date: string;
}

interface FormErrors {
  livestock_id?: string;
  health_status?: string;
  diagnosis?: string;
  'diagnosis.*'?: string;
  treatment?: string;
  'treatment.*'?: string;
  notes?: string;
  medicines?: string;
  'medicines.*.name'?: string;
  'medicines.*.type'?: string;
  'medicines.*.quantity'?: string;
  'medicines.*.dosage'?: string;
  record_date?: string;
}

interface InventoryItem {
  id: number;
  name: string;
  sku?: string;
  stock: number;
  unit: {
    id: number;
    name: string;
    symbol: string;
  };
  category: {
    id: number;
    name: string;
  };
}

interface Props {
  form: FormData;
  errors: FormErrors;
  livestocks: Livestock[];
  inventoryMedicines: InventoryItem[];
  processing?: boolean;
  submitText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  processing: false,
  submitText: 'Simpan Catatan',
});

const emit = defineEmits<{
  'update:form': [value: FormData];
  submit: [];
}>();

// Create reactive form data
const formData = ref({ ...props.form });

// Watch for changes and sync with parent
watch(formData, (newData) => {
  emit('update:form', newData);
}, { deep: true });

// Watch for prop changes and update local form
watch(() => props.form, (newForm) => {
  Object.assign(formData.value, newForm);
}, { deep: true });

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
  { code: 'V001', label: 'Mastitis', description: 'Inflammation of mammary glands' },
  { code: 'V002', label: 'Busuk Kuku', description: 'Hoof rot or foot rot' },
  { code: 'V003', label: 'Radang Paru (Pneumonia)', description: 'Lung inflammation' },
  { code: 'V004', label: 'Diare', description: 'Diarrhea or loose stools' },
  { code: 'V005', label: 'Kembung', description: 'Bloat or gastric tympany' },
  { code: 'V006', label: 'Demam Susu', description: 'Milk fever or hypocalcemia' },
  { code: 'V007', label: 'Ketosis', description: 'Ketosis or ketonemia' },
  { code: 'V008', label: 'Tertahan Plasenta', description: 'Retained placenta' },
  { code: 'V009', label: 'Endometritis', description: 'Uterine inflammation' },
  { code: 'V010', label: 'Pincang', description: 'Lameness or limping' },
  { code: 'V011', label: 'Infeksi Parasit', description: 'Parasitic infection or worms' },
  { code: 'V012', label: 'Penyakit Kulit', description: 'Skin disease or dermatitis' },
  { code: 'V013', label: 'Infeksi Mata', description: 'Eye infection or conjunctivitis' },
  { code: 'V014', label: 'Penyakit Pernapasan', description: 'Respiratory disease' },
  { code: 'V015', label: 'Gangguan Pencernaan', description: 'Digestive disorder' },
];

// ATCvet Codes (WHO) - Common veterinary medicines
// Medicine templates are now passed as props from the controller

// Medicine types are now defined by the units in medicine templates

// Filtered diagnosis options
const filteredDiagnosis = computed(() => {
  if (!diagnosisSearch.value) return venomCodes;
  return venomCodes.filter(code =>
    code.label.toLowerCase().includes(diagnosisSearch.value.toLowerCase()) ||
    code.description.toLowerCase().includes(diagnosisSearch.value.toLowerCase()) ||
    code.code.toLowerCase().includes(diagnosisSearch.value.toLowerCase())
  );
});

// Filtered medicine options from inventory
const filteredMedicines = computed(() => {
  // Check if inventoryMedicines exists and has data
  if (!props.inventoryMedicines || !Array.isArray(props.inventoryMedicines)) {
    return [];
  }

  if (props.inventoryMedicines.length === 0) {
    return [];
  }

  const medicines = props.inventoryMedicines.map(item => ({
    id: item.id,
    code: item.sku || `INV-${item.id}`,
    label: item.name,
    description: `Available: ${item.stock} ${item.unit?.symbol || 'unit'}`,
    unit: item.unit,
    currentStock: item.stock
  }));

  if (!medicineSearch.value) return medicines;
  return medicines.filter(item =>
    item.label.toLowerCase().includes(medicineSearch.value.toLowerCase()) ||
    item.description.toLowerCase().includes(medicineSearch.value.toLowerCase()) ||
    item.code.toLowerCase().includes(medicineSearch.value.toLowerCase())
  );
});

// Functions for managing multiple diagnoses
const addDiagnosis = () => {
  if (!formData.value.diagnosis) {
    formData.value.diagnosis = [];
  }
  formData.value.diagnosis.push('');
};

const removeDiagnosis = (index: number) => {
  if (formData.value.diagnosis && formData.value.diagnosis.length > 1) {
    formData.value.diagnosis.splice(index, 1);
  }
};

// Functions for managing multiple medicines
const addMedicine = () => {
  if (!formData.value.medicines) {
    formData.value.medicines = [];
  }
  formData.value.medicines.push({
    inventory_item_id: undefined,
    name: '',
    type: '',
    quantity: undefined,
    dosage: '',
    current_stock: undefined,
    template: undefined
  });
};

const selectMedicine = (index: number, item: any) => {
  if (formData.value.medicines && formData.value.medicines[index]) {
    formData.value.medicines[index].name = item.label;
    formData.value.medicines[index].type = item.unit.symbol;
    formData.value.medicines[index].current_stock = item.currentStock;
    formData.value.medicines[index].inventory_item_id = item.id;
    formData.value.medicines[index].template = item;
  }
};

const removeMedicine = (index: number) => {
  if (formData.value.medicines && formData.value.medicines.length > 1) {
    formData.value.medicines.splice(index, 1);
  }
};

// Functions for managing multiple treatments
const addTreatment = () => {
  if (!formData.value.treatment) {
    formData.value.treatment = [];
  }
  formData.value.treatment.push('');
};

const removeTreatment = (index: number) => {
  if (formData.value.treatment && formData.value.treatment.length > 1) {
    formData.value.treatment.splice(index, 1);
  }
};

const updateNotes = (event: Event) => {
  const target = event.target as HTMLTextAreaElement;
  formData.value.notes = target.value;
};

const handleSubmit = () => {
  emit('submit');
};
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Top Section: Basic Information -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- ID Ternak -->
      <div>
        <Label for="livestock_id" class="block text-sm font-medium mb-2">ID Ternak</Label>
        <Combobox v-model="formData.livestock_id">
          <div class="relative">
            <ComboboxButton
              class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
              <span class="block truncate text-foreground">{{
                filteredLivestocks.find(l => l.id === formData.livestock_id)?.tag_id
                  ? `${filteredLivestocks.find(l => l.id === formData.livestock_id)?.tag_id} -
                ${filteredLivestocks.find(l => l.id === formData.livestock_id)?.name} (${filteredLivestocks.find(l =>
                    l.id ===
                    formData.livestock_id)?.breed_name})`
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
                  :display-value="() => ''" @change="livestockSearch = $event.target.value"
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
        <InputError :message="errors.livestock_id" class="mt-1" />
      </div>

      <!-- Status Kesehatan -->
      <div>
        <fieldset>
          <legend class="block text-sm font-medium mb-2">Status Kesehatan</legend>
          <div class="flex items-center gap-6">
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="health_status" v-model="formData.health_status" value="healthy"
                class="h-4 w-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:focus:ring-emerald-400 dark:bg-gray-700" />
              <span class="text-gray-700 dark:text-gray-300">Sehat</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="radio" name="health_status" v-model="formData.health_status" value="sick"
                class="h-4 w-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:focus:ring-emerald-400 dark:bg-gray-700" />
              <span class="text-gray-700 dark:text-gray-300">Sakit</span>
            </label>
          </div>
          <InputError :message="errors.health_status" class="mt-1" />
        </fieldset>
      </div>

      <!-- Tanggal Pencatatan -->
      <div>
        <Label for="record_date" class="block text-sm font-medium mb-2">Tanggal Pencatatan</Label>
        <Input id="record_date" v-model="formData.record_date" type="date" class="w-full" />
        <InputError :message="errors.record_date" class="mt-1" />
      </div>
    </div>

    <!-- Keterangan -->
    <div>
      <Label for="notes" class="block text-sm font-medium mb-2">Keterangan / Gejala</Label>
      <Textarea id="notes" :value="formData.notes" @input="updateNotes" rows="4"
        placeholder="Ceritakan kondisi ternak. Jika sakit, tulis ciri-cirinya." class="w-full" />
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ceritakan kondisi ternak Anda. Jika sakit,
        deskripsikan ciri-ciri penyakit yang dialami.</p>
      <InputError :message="errors.notes" class="mt-1" />
    </div>

    <!-- Diagnosa & Treatment -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <div class="flex justify-between items-center">
          <Label class="block text-sm font-medium">Diagnosa</Label>
          <Button type="button" @click="addDiagnosis" size="sm" variant="link">
            <Plus class="h-3 w-3 mr-1" />
            Tambah Diagnosa
          </Button>
        </div>

        <div class="space-y-3">
          <div v-for="(_, index) in (formData.diagnosis || [])" :key="index" class="flex gap-2">
            <div class="flex-1">
              <Combobox v-model="formData.diagnosis[index]">
                <div class="relative">
                  <ComboboxButton
                    class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                    <span class="block truncate text-foreground">{{
                      formData.diagnosis[index] || 'Pilih diagnosa...'
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
                        :display-value="() => ''" @change="diagnosisSearch = $event.target.value"
                        placeholder="Cari kode diagnosa atau deskripsi..." />
                    </div>
                    <div v-if="filteredDiagnosis.length === 0 && diagnosisSearch !== ''"
                      class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                      Tidak ada diagnosa ditemukan.
                    </div>
                    <ComboboxOption v-for="diagnosisOption in filteredDiagnosis" :key="diagnosisOption.code"
                      v-slot="{ selected, active }" :value="diagnosisOption.label" as="template">
                      <li :class="[
                        active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                        'relative cursor-default select-none py-2 pl-10 pr-4',
                      ]">
                        <div class="flex flex-col">
                          <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                            {{ diagnosisOption.label }}
                          </span>
                          <span
                            :class="[active ? 'text-accent-foreground/70' : 'text-muted-foreground', 'block text-xs truncate']">
                            {{ diagnosisOption.code }} - {{ diagnosisOption.description }}
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
            </div>
            <Button v-if="formData.diagnosis && formData.diagnosis.length > 1" type="button"
              @click="removeDiagnosis(index)" size="sm" variant="link">
              <Trash2 class="h-3 w-3 text-red-500" />
            </Button>
          </div>
        </div>
        <InputError :message="errors.diagnosis || errors['diagnosis.*']" class="mt-1" />
      </div>

      <div>
        <div class="flex justify-between items-center">
          <Label class="block text-sm font-medium">Treatment</Label>
          <Button type="button" @click="addTreatment" size="sm" variant="link">
            <Plus class="h-3 w-3 mr-1" />
            Tambah Treatment
          </Button>
        </div>

        <div class="space-y-3">
          <div v-for="(_, index) in (formData.treatment || [])" :key="index" class="flex gap-2">
            <div class="flex-1">
              <Input v-model="formData.treatment[index]" type="text"
                placeholder="Pemberian obat, vitamin, rawat dokter" class="w-full" />
            </div>
            <Button v-if="formData.treatment && formData.treatment.length > 1" type="button"
              @click="removeTreatment(index)" size="sm" variant="link">
              <Trash2 class="h-3 w-3 text-red-500" />
            </Button>
          </div>
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Contoh: pemberian obat, vitamin, atau rawat
          dokter hewan.</p>
        <InputError :message="errors.treatment || errors['treatment.*']" class="mt-1" />
      </div>
    </div>

    <!-- Obat -->
    <div>
      <div class="flex justify-between items-center mb-2">
        <Label class="block text-sm font-medium">Obat-obatan</Label>
        <Button type="button" @click="addMedicine" size="sm" variant="link">
          <Plus class="h-3 w-3 mr-1" />
          Tambah Obat
        </Button>
      </div>

      <div class="space-y-4">
        <div class="border border-border rounded-lg p-4">
          <template v-for="(_, index) in (formData.medicines || [])" :key="index">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
              <div class="lg:col-span-3">
                <Label v-if="index === 0" class="block text-sm font-medium mb-2">Nama Obat</Label>
                <Combobox v-model="formData.medicines[index].name">
                  <div class="relative">
                    <ComboboxButton
                      class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                      <span class="block truncate text-foreground">{{
                        formData.medicines[index].name || 'Pilih obat...'
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
                          :display-value="() => ''" @change="medicineSearch = $event.target.value"
                          placeholder="Cari kode obat atau nama..." />
                      </div>
                      <div v-if="filteredMedicines.length === 0 && medicineSearch !== ''"
                        class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                        Tidak ada obat ditemukan.
                      </div>
                      <ComboboxOption v-for="medicineOption in filteredMedicines" :key="medicineOption.code"
                        v-slot="{ selected, active }" :value="medicineOption.label" as="template"
                        @click="selectMedicine(index, medicineOption)">
                        <li :class="[
                          active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                          'relative cursor-default select-none py-2 pl-10 pr-4',
                        ]">
                          <div class="flex flex-col">
                            <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                              {{ medicineOption.label }}
                              <span class="text-green-600 text-xs ml-1">[Stock: {{ medicineOption.currentStock }}]</span>
                            </span>
                            <span
                              :class="[active ? 'text-accent-foreground/70' : 'text-muted-foreground', 'block text-xs truncate']">
                              {{ medicineOption.code }} - {{ medicineOption.description }}
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
              </div>

              <div class="lg:col-span-1">
                <Label v-if="index === 0" class="block text-sm font-medium mb-2">Satuan</Label>
                <div class="h-10 px-3 py-2 bg-muted border border-input rounded-md flex items-center text-sm text-muted-foreground">
                  {{ formData.medicines[index].template?.unit?.name || 'Pilih obat dulu' }}
                  <span v-if="formData.medicines[index].template?.unit?.symbol" class="ml-1">
                    ({{ formData.medicines[index].template.unit.symbol }})
                  </span>
                </div>
              </div>

              <div class="lg:col-span-1">
                <Label v-if="index === 0" class="block text-sm font-medium mb-2">Jumlah</Label>
                <div class="relative">
                  <Input v-model.number="formData.medicines[index].quantity" type="number" min="1"
                    :max="formData.medicines[index].current_stock || undefined"
                    placeholder="Masukkan jumlah" class="w-full" />
                  <div v-if="formData.medicines[index].current_stock !== undefined" 
                       class="absolute -bottom-5 left-0 text-xs text-muted-foreground">
                    Stok: {{ formData.medicines[index].current_stock }}
                  </div>
                </div>
              </div>

              <div class="lg:col-span-1">
                <Label v-if="index === 0" class="block text-sm font-medium mb-2">Dosis</Label>
                <Input v-model="formData.medicines[index].dosage" type="text" placeholder="2x sehari" class="w-full" />
              </div>

              <div class="lg:col-span-1 flex items-end">
                <Button v-if="formData.medicines && formData.medicines.length > 1" type="button"
                  @click="removeMedicine(index)" variant="link" class="text-red-500 p-2">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </template>
        </div>
      </div>
      <InputError :message="errors.medicines" class="mt-1" />
    </div>

    <!-- Submit -->
    <div class="flex justify-end">
      <Button type="submit" :disabled="processing">
        <span v-if="processing">Menyimpan...</span>
        <span v-else>{{ submitText }}</span>
      </Button>
    </div>
  </form>
</template>