<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import type { Livestock, Species, Breed, Herd } from '@/types';

import { Check, ArrowLeft, ChevronsUpDown } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue';
import LivestockUploader from "./LivestockUploader.vue";
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox'

// Composables
import { useNavigation } from '@/composables/useNavigation';

interface Props {
  livestock: Livestock;
  species: Species[];
  male_livestock?: string;
  female_livestock?: string;
}

const props = defineProps<Props>();

const breeds = ref<Breed[]>([]);
const selected_male_parent = ref<Livestock | null>(null);
const selected_female_parent = ref<Livestock | null>(null);

const form_1 = ref<HTMLFormElement | null>(null)
const livestockUploaderRef = ref<InstanceType<typeof LivestockUploader> | null>(null)

const form = useForm({
  name: props.livestock.name,
  origin: props.livestock.origin,
  status: props.livestock.status,
  species_id: props.livestock.breed?.species_id || props.livestock.species_id,
  breed_id: props.livestock.breed_id,
  herd_id: props.livestock.herd_id,
  male_parent_id: props.livestock.male_parent_id,
  female_parent_id: props.livestock.female_parent_id,
  sex: props.livestock.sex,
  tag_type: props.livestock.tag_type || 'eartag',
  tag_id: props.livestock.tag_id,
  birthdate: props.livestock.birthdate,
  purchase_date: props.livestock.purchase_date,
  birth_weight: props.livestock.birth_weight,
  weight: props.livestock.weight,
  photo: props.livestock.photo || [],
  barter_livestock_id: props.livestock.barter_livestock_id,
  barter_from: props.livestock.barter_from,
  barter_date: props.livestock.barter_date,
  purchase_price: props.livestock.purchase_price,
  purchase_from: props.livestock.purchase_from,
  grant_from: props.livestock.grant_from,
  grant_date: props.livestock.grant_date,
  borrowed_from: props.livestock.borrowed_from,
  borrowed_date: props.livestock.borrowed_date,
  entry_date: props.livestock.entry_date || new Date().toISOString().slice(0, 10),
});

const saveAction = async () => {
  console.log('SaveAction called')
  console.log('Form processing state:', form.processing)
  console.log('Form errors:', form.errors)

  try {
    // Process all images to ensure they are resized to 16:9 aspect ratio
    if (livestockUploaderRef.value) {
      console.log('Getting processed images...')
      const processedImages = await livestockUploaderRef.value.getAllImagesAsFiles()
      console.log('Processed images for save action:', processedImages.map((f: File) => ({
        name: f.name,
        size: `${(f.size / 1024 / 1024).toFixed(2)}MB`,
        type: f.type,
        lastModified: f.lastModified,
        constructor: f.constructor.name,
        webkitRelativePath: f.webkitRelativePath,
        // Show all enumerable properties
        allProperties: Object.getOwnPropertyNames(f).reduce((acc: any, prop) => {
          try {
            acc[prop] = (f as any)[prop]
          } catch {
            acc[prop] = '[Error accessing property]'
          }
          return acc
        }, {})
      })))
      form.photo = processedImages
    }

    console.log('Save action - submitting form with data:', {
      ...form.data(),
      photo: form.photo ? `${form.photo.length} files` : 'no files'
    })

    const options = {
      onSuccess: (page: any) => {
        console.log('Save action successful:', page)
      },
      onError: (errors: any) => {
        console.error('Save action errors:', errors)
      },
      onFinish: () => {
        console.log('Save action finished')
      },
      onStart: () => {
        console.log('Save action started')
      }
    }

    if (props.livestock.id) {
      console.log('Updating livestock with ID:', props.livestock.id)
      form.transform((data) => ({
        ...data,
        _method: 'put',
      })).post(route('livestocks.update', { livestock: props.livestock.id }), options);
    } else {
      console.log('Creating new livestock')
      form.post(route('livestocks.store'), options);
    }
  } catch (error) {
    console.error('Error processing images before save action:', error)
    // Continue with original photos if processing fails
    const options = {
      onError: (errors: any) => {
        console.error('Save action errors (fallback):', errors)
      }
    }

    if (props.livestock.id) {
      form.transform((data) => ({
        ...data,
        _method: 'put',
      })).post(route('livestocks.update', { livestock: props.livestock.id }), options);
    } else {
      form.post(route('livestocks.store'), options);
    }
  }
};

