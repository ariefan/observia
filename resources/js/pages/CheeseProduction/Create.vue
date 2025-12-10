<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Plus, Milk } from 'lucide-vue-next';

interface Props {
  availableBatches: any[];
  cheeseTypes: string[];
}

const props = defineProps<Props>();

const form = useForm({
  cheese_type: '',
  production_date: new Date().toISOString().split('T')[0],
  milk_batch_ids: [] as number[],
  recipe_notes: '',
  starter_culture: '',
  rennet_type: '',
  rennet_amount: '',
  additional_ingredients: [] as any[],
  cheese_weight_kg: null as number | null,
  aging_target_days: null as number | null,
  storage_location: '',
});

const totalMilkVolume = computed(() => {
  return props.availableBatches
    .filter(b => form.milk_batch_ids.includes(b.id))
    .reduce((sum, b) => sum + parseFloat(b.total_volume), 0);
});

const estimatedYield = computed(() => {
  if (!form.cheese_weight_kg || totalMilkVolume.value === 0) return 0;
  return ((form.cheese_weight_kg / totalMilkVolume.value) * 100).toFixed(2);
});

const toggleBatch = (batchId: number) => {
  const index = form.milk_batch_ids.indexOf(batchId);
  if (index > -1) {
    form.milk_batch_ids.splice(index, 1);
  } else {
    form.milk_batch_ids.push(batchId);
  }
};

const submit = () => {
  form.post(route('cheese-productions.store'));
};
</script>

<template>
  <Head title="Produksi Keju Baru" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="cheese-productions.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-5xl mx-auto">
        <div class="flex items-center gap-4">
          <Link :href="route('cheese-productions.index')">
            <Button variant="ghost" size="sm">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-xl font-semibold">Produksi Keju Baru</h1>
            <p class="text-sm text-muted-foreground">Buat batch produksi keju dari susu yang disetujui</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Informasi Dasar</CardTitle>
            </CardHeader>
            <CardContent class="grid grid-cols-2 gap-4">
              <div>
                <Label>Tipe Keju <span class="text-red-500">*</span></Label>
                <Select v-model="form.cheese_type" required>
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih tipe keju" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="type in cheeseTypes" :key="type" :value="type">
                      {{ type }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Tanggal Produksi <span class="text-red-500">*</span></Label>
                <Input type="date" v-model="form.production_date" required />
              </div>
              <div>
                <Label>Berat Keju (kg)</Label>
                <Input type="number" step="0.01" v-model.number="form.cheese_weight_kg" />
              </div>
              <div>
                <Label>Target Aging (Hari)</Label>
                <Input type="number" v-model.number="form.aging_target_days" />
              </div>
              <div class="col-span-2">
                <Label>Lokasi Penyimpanan</Label>
                <Input v-model="form.storage_location" placeholder="Contoh: Cave A - Shelf 3" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Milk class="w-5 h-5" />
                Pilih Batch Susu
              </CardTitle>
              <p class="text-sm text-muted-foreground">
                Total Volume: {{ totalMilkVolume.toFixed(2) }}L
                <span v-if="estimatedYield > 0" class="ml-2">| Estimasi Yield: {{ estimatedYield }}%</span>
              </p>
            </CardHeader>
            <CardContent>
              <div v-if="availableBatches.length === 0" class="p-4 text-center text-muted-foreground">
                Tidak ada batch susu yang tersedia untuk produksi
              </div>
              <div v-else class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="batch in availableBatches"
                  :key="batch.id"
                  class="flex items-center gap-3 p-3 border rounded-lg hover:bg-muted/50"
                >
                  <Checkbox
                    :checked="form.milk_batch_ids.includes(batch.id)"
                    @update:checked="toggleBatch(batch.id)"
                  />
                  <div class="flex-1">
                    <div class="font-mono text-sm font-semibold">{{ batch.batch_code }}</div>
                    <div class="text-xs text-muted-foreground">
                      {{ batch.total_volume }}L - Grade {{ batch.quality_grade }}
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Resep & Proses</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label>Starter Culture</Label>
                  <Input v-model="form.starter_culture" placeholder="Contoh: Mesophilic MA4001" />
                </div>
                <div>
                  <Label>Tipe Rennet</Label>
                  <Input v-model="form.rennet_type" placeholder="Contoh: Microbial" />
                </div>
              </div>
              <div>
                <Label>Jumlah Rennet</Label>
                <Input v-model="form.rennet_amount" placeholder="Contoh: 2.5ml per 10L" />
              </div>
              <div>
                <Label>Catatan Resep</Label>
                <Textarea v-model="form.recipe_notes" rows="4" placeholder="Deskripsi proses produksi..." />
              </div>
            </CardContent>
          </Card>

          <div class="flex justify-end gap-2">
            <Link :href="route('cheese-productions.index')">
              <Button type="button" variant="outline">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing || form.milk_batch_ids.length === 0"
            >
              <Plus class="w-4 h-4 mr-2" />
              {{ form.processing ? 'Membuat...' : 'Buat Produksi' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
