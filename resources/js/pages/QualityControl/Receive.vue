<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Thermometer, Eye, Nose } from 'lucide-vue-next';

interface Props {
  batch: any;
  temperatureRange: { pickup_min: number; pickup_max: number; warning_threshold: number };
}

const props = defineProps<Props>();

const form = useForm({
  transport_temp_delivery: props.batch.transport_temp_pickup || 6.0,
  transport_duration_minutes: null as number | null,
  visual_check: 'normal',
  smell_check: 'normal',
  transport_notes: '',
});

const tempColor = computed(() => {
  const temp = form.transport_temp_delivery;
  if (temp >= props.temperatureRange.pickup_min && temp <= props.temperatureRange.pickup_max) return 'text-green-600';
  if (temp <= props.temperatureRange.warning_threshold) return 'text-yellow-600';
  return 'text-red-600';
});

const canProceed = computed(() => {
  return form.visual_check === 'normal' && form.smell_check === 'normal';
});

const submit = () => {
  form.post(route('quality-control.receive', props.batch.id));
};
</script>

<template>
  <Head title="Terima Batch Susu" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="quality-control.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-3xl mx-auto">
        <div class="flex items-center gap-4">
          <Link :href="route('quality-control.index')">
            <Button variant="ghost" size="sm">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-xl font-semibold">Terima Batch: {{ batch.batch_code }}</h1>
            <p class="text-sm text-muted-foreground">Pemeriksaan penerimaan susu</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Thermometer class="w-5 h-5" />
                Suhu & Durasi
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label>Suhu Pengiriman (°C) <span class="text-red-500">*</span></Label>
                <Input
                  type="number"
                  step="0.1"
                  v-model.number="form.transport_temp_delivery"
                  required
                />
                <p class="text-xs mt-1" :class="tempColor">
                  Ideal: {{ temperatureRange.pickup_min }}-{{ temperatureRange.pickup_max }}°C
                </p>
              </div>
              <div>
                <Label>Durasi Transportasi (Menit)</Label>
                <Input
                  type="number"
                  v-model.number="form.transport_duration_minutes"
                />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Eye class="w-5 h-5" />
                Pemeriksaan Fisik
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label>Pemeriksaan Visual <span class="text-red-500">*</span></Label>
                <Select v-model="form.visual_check" required>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="normal">✓ Normal</SelectItem>
                    <SelectItem value="abnormal">✗ Abnormal</SelectItem>
                    <SelectItem value="foamy">⚠ Berbusa</SelectItem>
                    <SelectItem value="discolored">⚠ Berubah Warna</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Pemeriksaan Bau <span class="text-red-500">*</span></Label>
                <Select v-model="form.smell_check" required>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="normal">✓ Normal</SelectItem>
                    <SelectItem value="sour">✗ Asam</SelectItem>
                    <SelectItem value="abnormal">✗ Abnormal</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Catatan Tambahan</Label>
                <Textarea v-model="form.transport_notes" rows="3" />
              </div>
            </CardContent>
          </Card>

          <div v-if="!canProceed" class="p-4 bg-destructive/10 border border-destructive rounded-lg">
            <p class="text-sm text-destructive font-medium">
              ⚠ Batch akan otomatis ditolak karena pemeriksaan visual/bau tidak normal
            </p>
          </div>

          <div class="flex justify-end gap-2">
            <Link :href="route('quality-control.index')">
              <Button type="button" variant="outline">Batal</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Memproses...' : 'Terima Batch' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
