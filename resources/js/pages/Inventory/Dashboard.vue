<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Package, AlertTriangle, TrendingUp, DollarSign } from 'lucide-vue-next';
import { formatRupiah, formatQuantity } from '@/utils/currency';

interface Stats {
  totalItems: number;
  lowStockItems: number;
  expiringItems: number;
  totalValue: number;
}

interface Category {
  id: number;
  name: string;
  color: string;
  icon: string;
  items_count: number;
}

interface Props {
  stats: Stats;
  categories: Category[];
  recentTransactions: any[];
}

defineProps<Props>();
</script>

<template>

  <Head title="Dashboard Inventaris" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="inventory.dashboard" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <div>
          <h1 class="text-xl font-semibold text-primary">Dashboard Inventaris</h1>
          <p class="text-sm text-muted-foreground">Kelola inventaris peternakan Anda</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card>
            <CardContent class="flex items-center p-4">
              <Package class="h-8 w-8 text-blue-600 mr-3" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Barang</p>
                <p class="text-2xl font-bold">{{ stats.totalItems }}</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="flex items-center p-4">
              <AlertTriangle class="h-8 w-8 text-orange-600 mr-3" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">Stok Menipis</p>
                <p class="text-2xl font-bold">{{ stats.lowStockItems }}</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="flex items-center p-4">
              <TrendingUp class="h-8 w-8 text-red-600 mr-3" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">Hampir Kadaluarsa</p>
                <p class="text-2xl font-bold">{{ stats.expiringItems }}</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="flex items-center p-4">
              <span class="text-green-600 mr-3 font-semibold text-2xl">Rp</span>
              <div>
                <p class="text-sm font-medium text-muted-foreground">Total Nilai</p>
                <p class="text-2xl font-bold">{{ formatQuantity(stats.totalValue) }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Categories Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <Card>
            <CardHeader>
              <CardTitle>Kategori Inventaris</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div v-for="category in categories" :key="category.id"
                  class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                  <div class="flex items-center gap-3">
                    <div :class="['w-3 h-3 rounded-full']" :style="{ backgroundColor: category.color }"></div>
                    <span class="font-medium">{{ category.name }}</span>
                  </div>
                  <Badge variant="secondary">{{ category.items_count }} item</Badge>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Transaksi Terbaru</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="recentTransactions.length === 0" class="text-center py-8 text-muted-foreground">
                Belum ada transaksi
              </div>
              <div v-else class="space-y-3">
                <!-- TODO: Add recent transactions list -->
                <p class="text-sm text-muted-foreground">Transaksi terbaru akan ditampilkan di sini</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>