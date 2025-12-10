<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, PackageOpen, Clock, CheckCircle } from 'lucide-vue-next';

interface Props {
  productions: { data: any[]; total: number; links: any[] };
  filters?: any;
  stats?: any;
  cheeseTypes: string[];
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedType = ref(props.filters?.cheese_type || '');

const applyFilters = () => {
  router.get(route('cheese-productions.index'), {
    search: searchQuery.value || undefined,
    status: selectedStatus.value || undefined,
    cheese_type: selectedType.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, { variant: string; text: string }> = {
    in_production: { variant: 'secondary', text: 'Produksi' },
    aging: { variant: 'default', text: 'Aging' },
    completed: { variant: 'default', text: 'Selesai' },
    rejected: { variant: 'destructive', text: 'Ditolak' },
  };
  return badges[status] || { variant: 'default', text: status };
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};
</script>

<template>
  <Head title="Produksi Keju" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="cheese-productions.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-primary">Produksi Keju</h1>
            <p class="text-sm text-muted-foreground">Kelola produksi dan aging keju</p>
          </div>
          <Link :href="route('cheese-productions.create')">
            <Button>
              <Plus class="w-4 h-4 mr-2" />
              Produksi Baru
            </Button>
          </Link>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Produksi Bulan Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.total_productions_month }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Berat (kg)</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ Number(stats.total_cheese_weight_month || 0).toFixed(1) }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Dalam Produksi</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-orange-600">{{ stats.in_production }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Aging</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-blue-600">{{ stats.aging }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Yield Rata-rata</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">{{ Number(stats.avg_yield || 0).toFixed(1) }}%</div>
            </CardContent>
          </Card>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
          <div>
            <label class="text-xs font-medium">Cari</label>
            <input
              v-model="searchQuery"
              @input="applyFilters"
              type="text"
              placeholder="Kode batch / tipe keju..."
              class="h-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>
          <div>
            <label class="text-xs font-medium">Status</label>
            <select v-model="selectedStatus" @change="applyFilters" class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Status</option>
              <option value="in_production">Produksi</option>
              <option value="aging">Aging</option>
              <option value="completed">Selesai</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-medium">Tipe Keju</label>
            <select v-model="selectedType" @change="applyFilters" class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Tipe</option>
              <option v-for="type in cheeseTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
        </div>

        <!-- Productions Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                    <th class="p-3 text-left text-xs font-medium">Tipe Keju</th>
                    <th class="p-3 text-left text-xs font-medium">Tanggal Produksi</th>
                    <th class="p-3 text-left text-xs font-medium">Berat (kg)</th>
                    <th class="p-3 text-left text-xs font-medium">Yield</th>
                    <th class="p-3 text-left text-xs font-medium">Status</th>
                    <th class="p-3 text-left text-xs font-medium">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="productions.data.length === 0">
                    <td colspan="7" class="p-8 text-center text-muted-foreground">
                      <PackageOpen class="w-12 h-12 mx-auto mb-2 opacity-30" />
                      <p>Belum ada produksi keju</p>
                    </td>
                  </tr>
                  <tr v-for="prod in productions.data" :key="prod.id" class="border-b hover:bg-muted/30">
                    <td class="p-3 text-sm font-mono">{{ prod.batch_code }}</td>
                    <td class="p-3 text-sm font-semibold">{{ prod.cheese_type }}</td>
                    <td class="p-3 text-sm">{{ formatDate(prod.production_date) }}</td>
                    <td class="p-3 text-sm">{{ prod.cheese_weight_kg || '-' }}</td>
                    <td class="p-3 text-sm">{{ prod.yield_percentage ? prod.yield_percentage + '%' : '-' }}</td>
                    <td class="p-3">
                      <Badge :variant="getStatusBadge(prod.status).variant">
                        {{ getStatusBadge(prod.status).text }}
                      </Badge>
                    </td>
                    <td class="p-3 space-x-2">
                      <Link :href="route('cheese-productions.show', prod.id)">
                        <Button variant="ghost" size="sm">Detail</Button>
                      </Link>
                      <Link v-if="prod.status === 'aging'" :href="route('cheese-productions.aging', prod.id)">
                        <Button variant="outline" size="sm">
                          <Clock class="w-3 h-3 mr-1" />
                          Aging
                        </Button>
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
