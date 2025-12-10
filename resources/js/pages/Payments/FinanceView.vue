<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Wallet, CheckCircle, Clock, DollarSign } from 'lucide-vue-next';

interface Props {
  payments: { data: any[]; total: number; links: any[] };
  stats?: any;
  filters?: any;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'draft');

const applyFilters = () => {
  router.get(route('payments.finance'), {
    search: searchQuery.value || undefined,
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

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

const approvePayment = (paymentId: number) => {
  if (confirm('Yakin ingin menyetujui pembayaran ini?')) {
    router.post(route('payments.approve', paymentId), {}, {
      preserveState: true,
    });
  }
};

const markAsPaid = (paymentId: number) => {
  const reference = prompt('Masukkan nomor referensi pembayaran:');
  if (reference) {
    router.post(route('payments.mark-paid', paymentId), {
      payment_reference: reference,
      payment_method: 'bank_transfer',
      paid_at: new Date().toISOString(),
    }, {
      preserveState: true,
    });
  }
};
</script>

<template>
  <Head title="Manajemen Pembayaran" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="payments.finance" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-primary">Manajemen Pembayaran</h1>
            <p class="text-sm text-muted-foreground">Kelola pembayaran susu ke peternak</p>
          </div>
          <Link :href="route('payments.calculate')">
            <Button>
              <DollarSign class="w-4 h-4 mr-2" />
              Hitung Pembayaran
            </Button>
          </Link>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Draft</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.draft_count || 0 }}</div>
              <div class="text-xs text-muted-foreground">{{ formatCurrency(stats.draft_amount || 0) }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Menunggu Bayar</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-orange-600">{{ stats.approved_count || 0 }}</div>
              <div class="text-xs text-muted-foreground">{{ formatCurrency(stats.approved_amount || 0) }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Dibayar Bulan Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">{{ stats.paid_count_month || 0 }}</div>
              <div class="text-xs text-muted-foreground">{{ formatCurrency(stats.paid_amount_month || 0) }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Tahun Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ formatCurrency(stats.paid_amount_year || 0) }}</div>
            </CardContent>
          </Card>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
          <div>
            <label class="text-xs font-medium">Cari</label>
            <input
              v-model="searchQuery"
              @input="applyFilters"
              type="text"
              placeholder="Nama farm / periode..."
              class="h-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>
          <div>
            <label class="text-xs font-medium">Status</label>
            <select v-model="selectedStatus" @change="applyFilters" class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="approved">Disetujui</option>
              <option value="paid">Dibayar</option>
            </select>
          </div>
        </div>

        <!-- Payments Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-3 text-left text-xs font-medium">Farm</th>
                    <th class="p-3 text-left text-xs font-medium">Periode</th>
                    <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
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
                      <p>Belum ada data pembayaran</p>
                    </td>
                  </tr>
                  <tr v-for="payment in payments.data" :key="payment.id" class="border-b hover:bg-muted/30">
                    <td class="p-3 text-sm font-medium">{{ payment.farm?.name }}</td>
                    <td class="p-3 text-sm">
                      {{ formatDate(payment.payment_period_start) }} - {{ formatDate(payment.payment_period_end) }}
                    </td>
                    <td class="p-3 text-sm">{{ payment.total_liters }}</td>
                    <td class="p-3 text-right text-sm">{{ formatCurrency(payment.gross_amount) }}</td>
                    <td class="p-3 text-right text-sm text-red-600">{{ formatCurrency(payment.deductions_total) }}</td>
                    <td class="p-3 text-right text-sm font-semibold">{{ formatCurrency(payment.net_amount) }}</td>
                    <td class="p-3">
                      <Badge :variant="getStatusBadge(payment.status).variant">
                        {{ getStatusBadge(payment.status).text }}
                      </Badge>
                    </td>
                    <td class="p-3 space-x-2">
                      <Link :href="route('payments.show', payment.id)">
                        <Button variant="ghost" size="sm">Detail</Button>
                      </Link>
                      <Button
                        v-if="payment.status === 'draft'"
                        variant="outline"
                        size="sm"
                        @click="approvePayment(payment.id)"
                      >
                        <CheckCircle class="w-3 h-3 mr-1" />
                        Setujui
                      </Button>
                      <Button
                        v-if="payment.status === 'approved'"
                        variant="default"
                        size="sm"
                        @click="markAsPaid(payment.id)"
                      >
                        <DollarSign class="w-3 h-3 mr-1" />
                        Bayar
                      </Button>
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
