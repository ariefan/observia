<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { IconHorse } from '@tabler/icons-vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Plus, MoreVertical, Pencil, Trash2, Calendar, Stethoscope } from 'lucide-vue-next';
import SecondSidebar from '@/components/SecondSidebar.vue';

interface Medicine {
  name: string;
  type?: string;
  quantity?: number;
  dosage?: string;
}

interface HealthRecord {
  id: string;
  health_status: 'healthy' | 'sick';
  diagnosis: string[] | string | null;
  treatment: string[] | string | null;
  notes: string | null;
  medicines: Medicine[] | null;
  medicine_name: string | null; // Backward compatibility
  medicine_type: string | null; // Backward compatibility
  medicine_quantity: number | null; // Backward compatibility
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

const props = defineProps<{
  healthRecords: PaginatedHealthRecords;
  livestockStats?: {
    total: number;
    healthy: number;
    sick: number;
  };
}>();

// Group health records by livestock and get the most recent status
const groupedLivestock = computed(() => {
  const grouped = new Map();

  props.healthRecords.data.forEach(record => {
    const livestockId = record.livestock.id;

    if (!grouped.has(livestockId)) {
      grouped.set(livestockId, {
        livestock: record.livestock,
        latestRecord: record,
        allRecords: [record]
      });
    } else {
      const existing = grouped.get(livestockId);
      existing.allRecords.push(record);

      // Update latest record if this one is more recent
      if (new Date(record.record_date) > new Date(existing.latestRecord.record_date)) {
        existing.latestRecord = record;
      }
    }
  });

  return Array.from(grouped.values());
});

// Use backend-provided stats or fallback to computed stats
const displayStats = computed(() => {
  if (props.livestockStats) {
    return props.livestockStats;
  }

  // Fallback for backward compatibility
  const healthyCount = groupedLivestock.value.filter(
    item => item.latestRecord.health_status === 'healthy'
  ).length;

  const sickCount = groupedLivestock.value.filter(
    item => item.latestRecord.health_status === 'sick'
  ).length;

  return {
    total: groupedLivestock.value.length,
    healthy: healthyCount,
    sick: sickCount
  };
});

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

const deleteRecord = (id: string) => {
  if (confirm('Yakin ingin menghapus catatan kesehatan ini?')) {
    router.delete(route('health-records.destroy', { id }));
  }
};

const getStatusVariant = (status: string) => {
  return status === 'healthy' ? 'default' : 'destructive';
};

const getStatusText = (status: string) => {
  return status === 'healthy' ? 'Sehat' : 'Sakit';
};
</script>

<template>

  <Head title="Catatan Kesehatan Ternak" />

  <AppLayout>
    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <SecondSidebar current-route="health-records.index" />

      <div class="flex-1 max-w-7xl mx-auto p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Catatan Kesehatan Ternak</h1>
            <p class="text-gray-600 dark:text-gray-400">Kelola catatan kesehatan ternak Anda</p>
          </div>
          <Button asChild>
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
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Ternak</p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ displayStats.total }}</p>
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
                    {{ displayStats.healthy }}
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
                    {{ displayStats.sick }}
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
            <div v-if="groupedLivestock.length === 0" class="text-center py-12">
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
                    <TableHead>Tanggal Terakhir</TableHead>
                    <TableHead class="w-16"></TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="item in groupedLivestock" :key="item.livestock.id">
                    <TableCell>
                      <div>
                        <div class="font-medium">{{ item.livestock.tag_id }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ item.livestock.name }} - {{ item.livestock.breed.name }}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">
                          {{ item.allRecords.length }} catatan
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="getStatusVariant(item.latestRecord.health_status)">
                        {{ getStatusText(item.latestRecord.health_status) }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div v-if="item.latestRecord.diagnosis">
                        <span v-if="Array.isArray(item.latestRecord.diagnosis)" class="space-y-1">
                          <div v-for="(diag, index) in item.latestRecord.diagnosis" :key="index" class="text-sm">
                            • {{ diag }}
                          </div>
                        </span>
                        <span v-else>{{ item.latestRecord.diagnosis }}</span>
                      </div>
                      <span v-else class="text-gray-400 dark:text-gray-500 italic">-</span>
                    </TableCell>
                    <TableCell>
                      <div v-if="item.latestRecord.treatment">
                        <span v-if="Array.isArray(item.latestRecord.treatment)" class="space-y-1">
                          <div v-for="(treatment, index) in item.latestRecord.treatment" :key="index" class="text-sm">
                            • {{ treatment }}
                          </div>
                        </span>
                        <span v-else>{{ item.latestRecord.treatment }}</span>
                      </div>
                      <span v-else class="text-gray-400 dark:text-gray-500 italic">-</span>
                    </TableCell>
                    <TableCell>
                      <div v-if="item.latestRecord.medicines && item.latestRecord.medicines.length > 0">
                        <div v-for="(medicine, index) in item.latestRecord.medicines" :key="index"
                          class="mb-2 last:mb-0">
                          <div class="font-medium">{{ medicine.name }}</div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="medicine.type">{{ medicine.type }}</span>
                            <span v-if="medicine.quantity">{{ medicine.type ? ' - ' : '' }}{{ medicine.quantity
                            }}</span>
                            <span v-if="medicine.dosage">{{ (medicine.type || medicine.quantity) ? ' - ' : '' }}{{
                              medicine.dosage }}</span>
                          </div>
                        </div>
                      </div>
                      <div v-else-if="item.latestRecord.medicine_name">
                        <div class="font-medium">{{ item.latestRecord.medicine_name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400"
                          v-if="item.latestRecord.medicine_type || item.latestRecord.medicine_quantity">
                          {{ item.latestRecord.medicine_type }}{{ item.latestRecord.medicine_quantity ? ` -
                          ${item.latestRecord.medicine_quantity}` : '' }}
                        </div>
                      </div>
                      <span v-else class="text-gray-400 dark:text-gray-500 italic">-</span>
                    </TableCell>
                    <TableCell>
                      {{ formatDate(item.latestRecord.record_date) }}
                    </TableCell>
                    <TableCell>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                            <MoreVertical class="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <!-- <DropdownMenuItem asChild>
                          <Link :href="route('health-records.show', { id: item.latestRecord.id })" class="cursor-pointer">
                            Lihat Catatan
                          </Link>
                        </DropdownMenuItem> -->
                          <DropdownMenuItem asChild>
                            <Link :href="route('health-records.edit', { id: item.latestRecord.id })"
                              class="cursor-pointer">
                            <Pencil class="h-4 w-4 mr-2" />
                            Edit
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem asChild>
                            <Link :href="route('livestocks.show', { id: item.livestock.id })" class="cursor-pointer">
                            <IconHorse class="h-4 w-4 mr-2" />
                            Lihat Detail
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem @click="deleteRecord(item.latestRecord.id)"
                            class="text-red-600 cursor-pointer">
                            <Trash2 class="h-4 w-4 mr-2" />
                            Hapus Terkini
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>

              <!-- Summary -->
              <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  Menampilkan {{ groupedLivestock.length }} ternak dari {{ healthRecords.total }} total catatan
                  kesehatan
                </div>
                <div v-if="healthRecords.last_page > 1" class="flex items-center gap-2">
                  <template v-for="link in healthRecords.links" :key="link.label">
                    <Button v-if="link.url" :variant="link.active ? 'default' : 'outline'" size="sm"
                      @click="router.get(link.url)">
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
    </div>
  </AppLayout>
</template>