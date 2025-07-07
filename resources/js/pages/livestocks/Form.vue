<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted } from "vue";
import _, { map } from "underscore";

import { Check, Circle, Dot } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import LivestockUploader from "./LivestockUploader.vue";

const props = defineProps({
  livestock: {
    default: () => ({}),
  },
  species: {
    type: Array,
  },
  male_livestock: {
    type: String,
  },
  female_livestock: {
    type: String,
  },
});

const breeds = ref([]);
const male_livestocks = ref([]);
const male_livestock = ref(props.male_livestock);
const female_livestocks = ref([]);
const female_livestock = ref(props.female_livestock);

const form_1 = ref(null)

const form = useForm({
  name: props.livestock.name,
  origin: props.livestock.origin,
  status: props.livestock.status,
  speciesSelected: {},
  breed_id: props.livestock.breed_id,
  male_parent_id: props.livestock.male_parent_id,
  female_parent_id: props.livestock.female_parent_id,
  sex: props.livestock.sex,
  tag_type: props.livestock.tag_type,
  tag_id: props.livestock.tag_id,
  birthdate: props.livestock.birthdate,
  purchase_date: props.livestock.purchase_date,
  birth_weight: props.livestock.birth_weight,
  weight: props.livestock.weight,
  photo: [],
  barter_livestock_id: props.livestock.barter_livestock_id,
  barter_from: props.livestock.barter_from,
  barter_date: props.livestock.barter_date,
  purchase_price: props.livestock.purchase_price,
});

const submitStep1 = (nextStep) => {
  const options = {
    onSuccess: () => {
      nextStep();
    },
  };
  if (props.livestock.id) {
    form.put(route('livestocks.update', props.livestock.id), options);
  } else {
    form.post(route('livestocks.store'), options);
  }
};

const stepIndex = ref(1)
const steps = [
  { step: 1, title: 'Data Ternak', description: '' },
  { step: 2, title: 'Data induk Jantan', description: '' },
  { step: 3, title: 'Data induk Betina', description: '' },
]

const saveAction = () => {
  if (props.livestock.id) {
    form.put(route('livestocks.update', props.livestock.id));
  } else {
    form.post(route('livestocks.store'));
  }
};

async function fetchBreeds() {
  if (form.speciesSelected && form.speciesSelected.id) {
    const response = await fetch(
      `/api/species/${form.speciesSelected.id}/breeds`
    );
    breeds.value = await response.json();
  }
}

const filterParentlivestocks = _.debounce((query, sex) => {
  fetchLivestocks(query, sex);
}, 500);

const selectLivestock = (livestock, sex) => {
  if (sex === "M") {
    male_livestock.value = livestock.name;
    form.male_parent_id = livestock.id;
    male_livestocks.value = [];
  } else {
    female_livestock.value = livestock.name;
    form.female_parent_id = livestock.id;
    female_livestocks.value = [];
  }
};

async function fetchLivestocks(query, sex) {
  if (query.length > 2) {
    const response = await fetch(
      `/api/livestocks?query=${query}&sex=${sex}`
    );
    if (sex === "M") {
      male_livestocks.value = await response.json();
    } else {
      female_livestocks.value = await response.json();
    }
  }
}

onMounted(() => {
  if (props.livestock.breed) {
    form.speciesSelected = props.livestock.breed.species;
    fetchBreeds();
  }
});

function onSubmit(values) {
  console.log('submitted', values)
}

const meta = { valid: true }

</script>

