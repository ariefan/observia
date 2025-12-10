<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Wallet, TrendingUp, Calendar, DollarSign } from 'lucide-vue-next';

interface Props {
  payments: { data: any[]; total: number; links: any[] };
  stats?: any;
}

const props = defineProps<Props>();

const getStatusBadge = (status: string) => {
  const badges: Record<string, { variant: string; text: string }> = {
    draft: { variant: 'secondary', text: 'Draft' },
    approved: { variant: 'default', text: 'Disetujui' },
    paid: { variant: 'default', text: 'Dibayar' },
  };
  return badges[status] || { variant: 'default', text: status };
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
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
  <Head title="Riwayat Pembayaran" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="payments.farmer" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-primary">Riwayat Pembayaran</h1>
            <p class="text-sm text-muted-foreground">Lihat pembayaran susu Anda</p>
          </div>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Menunggu Pembayaran</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(stats.pending_amount || 0) }}</div>
              <div class="text-xs text-muted-foreground">{{ stats.pending_count || 0 }} transaksi</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Diterima Bulan Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.paid_month || 0) }}</div>
              <div class="text-xs text-muted-foreground">{{ stats.paid_count_month || 0 }} transaksi</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Tahun Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ formatCurrency(stats.paid_year || 0) }}</div>
              <div class="text-xs text-muted-foreground">{{ stats.total_liters_year || 0 }} liter</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Rata-rata per Bulan</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ formatCurrency(stats.avg_monthly || 0) }}</div>
            </CardContent>
          </Card>
        </div>

        <!-- Payment History -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Wallet class="w-5 h-5" />
              Riwayat Pembayaran
            </CardTitle>
          </CardHeader>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-3 text-left text-xs font-medium">Periode</th>
                    <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                    <th class="p-3 text-left text-xs font-medium">Grade A/B/C</th>
                    <th class="p-3 text-right text-xs font-medium">Jumlah Kotor</th>
                    <th class="p-3 text-right text-xs font-medium">Potongan</th>
                    <th class="p-3 text-right text-xs font-medium">Jumlah Bersih</th>
                    <th class="p-3 text-left text-xs font-medium">Status</th>
                    <th class="p-3 text-left text-xs font-medium">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="payments.data.length === 0">
                    <td colspan="8" class="p-8 text-center text-muted-foreground">
                      <Wallet class="w-12 h-12 mx-auto mb-2 opacity-30" />
                      <p>Belum ada riwayat pembayaran</p>
                    </td>
                  </tr>
                  <tr v-for="payment in payments.data" :key="payment.id" class="border-b hover:bg-muted/30">
                    <td class="p-3 text-sm">
                      {{ formatDate(payment.payment_period_start) }} - {{ formatDate(payment.payment_period_end) }}
                    </td>
                    <td class="p-3 text-sm font-semibold">{{ payment.total_liters }}</td>
                    <td class="p-3 text-xs">
                      <div class="flex gap-1">
                        <Badge v-if="payment.grade_breakdown?.A" variant="default" class="text-xs">
                          A: {{ payment.grade_breakdown.A.liters }}L
                        </Badge>
                        <Badge v-if="payment.grade_breakdown?.B" variant="secondary" class="text-xs">
                          B: {{ payment.grade_breakdown.B.liters }}L
                        </Badge>
                        <Badge v-if="payment.grade_breakdown?.C" variant="secondary" class="text-xs">
                          C: {{ payment.grade_breakdown.C.liters }}L
                        </Badge>
                      </div>
                    </td>
                    <td class="p-3 text-right text-sm">{{ formatCurrency(payment.gross_amount) }}</td>
                    <td class="p-3 text-right text-sm text-red-600">{{ formatCurrency(payment.deductions_total) }}</td>
                    <td class="p-3 text-right text-sm font-semibold text-green-600">{{ formatCurrency(payment.net_amount) }}</td>
                    <td class="p-3">
                      <Badge :variant="getStatusBadge(payment.status).variant">
                        {{ getStatusBadge(payment.status).text }}
                      </Badge>
                    </td>
                    <td class="p-3">
                      <Link :href="route('payments.show', payment.id)">
                        <Button variant="ghost" size="sm">Detail</Button>
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
