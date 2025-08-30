<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';
import HealthRecordForm from '@/components/forms/HealthRecordForm.vue';
import { reactive, ref } from 'vue';

const { livestocks } = defineProps<{
  livestocks: Array<{
    id: string;
    name: string;
    tag_id: string;
    breed_name: string;
  }>;
}>();

interface Medicine {
  name: string;
  type: string;
  quantity: number | null;
  dosage: string;
}

// Form state
const localFormData = reactive({
  livestock_id: '',
  health_status: '',
  diagnosis: [''],
  treatment: '',
  notes: '',
  medicines: [{
    name: '',
    type: '',
    quantity: null,
    dosage: ''
  }],
  record_date: new Date().toISOString().split('T')[0],
});

const errors = ref({});
const processing = ref(false);

const submit = () => {
  processing.value = true;
  errors.value = {};
  
  // Direct submission with local form data
  const submitData = {
    livestock_id: localFormData.livestock_id,
    health_status: localFormData.health_status,
    diagnosis: localFormData.diagnosis,
    treatment: localFormData.treatment,
    notes: localFormData.notes,
    medicines: localFormData.medicines,
    record_date: localFormData.record_date,
  };
  
  // Debug: Log what we're sending
  console.log('Submitting data:', submitData);
  
  router.post(route('health-records.store'), submitData, {
    onSuccess: () => {
      // Reset local form data
      localFormData.livestock_id = '';
      localFormData.health_status = '';
      localFormData.diagnosis = [''];
      localFormData.treatment = '';
      localFormData.notes = '';
      localFormData.medicines = [{
        name: '',
        type: '',
        quantity: null,
        dosage: ''
      }];
      localFormData.record_date = new Date().toISOString().split('T')[0];
      processing.value = false;
    },
    onError: (formErrors) => {
      errors.value = formErrors;
      processing.value = false;
    },
    onFinish: () => {
      processing.value = false;
    },
  });
};

const goBack = () => {
  router.visit(route('health-records.index'));
};
</script>

<template>

  <Head title="Catat Kesehatan Ternak" />

  <AppLayout>
    <div class="max-w-6xl mx-auto p-6">
      <div>
        <div class="flex items-center gap-4 mb-4">
          <Button variant="ghost" size="sm" @click="goBack" class="p-2">
            <ArrowLeft class="h-4 w-4" />
          </Button>
          <div>
            <div class="text-2xl">Catat Kesehatan Ternak</div>
            <div>Isi data kesehatan ternak Anda dengan lengkap.</div>
          </div>
        </div>
      </div>
      <Card>
        <CardContent class="pt-4">
          <HealthRecordForm
            v-model:form="localFormData"
            :errors="errors"
            :livestocks="livestocks"
            :processing="processing"
            @submit="submit"
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>