async function fetchBreeds() {
  if (form.species_id) {
    const response = await fetch(
      `/api/species/${form.species_id}/breeds`
    );
    breeds.value = await response.json();
  }
}

// --- Combobox state for parent selection ---
const maleParentQuery = ref('');
const femaleParentQuery = ref('');
const maleParentOptions = ref<Livestock[]>([]);
const femaleParentOptions = ref<Livestock[]>([]);
const selectedMaleParent = ref<Livestock | null>(selected_male_parent.value || null);
const selectedFemaleParent = ref<Livestock | null>(selected_female_parent.value || null);

// Fetch parent options
const fetchParentOptions = async (query: string, sex: 'M' | 'F') => {
  // Always fetch, even for empty query
  const response = await fetch(route('livestocks.search', { q: query || '', sex }));
  const data = await response.json();
  if (sex === 'M') maleParentOptions.value = data;
  else femaleParentOptions.value = data;
};

watch(maleParentQuery, (q) => {
  if (!q) {
    fetchParentOptions('', 'M'); // Show top 10 if empty
  } else if (q.length < 2) {
    maleParentOptions.value = [];
  } else {
    fetchParentOptions(q, 'M');
  }
});
watch(femaleParentQuery, (q) => {
  if (!q) {
    fetchParentOptions('', 'F'); // Show top 10 if empty
  } else if (q.length < 2) {
    femaleParentOptions.value = [];
  } else {
    fetchParentOptions(q, 'F');
  }
});

watch(selectedMaleParent, (val) => {
  if (val) {
    form.male_parent_id = val.id;
    selected_male_parent.value = val;
  } else {
    form.male_parent_id = undefined;
    selected_male_parent.value = null;
  }
});
watch(selectedFemaleParent, (val) => {
  if (val) {
    form.female_parent_id = val.id;
    selected_female_parent.value = val;
  } else {
    form.female_parent_id = undefined;
    selected_female_parent.value = null;
  }
});

onMounted(async () => {
  console.log('Form mounted. Props livestock:', props.livestock)
  console.log('Form photo field:', form.photo)

  if (props.livestock.breed) {
    form.species_id = props.livestock.breed.species.id;
    await fetchBreeds();
  }

  // Fetch top 10 male and female parents on mount
  await fetchParentOptions('', 'M');
  await fetchParentOptions('', 'F');

  loadHerds();

  if (props.livestock.male_parent) {
    selectedMaleParent.value = props.livestock.male_parent;
  }

  if (props.livestock.female_parent) {
    selectedFemaleParent.value = props.livestock.female_parent;
  }

  // 💥 New logic: set selected herd from props if it exists
  if (props.livestock.herd_id) {
    try {
      const response = await axios.get(`/api/herds?id=${props.livestock.herd_id}`);
      const herd = response.data[0]; // Get first row if exists
      console.log('Herd response:', herd)
      if (herd) {
        selectedHerd.value = herd;
        form.herd_id = herd.id;
      } else {
        console.warn('🟠 Herd not found for ID:', props.livestock.herd_id);
      }
    } catch (error) {
      console.error('🔥 Error fetching herd by ID:', error);
    }
  }

});







const selectedHerd = ref<Herd | null>(null)
const searchQuery = ref('')
const searchResults = ref<Herd[]>([])

const getDisplayValue = (herd: Herd | null) => {
  return herd ? `${herd.name} (Kapasitas: ${herd.capacity})` : ''
}

const loadHerds = async (query = '') => {
  try {
    const response = await axios.get(route('herds.search', { q: query }))
    searchResults.value = response.data
  } catch (error) {
    console.error('🚨 Error fetching herds:', error)
    searchResults.value = []
  }
}

watch(selectedHerd, (newValue) => {
  if (newValue) {
    form.herd_id = newValue.id;
  }
});

// Watch search query and fetch
watch(searchQuery, (newQuery) => {
  loadHerds(newQuery)
})







// Composables
const { back } = useNavigation();

