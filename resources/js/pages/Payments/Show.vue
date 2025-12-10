<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Wallet, FileText, Milk } from 'lucide-vue-next';

interface Props {
  payment: any;
  relatedBatches: any[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount);
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, string> = {
    draft: 'secondary',
    approved: 'default',
    paid: 'default',
  };
  return badges[status] || 'default';
};
</script>

<template>
  <Head :title="`Pembayaran - ${payment.farm?.name}`" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="payments.finance" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link :href="route('payments.finance')">
              <Button variant="ghost" size="sm">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Kembali
              </Button>
            </Link>
            <div>
              <div class="flex items-center gap-2">
                <h1 class="text-xl font-semibold">{{ payment.farm?.name }}</h1>
                <Badge :variant="getStatusBadge(payment.status)">
                  {{ payment.status }}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground">
                Periode: {{ formatDate(payment.payment_period_start) }} - {{ formatDate(payment.payment_period_end) }}
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Wallet class="w-5 h-5" />
                Ringkasan Pembayaran
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Total Volume</span>
                <span class="text-sm font-semibold">{{ payment.total_liters }} liter</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Jumlah Kotor</span>
                <span class="text-sm font-semibold">{{ formatCurrency(payment.gross_amount) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-muted-foreground">Total Potongan</span>
                <span class="text-sm font-semibold text-red-600">{{ formatCurrency(payment.deductions_total) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t">
                <span class="text-sm font-medium">Jumlah Bersih</span>
                <span class="text-lg font-bold text-green-600">{{ formatCurrency(payment.net_amount) }}</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Breakdown per Grade</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div v-for="(data, grade) in payment.grade_breakdown" :key="grade" class="p-3 bg-muted/30 rounded-lg">
                <div class="flex justify-between items-center mb-1">
                  <Badge>Grade {{ grade }}</Badge>
                  <span class="text-sm font-semibold">{{ data.liters }} liter</span>
                </div>
                <div class="flex justify-between items-center text-xs text-muted-foreground">
                  <span>{{ formatCurrency(data.rate) }} / liter</span>
                  <span class="font-semibold text-foreground">{{ formatCurrency(data.amount) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card v-if="payment.deductions && payment.deductions.length > 0">
          <CardHeader>
            <CardTitle>Potongan</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div v-for="(deduction, i) in payment.deductions" :key="i" class="flex justify-between items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                <div>
                  <div class="text-sm font-medium">{{ deduction.type }}</div>
                  <div class="text-xs text-muted-foreground">{{ deduction.description }}</div>
                </div>
                <span class="font-semibold text-red-600">{{ formatCurrency(deduction.amount) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <FileText class="w-5 h-5" />
              Informasi Pembayaran
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <span class="text-sm text-muted-foreground">Dihitung Oleh</span>
                <p class="text-sm font-medium">{{ payment.calculated_by?.name || '-' }}</p>
                <p class="text-xs text-muted-foreground">{{ payment.calculated_at ? formatDateTime(payment.calculated_at) : '-' }}</p>
              </div>
              <div>
                <span class="text-sm text-muted-foreground">Disetujui Oleh</span>
                <p class="text-sm font-medium">{{ payment.approved_by?.name || '-' }}</p>
                <p class="text-xs text-muted-foreground">{{ payment.approved_at ? formatDateTime(payment.approved_at) : '-' }}</p>
              </div>
              <div v-if="payment.status === 'paid'">
                <span class="text-sm text-muted-foreground">Dibayar Oleh</span>
                <p class="text-sm font-medium">{{ payment.paid_by?.name || '-' }}</p>
                <p class="text-xs text-muted-foreground">{{ payment.paid_at ? formatDateTime(payment.paid_at) : '-' }}</p>
              </div>
              <div v-if="payment.payment_reference">
                <span class="text-sm text-muted-foreground">Referensi Pembayaran</span>
                <p class="text-sm font-medium font-mono">{{ payment.payment_reference }}</p>
                <p class="text-xs text-muted-foreground">{{ payment.payment_method }}</p>
              </div>
            </div>
            <div v-if="payment.notes" class="pt-2 border-t">
              <span class="text-sm text-muted-foreground">Catatan</span>
              <p class="text-sm mt-1">{{ payment.notes }}</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Milk class="w-5 h-5" />
              Batch Susu Terkait ({{ relatedBatches.length }})
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-2 text-left">Kode Batch</th>
                    <th class="p-2 text-left">Tanggal Koleksi</th>
                    <th class="p-2 text-left">Session</th>
                    <th class="p-2 text-right">Volume (L)</th>
                    <th class="p-2 text-left">Grade</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="batch in relatedBatches" :key="batch.id" class="border-b hover:bg-muted/30">
                    <td class="p-2 font-mono">
                      <Link :href="route('milk-batches.show', batch.id)" class="hover:underline text-blue-600">
                        {{ batch.batch_code }}
                      </Link>
                    </td>
                    <td class="p-2">{{ formatDate(batch.collection_date) }}</td>
                    <td class="p-2">{{ batch.session }}</td>
                    <td class="p-2 text-right font-semibold">{{ batch.total_volume }}</td>
                    <td class="p-2">
                      <Badge>Grade {{ batch.quality_grade }}</Badge>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-muted/50 font-semibold">
                  <tr>
                    <td colspan="3" class="p-2">Total</td>
                    <td class="p-2 text-right">{{ payment.total_liters }} L</td>
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
