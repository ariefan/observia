<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Microscope, Beaker, AlertCircle } from 'lucide-vue-next';

interface Props {
  batch: any;
  qualityStandards: {
    grade_a: any;
    grade_b: any;
    grade_c: any;
  };
}

const props = defineProps<Props>();

const form = useForm({
  quality_data: {
    pH: 6.7,
    fat_percentage: 3.5,
    protein_percentage: null as number | null,
    bacteria_count: 50000,
    temperature: 6.0,
    snf_percentage: null as number | null,
  },
  quality_notes: '',
});

const predictedGrade = ref<string | null>(null);

const calculateGrade = () => {
  const data = form.quality_data;
  const standards = props.qualityStandards;

  // Check Grade A
  if (
    data.pH >= standards.grade_a.pH_min &&
    data.pH <= standards.grade_a.pH_max &&
    data.fat_percentage >= standards.grade_a.fat_min &&
    data.bacteria_count <= standards.grade_a.bacteria_max
  ) {
    predictedGrade.value = 'A';
    return;
  }

  // Check Grade B
  if (
    data.pH >= standards.grade_b.pH_min &&
    data.pH <= standards.grade_b.pH_max &&
    data.fat_percentage >= standards.grade_b.fat_min &&
    data.bacteria_count <= standards.grade_b.bacteria_max
  ) {
    predictedGrade.value = 'B';
    return;
  }

  // Check Grade C
  if (
    data.pH >= standards.grade_c.pH_min &&
    data.pH <= standards.grade_c.pH_max &&
    data.fat_percentage >= standards.grade_c.fat_min &&
    data.bacteria_count <= standards.grade_c.bacteria_max
  ) {
    predictedGrade.value = 'C';
    return;
  }

  predictedGrade.value = 'Reject';
};

watch(() => form.quality_data, calculateGrade, { deep: true });

const submit = () => {
  form.post(route('quality-control.test', props.batch.id));
};

const getGradeColor = (grade: string | null) => {
  if (!grade) return 'text-gray-500';
  const colors: Record<string, string> = {
    A: 'text-green-600',
    B: 'text-blue-600',
    C: 'text-yellow-600',
    Reject: 'text-red-600',
  };
  return colors[grade] || 'text-gray-500';
};
</script>

<template>
  <Head title="Uji Kualitas Batch" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="quality-control.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-4xl mx-auto">
        <div class="flex items-center gap-4">
          <Link :href="route('quality-control.index')">
            <Button variant="ghost" size="sm">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-xl font-semibold">Uji Kualitas: {{ batch.batch_code }}</h1>
            <p class="text-sm text-muted-foreground">Input hasil pengujian laboratorium</p>
          </div>
        </div>

        <!-- Predicted Grade Alert -->
        <Card v-if="predictedGrade" class="border-2" :class="{
          'border-green-500': predictedGrade === 'A',
          'border-blue-500': predictedGrade === 'B',
          'border-yellow-500': predictedGrade === 'C',
          'border-red-500': predictedGrade === 'Reject',
        }">
          <CardContent class="pt-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <Beaker class="w-8 h-8" :class="getGradeColor(predictedGrade)" />
                <div>
                  <p class="text-sm text-muted-foreground">Prediksi Grade</p>
                  <p class="text-2xl font-bold" :class="getGradeColor(predictedGrade)">
                    Grade {{ predictedGrade }}
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <form @submit.prevent="submit" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Parameter Kualitas Utama</CardTitle>
              <CardDescription>Masukkan hasil pengujian laboratorium</CardDescription>
            </CardHeader>
            <CardContent class="grid grid-cols-2 gap-4">
              <div>
                <Label>pH <span class="text-red-500">*</span></Label>
                <Input
                  type="number"
                  step="0.01"
                  min="0"
                  max="14"
                  v-model.number="form.quality_data.pH"
                  required
                />
                <p class="text-xs text-muted-foreground mt-1">Normal: 6.4-7.0</p>
              </div>
              <div>
                <Label>Lemak (%) <span class="text-red-500">*</span></Label>
                <Input
                  type="number"
                  step="0.1"
                  min="0"
                  v-model.number="form.quality_data.fat_percentage"
                  required
                />
                <p class="text-xs text-muted-foreground mt-1">Minimal: 2.5%</p>
              </div>
              <div>
                <Label>Jumlah Bakteri (CFU/ml) <span class="text-red-500">*</span></Label>
                <Input
                  type="number"
                  min="0"
                  v-model.number="form.quality_data.bacteria_count"
                  required
                />
                <p class="text-xs text-muted-foreground mt-1">Maksimal Grade A: 100,000</p>
              </div>
              <div>
                <Label>Suhu (°C) <span class="text-red-500">*</span></Label>
                <Input
                  type="number"
                  step="0.1"
                  min="0"
                  v-model.number="form.quality_data.temperature"
                  required
                />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Parameter Tambahan (Opsional)</CardTitle>
            </CardHeader>
            <CardContent class="grid grid-cols-2 gap-4">
              <div>
                <Label>Protein (%)</Label>
                <Input
                  type="number"
                  step="0.1"
                  min="0"
                  v-model.number="form.quality_data.protein_percentage"
                />
              </div>
              <div>
                <Label>SNF - Solids Not Fat (%)</Label>
                <Input
                  type="number"
                  step="0.1"
                  min="0"
                  v-model.number="form.quality_data.snf_percentage"
                />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Catatan QC</CardTitle>
            </CardHeader>
            <CardContent>
              <Textarea
                v-model="form.quality_notes"
                placeholder="Catatan tambahan tentang hasil pengujian..."
                rows="4"
              />
            </CardContent>
          </Card>

          <div class="flex justify-end gap-2">
            <Link :href="route('quality-control.index')">
              <Button type="button" variant="outline">Batal</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">
              <Microscope class="w-4 h-4 mr-2" />
              {{ form.processing ? 'Memproses...' : 'Simpan Hasil Uji' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
