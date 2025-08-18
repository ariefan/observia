<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Edit2, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Species {
  id: string;
  name: string;
  code: string;
  binomial_nomenclature: string;
  breeds_count: number;
  livestocks_count: number;
  created_at: string;
}

interface Props {
  species: {
    data: Species[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  search?: string;
}

const props = defineProps<Props>();

const search = ref(props.search || '');
let searchTimeout: NodeJS.Timeout | null = null;

const performSearch = () => {
  router.get(route('species.index'), { search: search.value }, { 
    preserveState: true,
    replace: true 
  });
};

const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(performSearch, 500);
};

// Watch for changes in search input
watch(search, debouncedSearch);

const deleteSpecies = (species: Species) => {
  if (species.livestocks_count > 0) {
    alert(`Tidak dapat menghapus spesies. ${species.livestocks_count} catatan ternak menggunakan spesies ini.`);
    return;
  }
  
  if (confirm(`Apakah Anda yakin ingin menghapus ${species.name}?`)) {
    router.delete(route('species.destroy', species.id));
  }
};
</script>

<template>
  <Head title="Manajemen Spesies" />

  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Manajemen Spesies
      </h2>
    </template>

    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <SecondSidebar current-route="species.index" />

      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <Card class="min-h-[600px]">
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Manajemen Spesies</CardTitle>
                <CardDescription>
                  Kelola spesies dan lihat statistik penggunaan ternak
                </CardDescription>
              </div>
              <Link :href="route('species.create')">
                <Button>
                  <Plus class="h-4 w-4 mr-2" />
                  Tambah Spesies
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent class="space-y-2">
            <div class="relative max-w-md mb-4">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <Input
                v-model="search"
                placeholder="Cari spesies..."
                class="pl-10"
              />
            </div>

            <div class="relative overflow-x-auto sm:rounded-lg">
              <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nama</TableHead>
              <TableHead>Kode</TableHead>
              <TableHead>Nama Ilmiah</TableHead>
              <TableHead>Ras</TableHead>
              <TableHead>Jumlah Ternak</TableHead>
              <TableHead>Dibuat</TableHead>
              <TableHead class="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="species in props.species.data" :key="species.id">
              <TableCell class="font-medium">{{ species.name }}</TableCell>
              <TableCell>
                <Badge variant="outline">{{ species.code }}</Badge>
              </TableCell>
              <TableCell class="italic">{{ species.binomial_nomenclature }}</TableCell>
              <TableCell>{{ species.breeds_count }} ras</TableCell>
              <TableCell>
                <Badge :variant="species.livestocks_count > 0 ? 'default' : 'secondary'">
                  {{ species.livestocks_count }} ternak
                </Badge>
              </TableCell>
              <TableCell class="text-gray-500">
                {{ new Date(species.created_at).toLocaleDateString() }}
              </TableCell>
              <TableCell class="text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('species.edit', species.id)">
                    <Button variant="ghost" size="sm">
                      <Edit2 class="h-4 w-4" />
                    </Button>
                  </Link>
                  <Button 
                    variant="ghost" 
                    size="sm"
                    @click="deleteSpecies(species)"
                    :disabled="species.livestocks_count > 0"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
              </Table>
            </div>

            <div v-if="props.species.data.length === 0" class="text-center py-12">
              <p class="text-gray-500 dark:text-gray-400">Tidak ada spesies ditemukan.</p>
            </div>

            <!-- Pagination -->
            <div v-if="props.species.last_page > 1" class="mt-4">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  Menampilkan {{ ((props.species.current_page - 1) * props.species.per_page) + 1 }} sampai 
                  {{ Math.min(props.species.current_page * props.species.per_page, props.species.total) }} 
                  dari {{ props.species.total }} hasil
                </div>
                <div class="flex gap-1">
                  <Link 
                    v-for="link in props.species.links" 
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
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>