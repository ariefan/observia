<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Package, AlertTriangle, Search, Filter, Edit, Trash2, Eye } from 'lucide-vue-next';
import { formatRupiah, formatQuantity } from '@/utils/currency';

interface InventoryItem {
  id: number;
  name: string;
  brand?: string;
  description?: string;
  current_stock: number;
  minimum_stock: number;
  unit_cost?: number;
  category: {
    id: number;
    name: string;
    color: string;
  };
  unit: {
    id: number;
    name: string;
    symbol: string;
  };
}

interface Category {
  id: number;
  name: string;
  color: string;
}

interface Props {
  items: {
    data: InventoryItem[];
    links: any[];
    total: number;
  };
  categories: Category[];
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

const deleteItem = (item: InventoryItem) => {
  if (confirm(`Apakah Anda yakin ingin menghapus item "${item.name}"?`)) {
    router.delete(route('inventory.items.destroy', item.id));
  }
};
</script>

<template>
  <Head title="Item Inventaris" />
  
  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="inventory.items.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-primary">Item Inventaris</h1>
          <p class="text-sm text-muted-foreground">Kelola item inventaris peternakan</p>
        </div>
        <Link :href="route('inventory.items.create')">
          <Button>
            <Plus class="w-4 h-4 mr-2" />
            Tambah Item
          </Button>
        </Link>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
        <div>
          <label class="text-xs font-medium">Cari Item</label>
          <div class="relative">
            <Search class="absolute left-2 top-2 h-3.5 w-3.5 text-muted-foreground" />
            <input 
              type="text" 
              placeholder="Nama, merek..." 
              class="h-8 pl-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>
        </div>
        
        <div>
          <label class="text-xs font-medium">Kategori</label>
          <select class="h-8 text-xs w-full border rounded-md px-3 py-1">
            <option value="">Semua Kategori</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-xs font-medium">Status Stok</label>
          <select class="h-8 text-xs w-full border rounded-md px-3 py-1">
            <option value="">Semua Status</option>
            <option value="normal">Normal</option>
            <option value="low">Menipis</option>
            <option value="empty">Habis</option>
          </select>
        </div>

        <div class="flex items-end gap-2">
          <Button class="h-8 flex-1 text-xs">
            Cari
          </Button>
          <Button variant="outline" class="h-8 text-xs">
            Reset
          </Button>
        </div>
      </div>

      <!-- Results Summary -->
      <div>
        <p class="text-sm text-muted-foreground">
          Menampilkan {{ items.data?.length || 0 }} dari {{ items.total || 0 }} item
        </p>
      </div>

      <!-- Items Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="item in items.data" :key="item.id" 
              class="hover:shadow-lg transition-shadow">
          <CardHeader class="pb-2">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <CardTitle class="text-lg">{{ item.name }}</CardTitle>
                <p v-if="item.brand" class="text-sm text-muted-foreground">{{ item.brand }}</p>
              </div>
              <div class="flex items-center gap-2">
                <Badge :variant="getStockStatusColor(item.current_stock, item.minimum_stock)">
                  {{ getStockStatusText(item.current_stock, item.minimum_stock) }}
                </Badge>
                <!-- Action Buttons -->
                <div class="flex items-center gap-1">
                  <Link :href="route('inventory.items.show', item.id)">
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                      <Eye class="h-3 w-3" />
                    </Button>
                  </Link>
                  <Link :href="route('inventory.items.edit', item.id)">
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                      <Edit class="h-3 w-3" />
                    </Button>
                  </Link>
                  <Button @click="deleteItem(item)" variant="ghost" size="sm" class="h-8 w-8 p-0 text-red-500 hover:text-red-700">
                    <Trash2 class="h-3 w-3" />
                  </Button>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Kategori:</span>
                <div class="flex items-center gap-2">
                  <div :class="['w-2 h-2 rounded-full']" :style="{ backgroundColor: item.category.color }"></div>
                  <span class="text-sm">{{ item.category.name }}</span>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Stok:</span>
                <span class="text-sm font-medium">
                  {{ formatQuantity(item.current_stock) }} {{ item.unit.symbol }}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Minimum:</span>
                <span class="text-sm">{{ formatQuantity(item.minimum_stock) }} {{ item.unit.symbol }}</span>
              </div>
              
              <div v-if="item.unit_cost" class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Harga per unit:</span>
                <span class="text-sm font-medium">{{ formatRupiah(item.unit_cost) }}</span>
              </div>

              <!-- Stock Level Indicator -->
              <div class="pt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="h-2 rounded-full transition-all"
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
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- No Results -->
      <div v-if="!items.data || items.data.length === 0" class="text-center py-8">
        <Package class="mx-auto h-12 w-12 text-gray-400 mb-4" />
        <p class="text-muted-foreground">Belum ada item inventaris</p>
        <Link :href="route('inventory.items.create')" class="mt-2">
          <Button variant="outline">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Item Pertama
          </Button>
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="items.links && items.links.length > 3" class="mt-6 flex justify-center">
        <div class="flex space-x-1">
          <template v-for="link in items.links" :key="link.label">
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