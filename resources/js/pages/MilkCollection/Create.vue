<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Thermometer, Droplet, AlertTriangle, CheckCircle } from 'lucide-vue-next';

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
  availableMilkings: LivestockMilking[];
  estimatedVolume: number;
  collectionDate: string;
  session: string;
}

const props = defineProps<Props>();

const form = useForm({
  collection_date: props.collectionDate,
  session: props.session,
  source_livestock_milking_ids: props.availableMilkings.map(m => m.id),
  estimated_volume: props.estimatedVolume,
  actual_volume: props.estimatedVolume,
  transport_temp_pickup: 5.0,
  transport_notes: '',
});

const variance = computed(() => {
  if (form.estimated_volume === 0) return 0;
  return (((form.actual_volume - form.estimated_volume) / form.estimated_volume) * 100).toFixed(2);
});

const varianceColor = computed(() => {
  const v = parseFloat(variance.value);
  if (Math.abs(v) <= 5) return 'text-green-600';
  if (Math.abs(v) <= 10) return 'text-yellow-600';
  return 'text-red-600';
});

const tempColor = computed(() => {
  const temp = form.transport_temp_pickup;
  if (temp >= 4 && temp <= 7) return 'text-green-600';
  if (temp < 4 || temp <= 10) return 'text-yellow-600';
  return 'text-red-600';
});

const tempStatus = computed(() => {
  const temp = form.transport_temp_pickup;
  if (temp >= 4 && temp <= 7) return '✓ Suhu Ideal';
  if (temp < 4) return '⚠ Terlalu Dingin';
  if (temp <= 10) return '⚠ Sedikit Hangat';
  return '✗ Terlalu Hangat';
});

const submit = () => {
  form.post(route('milk-batches.store'), {
    preserveScroll: true,
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
  <Head title="Buat Batch Susu Baru" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="milk-batches.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4">
          <Link :href="route('milk-batches.index')">
            <Button variant="ghost" size="sm">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-xl font-semibold text-primary">Buat Batch Susu Baru</h1>
            <p class="text-sm text-muted-foreground">
              Kumpulkan hasil pemerahan individual menjadi satu batch untuk transportasi
            </p>
          </div>
        </div>

        <!-- Warning if no milkings available -->
        <Alert v-if="availableMilkings.length === 0" variant="destructive">
          <AlertTriangle class="h-4 w-4" />
          <AlertDescription>
            Tidak ada data pemerahan yang tersedia untuk tanggal dan sesi ini.
            Pastikan sudah ada data pemerahan ternak yang belum dibatch.
          </AlertDescription>
        </Alert>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Collection Info -->
          <Card>
            <CardHeader>
              <CardTitle>Informasi Pengumpulan</CardTitle>
              <CardDescription>Detail tanggal dan sesi pengumpulan susu</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label>Tanggal Pengumpulan</Label>
                  <Input
                    type="date"
                    v-model="form.collection_date"
                    :disabled="true"
                    class="bg-muted"
                  />
                </div>
                <div>
                  <Label>Sesi Pemerahan</Label>
                  <Input
                    type="text"
                    :value="formatSession(form.session)"
                    :disabled="true"
                    class="bg-muted"
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Volume & Temperature -->
          <Card>
            <CardHeader>
              <CardTitle>Volume & Suhu Pengambilan</CardTitle>
              <CardDescription>Catat volume aktual dan suhu saat pengambilan</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label>Volume Estimasi (Liter)</Label>
                  <div class="relative">
                    <Droplet class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      type="number"
                      step="0.01"
                      v-model.number="form.estimated_volume"
                      :disabled="true"
                      class="pl-10 bg-muted"
                    />
                  </div>
                  <p class="text-xs text-muted-foreground mt-1">
                    Total dari {{ availableMilkings.length }} pemerahan individual
                  </p>
                </div>

                <div>
                  <Label>Volume Aktual (Liter) <span class="text-red-500">*</span></Label>
                  <div class="relative">
                    <Droplet class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      type="number"
                      step="0.01"
                      min="0"
                      v-model.number="form.actual_volume"
                      :class="{ 'border-red-500': form.errors.actual_volume }"
                      class="pl-10"
                      required
                    />
                  </div>
                  <p v-if="form.errors.actual_volume" class="text-xs text-red-500 mt-1">
                    {{ form.errors.actual_volume }}
                  </p>
                  <p v-else class="text-xs mt-1" :class="varianceColor">
                    Selisih: {{ variance }}%
                  </p>
                </div>
              </div>

              <div>
                <Label>Suhu Pengambilan (°C) <span class="text-red-500">*</span></Label>
                <div class="relative">
                  <Thermometer class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    type="number"
                    step="0.1"
                    min="0"
                    max="15"
                    v-model.number="form.transport_temp_pickup"
                    :class="{ 'border-red-500': form.errors.transport_temp_pickup }"
                    class="pl-10"
                    required
                  />
                </div>
                <p v-if="form.errors.transport_temp_pickup" class="text-xs text-red-500 mt-1">
                  {{ form.errors.transport_temp_pickup }}
                </p>
                <p v-else class="text-xs mt-1" :class="tempColor">
                  {{ tempStatus }} (Ideal: 4-7°C)
                </p>
              </div>

              <div>
                <Label>Catatan Transportasi</Label>
                <Textarea
                  v-model="form.transport_notes"
                  placeholder="Contoh: Cuaca cerah, perjalanan lancar, tidak ada kendala..."
                  rows="3"
                />
                <p v-if="form.errors.transport_notes" class="text-xs text-red-500 mt-1">
                  {{ form.errors.transport_notes }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Source Milkings -->
          <Card>
            <CardHeader>
              <CardTitle>Sumber Pemerahan ({{ availableMilkings.length }})</CardTitle>
              <CardDescription>
                Daftar pemerahan individual yang akan digabung menjadi batch ini
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm">
                  <thead class="bg-muted/50 sticky top-0">
                    <tr>
                      <th class="p-2 text-left">Ear Tag</th>
                      <th class="p-2 text-left">Nama Ternak</th>
                      <th class="p-2 text-right">Volume (L)</th>
                      <th class="p-2 text-left">Waktu Pemerahan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="milking in availableMilkings"
                      :key="milking.id"
                      class="border-b hover:bg-muted/30"
                    >
                      <td class="p-2 font-mono text-xs">{{ milking.livestock.ear_tag }}</td>
                      <td class="p-2">{{ milking.livestock.name }}</td>
                      <td class="p-2 text-right font-semibold">{{ milking.milk_volume }}</td>
                      <td class="p-2 text-xs text-muted-foreground">
                        {{ new Date(milking.milking_time).toLocaleTimeString('id-ID') }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-muted/50 font-semibold sticky bottom-0">
                    <tr>
                      <td colspan="2" class="p-2">Total</td>
                      <td class="p-2 text-right">{{ estimatedVolume }} L</td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </CardContent>
          </Card>

          <!-- Actions -->
          <div class="flex items-center justify-between">
            <Link :href="route('milk-batches.index')">
              <Button type="button" variant="outline">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing || availableMilkings.length === 0"
            >
              <CheckCircle class="w-4 h-4 mr-2" />
              {{ form.processing ? 'Membuat...' : 'Buat Batch' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
