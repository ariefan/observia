<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import InventoryItemForm from './components/InventoryItemForm.vue';
import { Button } from '@/components/ui/button';
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
  expiry_date: string;
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
  expiry_date: props.item.expiry_date || '',
  specifications: props.item.specifications || {},
});

const submit = () => {
  form.put(route('inventory.items.update', props.item.id), {
    onSuccess: () => {
      router.visit(route('inventory.items.index'));
    },
  });
};

const updateForm = (data: any) => {
  Object.keys(data).forEach(key => {
    form[key] = data[key];
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

      <InventoryItemForm
        :categories="categories"
        :units="units"
        :form="form"
        :errors="form.errors"
        :processing="form.processing"
        mode="edit"
        :item-name="item.name"
        @submit="submit"
        @cancel="goBack"
        @update:form="updateForm"
      />
        </div>
      </div>
    </div>
  </AppLayout>
</template>