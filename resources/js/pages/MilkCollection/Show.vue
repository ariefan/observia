<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
  ArrowLeft,
  Droplet,
  Thermometer,
  Calendar,
  User,
  TrendingUp,
  Eye,
  Microscope,
  CheckCircle,
  XCircle,
  Package
} from 'lucide-vue-next';

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
  estimated_volume?: number;
  actual_volume?: number;
  variance_percentage?: number;
  transport_temp_pickup?: number;
  transport_temp_delivery?: number;
  transport_duration_minutes?: number;
  transport_notes?: string;
  visual_check?: string;
  smell_check?: string;
  quality_data?: {
    pH: number;
    fat_percentage: number;
    protein_percentage?: number;
    bacteria_count: number;
    temperature: number;
    snf_percentage?: number;
  };
  quality_grade?: string;
  quality_notes?: string;
  status: string;
  rejection_reason?: string;
  collected_by?: {
    id: string;
    name: string;
  };
  collected_at?: string;
  received_by?: {
    id: string;
    name: string;
  };
  received_at?: string;
  quality_tested_by?: {
    id: string;
    name: string;
  };
  quality_tested_at?: string;
  created_at: string;
}

interface LivestockMilking {
  id: number;
  livestock: {
    id: number;
    name: string;
    ear_tag: string;
  };
  milk_volume: number;
  milking_time: string;
}

interface Props {
  batch: MilkBatch;
  sourceMilkings: LivestockMilking[];
}

const props = defineProps<Props>();

const getStatusBadge = (status: string) => {
  const badges: Record<string, { variant: string; text: string; icon: any }> = {
    collected: { variant: 'default', text: 'Dikumpulkan', icon: Package },
    in_transit: { variant: 'secondary', text: 'Dalam Perjalanan', icon: TrendingUp },
    received: { variant: 'default', text: 'Diterima', icon: CheckCircle },
    tested: { variant: 'default', text: 'Diuji', icon: Microscope },
    approved: { variant: 'default', text: 'Disetujui', icon: CheckCircle },
    rejected: { variant: 'destructive', text: 'Ditolak', icon: XCircle },
  };
  return badges[status] || { variant: 'default', text: status, icon: Package };
};

