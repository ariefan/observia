<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { BarChart3, TrendingUp } from 'lucide-vue-next';

interface Props {
  batches: { data: any[]; total: number; links: any[] };
  gradeStats: any;
  filters?: { grade?: string; date_from?: string; date_to?: string };
}

const props = defineProps<Props>();

const selectedGrade = ref(props.filters?.grade || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const applyFilters = () => {
  router.get(route('quality-control.history'), {
    grade: selectedGrade.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
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
  <Head title="Riwayat Grading" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="quality-control.history" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div>
          <h1 class="text-xl font-semibold text-primary">Riwayat Grading</h1>
          <p class="text-sm text-muted-foreground">Histori hasil pengujian kualitas susu</p>
        </div>

        <!-- Stats Cards -->
        <div v-if="gradeStats" class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Diuji</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ gradeStats.total }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Grade A</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">{{ gradeStats.grade_a_percentage }}%</div>
              <p class="text-xs text-muted-foreground">{{ gradeStats.grade_a_count }} batch</p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Grade B</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-blue-600">{{ gradeStats.grade_b_percentage }}%</div>
              <p class="text-xs text-muted-foreground">{{ gradeStats.grade_b_count }} batch</p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Grade C</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-yellow-600">{{ gradeStats.grade_c_percentage }}%</div>
              <p class="text-xs text-muted-foreground">{{ gradeStats.grade_c_count }} batch</p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Ditolak</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-red-600">{{ gradeStats.reject_percentage }}%</div>
              <p class="text-xs text-muted-foreground">{{ gradeStats.reject_count }} batch</p>
            </CardContent>
          </Card>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
          <div>
            <label class="text-xs font-medium">Grade</label>
            <select v-model="selectedGrade" @change="applyFilters" class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Grade</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="Reject">Ditolak</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-medium">Dari Tanggal</label>
            <input v-model="dateFrom" @change="applyFilters" type="date" class="h-8 text-xs w-full border rounded-md px-3 py-1" />
          </div>
          <div>
            <label class="text-xs font-medium">Sampai Tanggal</label>
            <input v-model="dateTo" @change="applyFilters" type="date" class="h-8 text-xs w-full border rounded-md px-3 py-1" />
          </div>
        </div>

        <!-- Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                    <th class="p-3 text-left text-xs font-medium">Tanggal Uji</th>
                    <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                    <th class="p-3 text-left text-xs font-medium">pH</th>
                    <th class="p-3 text-left text-xs font-medium">Lemak</th>
                    <th class="p-3 text-left text-xs font-medium">Bakteri</th>
                    <th class="p-3 text-left text-xs font-medium">Grade</th>
                    <th class="p-3 text-left text-xs font-medium">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="batch in batches.data" :key="batch.id" class="border-b hover:bg-muted/30">
                    <td class="p-3 text-sm font-mono">{{ batch.batch_code }}</td>
                    <td class="p-3 text-sm">{{ formatDate(batch.quality_tested_at) }}</td>
                    <td class="p-3 text-sm font-semibold">{{ batch.total_volume }}</td>
                    <td class="p-3 text-sm">{{ batch.quality_data?.pH || '-' }}</td>
                    <td class="p-3 text-sm">{{ batch.quality_data?.fat_percentage || '-' }}%</td>
                    <td class="p-3 text-sm">{{ batch.quality_data?.bacteria_count?.toLocaleString() || '-' }}</td>
                    <td class="p-3">
                      <Badge :variant="batch.quality_grade === 'A' ? 'default' : batch.quality_grade === 'B' ? 'secondary' : batch.quality_grade === 'C' ? 'outline' : 'destructive'">
                        Grade {{ batch.quality_grade }}
                      </Badge>
                    </td>
                    <td class="p-3">
                      <Link :href="route('milk-batches.show', batch.id)">
                        <Button variant="ghost" size="sm">Detail</Button>
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
