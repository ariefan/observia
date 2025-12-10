<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Clock, Package, Milk, Calendar } from 'lucide-vue-next';

interface Props {
  production: any;
  sourceBatches: any[];
  daysAged: number | null;
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    in_production: 'secondary',
    aging: 'default',
    completed: 'default',
  };
  return badges[status] || 'default';
};
</script>

<template>
  <Head :title="`Produksi ${production.batch_code}`" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="cheese-productions.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link :href="route('cheese-productions.index')">
              <Button variant="ghost" size="sm">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali
              </Button>
            </Link>
            <div>
              <div class="flex items-center gap-2">
                <h1 class="text-xl font-semibold font-mono">{{ production.batch_code }}</h1>
                <Badge :variant="getStatusBadge(production.status)">
                  {{ production.status }}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground">{{ production.cheese_type }}</p>
            </div>
          </div>
          <Link v-if="production.status === 'aging'" :href="route('cheese-productions.aging', production.id)">
            <Button>
              <Clock class="w-4 h-4 mr-2" />
              Kelola Aging
            </Button>
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Package class="w-5 h-5" />
                Info Produksi
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Tanggal Produksi</span>
                <span class="text-sm font-medium">{{ formatDate(production.production_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Diproduksi Oleh</span>
                <span class="text-sm font-medium">{{ production.produced_by?.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Total Susu Digunakan</span>
                <span class="text-sm font-semibold">{{ production.total_milk_volume }}L</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Berat Keju</span>
                <span class="text-sm font-semibold">{{ production.cheese_weight_kg || '-' }} kg</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Yield</span>
                <span class="text-sm font-semibold text-green-600">{{ production.yield_percentage || '-' }}%</span>
              </div>
              <div v-if="production.storage_location" class="flex justify-between">
                <span class="text-sm text-muted-foreground">Lokasi</span>
                <span class="text-sm font-medium">{{ production.storage_location }}</span>
              </div>
            </CardContent>
          </Card>

          <Card v-if="production.aging_target_days">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Calendar class="w-5 h-5" />
                Info Aging
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Mulai Aging</span>
                <span class="text-sm font-medium">
                  {{ production.aging_start_date ? formatDate(production.aging_start_date) : 'Belum dimulai' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Target Aging</span>
                <span class="text-sm font-medium">{{ production.aging_target_days }} hari</span>
              </div>
              <div v-if="daysAged !== null" class="flex justify-between">
                <span class="text-sm text-muted-foreground">Sudah Aging</span>
                <span class="text-sm font-semibold text-blue-600">{{ daysAged }} hari</span>
              </div>
              <div v-if="production.aging_completed_at" class="flex justify-between">
                <span class="text-sm text-muted-foreground">Selesai Aging</span>
                <span class="text-sm font-medium">{{ formatDate(production.aging_completed_at) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card v-if="production.starter_culture || production.rennet_type">
          <CardHeader>
            <CardTitle>Resep & Proses</CardTitle>
          </CardHeader>
          <CardContent class="grid grid-cols-2 gap-4">
            <div v-if="production.starter_culture">
              <span class="text-sm text-muted-foreground">Starter Culture</span>
              <p class="text-sm font-medium">{{ production.starter_culture }}</p>
            </div>
            <div v-if="production.rennet_type">
              <span class="text-sm text-muted-foreground">Rennet</span>
              <p class="text-sm font-medium">{{ production.rennet_type }} - {{ production.rennet_amount }}</p>
            </div>
            <div v-if="production.recipe_notes" class="col-span-2">
              <span class="text-sm text-muted-foreground">Catatan</span>
              <p class="text-sm mt-1">{{ production.recipe_notes }}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Milk class="w-5 h-5" />
              Batch Susu Sumber ({{ sourceBatches.length }})
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-2 text-left">Kode Batch</th>
                    <th class="p-2 text-left">Grade</th>
                    <th class="p-2 text-right">Volume (L)</th>
                    <th class="p-2 text-left">Tanggal Pengumpulan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="batch in sourceBatches" :key="batch.id" class="border-b hover:bg-muted/30">
                    <td class="p-2 font-mono">{{ batch.batch_code }}</td>
                    <td class="p-2"><Badge>Grade {{ batch.quality_grade }}</Badge></td>
                    <td class="p-2 text-right font-semibold">{{ batch.total_volume }}</td>
                    <td class="p-2 text-xs text-muted-foreground">{{ formatDate(batch.collection_date) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-muted/50 font-semibold">
                  <tr>
                    <td colspan="2" class="p-2">Total</td>
                    <td class="p-2 text-right">{{ production.total_milk_volume }} L</td>
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
