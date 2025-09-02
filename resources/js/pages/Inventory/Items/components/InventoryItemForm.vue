<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

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

interface FormData {
  category_id: string;
  unit_id: string;
  name: string;
  brand: string;
  description: string;
  sku: string;
  unit_cost: string;
  selling_price: string;
  minimum_stock: string;
  current_stock: string;
  track_expiry: boolean;
  expiry_date: string;
  specifications: Record<string, any>;
}

interface FormErrors {
  [key: string]: string;
}

interface Props {
  categories: Category[];
  units: Unit[];
  form: FormData;
  errors: FormErrors;
  processing: boolean;
  mode: 'create' | 'edit';
  itemName?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  submit: [];
  cancel: [];
  'update:form': [value: FormData];
}>();

const formData = computed({
  get: () => props.form,
  set: (value: FormData) => emit('update:form', value)
});

const submitButtonText = computed(() => {
  if (props.processing) return 'Menyimpan...';
  return props.mode === 'create' ? 'Simpan Item' : 'Perbarui Item';
});

const cardTitle = computed(() => {
  return props.mode === 'create' ? 'Informasi Item' : `Edit Item: ${props.itemName}`;
});
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>{{ cardTitle }}</CardTitle>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="emit('submit')" class="space-y-6">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="name">Nama Item *</Label>
            <Input id="name" v-model="formData.name" type="text" :class="{ 'border-red-500': errors.name }"
              placeholder="Contoh: Antibiotik XYZ" />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
          </div>

          <div>
            <Label for="brand">Merek</Label>
            <Input id="brand" v-model="formData.brand" type="text" :class="{ 'border-red-500': errors.brand }"
              placeholder="Contoh: Medion" />
            <p v-if="errors.brand" class="text-red-500 text-sm mt-1">{{ errors.brand }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="category_id">Kategori *</Label>
            <Select v-model="formData.category_id">
              <SelectTrigger :class="{ 'border-red-500': errors.category_id }">
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
            <p v-if="errors.category_id" class="text-red-500 text-sm mt-1">{{ errors.category_id }}</p>
          </div>

          <div>
            <Label for="unit_id">Satuan *</Label>
            <Select v-model="formData.unit_id">
              <SelectTrigger :class="{ 'border-red-500': errors.unit_id }">
                <SelectValue placeholder="Pilih satuan" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="unit in units" :key="unit.id" :value="unit.id.toString()">
                  {{ unit.name }} ({{ unit.symbol }}) <!-- - {{ unit.type }} -->
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="errors.unit_id" class="text-red-500 text-sm mt-1">{{ errors.unit_id }}</p>
          </div>
        </div>

        <div>
          <Label for="description">Deskripsi</Label>
          <Textarea id="description" v-model="formData.description" :class="{ 'border-red-500': errors.description }"
            placeholder="Deskripsi item inventaris..." rows="3" />
          <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
        </div>

        <div>
          <Label for="sku">SKU/Kode Item</Label>
          <Input id="sku" v-model="formData.sku" type="text" :class="{ 'border-red-500': errors.sku }"
            placeholder="Contoh: MED-001" />
          <p v-if="errors.sku" class="text-red-500 text-sm mt-1">{{ errors.sku }}</p>
        </div>

        <!-- Pricing -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="unit_cost">Harga Beli per Unit</Label>
            <Input id="unit_cost" v-model="formData.unit_cost" type="number" step="0.01"
              :class="{ 'border-red-500': errors.unit_cost }" placeholder="0.00" />
            <p v-if="errors.unit_cost" class="text-red-500 text-sm mt-1">{{ errors.unit_cost }}</p>
          </div>

          <div>
            <Label for="selling_price">Harga Jual per Unit</Label>
            <Input id="selling_price" v-model="formData.selling_price" type="number" step="0.01"
              :class="{ 'border-red-500': errors.selling_price }" placeholder="0.00" />
            <p v-if="errors.selling_price" class="text-red-500 text-sm mt-1">{{ errors.selling_price }}</p>
          </div>
        </div>

        <!-- Stock Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label for="current_stock">Stok Saat Ini *</Label>
            <Input id="current_stock" v-model="formData.current_stock" type="number" step="0.01" min="0"
              :class="{ 'border-red-500': errors.current_stock }" placeholder="0.00" />
            <p v-if="errors.current_stock" class="text-red-500 text-sm mt-1">{{ errors.current_stock }}</p>
          </div>

          <div>
            <Label for="minimum_stock">Stok Minimum *</Label>
            <Input id="minimum_stock" v-model="formData.minimum_stock" type="number" step="0.01" min="0"
              :class="{ 'border-red-500': errors.minimum_stock }" placeholder="0.00" />
            <p v-if="errors.minimum_stock" class="text-red-500 text-sm mt-1">{{ errors.minimum_stock }}</p>
          </div>
        </div>

        <!-- Tracking Options -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold">Opsi Pelacakan</h3>

          <div class="space-y-3">
            <div class="flex items-start space-x-3">
              <Checkbox id="track_expiry" v-model:checked="formData.track_expiry" class="mt-1" />
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

            <div v-if="formData.track_expiry" class="mt-4">
              <Label for="expiry_date" class="text-sm font-medium">
                Tanggal Kadaluarsa
              </Label>
              <Input id="expiry_date" v-model="formData.expiry_date" type="date"
                :class="{ 'border-red-500': errors.expiry_date }" class="mt-1" />
              <p v-if="errors.expiry_date" class="text-red-500 text-sm mt-1">{{ errors.expiry_date }}</p>
            </div>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-2 pt-4">
          <Button type="submit" :disabled="processing">
            {{ submitButtonText }}
          </Button>
          <Button type="button" variant="outline" @click="emit('cancel')">
            Batal
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>