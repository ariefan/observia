<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted } from "vue";
import _, { map } from "underscore";

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
        <div
          class="mb-10 rounded-lg border border-gray-200 bg-white p-6 text-gray-500 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="grid grid-cols-2 md:grid-cols-2">
            <ul class="max-w-md list-inside list-disc space-y-1 dark:text-gray-400">
              <li>
                Format foto
                <strong class="font-semibold text-gray-900 dark:text-white">.jpeg</strong>,
                <strong class="font-semibold text-gray-900 dark:text-white">.jpg</strong>, dan
                <strong class="font-semibold text-gray-900 dark:text-white">.png</strong>. Ukuran maksimal
                <strong class="font-semibold text-gray-900 dark:text-white">2 MB</strong>
              </li>
              <li>
                Pilih foto landscape yang jelas, simetris, dan dapat
                diidentifikasi
              </li>
              <li>
                Pastikan hanya ada
                <strong class="font-semibold text-gray-900 dark:text-white">1 ternak</strong>
                di dalam tiap foto
              </li>
              <li>Anda dapat mengupload/unggah maksimal 5 foto</li>
              <li>Contoh gambar seperti disamping</li>
            </ul>
            <img class="h-52 w-1/2 object-cover"
              src="https://media.4-paws.org/a/4/8/b/a48b18270b120e60e9bd4783e9106940a2808acd/VIER%20PFOTEN_2013-07-19_024-3851x2665-1920x1329.jpg"
              alt="Goat" />
          </div>
        </div>

        <div v-if="livestock.photo.length > 0" id="indicators-carousel" class="relative mb-8 w-full"
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

        <p class="text-lg text-gray-700 dark:text-white">
          {{ livestock.id ? "Edit" : "Tambah" }} Ternak
        </p>
        <div class="container mx-auto p-4">
          <form class="space-y-4" @submit.prevent="saveAction">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div class="col-span-1">
                <label for="name" :class="form.errors.name
                  ? `block text-sm font-medium text-red-700 dark:text-red-500`
                  : `block text-sm font-medium text-gray-700`
                  ">Nama</label>
                <input type="text" id="name" v-model="form.name" :class="form.errors.name
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  " />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.name }}
                </p>
              </div>
              <div class="col-span-1">
                <label for="origin" class="block text-sm font-medium text-gray-700">Asal ternak</label>
                <select id="origin" v-model="form.origin"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                  <option value="0">Lahir di kandang</option>
                  <option value="1">Lahir di klinik</option>
                  <option value="2">Beli</option>
                  <option value="3">Hibah</option>
                  <option value="4">Barter</option>
                </select>
              </div>
              <div class="col-span-1">
                <label for="status" class="block text-sm font-medium text-gray-700">Status ternak</label>
                <select id="status" v-model="form.status"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                  <option value="1">Hidup</option>
                  <option value="0">Mati</option>
                  <option value="2">Keluar kandang</option>
                  <option value="3">Bunting</option>
                </select>
              </div>
              <div class="col-span-1">
                <label for="type" class="block text-sm font-medium text-gray-700">Jenis ternak</label>
                <select id="species" v-model="form.speciesSelected" @change="fetchBreeds"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                  <!-- Options go here -->
                  <option v-for="spc in species" :key="spc.id" :value="spc">
                    {{ spc.name }}
                  </option>
                </select>
              </div>
              <div class="col-span-1">
                <label for="breed" :class="form.errors.breed_id
                  ? `block text-sm font-medium text-red-700 dark:text-red-500`
                  : `block text-sm font-medium text-gray-700`
                  ">Jenis kambing/ Jenis domba</label>
                <select id="breed" v-model="form.breedSelected" :class="form.errors.breed_id
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  ">
                  <!-- Options go here -->
                  <option v-for="breed in breeds" :key="breed.id" :value="breed">
                    {{ breed.name }}
                  </option>
                </select>
                <p v-if="form.errors.breed_id" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.breed_id }}
                </p>
              </div>
              <div class="col-span-1">
                <label for="gender" class="block text-sm font-medium text-gray-700">Jenis kelamin</label>
                <select id="gender" v-model="form.sex"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                  <option value="F">Betina</option>
                  <option value="M">Jantan</option>
                </select>
              </div>
              <div class="col-span-1 hidden">
                <label for="tag-type" :class="form.errors.tag_type
                  ? `block text-sm font-medium text-red-700 dark:text-red-500`
                  : `block text-sm font-medium text-gray-700`
                  ">Jenis tag</label>
                <input type="text" id="tag-type" v-model="form.tag_type" :class="form.errors.breed_id
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  " />
                <p v-if="form.errors.tag_type" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.tag_type }}
                </p>
              </div>
              <div class="col-span-1">
                <label for="tag-number" class="block text-sm font-medium text-gray-700">ID Ternak (Eartag)</label>
                <input type="text" v-model="form.tag_id" id="tag-number" :class="form.errors.tag_type
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  " />
                <p v-if="form.errors.tag_type" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.breed_id }}
                </p>
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
                <input type="date" v-model="form.purchase_date" id="entry-date" :class="form.errors.purchase_date
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
                <input type="number" v-model="form.birth_weight" id="birth-weight" :class="form.errors.purchase_date
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  " />
                <p v-if="form.errors.birth_weight" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.birth_weight }}
                </p>
              </div>
              <div class="col-span-1">
                <label for="current-weight" :class="form.errors.weight
                  ? `block text-sm font-medium text-red-700 dark:text-red-500`
                  : `block text-sm font-medium text-gray-700`
                  ">Bobot sekarang (Kg)</label>
                <input type="number" v-model="form.weight" id="current-weight" :class="form.errors.weight
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  " />
                <p v-if="form.errors.weight" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.weight }}
                </p>
              </div>

              <div class="col-span-1">
                <label for="parent_male" :class="form.errors.male_parent_id
                  ? `block text-sm font-medium text-red-700 dark:text-red-500`
                  : `block text-sm font-medium text-gray-700`
                  ">Induk Jantan</label>
                <input type="text" v-model="male_livestock" @input="filterParentlivestocks(male_livestock, 'M')" :class="form.errors.male_parent_id
                  ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                  : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                  " required />
                <div v-if="male_livestocks.length > 0"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white">
                  <ul>
                    <li v-for="(male_livestock, index) in male_livestocks" @click="selectLivestock(male_livestock, 'M')"
                      class="cursor-pointer p-2 hover:bg-gray-100">
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
                <input type="text" v-model="female_livestock" @input="filterParentlivestocks(female_livestock, 'F')"
                  :class="form.errors.female_parent_id
                    ? `block w-full rounded-lg border border-red-500 bg-red-50 p-2.5 text-sm text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-gray-700 dark:text-red-500 dark:placeholder-red-500`
                    : `mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm`
                    " required />
                <div v-if="female_livestocks.length > 0"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white">
                  <ul>
                    <li v-for="(female_livestock, index) in female_livestocks"
                      @click="selectLivestock(female_livestock, 'F')" class="cursor-pointer p-2 hover:bg-gray-100">
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
                <input
                  class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:placeholder-gray-400"
                  id="multiple_files" type="file" @input="form.photo = $event.target.files" accept="image/*" multiple />
                <p v-if="form.errors.photo" class="mt-2 text-sm text-red-600 dark:text-red-500">
                  {{ form.errors.photo }}
                </p>
                <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                  {{ form.progress.percentage }}%
                </progress>
              </div>
            </div>
            <div class="flex justify-end space-x-4">
              <Link :href="route('livestocks.index')"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
              Kembali</Link>
              <button type="submit" :disabled="form.processing"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
