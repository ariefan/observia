<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Button } from '@/components/ui/button';
import { Milk, TrendingUp, Clock, Package, AlertTriangle, CheckCircle, Wallet, Activity } from 'lucide-vue-next';

interface Props {
  stats: any;
  recentBatches: any[];
  agingCheese: any[];
  alerts: any[];
}

const props = defineProps<Props>();

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

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    collected: 'bg-blue-100 text-blue-800',
    in_transit: 'bg-yellow-100 text-yellow-800',
    received: 'bg-purple-100 text-purple-800',
    tested: 'bg-indigo-100 text-indigo-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    in_production: 'bg-orange-100 text-orange-800',
    aging: 'bg-teal-100 text-teal-800',
    completed: 'bg-gray-100 text-gray-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const getAlertIcon = (type: string) => {
  return type === 'error' ? AlertTriangle : type === 'warning' ? AlertTriangle : CheckCircle;
};

const getAlertVariant = (type: string) => {
  return type === 'error' ? 'destructive' : type === 'warning' ? 'default' : 'default';
};
</script>

<template>
  <Head title="Supply Chain Dashboard" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="supply-chain.dashboard" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div>
          <h1 class="text-xl font-semibold text-primary">Supply Chain Dashboard</h1>
          <p class="text-sm text-muted-foreground">Monitor rantai pasok susu dan produksi keju</p>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium flex items-center gap-2">
                <Milk class="w-4 h-4" />
                Koleksi Hari Ini
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.collections_today }}</div>
              <div class="text-xs text-muted-foreground">{{ Number(stats.volume_today || 0).toFixed(1) }} liter</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium flex items-center gap-2">
                <Clock class="w-4 h-4" />
                Dalam Perjalanan
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-yellow-600">{{ stats.in_transit }}</div>
              <div class="text-xs text-muted-foreground">batch sedang dikirim</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium flex items-center gap-2">
                <Activity class="w-4 h-4" />
                Menunggu QC
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-orange-600">{{ stats.awaiting_qc }}</div>
              <div class="text-xs text-muted-foreground">batch perlu diuji</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium flex items-center gap-2">
                <Package class="w-4 h-4" />
                Dalam Produksi
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-blue-600">{{ stats.in_production }}</div>
              <div class="text-xs text-muted-foreground">batch keju aktif</div>
            </CardContent>
          </Card>
        </div>

        <!-- Grade Distribution & Monthly Volume -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Card>
            <CardHeader>
              <CardTitle class="text-sm font-medium">Distribusi Grade (30 hari terakhir)</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span>Grade A</span>
                  <span class="font-semibold">{{ stats.grade_a_percentage }}%</span>
                </div>
                <Progress :model-value="stats.grade_a_percentage" class="h-2 bg-green-100" />
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span>Grade B</span>
                  <span class="font-semibold">{{ stats.grade_b_percentage }}%</span>
                </div>
                <Progress :model-value="stats.grade_b_percentage" class="h-2 bg-yellow-100" />
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span>Grade C</span>
                  <span class="font-semibold">{{ stats.grade_c_percentage }}%</span>
                </div>
                <Progress :model-value="stats.grade_c_percentage" class="h-2 bg-orange-100" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle class="text-sm font-medium flex items-center gap-2">
                <TrendingUp class="w-4 h-4" />
                Ringkasan Bulan Ini
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Total Volume Susu</span>
                <span class="text-lg font-bold">{{ Number(stats.monthly_volume || 0).toFixed(1) }} L</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Keju Sedang Aging</span>
                <span class="text-lg font-bold text-blue-600">{{ stats.aging_cheese_count }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Pembayaran Pending</span>
                <span class="text-lg font-bold text-orange-600">{{ formatCurrency(stats.pending_payments || 0) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Alerts -->
        <div v-if="alerts && alerts.length > 0" class="space-y-2">
          <Alert v-for="(alert, i) in alerts" :key="i" :variant="getAlertVariant(alert.type)">
            <component :is="getAlertIcon(alert.type)" class="h-4 w-4" />
            <AlertTitle>{{ alert.title }}</AlertTitle>
            <AlertDescription>{{ alert.message }}</AlertDescription>
          </Alert>
        </div>

        <!-- Recent Batches -->
        <Card>
          <CardHeader>
            <div class="flex justify-between items-center">
              <CardTitle class="flex items-center gap-2">
                <Milk class="w-5 h-5" />
                Batch Susu Terbaru
              </CardTitle>
              <Link :href="route('milk-batches.index')">
                <Button variant="ghost" size="sm">Lihat Semua</Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="recentBatches.length === 0" class="text-center text-muted-foreground py-4">
              Belum ada batch susu
            </div>
            <div v-else class="space-y-2">
              <div v-for="batch in recentBatches" :key="batch.id"
                   class="flex items-center justify-between p-3 bg-muted/30 rounded-lg hover:bg-muted/50 transition-colors">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <Link :href="route('milk-batches.show', batch.id)" class="font-mono font-semibold hover:underline text-blue-600">
                      {{ batch.batch_code }}
                    </Link>
                    <Badge :class="getStatusColor(batch.status)" variant="outline">
                      {{ batch.status }}
                    </Badge>
                    <Badge v-if="batch.quality_grade" variant="default">
                      Grade {{ batch.quality_grade }}
                    </Badge>
                  </div>
                  <div class="text-xs text-muted-foreground mt-1">
                    {{ formatDate(batch.collection_date) }} · {{ batch.total_volume }}L
                    <span v-if="batch.collected_by"> · {{ batch.collected_by.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Aging Cheese -->
        <Card>
          <CardHeader>
            <div class="flex justify-between items-center">
              <CardTitle class="flex items-center gap-2">
                <Package class="w-5 h-5" />
                Keju Sedang Aging
              </CardTitle>
              <Link :href="route('cheese-productions.index')">
                <Button variant="ghost" size="sm">Lihat Semua</Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="agingCheese.length === 0" class="text-center text-muted-foreground py-4">
              Tidak ada keju yang sedang aging
            </div>
            <div v-else class="space-y-3">
              <div v-for="cheese in agingCheese" :key="cheese.id"
                   class="p-3 bg-muted/30 rounded-lg">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <Link :href="route('cheese-productions.show', cheese.id)" class="font-semibold hover:underline text-blue-600">
                      {{ cheese.batch_code }}
                    </Link>
                    <div class="text-sm text-muted-foreground">{{ cheese.cheese_type }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-lg font-bold">{{ cheese.progress }}%</div>
                    <div class="text-xs text-muted-foreground">
                      {{ cheese.days_aged }} / {{ cheese.target_days }} hari
                    </div>
                  </div>
                </div>
                <Progress :model-value="cheese.progress" class="h-2 mb-1" />
                <div class="text-xs text-muted-foreground">
                  <span v-if="cheese.days_remaining > 0">{{ cheese.days_remaining }} hari lagi</span>
                  <span v-else class="text-green-600 font-semibold">Siap diselesaikan!</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
