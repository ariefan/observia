<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Edit2, Trash2, Filter } from 'lucide-vue-next';
import { ref } from 'vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

interface Species {
  id: string;
  name: string;
}

interface Breed {
  id: string;
  name: string;
  code: string;
  origin: string;
  description: string;
  species: Species;
  livestocks_count: number;
  created_at: string;
}

interface Props {
  breeds: {
    data: Breed[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  species: Species[];
  search?: string;
  speciesFilter?: string;
}

const props = defineProps<Props>();

const search = ref(props.search || '');
const speciesFilter = ref(props.speciesFilter || '');

const performSearch = () => {
  router.get(route('breeds.index'), { 
    search: search.value,
    species_id: speciesFilter.value || undefined
  }, { 
    preserveState: true,
    replace: true 
  });
};

const clearFilters = () => {
  search.value = '';
  speciesFilter.value = '';
  router.get(route('breeds.index'));
};

const deleteBreed = (breed: Breed) => {
  if (breed.livestocks_count > 0) {
    alert(`Tidak dapat menghapus ras. ${breed.livestocks_count} catatan ternak menggunakan ras ini.`);
    return;
  }
  
  if (confirm(`Apakah Anda yakin ingin menghapus ${breed.name}?`)) {
    router.delete(route('breeds.destroy', breed.id));
  }
};
</script>

<template>
  <Head title="Manajemen Ras" />

  <AppHeaderLayout>
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Manajemen Ras</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Kelola ras dan lihat statistik penggunaan ternak
        </p>
      </div>
      <Link :href="route('breeds.create')">
        <Button>
          <Plus class="h-4 w-4 mr-2" />
          Tambah Ras
        </Button>
      </Link>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex gap-4 items-end">
          <div class="relative flex-1 max-w-md">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
            <Input
              v-model="search"
              placeholder="Cari ras..."
              class="pl-10"
              @keyup.enter="performSearch"
            />
          </div>
          
          <div class="min-w-[200px]">
            <Select v-model="speciesFilter">
              <SelectTrigger>
                <SelectValue placeholder="Filter berdasarkan spesies" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">Semua Spesies</SelectItem>
                <SelectItem v-for="species in props.species" :key="species.id" :value="species.id">
                  {{ species.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <Button @click="performSearch">
            <Filter class="h-4 w-4 mr-2" />
            Filter
          </Button>
          
          <Button variant="outline" @click="clearFilters">
            Bersihkan
          </Button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nama</TableHead>
              <TableHead>Kode</TableHead>
              <TableHead>Spesies</TableHead>
              <TableHead>Asal</TableHead>
              <TableHead>Jumlah Ternak</TableHead>
              <TableHead>Dibuat</TableHead>
              <TableHead class="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="breed in props.breeds.data" :key="breed.id">
              <TableCell class="font-medium">{{ breed.name }}</TableCell>
              <TableCell>
                <Badge variant="outline">{{ breed.code }}</Badge>
              </TableCell>
              <TableCell>{{ breed.species.name }}</TableCell>
              <TableCell>{{ breed.origin }}</TableCell>
              <TableCell>
                <Badge :variant="breed.livestocks_count > 0 ? 'default' : 'secondary'">
                  {{ breed.livestocks_count }} ternak
                </Badge>
              </TableCell>
              <TableCell class="text-gray-500">
                {{ new Date(breed.created_at).toLocaleDateString() }}
              </TableCell>
              <TableCell class="text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('breeds.edit', breed.id)">
                    <Button variant="ghost" size="sm">
                      <Edit2 class="h-4 w-4" />
                    </Button>
                  </Link>
                  <Button 
                    variant="ghost" 
                    size="sm"
                    @click="deleteBreed(breed)"
                    :disabled="breed.livestocks_count > 0"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="props.breeds.data.length === 0" class="text-center py-12">
        <p class="text-gray-500 dark:text-gray-400">Tidak ada ras ditemukan.</p>
      </div>

      <!-- Pagination -->
      <div v-if="props.breeds.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ ((props.breeds.current_page - 1) * props.breeds.per_page) + 1 }} sampai 
            {{ Math.min(props.breeds.current_page * props.breeds.per_page, props.breeds.total) }} 
            dari {{ props.breeds.total }} hasil
          </div>
          <div class="flex gap-1">
            <Link 
              v-for="link in props.breeds.links" 
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1 text-sm rounded',
                link.active 
                  ? 'bg-primary text-primary-foreground' 
                  : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AppHeaderLayout>
</template>