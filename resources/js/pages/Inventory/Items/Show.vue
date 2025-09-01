<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, ArrowRight, ArrowUp, Minus, RotateCcw } from 'lucide-vue-next';
import { formatRupiah, formatQuantity } from '@/utils/currency';

interface Category {
  id: number;
  name: string;
  color: string;
}

interface Unit {
  id: number;
  name: string;
  symbol: string;
  type: string;
}

interface Transaction {
  id: number;
  type: string;
  quantity: number;
  unit_cost?: number;
  total_cost?: number;
  notes?: string;
  transaction_date: string;
  user: {
    name: string;
  };
}

interface InventoryItem {
  id: number;
  name: string;
  brand?: string;
  description?: string;
  sku?: string;
  unit_cost?: number;
  selling_price?: number;
  minimum_stock: number;
  current_stock: number;
  track_expiry: boolean;
  track_batch: boolean;
  category: Category;
  unit: Unit;
  transactions?: Transaction[];
  specifications?: Record<string, any>;
}

interface Props {
  item: InventoryItem;
}

const props = defineProps<Props>();

const getStockStatusColor = (current: number, minimum: number) => {
  if (current <= minimum) return 'destructive';
  if (current <= minimum * 1.5) return 'warning';
  return 'default';
};

const getStockStatusText = (current: number, minimum: number) => {
  if (current <= minimum) return 'Habis';
  if (current <= minimum * 1.5) return 'Menipis';
  return 'Normal';
};

const getTransactionIcon = (type: string) => {
  switch (type) {
    case 'in': return ArrowRight;
    case 'out': return ArrowLeft;
    case 'adjustment': return RotateCcw;
    default: return Minus;
  }
};

