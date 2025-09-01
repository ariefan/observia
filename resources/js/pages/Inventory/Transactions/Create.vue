<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-vue-next';

interface InventoryItem {
  id: number;
  name: string;
  brand?: string;
  current_stock: number;
  unit: {
    id: number;
    name: string;
    symbol: string;
  };
  category: {
    id: number;
    name: string;
    color: string;
  };
}

interface Props {
  inventoryItems: InventoryItem[];
}

const props = defineProps<Props>();

const form = useForm({
  inventory_item_id: '',
  type: '',
  quantity: '',
  unit_cost: '',
  total_cost: '',
  notes: '',
  transaction_date: new Date().toISOString().split('T')[0],
});

const submit = () => {
  form.post(route('inventory.transactions.store'), {
    onSuccess: () => {
      router.visit(route('inventory.transactions.index'));
    },
  });
};

const goBack = () => {
  router.visit(route('inventory.transactions.index'));
};

const calculateTotalCost = () => {
  if (form.quantity && form.unit_cost) {
    form.total_cost = (parseFloat(form.quantity) * parseFloat(form.unit_cost)).toString();
  }
};
</script>

<template>
  <Head title="Tambah Transaksi Inventaris" />
  
  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="inventory.transactions.create" />
      <div class="flex-1 p-6">
        <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Button variant="ghost" size="sm" @click="goBack" class="p-2">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-2xl font-bold">Tambah Transaksi Inventaris</h1>
          <p class="text-muted-foreground">Catat transaksi masuk, keluar, atau penyesuaian stok</p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Informasi Transaksi</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="inventory_item_id">Item Inventaris *</Label>
                <Select v-model="form.inventory_item_id">
                  <SelectTrigger :class="{ 'border-red-500': form.errors.inventory_item_id }">
                    <SelectValue placeholder="Pilih item inventaris" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="item in inventoryItems" :key="item.id" :value="item.id.toString()">
                      <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                          <div :class="['w-2 h-2 rounded-full']" :style="{ backgroundColor: item.category.color }"></div>
                          <span>{{ item.name }}</span>
                          <span v-if="item.brand" class="text-muted-foreground">• {{ item.brand }}</span>
                        </div>
                        <span class="text-sm text-muted-foreground">{{ item.current_stock }} {{ item.unit.symbol }}</span>
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.inventory_item_id" class="text-red-500 text-sm mt-1">{{ form.errors.inventory_item_id }}</p>
              </div>

              <div>
                <Label for="type">Tipe Transaksi *</Label>
                <Select v-model="form.type">
                  <SelectTrigger :class="{ 'border-red-500': form.errors.type }">
                    <SelectValue placeholder="Pilih tipe transaksi" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="in">Masuk (Pembelian/Penerimaan)</SelectItem>
                    <SelectItem value="out">Keluar (Pemakaian/Penjualan)</SelectItem>
                    <SelectItem value="adjustment">Penyesuaian Stok</SelectItem>
                    <SelectItem value="expired">Kadaluarsa</SelectItem>
                    <SelectItem value="damaged">Rusak</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="quantity">Jumlah *</Label>
                <Input 
                  id="quantity"
                  v-model="form.quantity"
                  type="number" 
                  step="0.001"
                  :class="{ 'border-red-500': form.errors.quantity }"
                  placeholder="0"
                  @input="calculateTotalCost"
                />
                <p v-if="form.errors.quantity" class="text-red-500 text-sm mt-1">{{ form.errors.quantity }}</p>
              </div>

              <div>
                <Label for="transaction_date">Tanggal Transaksi *</Label>
                <Input 
                  id="transaction_date"
                  v-model="form.transaction_date"
                  type="date" 
                  :class="{ 'border-red-500': form.errors.transaction_date }"
                />
                <p v-if="form.errors.transaction_date" class="text-red-500 text-sm mt-1">{{ form.errors.transaction_date }}</p>
              </div>
            </div>

            <!-- Pricing Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" v-if="form.type === 'in'">
              <div>
                <Label for="unit_cost">Harga per Unit</Label>
                <Input 
                  id="unit_cost"
                  v-model="form.unit_cost"
                  type="number" 
                  step="0.01"
                  :class="{ 'border-red-500': form.errors.unit_cost }"
                  placeholder="0.00"
                  @input="calculateTotalCost"
                />
                <p v-if="form.errors.unit_cost" class="text-red-500 text-sm mt-1">{{ form.errors.unit_cost }}</p>
              </div>

              <div>
                <Label for="total_cost">Total Biaya</Label>
                <Input 
                  id="total_cost"
                  v-model="form.total_cost"
                  type="number" 
                  step="0.01"
                  :class="{ 'border-red-500': form.errors.total_cost }"
                  placeholder="0.00"
                />
                <p v-if="form.errors.total_cost" class="text-red-500 text-sm mt-1">{{ form.errors.total_cost }}</p>
              </div>
            </div>

            <div>
              <Label for="notes">Catatan</Label>
              <Textarea 
                id="notes"
                v-model="form.notes"
                :class="{ 'border-red-500': form.errors.notes }"
                placeholder="Catatan tambahan untuk transaksi ini..."
                rows="3"
              />
              <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-2 pt-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
              </Button>
              <Button type="button" variant="outline" @click="goBack">
                Batal
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>