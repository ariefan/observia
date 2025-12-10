<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Milk, Search, TrendingUp, TrendingDown } from 'lucide-vue-next';

const debounce = (func: Function, delay: number) => {
  let timeoutId: NodeJS.Timeout;
  return (...args: any[]) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(null, args), delay);
  };
};

interface MilkBatch {
  id: number;
  batch_code: string;
  farm: {
    id: string;
    name: string;
  };
  collection_date: string;
  session: string;
  total_volume: number;
  quality_grade?: string;
  status: string;
  collected_by?: {
    id: string;
    name: string;
  };
}

interface Props {
  batches: {
    data: MilkBatch[];
    links: any[];
    total: number;
  };
  filters?: {
    search?: string;
    status?: string;
    grade?: string;
    date_from?: string;
    date_to?: string;
    session?: string;
  };
  stats?: {
    total_batches_month: number;
    total_volume_month: number;
    grade_a_percentage: number;
    grade_b_percentage: number;
    grade_c_percentage: number;
    rejected_percentage: number;
  };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedGrade = ref(props.filters?.grade || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const selectedSession = ref(props.filters?.session || '');

const debouncedSearch = debounce(() => {
  performSearch();
}, 300);

const performSearch = () => {
  const params: any = {
    search: searchQuery.value || undefined,
    status: selectedStatus.value || undefined,
    grade: selectedGrade.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    session: selectedSession.value || undefined,
  };

  Object.keys(params).forEach(key => {
    if (params[key] === undefined) {
      delete params[key];
    }
  });

  router.get(route('milk-batches.index'), params, {
    preserveState: true,
    replace: true,
  });
};

watch(searchQuery, () => {
  debouncedSearch();
});

const handleFilterChange = () => {
  performSearch();
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedStatus.value = '';
  selectedGrade.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  selectedSession.value = '';
  router.get(route('milk-batches.index'), {}, {
    preserveState: true,
    replace: true,
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, { variant: string; text: string }> = {
    collected: { variant: 'default', text: 'Dikumpulkan' },
    in_transit: { variant: 'secondary', text: 'Dalam Perjalanan' },
    received: { variant: 'default', text: 'Diterima' },
    tested: { variant: 'default', text: 'Diuji' },
    approved: { variant: 'default', text: 'Disetujui' },
    rejected: { variant: 'destructive', text: 'Ditolak' },
  };
  return badges[status] || { variant: 'default', text: status };
};

const getGradeBadge = (grade?: string) => {
  if (!grade) return null;
  const badges: Record<string, { variant: string }> = {
    A: { variant: 'default' },
    B: { variant: 'secondary' },
    C: { variant: 'outline' },
    Reject: { variant: 'destructive' },
  };
  return badges[grade] || { variant: 'default' };
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};

const formatSession = (session: string) => {
  const sessions: Record<string, string> = {
    morning: 'Pagi',
    afternoon: 'Siang',
    evening: 'Sore'
  };
  return sessions[session] || session;
};
</script>

<template>
  <Head title="Koleksi Batch Susu" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="milk-batches.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-primary">Koleksi Batch Susu</h1>
            <p class="text-sm text-muted-foreground">Kelola pengumpulan dan batch susu</p>
          </div>
          <Link :href="route('milk-batches.create')">
            <Button>
              <Plus class="w-4 h-4 mr-2" />
              Buat Batch Baru
            </Button>
          </Link>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Batch Bulan Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.total_batches_month }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Volume (L)</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ Number(stats.total_volume_month || 0).toFixed(2) }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Grade A</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">{{ stats.grade_a_percentage }}%</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Ditolak</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-red-600">{{ stats.rejected_percentage }}%</div>
            </CardContent>
          </Card>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
          <div>
            <label class="text-xs font-medium">Cari Kode Batch</label>
            <div class="relative">
              <Search class="absolute left-2 top-2 h-3.5 w-3.5 text-muted-foreground" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="MB-20250310-001"
                class="h-8 pl-8 text-xs w-full border rounded-md px-3 py-1"
              />
            </div>
          </div>

          <div>
            <label class="text-xs font-medium">Status</label>
            <select
              v-model="selectedStatus"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Status</option>
              <option value="collected">Dikumpulkan</option>
              <option value="in_transit">Dalam Perjalanan</option>
              <option value="received">Diterima</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-medium">Grade</label>
            <select
              v-model="selectedGrade"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Grade</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="Reject">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-medium">Sesi</label>
            <select
              v-model="selectedSession"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Sesi</option>
              <option value="morning">Pagi</option>
              <option value="afternoon">Siang</option>
              <option value="evening">Sore</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-medium">Dari Tanggal</label>
            <input
              v-model="dateFrom"
              type="date"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>

          <div>
            <label class="text-xs font-medium">Sampai Tanggal</label>
            <input
              v-model="dateTo"
              type="date"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>
        </div>

        <!-- Batches Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                    <th class="p-3 text-left text-xs font-medium">Tanggal</th>
                    <th class="p-3 text-left text-xs font-medium">Sesi</th>
                    <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                    <th class="p-3 text-left text-xs font-medium">Grade</th>
                    <th class="p-3 text-left text-xs font-medium">Status</th>
                    <th class="p-3 text-left text-xs font-medium">Dikumpulkan Oleh</th>
                    <th class="p-3 text-left text-xs font-medium">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="batches.data.length === 0">
                    <td colspan="8" class="p-8 text-center text-muted-foreground">
                      <Milk class="w-12 h-12 mx-auto mb-2 opacity-30" />
                      <p>Belum ada batch susu.</p>
                      <Link :href="route('milk-batches.create')">
                        <Button variant="link" class="mt-2">Buat batch pertama</Button>
                      </Link>
                    </td>
                  </tr>
                  <tr
                    v-for="batch in batches.data"
                    :key="batch.id"
                    class="border-b hover:bg-muted/30 transition-colors"
                  >
                    <td class="p-3 text-sm font-mono">{{ batch.batch_code }}</td>
                    <td class="p-3 text-sm">{{ formatDate(batch.collection_date) }}</td>
                    <td class="p-3 text-sm">{{ formatSession(batch.session) }}</td>
                    <td class="p-3 text-sm font-semibold">{{ batch.total_volume }}</td>
                    <td class="p-3">
                      <Badge v-if="batch.quality_grade" :variant="getGradeBadge(batch.quality_grade)?.variant">
                        Grade {{ batch.quality_grade }}
                      </Badge>
                      <span v-else class="text-xs text-muted-foreground">Belum diuji</span>
                    </td>
                    <td class="p-3">
                      <Badge :variant="getStatusBadge(batch.status).variant">
                        {{ getStatusBadge(batch.status).text }}
                      </Badge>
                    </td>
                    <td class="p-3 text-sm">{{ batch.collected_by?.name || '-' }}</td>
                    <td class="p-3">
                      <Link :href="route('milk-batches.show', batch.id)">
                        <Button variant="ghost" size="sm">Lihat Detail</Button>
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="batches.links.length > 3" class="flex items-center justify-center gap-2 p-4 border-t">
              <Button
                v-for="(link, index) in batches.links"
                :key="index"
                :variant="link.active ? 'default' : 'ghost'"
                size="sm"
                :disabled="!link.url"
                @click="link.url && router.visit(link.url)"
                v-html="link.label"
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
