<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';
import HealthRecordForm from '@/components/forms/HealthRecordForm.vue';

const { livestocks } = defineProps<{
  livestocks: Array<{
    id: string;
    name: string;
    tag_id: string;
    breed_name: string;
  }>;
}>();


// Use Inertia form for proper type handling
const form = useForm({
  livestock_id: '',
  health_status: '',
  diagnosis: [''],
  treatment: [''],
  notes: '',
  medicines: [{
    name: '',
    type: '',
    quantity: undefined,
    dosage: ''
  }],
  record_date: new Date().toISOString().split('T')[0],
});

const submit = () => {
  form.post(route('health-records.store'), {
    onSuccess: () => {
      form.reset();
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
            :processing="form.processing"
            @submit="submit"
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>