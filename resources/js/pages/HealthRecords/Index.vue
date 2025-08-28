<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Plus, MoreVertical, Pencil, Trash2, Calendar, Stethoscope } from 'lucide-vue-next';

interface HealthRecord {
  id: string;
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
}

interface PaginatedHealthRecords {
  data: HealthRecord[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

defineProps<{
  healthRecords: PaginatedHealthRecords;
}>();

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

const deleteRecord = (id: string) => {
  if (confirm('Yakin ingin menghapus catatan kesehatan ini?')) {
    router.delete(route('health-records.destroy', id));
  }
};

const getStatusVariant = (status: string) => {
  return status === 'sehat' ? 'default' : 'destructive';
};

const getStatusText = (status: string) => {
  return status === 'sehat' ? 'Sehat' : 'Sakit';
};
</script>

<template>
  <Head title="Catatan Kesehatan Ternak" />

  <AppLayout>
    <div class="max-w-7xl mx-auto p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Catatan Kesehatan Ternak</h1>
          <p class="text-gray-600 dark:text-gray-400">Kelola catatan kesehatan ternak Anda</p>
        </div>
        <Button asChild class="bg-emerald-600 hover:bg-emerald-700">
          <Link :href="route('health-records.create')">
            <Plus class="h-4 w-4 mr-2" />
            Tambah Catatan
          </Link>
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-emerald-100 text-emerald-600">
                <Stethoscope class="h-6 w-6" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Catatan</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ healthRecords.total }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-green-100 text-green-600">
                <Calendar class="h-6 w-6" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ternak Sehat</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ healthRecords.data.filter(r => r.health_status === 'sehat').length }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-red-100 text-red-600">
                <Stethoscope class="h-6 w-6" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ternak Sakit</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ healthRecords.data.filter(r => r.health_status === 'sakit').length }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Health Records Table -->
      <Card>
        <CardHeader>
          <CardTitle>Daftar Catatan Kesehatan</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="healthRecords.data.length === 0" class="text-center py-12">
            <Stethoscope class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum ada catatan kesehatan</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Mulai catat kesehatan ternak Anda sekarang</p>
            <Button asChild class="bg-emerald-600 hover:bg-emerald-700">
              <Link :href="route('health-records.create')">
                <Plus class="h-4 w-4 mr-2" />
                Tambah Catatan Pertama
              </Link>
            </Button>
          </div>

          <div v-else>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Ternak</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Diagnosa</TableHead>
                  <TableHead>Treatment</TableHead>
                  <TableHead>Obat</TableHead>
                  <TableHead>Tanggal</TableHead>
                  <TableHead class="w-16"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="record in healthRecords.data" :key="record.id">
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ record.livestock.tag_id }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ record.livestock.name }} - {{ record.livestock.breed.name }}
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getStatusVariant(record.health_status)">
                      {{ getStatusText(record.health_status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <span v-if="record.diagnosis">{{ record.diagnosis }}</span>
                    <span v-else class="text-gray-400 dark:text-gray-500 italic">-</span>
                  </TableCell>
                  <TableCell>
                    <span v-if="record.treatment">{{ record.treatment }}</span>
                    <span v-else class="text-gray-400 dark:text-gray-500 italic">-</span>
                  </TableCell>
                  <TableCell>
                    <div v-if="record.medicine_name">
                      <div class="font-medium">{{ record.medicine_name }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400" v-if="record.medicine_type || record.medicine_quantity">
                        {{ record.medicine_type }}{{ record.medicine_quantity ? ` - ${record.medicine_quantity}` : '' }}
                      </div>
                    </div>
                    <span v-else class="text-gray-400 dark:text-gray-500 italic">-</span>
                  </TableCell>
                  <TableCell>
                    {{ formatDate(record.record_date) }}
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                          <MoreVertical class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem asChild>
                          <Link :href="route('health-records.show', record.id)" class="cursor-pointer">
                            Lihat Detail
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                          <Link :href="route('health-records.edit', record.id)" class="cursor-pointer">
                            <Pencil class="h-4 w-4 mr-2" />
                            Edit
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem 
                          @click="deleteRecord(record.id)"
                          class="text-red-600 cursor-pointer"
                        >
                          <Trash2 class="h-4 w-4 mr-2" />
                          Hapus
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>

            <!-- Pagination -->
            <div v-if="healthRecords.last_page > 1" class="flex items-center justify-between mt-6">
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ (healthRecords.current_page - 1) * healthRecords.per_page + 1 }} 
                sampai {{ Math.min(healthRecords.current_page * healthRecords.per_page, healthRecords.total) }} 
                dari {{ healthRecords.total }} catatan
              </div>
              <div class="flex items-center gap-2">
                <template v-for="link in healthRecords.links" :key="link.label">
                  <Button
                    v-if="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    @click="router.get(link.url)"
                  >
                    {{ link.label }}
                  </Button>
                  <span v-else class="px-3 py-1 text-gray-400 dark:text-gray-500">{{ link.label }}</span>
                </template>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>