<template>
  <AppLayout title="Form Ternak">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Form Ternak
      </h2>
    </template>

    <div class="py-2">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <Stepper v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }" v-model="stepIndex"
            class="block w-full">
            <div class="flex w-full flex-start gap-2">
              <StepperItem v-for="step in steps" :key="step.step" v-slot="{ state }"
                class="relative flex w-full flex-col items-center justify-center" :step="step.step">
                <StepperSeparator v-if="step.step !== steps[steps.length - 1].step"
                  class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />

                <StepperTrigger as-child>
                  <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'" size="icon"
                    class="z-10 size-6 rounded-full shrink-0"
                    :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                    :disabled="state !== 'completed' && !meta.valid">
                    <Check v-if="state === 'completed'" class="size-3" />
                    <Circle v-if="state === 'active'" />
                    <Dot v-if="state === 'inactive'" />
                  </Button>
                </StepperTrigger>

                <div class="mt-2 flex flex-col items-center text-center">
                  <StepperTitle :class="[state === 'active' && 'text-primary']"
                    class="text-sm font-semibold transition lg:text-base">
                    {{ step.title }}
                  </StepperTitle>
                  <StepperDescription :class="[state === 'active' && 'text-primary']"
                    class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm">
                    {{ step.description }}
                  </StepperDescription>
                </div>
              </StepperItem>
            </div>

            <div class="flex flex-col gap-4 mt-4">
              <template v-if="stepIndex === 1">

                <form ref="form_1" @submit.prevent="saveAction">
                  <div class="mt-6">
                    <LivestockUploader v-model="form.photo" />
                    <InputError :message="form.errors.photo" />
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                      <Label for="tag_id">ID Ternak</Label>
                      <Input id="tag_id" v-model="form.tag_id" type="text" />
                      <InputError :message="form.errors.tag_id" />
                    </div>
                    <div>
                      <Label for="name">Nama</Label>
                      <Input id="name" v-model="form.name" type="text" />
                      <InputError :message="form.errors.name" />
                    </div>
                    <div>
                      <Label for="status">Status</Label>
                      <Select v-model="form.status">
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="1">Aktif</SelectItem>
                            <SelectItem value="2">Terjual</SelectItem>
                            <SelectItem value="3">Mati</SelectItem>
                            <SelectItem value="4">Disembelih</SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError :message="form.errors.status" />
                    </div>
                    <div>
                      <Label for="species">Ternak</Label>
                      <Select v-model="form.speciesSelected" @update:modelValue="fetchBreeds">
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih ternak" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem v-for="specie in species" :key="specie.id" :value="specie">
                              {{ specie.name }}
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                    </div>
                    <div>
                      <Label for="breed">Ras</Label>
                      <Select v-model="form.breed_id">
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih ras" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem v-for="breed in breeds" :key="breed.id" :value="breed.id">
                              {{ breed.name }}
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError :message="form.errors.breed_id" />
                    </div>
                    <div>
                      <Label for="sex">Jenis Kelamin</Label>
                      <Select v-model="form.sex">
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih jenis kelamin" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="M">Jantan</SelectItem>
                            <SelectItem value="F">Betina</SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError :message="form.errors.sex" />
                    </div>
                  </div>
                  <div
                    :class="`grid grid-cols-1 md:grid-cols-2 ${form.origin == 4 ? 'lg:grid-cols-3' : 'lg:grid-cols-4'} gap-6 py-6`">
                    <div :class="(() => {
                      if (form.origin == 1) {
                        return 'col-span-4';
                      } else {
                        return 'col-span-1';
                      }
                    })()">
                      <Label for="origin">Asal</Label>
                      <Select v-model="form.origin">
                        <SelectTrigger>
                          <SelectValue placeholder="Pilih Asal" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="1">Kelahiran</SelectItem>
                            <SelectItem value="2">Pembelian</SelectItem>
                            <SelectItem value="3">Barter</SelectItem>
                            <SelectItem value="4">Hibah</SelectItem>
                            <SelectItem value="5">Peminjaman</SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError :message="form.errors.origin" />
                    </div>
                    <div v-if="form.origin == 2">
                      <Label for="purchase_date">Tanggal Pembelian</Label>
                      <Input id="purchase_date" v-model="form.purchase_date" type="date" />
                      <InputError :message="form.errors.purchase_date" />
                    </div>
                    <div v-if="form.origin == 2">
                      <!-- TODO -->
                      <Label for="">Beli Dari</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div v-if="form.origin == 2">
                      <Label for="purchase_price">Harga Beli</Label>
                      <Input id="purchase_price" v-model="form.purchase_price" type="number" />
                      <InputError :message="form.errors.purchase_price" />
                    </div>
                    <div v-if="form.origin == 3">
                      <!-- TODO -->
                      <Label for="">ID Ternak Barter</Label>
                      <Input id="" />
                      <InputError :message="''" />
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
                      <!-- TODO -->
                      <Label for="">Hibah Dari</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div v-if="form.origin == 4">
                      <!-- TODO -->
                      <Label for="">Tanggal Hibah</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div v-if="form.origin == 5">
                      <!-- TODO -->
                      <Label for="">Pinjam Dari</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div v-if="form.origin == 5">
                      <!-- TODO -->
                      <Label for="">Tanggal Pinjam</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                    <div class="hidden">
                      <!-- TODO -->
                      <Label for="male_parent">Pejantan</Label>
                      <Input id="male_parent" v-model="male_livestock" type="text"
                        @keyup="filterParentlivestocks($event.target.value, 'M')" />
                      <ul v-if="male_livestocks.length" class="border border-gray-300 rounded-md mt-1">
                        <li v-for="livestock in male_livestocks" :key="livestock.id"
                          @click="selectLivestock(livestock, 'M')" class="p-2 hover:bg-gray-200 cursor-pointer">
                          {{ livestock.name }}
                        </li>
                      </ul>
                      <InputError :message="form.errors.male_parent_id" />
                    </div>
                    <div class="hidden">
                      <!-- TODO -->
                      <Label for="female_parent">Induk</Label>
                      <Input id="female_parent" v-model="female_livestock" type="text"
                        @keyup="filterParentlivestocks($event.target.value, 'F')" />
                      <ul v-if="female_livestocks.length" class="border border-gray-300 rounded-md mt-1">
                        <li v-for="livestock in female_livestocks" :key="livestock.id"
                          @click="selectLivestock(livestock, 'F')" class="p-2 hover:bg-gray-200 cursor-pointer">
                          {{ livestock.name }}
                        </li>
                      </ul>
                      <InputError :message="form.errors.female_parent_id" />
                    </div>
                    <div>
                      <Label for="birthdate">Tanggal Lahir</Label>
                      <Input id="birthdate" v-model="form.birthdate" type="date" />
                      <InputError :message="form.errors.birthdate" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Tanggal Masuk Kandang</Label>
                      <Input type="date" id="" />
                      <InputError :message="''" />
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
                  <div class="flex justify-end mt-6">
                    <Button class="hidden" type="submit" :disabled="form.processing">
                      Simpan
                    </Button>
                  </div>
                </form>

              </template>

              <template v-if="stepIndex === 2">
                <!-- Todo page 2 -->
                <form ref="form_2">
                  <p class="font-semibold">Data Induk Jantan</p>
                  <p class="text-sm mb-6">Dengan mengisi informasi ini, kamu dapat melacak dan meningkatkan kualitas
                    keturunan serta mengoptimalkan hasil peternakan kambingmu.</p>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                    <div>
                      <!-- TODO -->
                      <Label for="">ID Ternak</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Aifarm ID</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Nama Induk Jantan</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Ras</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-6">
                    <div>
                      <!-- TODO -->
                      <Label for="">Nomer Identitas Kakek</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Nomer Identitas Nenek</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">No Buku Registrasi</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                  </div>
                  <div class="flex justify-end mt-6">
                    <Button class="hidden" type="submit" :disabled="form.processing">
                      Simpan
                    </Button>
                  </div>
                </form>
              </template>

              <template v-if="stepIndex === 3">
                <!-- Todo page 3 -->
                <form ref="form_3">
                  <p class="font-semibold">Data Induk Betina</p>
                  <p class="text-sm mb-6">Dengan mengisi informasi ini, kamu dapat melacak dan meningkatkan kualitas
                    keturunan serta mengoptimalkan hasil peternakan kambingmu.</p>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                    <div>
                      <!-- TODO -->
                      <Label for="">ID Ternak</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Aifarm ID</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Nama Induk Betina</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Ras</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-6">
                    <div>
                      <!-- TODO -->
                      <Label for="">Nomer Identitas Kakek</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">Nomer Identitas Nenek</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                    <div>
                      <!-- TODO -->
                      <Label for="">No Buku Registrasi</Label>
                      <Input id="" />
                      <InputError :message="''" />
                    </div>
                  </div>
                  <div class="flex justify-end mt-6">
                    <Button class="hidden" type="submit" :disabled="form.processing">
                      Simpan
                    </Button>
                  </div>
                </form>
              </template>
            </div>

            <div class="flex items-center justify-between mt-4">
              <Button :disabled="isPrevDisabled" variant="outline" @click="prevStep()">
                Kembali
              </Button>
              <div class="flex items-center gap-3">

                <Button v-if="stepIndex === 1" type="button" :disabled="isNextDisabled || form.processing"
                  @click="() => { submitStep1(nextStep); }">
                  Lanjut
                </Button>
                <Button v-if="stepIndex === 2" :type="meta.valid ? 'button' : 'submit'"
                  :disabled="isSelanjutnyaDisabled" @click="meta.valid && nextStep()">
                  Lanjut
                </Button>
                <Button v-if="stepIndex === 3" type="submit">
                  Selesai
                </Button>
              </div>
            </div>
          </Stepper>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
