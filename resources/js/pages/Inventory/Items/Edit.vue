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
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft } from 'lucide-vue-next';

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
  category_id: number;
  unit_id: number;
  category: Category;
  unit: Unit;
  specifications?: Record<string, any>;
}

interface Props {
  item: InventoryItem;
  categories: Category[];
  units: Unit[];
}

const props = defineProps<Props>();

const form = useForm({
  category_id: props.item.category_id.toString(),
  unit_id: props.item.unit_id.toString(),
  name: props.item.name,
  brand: props.item.brand || '',
  description: props.item.description || '',
  sku: props.item.sku || '',
  unit_cost: props.item.unit_cost?.toString() || '',
  selling_price: props.item.selling_price?.toString() || '',
  minimum_stock: (Number(props.item.minimum_stock) || 0).toFixed(2),
  current_stock: (Number(props.item.current_stock) || 0).toFixed(2),
  track_expiry: props.item.track_expiry,
  track_batch: props.item.track_batch,
  specifications: props.item.specifications || {},
});

const submit = () => {
  form.put(route('inventory.items.update', props.item.id), {
    onSuccess: () => {
      router.visit(route('inventory.items.index'));
    },
  });
};

const goBack = () => {
  router.visit(route('inventory.items.index'));
};
</script>

<template>
  <Head :title="`Edit Item: ${item.name}`" />
  
  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="inventory.items.edit" />
      <div class="flex-1 p-6">
        <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Button variant="ghost" size="sm" @click="goBack" class="p-2">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-2xl font-bold">Edit Item Inventaris</h1>
          <p class="text-muted-foreground">Edit informasi item {{ item.name }}</p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Informasi Item</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="name">Nama Item *</Label>
                <Input 
                  id="name"
                  v-model="form.name"
                  type="text" 
                  :class="{ 'border-red-500': form.errors.name }"
                  placeholder="Contoh: Antibiotik XYZ"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
              </div>

              <div>
                <Label for="brand">Merek</Label>
                <Input 
                  id="brand"
                  v-model="form.brand"
                  type="text" 
                  :class="{ 'border-red-500': form.errors.brand }"
                  placeholder="Contoh: Medion"
                />
                <p v-if="form.errors.brand" class="text-red-500 text-sm mt-1">{{ form.errors.brand }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="category_id">Kategori *</Label>
                <Select v-model="form.category_id">
                  <SelectTrigger :class="{ 'border-red-500': form.errors.category_id }">
                    <SelectValue placeholder="Pilih kategori" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                      <div class="flex items-center gap-2">
                        <div :class="['w-2 h-2 rounded-full']" :style="{ backgroundColor: category.color }"></div>
                        {{ category.name }}
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</p>
              </div>

              <div>
                <Label for="unit_id">Satuan *</Label>
                <Select v-model="form.unit_id">
                  <SelectTrigger :class="{ 'border-red-500': form.errors.unit_id }">
                    <SelectValue placeholder="Pilih satuan" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                      {{ unit.name }} ({{ unit.symbol }}) - {{ unit.type }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.unit_id" class="text-red-500 text-sm mt-1">{{ form.errors.unit_id }}</p>
              </div>
            </div>

            <div>
              <Label for="description">Deskripsi</Label>
              <Textarea 
                id="description"
                v-model="form.description"
                :class="{ 'border-red-500': form.errors.description }"
                placeholder="Deskripsi item inventaris..."
                rows="3"
              />
              <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>

            <div>
              <Label for="sku">SKU/Kode Item</Label>
              <Input 
                id="sku"
                v-model="form.sku"
                type="text" 
                :class="{ 'border-red-500': form.errors.sku }"
                placeholder="Contoh: MED-001"
              />
              <p v-if="form.errors.sku" class="text-red-500 text-sm mt-1">{{ form.errors.sku }}</p>
            </div>

            <!-- Pricing -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="unit_cost">Harga Beli per Unit</Label>
                <Input 
                  id="unit_cost"
                  v-model="form.unit_cost"
                  type="number" 
                  step="0.01"
                  :class="{ 'border-red-500': form.errors.unit_cost }"
                  placeholder="0.00"
                />
                <p v-if="form.errors.unit_cost" class="text-red-500 text-sm mt-1">{{ form.errors.unit_cost }}</p>
              </div>

              <div>
                <Label for="selling_price">Harga Jual per Unit</Label>
                <Input 
                  id="selling_price"
                  v-model="form.selling_price"
                  type="number" 
                  step="0.01"
                  :class="{ 'border-red-500': form.errors.selling_price }"
                  placeholder="0.00"
                />
                <p v-if="form.errors.selling_price" class="text-red-500 text-sm mt-1">{{ form.errors.selling_price }}</p>
              </div>
            </div>

            <!-- Stock Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="current_stock">Stok Saat Ini *</Label>
                <Input 
                  id="current_stock"
                  v-model="form.current_stock"
                  type="number" 
                  step="0.01"
                  min="0"
                  :class="{ 'border-red-500': form.errors.current_stock }"
                  placeholder="0.00"
                />
                <p v-if="form.errors.current_stock" class="text-red-500 text-sm mt-1">{{ form.errors.current_stock }}</p>
              </div>

              <div>
                <Label for="minimum_stock">Stok Minimum *</Label>
                <Input 
                  id="minimum_stock"
                  v-model="form.minimum_stock"
                  type="number" 
                  step="0.01"
                  min="0"
                  :class="{ 'border-red-500': form.errors.minimum_stock }"
                  placeholder="0.00"
                />
                <p v-if="form.errors.minimum_stock" class="text-red-500 text-sm mt-1">{{ form.errors.minimum_stock }}</p>
              </div>
            </div>

            <!-- Tracking Options -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Opsi Pelacakan</h3>
              
              <div class="space-y-3">
                <div class="flex items-start space-x-3">
                  <Checkbox 
                    id="track_expiry" 
                    v-model:checked="form.track_expiry" 
                    class="mt-1"
                  />
                  <div class="flex-1">
                    <Label for="track_expiry" class="text-sm font-medium">
                      Lacak tanggal kadaluarsa
                    </Label>
                    <p class="text-xs text-muted-foreground mt-1">
                      Aktifkan untuk obat-obatan, vaksin, atau item yang memiliki tanggal kadaluarsa. 
                      Sistem akan memberikan peringatan mendekati tanggal kadaluarsa.
                    </p>
                  </div>
                </div>

                <div class="flex items-start space-x-3">
                  <Checkbox 
                    id="track_batch" 
                    v-model:checked="form.track_batch" 
                    class="mt-1"
                  />
                  <div class="flex-1">
                    <Label for="track_batch" class="text-sm font-medium">
                      Lacak batch/lot
                    </Label>
                    <p class="text-xs text-muted-foreground mt-1">
                      Aktifkan untuk melacak nomor batch/lot produksi. Berguna untuk quality control, 
                      recall produk, dan traceability.
                    </p>
                  </div>
                </div>
              </div>
              
              <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                <p class="text-xs text-blue-700">
                  💡 <strong>Tips:</strong> Untuk obat-obatan dan vaksin, disarankan mengaktifkan kedua opsi ini 
                  untuk keamanan dan compliance yang optimal.
                </p>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-2 pt-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Perbarui Item' }}
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