const getGradeBadge = (grade?: string) => {
  if (!grade) return null;
  const badges: Record<string, { variant: string; color: string }> = {
    A: { variant: 'default', color: 'bg-green-500' },
    B: { variant: 'secondary', color: 'bg-blue-500' },
    C: { variant: 'outline', color: 'bg-yellow-500' },
    Reject: { variant: 'destructive', color: 'bg-red-500' },
  };
  return badges[grade] || { variant: 'default', color: 'bg-gray-500' };
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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

const formatCheck = (check?: string) => {
  const checks: Record<string, string> = {
    normal: '✓ Normal',
    abnormal: '✗ Abnormal',
    foamy: '⚠ Berbusa',
    discolored: '⚠ Berubah Warna',
    sour: '✗ Asam',
  };
  return checks[check || ''] || check || '-';
};

const getCheckColor = (check?: string) => {
  if (check === 'normal') return 'text-green-600';
  if (check === 'abnormal' || check === 'sour') return 'text-red-600';
  if (check === 'foamy' || check === 'discolored') return 'text-yellow-600';
  return 'text-muted-foreground';
};
</script>

<template>
  <Head :title="`Batch ${batch.batch_code}`" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="milk-batches.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link :href="route('milk-batches.index')">
              <Button variant="ghost" size="sm">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali
              </Button>
            </Link>
            <div>
              <div class="flex items-center gap-2">
                <h1 class="text-xl font-semibold text-primary font-mono">{{ batch.batch_code }}</h1>
                <Badge :variant="getStatusBadge(batch.status).variant">
                  {{ getStatusBadge(batch.status).text }}
                </Badge>
                <Badge v-if="batch.quality_grade" :variant="getGradeBadge(batch.quality_grade)?.variant">
                  Grade {{ batch.quality_grade }}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground">
                Dikumpulkan {{ formatDate(batch.collection_date) }} - Sesi {{ formatSession(batch.session) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Rejection Alert -->
        <Card v-if="batch.status === 'rejected'" class="border-red-500">
          <CardContent class="pt-4">
            <div class="flex items-start gap-3">
              <XCircle class="w-5 h-5 text-red-500 mt-0.5" />
              <div>
                <h3 class="font-semibold text-red-700">Batch Ditolak</h3>
                <p class="text-sm text-red-600">{{ batch.rejection_reason }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Collection Info -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Package class="w-5 h-5" />
                Informasi Pengumpulan
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Tanggal Pengumpulan</span>
                <span class="text-sm font-medium">{{ formatDate(batch.collection_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Sesi</span>
                <span class="text-sm font-medium">{{ formatSession(batch.session) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Dikumpulkan Oleh</span>
                <span class="text-sm font-medium">{{ batch.collected_by?.name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Waktu Pengumpulan</span>
                <span class="text-sm font-medium">
                  {{ batch.collected_at ? formatDate(batch.collected_at) : '-' }}
                </span>
              </div>
            </CardContent>
          </Card>

          <!-- Volume Info -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Droplet class="w-5 h-5" />
                Informasi Volume
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Volume Estimasi</span>
                <span class="text-sm font-medium">{{ batch.estimated_volume }} L</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Volume Aktual</span>
                <span class="text-sm font-semibold">{{ batch.actual_volume }} L</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Selisih</span>
                <span
                  class="text-sm font-medium"
                  :class="{
                    'text-green-600': Math.abs(batch.variance_percentage || 0) <= 5,
                    'text-yellow-600': Math.abs(batch.variance_percentage || 0) > 5 && Math.abs(batch.variance_percentage || 0) <= 10,
                    'text-red-600': Math.abs(batch.variance_percentage || 0) > 10
                  }"
                >
                  {{ batch.variance_percentage }}%
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Total Volume</span>
                <span class="text-lg font-bold text-primary">{{ batch.total_volume }} L</span>
              </div>
            </CardContent>
          </Card>

          <!-- Transport Info -->
          <Card v-if="batch.transport_temp_pickup">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Thermometer class="w-5 h-5" />
                Informasi Transportasi
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Suhu Pengambilan</span>
                <span
                  class="text-sm font-medium"
                  :class="{
                    'text-green-600': batch.transport_temp_pickup >= 4 && batch.transport_temp_pickup <= 7,
                    'text-yellow-600': batch.transport_temp_pickup < 4 || (batch.transport_temp_pickup > 7 && batch.transport_temp_pickup <= 10),
                    'text-red-600': batch.transport_temp_pickup > 10
                  }"
                >
                  {{ batch.transport_temp_pickup }}°C
                </span>
              </div>
              <div v-if="batch.transport_temp_delivery" class="flex justify-between">
                <span class="text-sm text-muted-foreground">Suhu Pengiriman</span>
                <span
                  class="text-sm font-medium"
                  :class="{
                    'text-green-600': batch.transport_temp_delivery >= 4 && batch.transport_temp_delivery <= 7,
                    'text-yellow-600': batch.transport_temp_delivery < 4 || (batch.transport_temp_delivery > 7 && batch.transport_temp_delivery <= 10),
                    'text-red-600': batch.transport_temp_delivery > 10
                  }"
                >
                  {{ batch.transport_temp_delivery }}°C
                </span>
              </div>
              <div v-if="batch.transport_duration_minutes" class="flex justify-between">
                <span class="text-sm text-muted-foreground">Durasi Transportasi</span>
                <span class="text-sm font-medium">{{ batch.transport_duration_minutes }} menit</span>
              </div>
              <div v-if="batch.transport_notes">
                <span class="text-sm text-muted-foreground">Catatan</span>
                <p class="text-sm mt-1">{{ batch.transport_notes }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Receiving Info -->
          <Card v-if="batch.received_at">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Eye class="w-5 h-5" />
                Informasi Penerimaan
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Diterima Oleh</span>
                <span class="text-sm font-medium">{{ batch.received_by?.name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Waktu Penerimaan</span>
                <span class="text-sm font-medium">{{ formatDate(batch.received_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Pemeriksaan Visual</span>
                <span class="text-sm font-medium" :class="getCheckColor(batch.visual_check)">
                  {{ formatCheck(batch.visual_check) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Pemeriksaan Bau</span>
                <span class="text-sm font-medium" :class="getCheckColor(batch.smell_check)">
                  {{ formatCheck(batch.smell_check) }}
                </span>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Quality Test Results -->
        <Card v-if="batch.quality_data">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Microscope class="w-5 h-5" />
              Hasil Uji Kualitas
            </CardTitle>
            <CardDescription>
              Diuji oleh {{ batch.quality_tested_by?.name }} pada {{ formatDate(batch.quality_tested_at!) }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div class="p-4 bg-muted/50 rounded-lg">
                <div class="text-sm text-muted-foreground mb-1">pH</div>
                <div class="text-2xl font-bold">{{ batch.quality_data.pH }}</div>
              </div>
              <div class="p-4 bg-muted/50 rounded-lg">
                <div class="text-sm text-muted-foreground mb-1">Lemak</div>
                <div class="text-2xl font-bold">{{ batch.quality_data.fat_percentage }}%</div>
              </div>
              <div v-if="batch.quality_data.protein_percentage" class="p-4 bg-muted/50 rounded-lg">
                <div class="text-sm text-muted-foreground mb-1">Protein</div>
                <div class="text-2xl font-bold">{{ batch.quality_data.protein_percentage }}%</div>
              </div>
              <div class="p-4 bg-muted/50 rounded-lg">
                <div class="text-sm text-muted-foreground mb-1">Bakteri</div>
                <div class="text-xl font-bold">{{ batch.quality_data.bacteria_count.toLocaleString() }}</div>
                <div class="text-xs text-muted-foreground">CFU/ml</div>
              </div>
              <div class="p-4 bg-muted/50 rounded-lg">
                <div class="text-sm text-muted-foreground mb-1">Suhu</div>
                <div class="text-2xl font-bold">{{ batch.quality_data.temperature }}°C</div>
              </div>
              <div v-if="batch.quality_data.snf_percentage" class="p-4 bg-muted/50 rounded-lg">
                <div class="text-sm text-muted-foreground mb-1">SNF</div>
                <div class="text-2xl font-bold">{{ batch.quality_data.snf_percentage }}%</div>
              </div>
            </div>
            <div v-if="batch.quality_notes" class="mt-4 p-4 bg-muted/30 rounded-lg">
              <div class="text-sm font-medium mb-1">Catatan QC</div>
              <p class="text-sm">{{ batch.quality_notes }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Source Milkings -->
        <Card>
          <CardHeader>
            <CardTitle>Sumber Pemerahan ({{ sourceMilkings.length }})</CardTitle>
            <CardDescription>Traceability: Pemerahan individual yang membentuk batch ini</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-2 text-left">Ear Tag</th>
                    <th class="p-2 text-left">Nama Ternak</th>
                    <th class="p-2 text-right">Volume (L)</th>
                    <th class="p-2 text-left">Waktu Pemerahan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="milking in sourceMilkings"
                    :key="milking.id"
                    class="border-b hover:bg-muted/30"
                  >
                    <td class="p-2 font-mono text-xs">{{ milking.livestock.ear_tag }}</td>
                    <td class="p-2">{{ milking.livestock.name }}</td>
                    <td class="p-2 text-right font-semibold">{{ milking.milk_volume }}</td>
                    <td class="p-2 text-xs text-muted-foreground">
                      {{ formatDate(milking.milking_time) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-muted/50 font-semibold">
                  <tr>
                    <td colspan="2" class="p-2">Total</td>
                    <td class="p-2 text-right">{{ batch.total_volume }} L</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
