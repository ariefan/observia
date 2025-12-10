<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Progress } from '@/components/ui/progress';
import { ArrowLeft, Clock, CheckCircle } from 'lucide-vue-next';

interface Props {
  production: any;
  daysAged: number | null;
  daysRemaining: number | null;
}

const props = defineProps<Props>();

const showAddNote = ref(false);

const noteForm = useForm({
  check_date: new Date().toISOString().split('T')[0],
  notes: '',
  weight_kg: null as number | null,
});

const addNote = () => {
  noteForm.post(route('cheese-productions.aging-note', props.production.id), {
    onSuccess: () => {
      noteForm.reset();
      showAddNote.value = false;
    },
  });
};

const completeAging = () => {
  if (confirm('Yakin ingin menyelesaikan proses aging?')) {
    useForm({}).post(route('cheese-productions.complete-aging', props.production.id));
  }
};

const progressPercent = () => {
  if (!props.production.aging_target_days || !props.daysAged) return 0;
  return Math.min((props.daysAged / props.production.aging_target_days) * 100, 100);
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
  <Head title="Aging Tracker" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="cheese-productions.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-5xl mx-auto">
        <div class="flex items-center gap-4">
          <Link :href="route('cheese-productions.show', production.id)">
            <Button variant="ghost" size="sm">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-xl font-semibold">Aging Tracker</h1>
            <p class="text-sm text-muted-foreground">{{ production.batch_code }} - {{ production.cheese_type }}</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Clock class="w-5 h-5" />
              Progress Aging
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div class="flex justify-between mb-2">
                <span class="text-sm font-medium">{{ daysAged || 0 }} / {{ production.aging_target_days }} hari</span>
                <span class="text-sm text-muted-foreground">
                  {{ daysRemaining !== null && daysRemaining > 0 ? `${daysRemaining} hari lagi` : 'Selesai' }}
                </span>
              </div>
              <Progress :model-value="progressPercent()" class="h-2" />
            </div>
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <div class="text-2xl font-bold">{{ daysAged || 0 }}</div>
                <div class="text-xs text-muted-foreground">Hari Aging</div>
              </div>
              <div>
                <div class="text-2xl font-bold">{{ production.aging_target_days }}</div>
                <div class="text-xs text-muted-foreground">Target</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-green-600">{{ progressPercent().toFixed(0) }}%</div>
                <div class="text-xs text-muted-foreground">Progress</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Catatan Aging</CardTitle>
              <Button v-if="!showAddNote" size="sm" @click="showAddNote = true">Tambah Catatan</Button>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-if="showAddNote" class="p-4 border rounded-lg space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <Label>Tanggal Pemeriksaan</Label>
                  <Input type="date" v-model="noteForm.check_date" />
                </div>
                <div>
                  <Label>Berat (kg)</Label>
                  <Input type="number" step="0.01" v-model.number="noteForm.weight_kg" />
                </div>
              </div>
              <div>
                <Label>Catatan</Label>
                <Textarea v-model="noteForm.notes" rows="3" placeholder="Kondisi keju, perubahan yang terlihat..." />
              </div>
              <div class="flex gap-2">
                <Button size="sm" @click="addNote" :disabled="noteForm.processing">Simpan</Button>
                <Button size="sm" variant="outline" @click="showAddNote = false">Batal</Button>
              </div>
            </div>

            <div v-if="production.aging_notes?.length > 0" class="space-y-3">
              <div v-for="(note, i) in production.aging_notes" :key="i" class="p-3 bg-muted/30 rounded-lg">
                <div class="flex justify-between text-sm mb-1">
                  <span class="font-medium">Hari ke-{{ note.days_aged }}</span>
                  <span class="text-muted-foreground">{{ formatDate(note.check_date) }}</span>
                </div>
                <p class="text-sm">{{ note.notes }}</p>
                <div v-if="note.weight_kg" class="text-xs text-muted-foreground mt-1">
                  Berat: {{ note.weight_kg }} kg
                </div>
              </div>
            </div>
            <div v-else class="text-center text-muted-foreground text-sm py-4">
              Belum ada catatan aging
            </div>
          </CardContent>
        </Card>

        <div v-if="daysRemaining !== null && daysRemaining <= 0" class="flex justify-end">
          <Button @click="completeAging" size="lg" class="bg-green-600 hover:bg-green-700">
            <CheckCircle class="w-5 h-5 mr-2" />
            Selesaikan Aging
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
