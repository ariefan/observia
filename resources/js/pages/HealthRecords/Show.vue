<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Pencil, Trash2, Calendar, Stethoscope, Pill, FileText } from 'lucide-vue-next';

interface HealthRecord {
  id: string;
  livestock_id: string;
  health_status: 'sehat' | 'sakit';
  diagnosis: string | null;
  treatment: string | null;
  notes: string | null;
  medicine_name: string | null;
  medicine_type: string | null;
  medicine_quantity: number | null;
  record_date: string;
  livestock: {
    id: string;
    name: string;
    tag_id: string;
    breed: {
      name: string;
    };
  };
  created_at: string;
  updated_at: string;
}

const props = defineProps<{
  healthRecord: HealthRecord;
}>();

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const deleteRecord = () => {
  if (confirm('Yakin ingin menghapus catatan kesehatan ini?')) {
    router.delete(route('health-records.destroy', { id: props.healthRecord.id }), {
      onSuccess: () => {
        router.visit(route('health-records.index'));
      },
    });
  }
};

const goBack = () => {
  router.visit(route('health-records.index'));
};

const getStatusVariant = (status: string) => {
  return status === 'sehat' ? 'default' : 'destructive';
};

const getStatusText = (status: string) => {
  return status === 'sehat' ? 'Sehat' : 'Sakit';
};

const getMedicineTypeText = (type: string | null) => {
  if (!type) return null;
  
  const types: Record<string, string> = {
    'tablet': 'Tablet',
    'kapsul': 'Kapsul',
    'cair': 'Cair/Inject',
    'salep': 'Salep',
    'serbuk': 'Serbuk',
  };
  
  return types[type] || type;
};
</script>

<template>
  <Head :title="`Detail Catatan Kesehatan - ${healthRecord.livestock.tag_id}`" />

  <AppLayout>
    <div class="max-w-4xl mx-auto p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <Button
            variant="ghost"
            size="sm"
            @click="goBack"
            class="p-2"
          >
            <ArrowLeft class="h-4 w-4" />
          </Button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Detail Catatan Kesehatan</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ healthRecord.livestock.tag_id }} - {{ healthRecord.livestock.name }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" size="sm" asChild>
            <Link :href="route('health-records.edit', { id: healthRecord.id })">
              <Pencil class="h-4 w-4 mr-2" />
              Edit
            </Link>
          </Button>
          <Button variant="destructive" size="sm" @click="deleteRecord">
            <Trash2 class="h-4 w-4 mr-2" />
            Hapus
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Info -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Stethoscope class="h-5 w-5" />
                Informasi Kesehatan
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ternak</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ healthRecord.livestock.tag_id }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ healthRecord.livestock.name }} - {{ healthRecord.livestock.breed.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Kesehatan</p>
                  <Badge :variant="getStatusVariant(healthRecord.health_status)" class="mt-1">
                    {{ getStatusText(healthRecord.health_status) }}
                  </Badge>
                </div>
              </div>

              <Separator />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="healthRecord.diagnosis">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Diagnosa</p>
                  <p class="text-gray-900 dark:text-gray-100">{{ healthRecord.diagnosis }}</p>
                </div>
                <div v-if="healthRecord.treatment">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Treatment</p>
                  <p class="text-gray-900 dark:text-gray-100">{{ healthRecord.treatment }}</p>
                </div>
              </div>

              <div v-if="healthRecord.notes">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Keterangan</p>
                <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ healthRecord.notes }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Medicine Info -->
          <Card v-if="healthRecord.medicine_name || healthRecord.medicine_type || healthRecord.medicine_quantity">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Pill class="h-5 w-5" />
                Informasi Obat
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-if="healthRecord.medicine_name">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Obat</p>
                  <p class="text-gray-900 dark:text-gray-100 font-medium">{{ healthRecord.medicine_name }}</p>
                </div>
                <div v-if="healthRecord.medicine_type">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis</p>
                  <p class="text-gray-900 dark:text-gray-100">{{ getMedicineTypeText(healthRecord.medicine_type) }}</p>
                </div>
                <div v-if="healthRecord.medicine_quantity">
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah</p>
                  <p class="text-gray-900 dark:text-gray-100">{{ healthRecord.medicine_quantity }}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Date Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Calendar class="h-5 w-5" />
                Informasi Tanggal
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pencatatan</p>
                <p class="text-gray-900 dark:text-gray-100 font-medium">{{ formatDate(healthRecord.record_date) }}</p>
              </div>
              
              <Separator />
              
              <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatDateTime(healthRecord.created_at) }}</p>
              </div>
              
              <div v-if="healthRecord.created_at !== healthRecord.updated_at">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Diperbarui</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatDateTime(healthRecord.updated_at) }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Tindakan Cepat
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                <Link :href="route('livestocks.show', { id: healthRecord.livestock.id })">
                  Lihat Detail Ternak
                </Link>
              </Button>
              <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                <Link :href="route('health-records.create')" :data="{ livestock_id: healthRecord.livestock_id }">
                  Tambah Catatan Baru
                </Link>
              </Button>
              <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                <Link :href="route('health-records.index')">
                  Lihat Semua Catatan
                </Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>