</script>

<template>
  <AppLayout title="Form Ternak">
    <template #header>
      <div class="flex items-center gap-4">
        <h3 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Form Ternak
        </h3>
      </div>
    </template>

    <div class="py-2">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <form ref="form_1" @submit.prevent="saveAction">
            <div class="flex items-center gap-4">
              <Button @click="back" variant="ghost" size="icon" class="h-10 w-10 shrink-0">
                <ArrowLeft class="h-5 w-5" />
              </Button>
              <h3 class="text-primary dark:text-primary-foreground font-semibold">Data Ternak</h3>
            </div>
            <div class="mt-2">
              <LivestockUploader ref="livestockUploaderRef" v-model="form.photo" />
              <InputError :message="form.errors.photo" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <Label for="tag_id">ID Ternak</Label>
                <Input id="tag_id" v-model="form.tag_id" type="text" />
                <InputError :message="form.errors.tag_id" />
              </div>
              <div></div>
              <div></div>
              <div>
                <Label for="herd_id">Kandang</Label>
                <Combobox v-model="selectedHerd" v-model:search-term="searchQuery" :display-value="getDisplayValue">
                  <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                      <Button variant="outline" class="justify-between w-full">
                        {{ selectedHerd ? getDisplayValue(selectedHerd) : 'Pilih Kandang' }}
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                      </Button>
                    </ComboboxTrigger>
                  </ComboboxAnchor>

                  <ComboboxList class="w-full">
                    <ComboboxInput v-model="searchQuery" placeholder="Cari kandang..." />
                    <ComboboxEmpty>Kandang tidak ditemukan.</ComboboxEmpty>
                    <ComboboxGroup>
                      <ComboboxItem v-for="result in searchResults" :key="result.id" :value="result">
                        {{ `${result.name} (${result.capacity})` }}
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
                <Label for="name">Nama</Label>
                <Input id="name" v-model="form.name" type="text" />
                <InputError :message="form.errors.name" />
              </div>
              <div>
                <Label for="status">Status</Label>
                <select id="status" v-model="form.status"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="1">Aktif</option>
                  <option value="2">Terjual</option>
                  <option value="3">Mati</option>
                  <option value="4">Disembelih</option>
                </select>
                <InputError :message="form.errors.status" />
              </div>
              <div>
                <Label for="species">Ternak</Label>
                <select id="species" v-model="form.species_id" @change="fetchBreeds"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">Pilih ternak</option>
                  <option v-for="specie in species" :key="specie.id" :value="specie.id">
                    {{ specie.name }}
                  </option>
                </select>
              </div>
              <div>
                <Label for="breed">Ras</Label>
                <select id="breed" v-model="form.breed_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">Pilih ras</option>
                  <option v-for="breed in breeds" :key="breed.id" :value="breed.id">
                    {{ breed.name }}
                  </option>
                </select>
                <InputError :message="form.errors.breed_id" />
              </div>
              <div>
                <Label for="sex">Jenis Kelamin</Label>
                <select id="sex" v-model="form.sex"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">Pilih jenis kelamin</option>
                  <option value="M">Jantan</option>
                  <option value="F">Betina</option>
                </select>
                <InputError :message="form.errors.sex" />
              </div>
            </div>
            <div
              :class="`grid grid-cols-1 md:grid-cols-2 ${form.origin == 4 ? 'lg:grid-cols-3' : 'lg:grid-cols-4'} gap-6 py-4`">
              <div :class="(() => {
                if (form.origin == 1) {
                  return 'col-span-4';
                } else {
                  return 'col-span-1';
                }
              })()">
                <Label for="origin">Asal</Label>
                <select id="origin" v-model="form.origin"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="1">Kelahiran</option>
                  <option value="2">Pembelian</option>
                  <option value="3">Barter</option>
                  <option value="4">Hibah</option>
                  <option value="5">Peminjaman</option>
                </select>
                <InputError :message="form.errors.origin" />
              </div>
              <div v-if="form.origin == 2">
                <Label for="purchase_date">Tanggal Pembelian</Label>
                <Input id="purchase_date" v-model="form.purchase_date" type="date" />
                <InputError :message="form.errors.purchase_date" />
              </div>
              <div v-if="form.origin == 2">
                <!-- TODO -->
                <Label for="purchase_from">Beli Dari</Label>
                <Input id="purchase_from" v-model="form.purchase_from" />
                <InputError :message="form.errors.purchase_from" />
              </div>
              <div v-if="form.origin == 2">
                <Label for="purchase_price">Harga Beli</Label>
                <Input id="purchase_price" v-model="form.purchase_price" type="number" />
                <InputError :message="form.errors.purchase_price" />
              </div>
              <div v-if="form.origin == 3">
                <Label for="barter_livestock_id">ID Ternak Barter</Label>
                <Input id="barter_livestock_id" v-model="form.barter_livestock_id" />
                <InputError :message="form.errors.barter_livestock_id" />
              </div>
              <div v-if="form.origin == 3">
                <Label for="barter_from">Asal Barter</Label>
                <Input id="barter_from" v-model="form.barter_from" type="text" />
                <InputError :message="form.errors.barter_from" />
              </div>
              <div v-if="form.origin == 3">
                <Label for="barter_date">Tanggal Barter</Label>
                <Input id="barter_date" v-model="form.barter_date" type="date" />
                <InputError :message="form.errors.barter_date" />
              </div>
              <div v-if="form.origin == 4">
                <Label for="grant_from">Hibah Dari</Label>
                <Input id="grant_from" v-model="form.grant_from" />
                <InputError :message="form.errors.grant_from" />
              </div>
              <div v-if="form.origin == 4">
                <Label for="grant_date">Tanggal Hibah</Label>
                <Input id="grant_date" v-model="form.grant_date" type="date" />
                <InputError :message="form.errors.grant_date" />
              </div>
              <div v-if="form.origin == 5">
                <Label for="borrowed_from">Pinjam Dari</Label>
                <Input id="borrowed_from" v-model="form.borrowed_from" />
                <InputError :message="form.errors.borrowed_from" />
              </div>
              <div v-if="form.origin == 5">
                <Label for="borrowed_date">Tanggal Pinjam</Label>
                <Input id="borrowed_date" v-model="form.borrowed_date" type="date" />
                <InputError :message="form.errors.borrowed_date" />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
              <div>
                <Label for="birthdate">Tanggal Lahir</Label>
                <Input id="birthdate" v-model="form.birthdate" type="date" />
                <InputError :message="form.errors.birthdate" />
              </div>
              <div>
                <Label for="entry_date">Tanggal Masuk Kandang</Label>
                <Input type="date" id="entry_date" v-model="form.entry_date" :disabled="form.origin == 1" />
                <InputError :message="form.errors.entry_date" />
              </div>
              <div class="hidden">
                <!-- Intentionally hidden for purpose -->
                <Label for="tag_type">Jenis Tanda</Label>
                <Input id="tag_type" v-model="form.tag_type" type="text" />
                <InputError :message="form.errors.tag_type" />
              </div>
              <div>
                <Label for="birth_weight">Bobot Lahir (kg)</Label>
                <Input id="birth_weight" v-model="form.birth_weight" type="number" step="0.01" />
                <InputError :message="form.errors.birth_weight" />
              </div>
              <div>
                <Label for="weight">Bobot Sekarang (kg)</Label>
                <Input id="weight" v-model="form.weight" type="number" step="0.01" />
                <InputError :message="form.errors.weight" />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
              <div>
                <h3 class="text-primary dark:text-primary-foreground font-semibold mt-6">Data Induk Jantan</h3>
                <Label for="male_parent">Cari Induk Jantan</Label>
                <Combobox v-model="selectedMaleParent" v-model:search-term="maleParentQuery"
                  :display-value="(item: Livestock | null) => item ? `${item.aifarm_id} - ${item.name}` : ''">
                  <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                      <Button variant="outline" class="justify-between w-full">
                        {{
                          selectedMaleParent ?
                            `${selectedMaleParent.aifarm_id} - ${selectedMaleParent.name}` :
                            'Pilih Induk Jantan'
                        }}
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                      </Button>
                    </ComboboxTrigger>
                  </ComboboxAnchor>
                  <ComboboxList class="w-full">
                    <ComboboxInput v-model="maleParentQuery" placeholder="Cari Induk Jantan..." />
                    <ComboboxEmpty>Tidak ada hasil</ComboboxEmpty>
                    <ComboboxGroup>
                      <ComboboxItem v-for="option in maleParentOptions" :key="option.id" :value="option">
                        {{ option.aifarm_id }} - {{ option.name }}
                        <ComboboxItemIndicator>
                          <Check class="ml-auto h-4 w-4" />
                        </ComboboxItemIndicator>
                      </ComboboxItem>
                    </ComboboxGroup>
                  </ComboboxList>
                </Combobox>
                <InputError :message="form.errors.male_parent_id" />
                <div v-if="selectedMaleParent"
                  class="mt-2 p-4 rounded-lg border border-blue-300 dark:border-blue-600 bg-blue-50 dark:bg-blue-900/20 flex items-center justify-between">
                  <div>
                    <div class="font-semibold text-blue-800 dark:text-blue-200">Induk Jantan Terpilih</div>
                    <div><strong>ID Aifarm:</strong> {{ selectedMaleParent.aifarm_id }}</div>
                    <div><strong>ID Tag:</strong> {{ selectedMaleParent.tag_id }}</div>
                    <div><strong>Nama:</strong> {{ selectedMaleParent.name }}</div>
                    <div><strong>Ras:</strong> {{ selectedMaleParent.breed?.name }}</div>
                  </div>
                  <Button variant="ghost" size="icon" class="text-blue-600" @click="selectedMaleParent = null"
                    title="Hapus pilihan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </Button>
                </div>
              </div>
              <div>
                <h3 class="text-primary dark:text-primary-foreground font-semibold mt-6">Data Induk Betina</h3>
                <Label for="female_parent">Cari Induk Betina</Label>
                <Combobox v-model="selectedFemaleParent" v-model:search-term="femaleParentQuery"
                  :display-value="(item: Livestock | null) => item ? `${item.aifarm_id} - ${item.name}` : ''">
                  <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                      <Button variant="outline" class="justify-between w-full">
                        {{
                          selectedFemaleParent ?
                            `${selectedFemaleParent.aifarm_id} - ${selectedFemaleParent.name}` :
                            'Pilih Induk Betina'
                        }}
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                      </Button>
                    </ComboboxTrigger>
                  </ComboboxAnchor>
                  <ComboboxList class="w-full">
                    <ComboboxInput v-model="femaleParentQuery" placeholder="Cari Induk Betina..." />
                    <ComboboxEmpty>Tidak ada hasil</ComboboxEmpty>
                    <ComboboxGroup>
                      <ComboboxItem v-for="option in femaleParentOptions" :key="option.id" :value="option">
                        {{ option.aifarm_id }} - {{ option.name }}
                        <ComboboxItemIndicator>
                          <Check class="ml-auto h-4 w-4" />
                        </ComboboxItemIndicator>
                      </ComboboxItem>
                    </ComboboxGroup>
                  </ComboboxList>
                </Combobox>
                <InputError :message="form.errors.female_parent_id" />
                <div v-if="selectedFemaleParent"
                  class="mt-2 p-4 rounded-lg border border-pink-300 dark:border-pink-600 bg-pink-50 dark:bg-pink-900/20 flex items-center justify-between">
                  <div>
                    <div class="font-semibold text-pink-800 dark:text-pink-200">Induk Betina Terpilih</div>
                    <div><strong>ID Aifarm:</strong> {{ selectedFemaleParent.aifarm_id }}</div>
                    <div><strong>ID Tag:</strong> {{ selectedFemaleParent.tag_id }}</div>
                    <div><strong>Nama:</strong> {{ selectedFemaleParent.name }}</div>
                    <div><strong>Ras:</strong> {{ selectedFemaleParent.breed?.name }}</div>
                  </div>
                  <Button variant="ghost" size="icon" class="text-pink-600" @click="selectedFemaleParent = null"
                    title="Hapus pilihan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </Button>
                </div>
              </div>
            </div>
            <div class="flex justify-end mt-6">
              <Button type="submit" :disabled="form.processing">
                Simpan
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
