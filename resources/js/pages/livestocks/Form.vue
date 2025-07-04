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
    type: Object,
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

const form = useForm({
  name: props.livestock.name,
  origin: props.livestock.origin,
  status: props.livestock.status,
  speciesSelected: {},
  breedSelected: {},
  maleParentSelected: {},
  femaleParentSelected: {},
  sex: props.livestock.sex,
  tag_type: props.livestock.tag_type,
  tag_id: props.livestock.tag_id,
  birthdate: props.livestock.birthdate,
  purchase_date: props.livestock.purchase_date,
  birth_weight: props.livestock.birth_weight,
  weight: props.livestock.weight,
  photo: [],
});

const saveAction = () => {
  if (props.livestock.id) {
    form
      .transform((data) => ({
        ...data,
        breed_id: form.breedSelected.id,
        male_parent_id: form.maleParentSelected.id,
        female_parent_id: form.femaleParentSelected.id,
        _method: "put",
      }))
      .post(`/livestocks/${props.livestock.id}`, {
        preserveScroll: true,
      });
  } else {
    form
      .transform((data) => ({
        ...data,
        breed_id: form.breedSelected.id,
        male_parent_id: form.maleParentSelected.id,
        female_parent_id: form.femaleParentSelected.id,
      }))
      .post(route("livestocks.store"), {
        preserveScroll: true,
      });
  }
};

async function fetchBreeds() {
  fetch(route("breed.get-by-species", { species_id: form.speciesSelected.id }))
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok.");
      }
      return response.json();
    })
    .then((data) => {
      breeds.value = data.data;
      form.breedSelected = data.data[0];
    })
    .catch((error) => {
      return error;
    });
}

const filterParentlivestocks = _.debounce((query, sex) => {
  fetchLivestocks(query, sex);
}, 500);

const selectLivestock = (livestock, sex) => {
  if (sex === "M") {
    form.maleParentSelected = livestock;
    male_livestock.value = livestock.name;
  } else {
    form.femaleParentSelected = livestock;
    female_livestock.value = livestock.name;
  }

  male_livestocks.value = [];
  female_livestocks.value = [];
};

async function fetchLivestocks(query, sex) {
  const queryparams = {
    q: query,
    sex: sex,
    type: "all",
  };

  fetch(route("livestocks.search", queryparams))
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok.");
      }
      return response.json();
    })
    .then((data) => {
      if (queryparams.sex === "M") {
        male_livestocks.value = data.data;
      } else {
        female_livestocks.value = data.data;
      }
    })
    .catch((error) => {
      return error;
    });
}

onMounted(() => {
  form.sex = props.livestock.sex ??= "M";
  form.origin = props.livestock.origin ??= "0";
  form.status = props.livestock.status ??= "1";

  if (props.livestock.id) {
    breeds.value.push({
      id: props.livestock.breed.id,
      name: props.livestock.breed.name,
    });
    form.breedSelected = {
      id: props.livestock.breed.id,
      name: props.livestock.breed.name,
    };
    form.speciesSelected = {
      id: props.livestock.breed.species.id,
      name: props.livestock.breed.species.name,
    };
  }
});











const stepIndex = ref(1)
const steps = [
  {
    step: 1,
    title: 'Data Ternak',
  },
  {
    step: 2,
    title: 'Data Induk Jantan',
  },
  {
    step: 3,
    title: 'Data Induk Betina',
  },
]

