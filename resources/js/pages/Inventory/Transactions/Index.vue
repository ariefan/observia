<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, ArrowRight, ArrowLeft, Minus, RotateCcw } from 'lucide-vue-next';
import { formatRupiah, formatQuantity } from '@/utils/currency';

interface Transaction {
  id: number;
  type: string;
  quantity: number;
  unit_cost?: number;
  total_cost?: number;
  notes?: string;
  transaction_date: string;
  inventory_item: {
    name: string;
    brand?: string;
    unit: {
      symbol: string;
    };
  };
  user: {
    name: string;
  };
}

interface Props {
  transactions: {
    data: Transaction[];
    links: any[];
    total: number;
  };
  search?: string;
  type?: string;
  from_date?: string;
  to_date?: string;
}

const props = defineProps<Props>();

// Reactive filter state
const search = ref(props.search || '');
const typeFilter = ref(props.type || '');
const fromDate = ref(props.from_date || '');
const toDate = ref(props.to_date || '');

let searchTimeout: NodeJS.Timeout | null = null;

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

// Search and filter functions
const performSearch = () => {
  router.get(route('inventory.transactions.index'), {
    search: search.value || undefined,
    type: typeFilter.value || undefined,
    from_date: fromDate.value || undefined,
    to_date: toDate.value || undefined,
  }, {
    preserveState: true,
    replace: true
  });
};

const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(performSearch, 500);
};

const resetFilters = () => {
  search.value = '';
  typeFilter.value = '';
  fromDate.value = '';
  toDate.value = '';
  router.get(route('inventory.transactions.index'), {}, {
    preserveState: true,
    replace: true
  });
};

// Watch for changes in search input
watch(search, debouncedSearch);
</script>

<template>
  <Head title="Transaksi Inventaris" />
  
  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="inventory.transactions.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-primary">Transaksi Inventaris</h1>
          <p class="text-sm text-muted-foreground">Riwayat transaksi masuk dan keluar inventaris</p>
        </div>
        <Link :href="route('inventory.transactions.create')">
          <Button>
            <Plus class="w-4 h-4 mr-2" />
            Transaksi Baru
          </Button>
        </Link>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
        <div>
          <label class="text-xs font-medium">Cari Item</label>
          <input 
            v-model="search"
            type="text" 
            placeholder="Nama item..." 
            class="h-8 text-xs w-full border rounded-md px-3 py-1"
          />
        </div>
        
        <div>
          <label class="text-xs font-medium">Tipe Transaksi</label>
          <select v-model="typeFilter" @change="performSearch" class="h-8 text-xs w-full border rounded-md px-3 py-1">
            <option value="">Semua Tipe</option>
            <option value="in">Masuk</option>
            <option value="out">Keluar</option>
            <option value="adjustment">Penyesuaian</option>
            <option value="expired">Kadaluarsa</option>
            <option value="damaged">Rusak</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-medium">Dari Tanggal</label>
          <input 
            v-model="fromDate"
            @change="performSearch"
            type="date" 
            class="h-8 text-xs w-full border rounded-md px-3 py-1"
          />
        </div>

        <div>
          <label class="text-xs font-medium">Sampai Tanggal</label>
          <input 
            v-model="toDate"
            @change="performSearch"
            type="date" 
            class="h-8 text-xs w-full border rounded-md px-3 py-1"
          />
        </div>

        <div class="flex items-end gap-2">
          <Button @click="performSearch" class="h-8 flex-1 text-xs">
            Cari
          </Button>
          <Button @click="resetFilters" variant="outline" class="h-8 text-xs">
            Reset
          </Button>
        </div>
      </div>

      <!-- Results Summary -->
      <div>
        <p class="text-sm text-muted-foreground">
          Menampilkan {{ transactions.data?.length || 0 }} dari {{ transactions.total || 0 }} transaksi
        </p>
      </div>

      <!-- Transactions List -->
      <Card>
        <CardContent class="p-0">
          <div class="divide-y">
            <div v-for="transaction in transactions.data" :key="transaction.id" 
                 class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <!-- Transaction Type Icon -->
                  <div :class="['p-2 rounded-full text-white', getTransactionColor(transaction.type)]">
                    <component :is="getTransactionIcon(transaction.type)" class="w-4 h-4" />
                  </div>
                  
                  <!-- Transaction Details -->
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <h3 class="font-medium">{{ transaction.inventory_item.name }}</h3>
                      <span v-if="transaction.inventory_item.brand" 
                            class="text-sm text-muted-foreground">
                        • {{ transaction.inventory_item.brand }}
                      </span>
                      <Badge :variant="transaction.type === 'in' ? 'default' : transaction.type === 'out' ? 'destructive' : 'secondary'">
                        {{ getTransactionType(transaction.type) }}
                      </Badge>
                    </div>
                    
                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                      <span>
                        Jumlah: {{ formatQuantity(transaction.quantity) }} {{ transaction.inventory_item.unit.symbol }}
                      </span>
                      <span v-if="transaction.unit_cost">
                        Harga: {{ formatRupiah(transaction.unit_cost) }}
                      </span>
                      <span v-if="transaction.total_cost">
                        Total: {{ formatRupiah(transaction.total_cost) }}
                      </span>
                      <span>{{ formatDate(transaction.transaction_date) }}</span>
                      <span>oleh {{ transaction.user.name }}</span>
                    </div>
                    
                    <p v-if="transaction.notes" class="text-sm text-muted-foreground mt-1">
                      Catatan: {{ transaction.notes }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- No Results -->
      <div v-if="!transactions.data || transactions.data.length === 0" class="text-center py-8">
        <ArrowRight class="mx-auto h-12 w-12 text-gray-400 mb-4" />
        <p class="text-muted-foreground">Belum ada transaksi inventaris</p>
      </div>

      <!-- Pagination -->
      <div v-if="transactions.links && transactions.links.length > 3" class="mt-6 flex justify-center">
        <div class="flex space-x-1">
          <template v-for="link in transactions.links" :key="link.label">
            <Link v-if="link.url" :href="link.url" :class="[
              'px-3 py-2 rounded-md',
              link.active
                ? 'bg-primary text-primary-foreground'
                : 'text-muted-foreground hover:text-foreground hover:bg-accent',
              (link.label.includes('Previous') || link.label.includes('Next')) ? 'text-lg font-bold' : 'text-sm'
            ]">
              {{
                link.label.includes('Previous') ? '‹' :
                link.label.includes('Next') ? '›' :
                link.label
              }}
            </Link>
            <span v-else :class="[
              'px-3 py-2 rounded-md',
              'text-muted-foreground pointer-events-none opacity-50',
              (link.label.includes('Previous') || link.label.includes('Next')) ? 'text-lg font-bold' : 'text-sm'
            ]">
              {{
                link.label.includes('Previous') ? '‹' :
                link.label.includes('Next') ? '›' :
                link.label
              }}
            </span>
          </template>
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>