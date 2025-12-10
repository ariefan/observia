<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Calculator, DollarSign, Plus, Trash } from 'lucide-vue-next';

interface Props {
  farms: any[];
  preview?: any;
}

const props = defineProps<Props>();

const form = useForm({
  farm_id: '',
  payment_period_start: '',
  payment_period_end: '',
  deductions: [] as { type: string; amount: number; description: string }[],
  notes: '',
});

const previewData = ref(props.preview || null);

const calculatePreview = () => {
  if (!form.farm_id || !form.payment_period_start || !form.payment_period_end) {
    return;
  }

  router.get(route('payments.calculate'), {
    farm_id: form.farm_id,
    payment_period_start: form.payment_period_start,
    payment_period_end: form.payment_period_end,
    preview: true,
  }, {
    preserveState: true,
    onSuccess: (page: any) => {
      previewData.value = page.props.preview;
    },
  });
};

const addDeduction = () => {
  form.deductions.push({ type: '', amount: 0, description: '' });
};

const removeDeduction = (index: number) => {
  form.deductions.splice(index, 1);
};

const totalDeductions = computed(() => {
  return form.deductions.reduce((sum, d) => sum + (Number(d.amount) || 0), 0);
});

const netAmount = computed(() => {
  if (!previewData.value) return 0;
  return previewData.value.gross_amount - totalDeductions.value;
});

const submit = () => {
  form.post(route('payments.calculate.store'));
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount);
};
</script>

<template>
  <Head title="Hitung Pembayaran" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="payments.finance" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-5xl mx-auto">
        <div class="flex items-center gap-4">
          <Link :href="route('payments.finance')">
            <Button variant="ghost" size="sm">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-xl font-semibold">Hitung Pembayaran Susu</h1>
            <p class="text-sm text-muted-foreground">Hitung pembayaran untuk peternak berdasarkan periode</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Informasi Dasar</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label>Farm Peternak <span class="text-red-500">*</span></Label>
                <Select v-model="form.farm_id" required @update:model-value="calculatePreview">
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih farm peternak" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="farm in farms" :key="farm.id" :value="farm.id">
                      {{ farm.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label>Tanggal Mulai <span class="text-red-500">*</span></Label>
                  <Input type="date" v-model="form.payment_period_start" required @change="calculatePreview" />
                </div>
                <div>
                  <Label>Tanggal Akhir <span class="text-red-500">*</span></Label>
                  <Input type="date" v-model="form.payment_period_end" required @change="calculatePreview" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card v-if="previewData">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Calculator class="w-5 h-5" />
                Preview Perhitungan
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-3 gap-4 p-4 bg-muted/30 rounded-lg">
                <div class="text-center">
                  <div class="text-2xl font-bold">{{ previewData.total_liters }}</div>
                  <div class="text-xs text-muted-foreground">Total Liter</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ formatCurrency(previewData.gross_amount) }}</div>
                  <div class="text-xs text-muted-foreground">Jumlah Kotor</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(netAmount) }}</div>
                  <div class="text-xs text-muted-foreground">Jumlah Bersih</div>
                </div>
              </div>

              <div>
                <div class="text-sm font-medium mb-2">Breakdown per Grade:</div>
                <div class="space-y-2">
                  <div v-for="(data, grade) in previewData.grade_breakdown" :key="grade" class="flex justify-between items-center p-3 bg-muted/20 rounded">
                    <div class="flex items-center gap-2">
                      <Badge>Grade {{ grade }}</Badge>
                      <span class="text-sm">{{ data.liters }} liter × {{ formatCurrency(data.rate) }}/L</span>
                    </div>
                    <span class="font-semibold">{{ formatCurrency(data.amount) }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle>Potongan</CardTitle>
                <Button type="button" variant="outline" size="sm" @click="addDeduction">
                  <Plus class="w-4 h-4 mr-2" />
                  Tambah Potongan
                </Button>
              </div>
            </CardHeader>
            <CardContent class="space-y-3">
              <div v-if="form.deductions.length === 0" class="text-center text-sm text-muted-foreground py-4">
                Tidak ada potongan
              </div>
              <div v-for="(deduction, index) in form.deductions" :key="index" class="flex gap-3 items-start p-3 border rounded-lg">
                <div class="flex-1 grid grid-cols-3 gap-2">
                  <Input v-model="deduction.type" placeholder="Tipe (misal: Pinjaman)" />
                  <Input type="number" v-model.number="deduction.amount" placeholder="Jumlah" />
                  <Input v-model="deduction.description" placeholder="Keterangan" />
                </div>
                <Button type="button" variant="ghost" size="sm" @click="removeDeduction(index)">
                  <Trash class="w-4 h-4 text-red-500" />
                </Button>
              </div>
              <div v-if="totalDeductions > 0" class="flex justify-between items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                <span class="text-sm font-medium">Total Potongan:</span>
                <span class="font-bold text-red-600">{{ formatCurrency(totalDeductions) }}</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Catatan</CardTitle>
            </CardHeader>
            <CardContent>
              <Textarea v-model="form.notes" rows="3" placeholder="Catatan tambahan untuk pembayaran..." />
            </CardContent>
          </Card>

          <div class="flex justify-end gap-2">
            <Link :href="route('payments.finance')">
              <Button type="button" variant="outline">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing || !previewData"
            >
              <DollarSign class="w-4 h-4 mr-2" />
              {{ form.processing ? 'Membuat...' : 'Buat Draft Pembayaran' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