function onSubmit(values) {
  toast({
    title: 'You submitted the following values:',
    description: h('pre', { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' }, h('code', { class: 'text-white' }, JSON.stringify(values, null, 2))),
  })
}

const meta = { valid: true }

</script>

<template>
  <AppLayout title="Tambah Populasi">
    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-10">
        <p class="text-3xl text-gray-700 dark:text-white">
          {{ livestock.id ? "Edit" : "Tambah" }} Ternak
        </p>
        <p class="mb-6 text-gray-400">
          Tambahkan informasi mengenai ternak anda dengan lengkap. Bantu kami
          untuk lebih mudah dalam mengelola ternak anda.
        </p>







        <Stepper v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }" v-model="stepIndex"
          class="block w-full">
          <form @submit="(e) => {
            e.preventDefault()
            validate()

            if (stepIndex === steps.length && meta.valid) {
              onSubmit(values)
            }
          }">
            <div class="flex w-full flex-start gap-2">
              <StepperItem v-for="step in steps" :key="step.step" v-slot="{ state }"
                class="relative flex w-full flex-col items-center justify-center" :step="step.step">
                <StepperSeparator v-if="step.step !== steps[steps.length - 1].step"
                  class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />

                <StepperTrigger as-child>
                  <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'" size="icon"
                    class="z-10 rounded-full shrink-0"
                    :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                    :disabled="state !== 'completed' && !meta.valid">
                    <Check v-if="state === 'completed'" class="size-5" />
                    <Circle v-if="state === 'active'" />
                    <Dot v-if="state === 'inactive'" />
                  </Button>
                </StepperTrigger>

                <div class="mt-5 flex flex-col items-center text-center">
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
                <form class="space-y-4" @submit.prevent="saveAction">

                  <LivestockUploader />


                  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Nama</Label>
                      <Input id="name" v-model="form.name" placeholder="Nama ternak" />
                      <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Asal Ternak</Label>
                      <Select v-model="form.origin">
                        <SelectTrigger class="max-w-sm">
                          <SelectValue placeholder="Pilih..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="born_in_farm">
                              Lahir di kandang
                            </SelectItem>
                            <SelectItem value="buy">
                              Beli
                            </SelectItem>
                            <SelectItem value="grant">
                              Hibah
                            </SelectItem>
                            <SelectItem value="barter">
                              Barter
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError class="mt-2" :message="form.errors.origin" />
                    </div>

                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Asal Ternak</Label>
                      <Select v-model="form.status">
                        <SelectTrigger class="max-w-sm">
                          <SelectValue placeholder="Pilih..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="alive">
                              Hidup
                            </SelectItem>
                            <SelectItem value="dead">
                              Mati
                            </SelectItem>
                            <SelectItem value="out_of_farm">
                              Keluar kandang
                            </SelectItem>
                            <SelectItem value="pregnant">
                              Bunting
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError class="mt-2" :message="form.errors.status" />
                    </div>



                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Jenis ternak</Label>
                      <Select id="species" v-model="form.speciesSelected" @change="fetchBreeds">
                        <SelectTrigger class="max-w-sm">
                          <SelectValue placeholder="Pilih..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem v-for="spc in species" :key="spc.id" :value="spc">
                              {{ spc.name }}
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError class="mt-2" :message="form.errors.speciesSelected" />
                    </div>

                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Breed</Label>
                      <Select id="species" v-model="form.breedSelected">
                        <SelectTrigger class="max-w-sm">
                          <SelectValue placeholder="Pilih..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem v-for="breed in breeds" :key="breed.id" :value="breed">
                              {{ breed.name }}
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError class="mt-2" :message="form.errors.breedSelected" />
                    </div>

                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Jenis kelamin</Label>
                      <Select id="species" v-model="form.sex">
                        <SelectTrigger class="max-w-sm">
                          <SelectValue placeholder="Pilih..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="female">
                              Betina
                            </SelectItem>
                            <SelectItem value="male">
                              Jantan
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError class="mt-2" :message="form.errors.sex" />
                    </div>

                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="name">Jenis kelamin</Label>
                      <Select id="species" v-model="form.sex">
                        <SelectTrigger class="max-w-sm">
                          <SelectValue placeholder="Pilih..." />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="female">
                              Betina
                            </SelectItem>
                            <SelectItem value="male">
                              Jantan
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                      <InputError class="mt-2" :message="form.errors.sex" />
                    </div>


                    <div class="grid w-full max-w-sm items-center gap-1.5">
                      <Label for="tag_id">ID Ternak (Eartag)</Label>
                      <Input id="tag_id" v-model="form.tag_id" placeholder="ID Ternak (Eartag)" />
                      <InputError class="mt-2" :message="form.errors.tag_id" />
                    </div>

                    <div class="col-span-1">
                      <label for="birthdate" :class="form.errors.birthdate
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        ">Tanggal lahir</label>
                      <input type="date" v-model="form.birthdate" id="birthdate" :class="form.errors.birthdate
                        ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                        : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                        " />
                      <p v-if="form.errors.birthdate" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.birthdate }}
                      </p>
                    </div>
                    <div class="col-span-1">
                      <label for="entry-date" :class="form.errors.purchase_date
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        ">Tanggal masuk</label>
                      <Input type="date" v-model="form.purchase_date" id="entry-date" :class="form.errors.purchase_date
                        ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                        : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                        " />
                      <p v-if="form.errors.purchase_date" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.purchase_date }}
                      </p>
                    </div>
                    <div class="col-span-1">
                      <label for="birth-weight" :class="form.errors.birth_weight
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        ">Bobot lahir (Kg)</label>
                      <Input type="number" v-model="form.birth_weight" id="birth-weight" />
                      <p v-if="form.errors.birth_weight" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.birth_weight }}
                      </p>
                    </div>
                    <div class="col-span-1">
                      <label for="current-weight" :class="form.errors.weight
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        ">Bobot sekarang (Kg)</label>
                      <Input type="number" v-model="form.weight" id="current-weight" />
                      <p v-if="form.errors.weight" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.weight }}
                      </p>
                    </div>

                    <div class="col-span-1">
                      <label for="parent_male" :class="form.errors.male_parent_id
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        ">Induk Jantan</label>
                      <Input type="text" v-model="male_livestock" @input="filterParentlivestocks(male_livestock, 'M')"
                        required />
                      <div v-if="male_livestocks.length > 0"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white">
                        <ul>
                          <li v-for="(male_livestock, index) in male_livestocks"
                            @click="selectLivestock(male_livestock, 'M')" class="cursor-pointer p-2 hover:bg-gray-100">
                            {{ male_livestock.tag_id }} - {{ male_livestock.name }}
                          </li>
                        </ul>
                      </div>
                      <p v-if="form.errors.male_parent_id" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.male_parent_id }}
                      </p>
                    </div>

                    <div class="col-span-1">
                      <label for="parent_male" :class="form.errors.male_parent_id
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        ">Induk Betina</label>
                      <Input type="text" v-model="female_livestock"
                        @input="filterParentlivestocks(female_livestock, 'F')" required />
                      <div v-if="female_livestocks.length > 0"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white">
                        <ul>
                          <li v-for="(female_livestock, index) in female_livestocks"
                            @click="selectLivestock(female_livestock, 'F')"
                            class="cursor-pointer p-2 hover:bg-gray-100">
                            {{ female_livestock.tag_id }} - {{ female_livestock.name }}
                          </li>
                        </ul>
                      </div>
                      <p v-if="form.errors.female_parent_id" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.female_parent_id }}
                      </p>
                    </div>

                    <div class="col-span-1">
                      <label :class="form.errors.photo
                        ? `block text-sm font-medium text-red-700 dark:text-red-500`
                        : `block text-sm font-medium text-gray-700`
                        " for="multiple_files">Pilih foto/gambar ternak</label>
                      <Input id="multiple_files" type="file" @input="form.photo = $event.target.files" accept="image/*"
                        multiple />
                      <p v-if="form.errors.photo" class="mt-2 text-sm text-red-600 dark:text-red-500">
                        {{ form.errors.photo }}
                      </p>
                      <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                        {{ form.progress.percentage }}%
                      </progress>
                    </div>
                  </div>
                  <!-- <div class="flex justify-end space-x-4">
                    <Link :href="route('livestocks.index')"
                      class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Kembali</Link>
                    <button type="submit" :disabled="form.processing"
                      class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                      Simpan
                    </button>
                  </div> -->
                </form>
              </template>

              <template v-if="stepIndex === 2">
                page 2
              </template>

              <template v-if="stepIndex === 3">
                page 3
              </template>
            </div>

            <div class="flex items-center justify-between mt-4">
              <Button :disabled="isPrevDisabled" variant="outline" size="sm" @click="prevStep()">
                Back
              </Button>
              <div class="flex items-center gap-3">
                <Button v-if="stepIndex !== 3" :type="meta.valid ? 'button' : 'submit'" :disabled="isNextDisabled"
                  size="sm" @click="meta.valid && nextStep()">
                  Next
                </Button>
                <Button v-if="stepIndex === 3" size="sm" type="submit">
                  Submit
                </Button>
              </div>
            </div>
          </form>
        </Stepper>







        <div v-if="livestock.photo.length > 0" id="indicators-carousel" class="relative mb-2 w-full"
          data-carousel="static">
          <!-- Carousel wrapper -->
          <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
            <!-- Item 1 -->
            <div v-for="(photo, index) in livestock.photo" :key="index" class="hidden duration-700 ease-in-out"
              data-carousel-item="active">
              <img :src="photo" class="absolute left-1/2 top-1/2 block w-full -translate-x-1/2 -translate-y-1/2"
                alt="..." />
            </div>
          </div>
          <!-- Slider controls -->
          <button type="button"
            class="group absolute start-0 top-0 z-30 flex h-full cursor-pointer items-center justify-center px-4 focus:outline-none"
            data-carousel-prev>
            <span
              class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/30 group-hover:bg-white/50 group-focus:outline-none group-focus:ring-4 group-focus:ring-white dark:bg-gray-800/30 dark:group-hover:bg-gray-800/60 dark:group-focus:ring-gray-800/70">
              <svg class="h-4 w-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 1 1 5l4 4" />
              </svg>
              <span class="sr-only">Previous</span>
            </span>
          </button>
          <button type="button"
            class="group absolute end-0 top-0 z-30 flex h-full cursor-pointer items-center justify-center px-4 focus:outline-none"
            data-carousel-next>
            <span
              class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/30 group-hover:bg-white/50 group-focus:outline-none group-focus:ring-4 group-focus:ring-white dark:bg-gray-800/30 dark:group-hover:bg-gray-800/60 dark:group-focus:ring-gray-800/70">
              <svg class="h-4 w-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m1 9 4-4-4-4" />
              </svg>
              <span class="sr-only">Next</span>
            </span>
          </button>
        </div>

        <div class="container mx-auto p-4">

        </div>
      </div>
    </div>
  </AppLayout>
</template>