const getTransactionColor = (type: string) => {
  switch (type) {
    case 'in': return 'bg-green-500';
    case 'out': return 'bg-red-500';
    case 'adjustment': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

const getTransactionType = (type: string) => {
  switch (type) {
    case 'in': return 'Masuk';
    case 'out': return 'Keluar';
    case 'adjustment': return 'Penyesuaian';
    case 'expired': return 'Kadaluarsa';
    case 'damaged': return 'Rusak';
    default: return type;
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<template>
  <Head :title="`Item: ${item.name}`" />
  
  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="inventory.items.show" />
      <div class="flex-1 p-6">
        <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <Link :href="route('inventory.items.index')">
            <Button variant="ghost" size="sm" class="p-2">
              <ArrowLeft class="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 class="text-2xl font-bold">{{ item.name }}</h1>
            <p v-if="item.brand" class="text-muted-foreground">{{ item.brand }}</p>
          </div>
        </div>
        <Link :href="route('inventory.items.edit', item.id)">
          <Button>
            <Edit class="h-4 w-4 mr-2" />
            Edit Item
          </Button>
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Item Information -->
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Informasi Item</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Nama</label>
                  <p class="text-sm">{{ item.name }}</p>
                </div>
                <div v-if="item.brand">
                  <label class="text-sm font-medium text-muted-foreground">Merek</label>
                  <p class="text-sm">{{ item.brand }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Kategori</label>
                  <div class="flex items-center gap-2">
                    <div :class="['w-2 h-2 rounded-full']" :style="{ backgroundColor: item.category.color }"></div>
                    <span class="text-sm">{{ item.category.name }}</span>
                  </div>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Satuan</label>
                  <p class="text-sm">{{ item.unit.name }} ({{ item.unit.symbol }})</p>
                </div>
                <div v-if="item.sku">
                  <label class="text-sm font-medium text-muted-foreground">SKU</label>
                  <p class="text-sm font-mono">{{ item.sku }}</p>
                </div>
              </div>

              <div v-if="item.description">
                <label class="text-sm font-medium text-muted-foreground">Deskripsi</label>
                <p class="text-sm">{{ item.description }}</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="item.unit_cost">
                  <label class="text-sm font-medium text-muted-foreground">Harga Beli per Unit</label>
                  <p class="text-sm">{{ formatRupiah(item.unit_cost) }}</p>
                </div>
                <div v-if="item.selling_price">
                  <label class="text-sm font-medium text-muted-foreground">Harga Jual per Unit</label>
                  <p class="text-sm">{{ formatRupiah(item.selling_price) }}</p>
                </div>
              </div>

              <div class="flex gap-4">
                <Badge v-if="item.track_expiry" variant="secondary">Lacak Kadaluarsa</Badge>
                <Badge v-if="item.track_batch" variant="secondary">Lacak Batch</Badge>
              </div>
            </CardContent>
          </Card>

          <!-- Recent Transactions -->
          <Card>
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle>Transaksi Terbaru</CardTitle>
                <Link :href="route('inventory.transactions.index', { search: item.name })">
                  <Button variant="outline" size="sm">
                    Lihat Semua
                  </Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              <div v-if="!item.transactions || item.transactions.length === 0" class="text-center py-8 text-muted-foreground">
                Belum ada transaksi untuk item ini
              </div>
              <div v-else class="space-y-3">
                <div v-for="transaction in item.transactions" :key="transaction.id" 
                     class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                  <div class="flex items-center gap-3">
                    <div :class="['p-1.5 rounded-full text-white', getTransactionColor(transaction.type)]">
                      <component :is="getTransactionIcon(transaction.type)" class="w-3 h-3" />
                    </div>
                    <div>
                      <div class="flex items-center gap-2">
                        <Badge :variant="transaction.type === 'in' ? 'default' : transaction.type === 'out' ? 'destructive' : 'secondary'">
                          {{ getTransactionType(transaction.type) }}
                        </Badge>
                        <span class="text-sm">{{ formatQuantity(transaction.quantity) }} {{ item.unit.symbol }}</span>
                      </div>
                      <div class="text-xs text-muted-foreground">
                        {{ formatDate(transaction.transaction_date) }} oleh {{ transaction.user.name }}
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div v-if="transaction.total_cost" class="text-sm font-medium">
                      {{ formatRupiah(transaction.total_cost) }}
                    </div>
                    <div v-if="transaction.notes" class="text-xs text-muted-foreground">
                      {{ transaction.notes }}
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Stock Information -->
        <div class="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Status Stok</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="text-center">
                <div class="text-3xl font-bold mb-2">{{ formatQuantity(item.current_stock) }}</div>
                <div class="text-sm text-muted-foreground mb-4">{{ item.unit.symbol }} tersedia</div>
                <Badge :variant="getStockStatusColor(item.current_stock, item.minimum_stock)" class="text-sm">
                  {{ getStockStatusText(item.current_stock, item.minimum_stock) }}
                </Badge>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Stok Minimum:</span>
                  <span>{{ formatQuantity(item.minimum_stock) }} {{ item.unit.symbol }}</span>
                </div>
                
                <!-- Stock Level Indicator -->
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    class="h-3 rounded-full transition-all"
                    :class="{
                      'bg-red-600': item.current_stock <= item.minimum_stock,
                      'bg-orange-500': item.current_stock > item.minimum_stock && item.current_stock <= item.minimum_stock * 1.5,
                      'bg-green-600': item.current_stock > item.minimum_stock * 1.5
                    }"
                    :style="{ 
                      width: `${Math.min(100, (item.current_stock / (item.minimum_stock * 2)) * 100)}%` 
                    }"
                  ></div>
                </div>
              </div>

              <div v-if="item.unit_cost && item.current_stock > 0" class="pt-4 border-t">
                <div class="text-sm text-muted-foreground mb-1">Nilai Stok Saat Ini</div>
                <div class="text-lg font-semibold">
                  {{ formatRupiah(item.current_stock * item.unit_cost) }}
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions -->
          <Card>
            <CardHeader>
              <CardTitle>Aksi Cepat</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Link :href="route('inventory.transactions.create', { item: item.id })" class="block">
                <Button class="w-full" variant="outline">
                  <ArrowRight class="w-4 h-4 mr-2" />
                  Tambah Transaksi
                </Button>
              </Link>
              <Link :href="route('inventory.items.edit', item.id)" class="block">
                <Button class="w-full" variant="outline">
                  <Edit class="w-4 h-4 mr-2" />
                  Edit Item
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>