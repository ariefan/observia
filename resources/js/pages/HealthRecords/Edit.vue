<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';
import HealthRecordForm from '@/components/forms/HealthRecordForm.vue';
import { reactive, watch } from 'vue';

interface Medicine {
  name: string;
  type: string;
  quantity: number | null;
  dosage: string;
}

interface HealthRecord {
  id: string;
  livestock_id: string;
  health_status: 'healthy' | 'sick';
  diagnosis: string[] | string | null;
  treatment: string | null;
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
}>();

const form = useForm<{
  livestock_id: string;
  health_status: 'healthy' | 'sick';
  diagnosis: string[];
  treatment: string;
  notes: string;
  medicines: Medicine[];
  record_date: string;
}>({
  livestock_id: props.healthRecord.livestock_id,
  health_status: props.healthRecord.health_status,
  diagnosis: props.healthRecord.diagnosis && Array.isArray(props.healthRecord.diagnosis) 
    ? props.healthRecord.diagnosis 
    : props.healthRecord.diagnosis ? [props.healthRecord.diagnosis] : [''],
  treatment: props.healthRecord.treatment || '',
  notes: props.healthRecord.notes || '',
  medicines: props.healthRecord.medicines && Array.isArray(props.healthRecord.medicines) && props.healthRecord.medicines.length > 0
    ? props.healthRecord.medicines
    : props.healthRecord.medicine_name 
      ? [{ 
          name: props.healthRecord.medicine_name || '', 
          type: props.healthRecord.medicine_type || '', 
          quantity: props.healthRecord.medicine_quantity || null, 
          dosage: '' 
        }]
      : [{ name: '', type: '', quantity: null, dosage: '' }],
  record_date: props.healthRecord.record_date,
});

// Create a local reactive copy for the form component
const localFormData = reactive({
  livestock_id: props.healthRecord.livestock_id,
  health_status: props.healthRecord.health_status,
  diagnosis: props.healthRecord.diagnosis && Array.isArray(props.healthRecord.diagnosis) 
    ? props.healthRecord.diagnosis 
    : props.healthRecord.diagnosis ? [props.healthRecord.diagnosis] : [''],
  treatment: props.healthRecord.treatment || '',
  notes: props.healthRecord.notes || '',
  medicines: props.healthRecord.medicines && Array.isArray(props.healthRecord.medicines) && props.healthRecord.medicines.length > 0
    ? props.healthRecord.medicines
    : props.healthRecord.medicine_name 
      ? [{ 
          name: props.healthRecord.medicine_name || '', 
          type: props.healthRecord.medicine_type || '', 
          quantity: props.healthRecord.medicine_quantity || null, 
          dosage: '' 
        }]
      : [{ name: '', type: '', quantity: null, dosage: '' }],
  record_date: props.healthRecord.record_date,
});

// Watch for changes and sync with Inertia form
watch(localFormData, (newData) => {
  Object.assign(form.data, newData);
}, { deep: true, immediate: true });



const submit = () => {
  // Ensure data is synced before submission
  Object.assign(form.data, localFormData);
  
  form.put(route('health-records.update', { id: props.healthRecord.id }));
};

const goBack = () => {
  router.visit(route('health-records.index'));
};
</script>

<template>
  <Head title="Edit Catatan Kesehatan" />

  <AppLayout>
    <div class="max-w-6xl mx-auto p-6">
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
            v-model:form="localFormData"
            :errors="form.errors"
            :livestocks="livestocks"
            :processing="form.processing"
            submit-text="Perbarui Catatan"
            @submit="submit"
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>