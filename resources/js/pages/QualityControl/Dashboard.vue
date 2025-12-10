<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Package, Microscope, CheckCircle, AlertTriangle, Eye } from 'lucide-vue-next';

interface MilkBatch {
  id: number;
  batch_code: string;
  farm: { id: string; name: string };
  collection_date: string;
  session: string;
  total_volume: number;
  quality_grade?: string;
  status: string;
  collected_by?: { name: string };
  received_by?: { name: string };
  quality_tested_by?: { name: string };
  collected_at?: string;
  received_at?: string;
  quality_tested_at?: string;
}

interface Props {
  toReceive: { data: MilkBatch[]; total: number };
  awaitingTest: { data: MilkBatch[]; total: number };
  recentlyTested: { data: MilkBatch[]; total: number };
  stats?: {
    pending_receive: number;
    pending_test: number;
    tested_today: number;
    tested_this_week: number;
    avg_grade_this_week?: string;
  };
  qualityStandards: any;
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getGradeBadge = (grade?: string) => {
  if (!grade) return null;
  const badges: Record<string, string> = {
    A: 'default',
    B: 'secondary',
    C: 'outline',
    Reject: 'destructive',
  };
  return badges[grade] || 'default';
};
</script>

<template>
  <Head title="Quality Control Dashboard" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="quality-control.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <!-- Header -->
        <div>
          <h1 class="text-xl font-semibold text-primary">Quality Control Dashboard</h1>
          <p class="text-sm text-muted-foreground">Penerimaan dan pengujian kualitas susu</p>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Menunggu Diterima</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-orange-600">{{ stats.pending_receive }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Menunggu Uji</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-blue-600">{{ stats.pending_test }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Diuji Hari Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.tested_today }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Diuji Minggu Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.tested_this_week }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Rata-rata Grade</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">
                {{ stats.avg_grade_this_week || '-' }}
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Tabs -->
        <Tabs default-value="receive" class="w-full">
          <TabsList class="grid w-full grid-cols-3">
            <TabsTrigger value="receive">
              <Package class="w-4 h-4 mr-2" />
              Perlu Diterima ({{ toReceive.total }})
            </TabsTrigger>
            <TabsTrigger value="test">
              <Microscope class="w-4 h-4 mr-2" />
              Perlu Diuji ({{ awaitingTest.total }})
            </TabsTrigger>
            <TabsTrigger value="tested">
              <CheckCircle class="w-4 h-4 mr-2" />
              Sudah Diuji ({{ recentlyTested.total }})
            </TabsTrigger>
          </TabsList>

          <!-- To Receive Tab -->
          <TabsContent value="receive">
            <Card>
              <CardContent class="p-0">
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-muted/50">
                      <tr>
                        <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                        <th class="p-3 text-left text-xs font-medium">Dikumpulkan</th>
                        <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                        <th class="p-3 text-left text-xs font-medium">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="toReceive.data.length === 0">
                        <td colspan="4" class="p-8 text-center text-muted-foreground">
                          <CheckCircle class="w-12 h-12 mx-auto mb-2 opacity-30" />
                          <p>Semua batch sudah diterima</p>
                        </td>
                      </tr>
                      <tr
                        v-for="batch in toReceive.data"
                        :key="batch.id"
                        class="border-b hover:bg-muted/30"
                      >
                        <td class="p-3 text-sm font-mono">{{ batch.batch_code }}</td>
                        <td class="p-3 text-sm">{{ formatDate(batch.collected_at!) }}</td>
                        <td class="p-3 text-sm font-semibold">{{ batch.total_volume }}</td>
                        <td class="p-3">
                          <Link :href="route('quality-control.receive-form', batch.id)">
                            <Button variant="default" size="sm">
                              <Eye class="w-4 h-4 mr-1" />
                              Terima
                            </Button>
                          </Link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Awaiting Test Tab -->
          <TabsContent value="test">
            <Card>
              <CardContent class="p-0">
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-muted/50">
                      <tr>
                        <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                        <th class="p-3 text-left text-xs font-medium">Diterima</th>
                        <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                        <th class="p-3 text-left text-xs font-medium">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="awaitingTest.data.length === 0">
                        <td colspan="4" class="p-8 text-center text-muted-foreground">
                          <CheckCircle class="w-12 h-12 mx-auto mb-2 opacity-30" />
                          <p>Tidak ada batch menunggu pengujian</p>
                        </td>
                      </tr>
                      <tr
                        v-for="batch in awaitingTest.data"
                        :key="batch.id"
                        class="border-b hover:bg-muted/30"
                      >
                        <td class="p-3 text-sm font-mono">{{ batch.batch_code }}</td>
                        <td class="p-3 text-sm">{{ formatDate(batch.received_at!) }}</td>
                        <td class="p-3 text-sm font-semibold">{{ batch.total_volume }}</td>
                        <td class="p-3">
                          <Link :href="route('quality-control.test-form', batch.id)">
                            <Button variant="default" size="sm">
                              <Microscope class="w-4 h-4 mr-1" />
                              Uji Kualitas
                            </Button>
                          </Link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <!-- Recently Tested Tab -->
          <TabsContent value="tested">
            <Card>
              <CardContent class="p-0">
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-muted/50">
                      <tr>
                        <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                        <th class="p-3 text-left text-xs font-medium">Diuji</th>
                        <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                        <th class="p-3 text-left text-xs font-medium">Grade</th>
                        <th class="p-3 text-left text-xs font-medium">Status</th>
                        <th class="p-3 text-left text-xs font-medium">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="recentlyTested.data.length === 0">
                        <td colspan="6" class="p-8 text-center text-muted-foreground">
                          <AlertTriangle class="w-12 h-12 mx-auto mb-2 opacity-30" />
                          <p>Belum ada batch yang diuji</p>
                        </td>
                      </tr>
                      <tr
                        v-for="batch in recentlyTested.data"
                        :key="batch.id"
                        class="border-b hover:bg-muted/30"
                      >
                        <td class="p-3 text-sm font-mono">{{ batch.batch_code }}</td>
                        <td class="p-3 text-sm">{{ formatDate(batch.quality_tested_at!) }}</td>
                        <td class="p-3 text-sm font-semibold">{{ batch.total_volume }}</td>
                        <td class="p-3">
                          <Badge :variant="getGradeBadge(batch.quality_grade)">
                            Grade {{ batch.quality_grade }}
                          </Badge>
                        </td>
                        <td class="p-3">
                          <Badge :variant="batch.status === 'approved' ? 'default' : 'destructive'">
                            {{ batch.status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                          </Badge>
                        </td>
                        <td class="p-3">
                          <Link :href="route('milk-batches.show', batch.id)">
                            <Button variant="ghost" size="sm">Lihat Detail</Button>
                          </Link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        <!-- Link to History -->
        <div class="flex justify-end">
          <Link :href="route('quality-control.history')">
            <Button variant="outline">
              Lihat Riwayat Grading Lengkap
            </Button>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
