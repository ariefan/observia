<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Search, Plus, Eye, Edit, Trash } from 'lucide-vue-next';

interface LivestockEnding {
  id: number;
  ending_date: string;
  ending_status: string;
  status_label: string;
  price?: number;
  buyer_name?: string;
  receiving_farm_name?: string;
  livestock: {
    id: string;
    name: string;
    aifarm_id: string;
    tag_id: string;
  };
  recorded_by: {
    name: string;
  };
}

interface Props {
  endings: {
    data: LivestockEnding[];
    links: any[];
  };
  statusOptions: Record<string, string>;
  filters: {
    status?: string;
    search?: string;
  };
}

const props = defineProps<Props>();

const filters = ref({
  status: props.filters.status || 'all',
  search: props.filters.search || '',
});

const search = () => {
  const queryParams = { ...filters.value };
  
  // Convert "all" to empty string for backend
  if (queryParams.status === 'all') {
    queryParams.status = '';
  }
  
  router.get(route('livestock-endings.index'), queryParams, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  filters.value = { status: 'all', search: '' };
  search();
};

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'sold':
      return 'default';
    case 'gifted':
      return 'secondary';
    case 'loaned':
      return 'outline';
    case 'died':
      return 'destructive';
    case 'slaughtered':
      return 'destructive';
    default:
      return 'secondary';
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID');
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount);
};

const deleteEnding = (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus data pengakhiran ini? Ternak akan diaktifkan kembali.')) {
    router.delete(route('livestock-endings.destroy', id));
  }
};
</script>

<template>
  <Head title="Riwayat Pengakhiran Ternak" />

  <AppLayout>
    <div class="max-w-7xl mx-auto p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold">Riwayat Pengakhiran Ternak</h1>
          <p class="text-muted-foreground">Kelola riwayat ternak yang sudah tidak aktif</p>
        </div>
        <Link :href="route('livestock-endings.create')">
          <Button class="gap-2">
            <Plus class="w-4 h-4" />
            Catat Pengakhiran
          </Button>
        </Link>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardHeader>
          <CardTitle>Filter</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="space-y-2">
              <label class="text-sm font-medium">Cari</label>
              <div class="relative">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="filters.search"
                  placeholder="Cari nama, ID AiFarm, atau tag..."
                  class="pl-8"
                  @keyup.enter="search"
                />
              </div>
            </div>

            <!-- Status Filter -->
            <div class="space-y-2">
              <label class="text-sm font-medium">Status</label>
              <Select v-model="filters.status">
                <SelectTrigger>
                  <SelectValue placeholder="Semua status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua status</SelectItem>
                  <SelectItem v-for="(label, value) in statusOptions" :key="value" :value="value">
                    {{ label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end space-x-2">
              <Button @click="search" class="flex-1">
                Cari
              </Button>
              <Button @click="clearFilters" variant="outline">
                Reset
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Results -->
      <Card>
        <CardHeader>
          <CardTitle>
            Riwayat Pengakhiran
            <span class="text-sm font-normal text-muted-foreground ml-2">
              ({{ endings.data?.length || 0 }} catatan)
            </span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Ternak</TableHead>
                  <TableHead>Tanggal</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Detail</TableHead>
                  <TableHead>Dicatat Oleh</TableHead>
                  <TableHead class="w-24">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="ending in endings.data" :key="ending.id">
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ ending.livestock.name }}</div>
                      <div class="text-sm text-muted-foreground">
                        {{ ending.livestock.aifarm_id }} | {{ ending.livestock.tag_id }}
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    {{ formatDate(ending.ending_date) }}
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getStatusBadgeVariant(ending.ending_status)">
                      {{ ending.status_label }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm">
                      <div v-if="ending.price">
                        Harga: {{ formatCurrency(ending.price) }}
                      </div>
                      <div v-if="ending.buyer_name">
                        Pembeli: {{ ending.buyer_name }}
                      </div>
                      <div v-if="ending.receiving_farm_name">
                        Ke: {{ ending.receiving_farm_name }}
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm">{{ ending.recorded_by.name }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-1">
                      <Link :href="route('livestock-endings.show', ending.id)">
                        <Button size="sm" variant="outline" class="w-8 h-8 p-0">
                          <Eye class="w-4 h-4" />
                        </Button>
                      </Link>
                      <Link :href="route('livestock-endings.edit', ending.id)">
                        <Button size="sm" variant="outline" class="w-8 h-8 p-0">
                          <Edit class="w-4 h-4" />
                        </Button>
                      </Link>
                      <Button 
                        size="sm" 
                        variant="outline" 
                        class="w-8 h-8 p-0 text-red-600 hover:text-red-700"
                        @click="deleteEnding(ending.id)"
                      >
                        <Trash class="w-4 h-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="!endings.data || endings.data.length === 0">
                  <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                    Belum ada data pengakhiran ternak
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div v-if="endings.links && endings.links.length > 3" class="mt-4 flex justify-center">
            <div class="flex space-x-1">
              <Link 
                v-for="link in endings.links" 
                :key="link.label" 
                :href="link.url" 
                :class="[
                  'px-3 py-2 rounded-md',
                  link.active
                    ? 'bg-primary text-primary-foreground'
                    : 'text-muted-foreground hover:text-foreground',
                  !link.url ? 'pointer-events-none opacity-50' : '',
                  (link.label.includes('Previous') || link.label.includes('Next')) ? 'text-lg font-bold' : 'text-sm'
                ]" 
                >
                {{
                  link.label.includes('Previous') ? '‹' :
                  link.label.includes('Next') ? '›' :
                  link.label
                }}
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>