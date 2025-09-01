<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';
import HealthRecordForm from '@/components/forms/HealthRecordForm.vue';
import { watch } from 'vue';

interface Medicine {
  inventory_item_id?: number;
  name: string;
  type: string;
  quantity: number | undefined;
  dosage: string;
  current_stock?: number;
  template?: any;
}

interface HealthRecord {
  id: string;
  livestock_id: string;
  health_status: 'healthy' | 'sick';
  diagnosis: string[] | string | null;
  treatment: string[] | string | null;
  notes: string | null;
  medicines: Medicine[] | null;
  medicine_name?: string | null; // Legacy field
  medicine_type?: string | null; // Legacy field
  medicine_quantity?: number | null; // Legacy field
  record_date: string;
  livestock: {
    id: string;
    name: string;
    tag_id: string;
    breed: {
      name: string;
    };
  };
}

const props = defineProps<{
  healthRecord: HealthRecord;
  livestocks: Array<{
    id: string;
    name: string;
    tag_id: string;
    breed_name: string;
  }>;
  inventoryMedicines: Array<{
    id: number;
    name: string;
    sku?: string;
    stock: number;
    unit: {
      id: number;
      name: string;
      symbol: string;
    };
    category: {
      id: number;
      name: string;
    };
  }>;
}>();

const form = useForm({
  livestock_id: props.healthRecord.livestock_id,
  health_status: props.healthRecord.health_status,
  diagnosis: props.healthRecord.diagnosis && Array.isArray(props.healthRecord.diagnosis) 
    ? props.healthRecord.diagnosis 
    : props.healthRecord.diagnosis ? [props.healthRecord.diagnosis] : [''],
  treatment: props.healthRecord.treatment && Array.isArray(props.healthRecord.treatment) 
    ? props.healthRecord.treatment 
    : props.healthRecord.treatment ? [props.healthRecord.treatment] : [''],
  notes: props.healthRecord.notes || '',
  medicines: props.healthRecord.medicines && Array.isArray(props.healthRecord.medicines) && props.healthRecord.medicines.length > 0
    ? props.healthRecord.medicines.map(m => ({
        inventory_item_id: m.inventory_item_id || undefined,
        name: m.name || '',
        type: m.type || '',
        quantity: m.quantity || undefined,
        dosage: m.dosage || '',
        current_stock: m.current_stock || undefined,
        template: m.template || undefined
      }))
    : props.healthRecord.medicine_name 
      ? [{ 
          inventory_item_id: undefined,
          name: props.healthRecord.medicine_name || '', 
          type: props.healthRecord.medicine_type || '', 
          quantity: props.healthRecord.medicine_quantity || undefined, 
          dosage: '',
          current_stock: undefined,
          template: undefined
        }]
      : [{ 
          inventory_item_id: undefined,
          name: '', 
          type: '', 
          quantity: undefined, 
          dosage: '',
          current_stock: undefined,
          template: undefined
        }],
  record_date: props.healthRecord.record_date ? new Date(props.healthRecord.record_date).toISOString().split('T')[0] : '',
});

// Watch for changes and sync with Inertia form
watch(form.data, () => {
  // Form data is automatically synced by Inertia
}, { deep: true });



const submit = () => {
  form.put(route('health-records.update', { id: props.healthRecord.id }));
};

const goBack = () => {
  router.visit(route('health-records.index'));
};
</script>

<template>
  <Head title="Edit Catatan Kesehatan" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="health-records.edit" />
      <div class="flex-1 p-6">
        <div class="max-w-6xl mx-auto">
      <Card>
        <CardHeader>
          <div class="flex items-center gap-4 mb-4">
            <Button
              variant="ghost"
              size="sm"
              @click="goBack"
              class="p-2"
            >
              <ArrowLeft class="h-4 w-4" />
            </Button>
            <div>
              <CardTitle class="text-2xl">Edit Catatan Kesehatan</CardTitle>
              <CardDescription>Perbarui informasi kesehatan ternak Anda.</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <HealthRecordForm
            :form="form.data()"
            @update:form="(data) => {
              form.livestock_id = data.livestock_id;
              form.health_status = data.health_status;
              form.diagnosis = data.diagnosis;
              form.treatment = data.treatment;
              form.notes = data.notes;
              form.medicines = data.medicines;
              form.record_date = data.record_date;
            }"
            :errors="form.errors"
            :livestocks="livestocks"
            :inventory-medicines="inventoryMedicines"
            :processing="form.processing"
            submit-text="Perbarui Catatan"
            @submit="submit"
          />
        </CardContent>
      </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>