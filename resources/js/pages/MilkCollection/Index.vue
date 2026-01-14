<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SecondSidebar from '@/components/SecondSidebar.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxOption,
  ComboboxOptions,
} from '@headlessui/vue';
import { Plus, Milk, Search, TrendingUp, TrendingDown, Truck, Package, Check, ChevronsUpDown } from 'lucide-vue-next';

const debounce = (func: Function, delay: number) => {
  let timeoutId: NodeJS.Timeout;
  return (...args: any[]) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(null, args), delay);
  };
};

interface MilkBatch {
  id: number;
  batch_code: string;
  tracking_number?: string;
  farm: {
    id: string;
    name: string;
  };
  destination_farm?: {
    id: string;
    name: string;
  };
  collection_date: string;
  session: string;
  total_volume: number;
  quality_grade?: string;
  status: string;
  transport_status?: string;
  collected_by?: {
    id: string;
    name: string;
  };
  courier?: {
    id: string;
    name: string;
  };
  courier_name?: string;
}

interface Farm {
  id: string;
  name: string;
  address?: string;
}

interface User {
  id: string;
  name: string;
  email: string;
}

interface Props {
  batches: {
    data: MilkBatch[];
    links: any[];
    total: number;
  };
  filters?: {
    search?: string;
    status?: string;
    grade?: string;
    date_from?: string;
    date_to?: string;
    session?: string;
  };
  stats?: {
    total_batches_month: number;
    total_volume_month: number;
    grade_a_percentage: number;
    grade_b_percentage: number;
    grade_c_percentage: number;
    rejected_percentage: number;
  };
  destinationFarms?: Farm[];
  availableCouriers?: User[];
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedGrade = ref(props.filters?.grade || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const selectedSession = ref(props.filters?.session || '');

const debouncedSearch = debounce(() => {
  performSearch();
}, 300);

const performSearch = () => {
  const params: any = {
    search: searchQuery.value || undefined,
    status: selectedStatus.value || undefined,
    grade: selectedGrade.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    session: selectedSession.value || undefined,
  };

  Object.keys(params).forEach(key => {
    if (params[key] === undefined) {
      delete params[key];
    }
  });

  router.get(route('milk-batches.index'), params, {
    preserveState: true,
    replace: true,
  });
};

watch(searchQuery, () => {
  debouncedSearch();
});

const handleFilterChange = () => {
  performSearch();
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedStatus.value = '';
  selectedGrade.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  selectedSession.value = '';
  router.get(route('milk-batches.index'), {}, {
    preserveState: true,
    replace: true,
  });
};

const getStatusBadge = (status: string) => {
  const badges: Record<string, { variant: string; text: string }> = {
    collected: { variant: 'default', text: 'Dikumpulkan' },
    in_transit: { variant: 'secondary', text: 'Dalam Perjalanan' },
    received: { variant: 'default', text: 'Diterima' },
    tested: { variant: 'default', text: 'Diuji' },
    approved: { variant: 'default', text: 'Disetujui' },
    rejected: { variant: 'destructive', text: 'Ditolak' },
  };
  return badges[status] || { variant: 'default', text: status };
};

const getGradeBadge = (grade?: string) => {
  if (!grade) return null;
  const badges: Record<string, { variant: string }> = {
    A: { variant: 'default' },
    B: { variant: 'secondary' },
    C: { variant: 'outline' },
    Reject: { variant: 'destructive' },
  };
  return badges[grade] || { variant: 'default' };
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};

const formatSession = (session: string) => {
  const sessions: Record<string, string> = {
    morning: 'Pagi',
    afternoon: 'Siang',
    evening: 'Sore'
  };
  return sessions[session] || session;
};

// Dispatch dialog state
const showDispatchDialog = ref(false);
const selectedBatch = ref<MilkBatch | null>(null);
const dispatchForm = ref({
  destination_farm_id: '',
  courier_user_id: '',
  courier_name: '',
  courier_phone: '',
  vehicle_number: '',
  expected_delivery_at: '',
  transport_notes: '',
});
const isSubmittingDispatch = ref(false);
const useCourierUser = ref(true);

// Combobox state for destination farm
const selectedDestinationFarm = ref<Farm | null>(null);
const destinationFarmSearchQuery = ref('');

// Combobox state for courier
const selectedCourier = ref<User | null>(null);
const courierSearchQuery = ref('');

// Computed filtered lists for combobox
const filteredDestinationFarms = computed(() => {
  const query = destinationFarmSearchQuery.value.toLowerCase();
  if (!query) return props.destinationFarms || [];
  return (props.destinationFarms || []).filter(farm => 
    farm.name.toLowerCase().includes(query) || 
    (farm.address && farm.address.toLowerCase().includes(query))
  );
});

const filteredCouriers = computed(() => {
  const query = courierSearchQuery.value.toLowerCase();
  if (!query) return props.availableCouriers || [];
  return (props.availableCouriers || []).filter(courier => 
    courier.name.toLowerCase().includes(query) || 
    courier.email.toLowerCase().includes(query)
  );
});

// Display value functions
const getDestinationFarmDisplayValue = (farm: Farm | null) => {
  if (!farm) return '';
  return farm.address ? `${farm.name} - ${farm.address}` : farm.name;
};

const getCourierDisplayValue = (courier: User | null) => {
  if (!courier) return '';
  return `${courier.name} (${courier.email})`;
};

const openDispatchDialog = (batch: MilkBatch) => {
  selectedBatch.value = batch;
  showDispatchDialog.value = true;
  // Reset form
  dispatchForm.value = {
    destination_farm_id: '',
    courier_user_id: '',
    courier_name: '',
    courier_phone: '',
    vehicle_number: '',
    expected_delivery_at: '',
    transport_notes: '',
  };
  // Reset combobox state
  selectedDestinationFarm.value = null;
  destinationFarmSearchQuery.value = '';
  selectedCourier.value = null;
  courierSearchQuery.value = '';
  useCourierUser.value = true;
};

const closeDispatchDialog = () => {
  showDispatchDialog.value = false;
  selectedBatch.value = null;
  selectedDestinationFarm.value = null;
  selectedCourier.value = null;
};

const submitDispatch = () => {
  if (!selectedBatch.value) return;

  isSubmittingDispatch.value = true;

  const formData: any = {
    destination_farm_id: selectedDestinationFarm.value?.id || dispatchForm.value.destination_farm_id,
    vehicle_number: dispatchForm.value.vehicle_number,
    expected_delivery_at: dispatchForm.value.expected_delivery_at,
    transport_notes: dispatchForm.value.transport_notes,
  };

  if (useCourierUser.value) {
    formData.courier_user_id = selectedCourier.value?.id || dispatchForm.value.courier_user_id;
  } else {
    formData.courier_name = dispatchForm.value.courier_name;
    formData.courier_phone = dispatchForm.value.courier_phone;
  }

  router.post(
    route('milk-batches.dispatch', { milkBatch: selectedBatch.value.id }),
    formData,
    {
      onSuccess: () => {
        closeDispatchDialog();
      },
      onFinish: () => {
        isSubmittingDispatch.value = false;
      },
    }
  );
};

const getTransportStatusBadge = (transportStatus?: string) => {
  if (!transportStatus) return null;
  const badges: Record<string, { variant: string; text: string }> = {
    pending: { variant: 'secondary', text: 'Menunggu' },
    dispatched: { variant: 'default', text: 'Dikirim' },
    in_transit: { variant: 'default', text: 'Dalam Perjalanan' },
    delivered: { variant: 'default', text: 'Terkirim' },
    returned: { variant: 'destructive', text: 'Dikembalikan' },
  };
  return badges[transportStatus] || { variant: 'default', text: transportStatus };
};

const canDispatch = (batch: MilkBatch) => {
  return batch.status === 'collected' && !batch.tracking_number;
};
</script>

<template>
  <Head title="Koleksi Batch Susu" />

  <AppLayout>
    <div class="flex min-h-screen">
      <SecondSidebar current-route="milk-batches.index" />
      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-primary">Koleksi Batch Susu</h1>
            <p class="text-sm text-muted-foreground">Kelola pengumpulan dan batch susu</p>
          </div>
          <Link :href="route('milk-batches.create')">
            <Button>
              <Plus class="w-4 h-4 mr-2" />
              Buat Batch Baru
            </Button>
          </Link>
        </div>

        <!-- Stats Cards -->
        <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Batch Bulan Ini</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.total_batches_month }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Total Volume (L)</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ Number(stats.total_volume_month || 0).toFixed(2) }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Grade A</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600">{{ stats.grade_a_percentage }}%</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium">Ditolak</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-red-600">{{ stats.rejected_percentage }}%</div>
            </CardContent>
          </Card>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
          <div>
            <label class="text-xs font-medium">Cari Kode Batch</label>
            <div class="relative">
              <Search class="absolute left-2 top-2 h-3.5 w-3.5 text-muted-foreground" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="MB-20250310-001"
                class="h-8 pl-8 text-xs w-full border rounded-md px-3 py-1"
              />
            </div>
          </div>

          <div>
            <label class="text-xs font-medium">Status</label>
            <select
              v-model="selectedStatus"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Status</option>
              <option value="collected">Dikumpulkan</option>
              <option value="in_transit">Dalam Perjalanan</option>
              <option value="received">Diterima</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-medium">Grade</label>
            <select
              v-model="selectedGrade"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Grade</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="Reject">Ditolak</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-medium">Sesi</label>
            <select
              v-model="selectedSession"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1">
              <option value="">Semua Sesi</option>
              <option value="morning">Pagi</option>
              <option value="afternoon">Siang</option>
              <option value="evening">Sore</option>
            </select>
          </div>

          <div>
            <label class="text-xs font-medium">Dari Tanggal</label>
            <input
              v-model="dateFrom"
              type="date"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>

          <div>
            <label class="text-xs font-medium">Sampai Tanggal</label>
            <input
              v-model="dateTo"
              type="date"
              @change="handleFilterChange"
              class="h-8 text-xs w-full border rounded-md px-3 py-1"
            />
          </div>
        </div>

        <!-- Batches Table -->
        <Card>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50">
                  <tr>
                    <th class="p-3 text-left text-xs font-medium">Kode Batch</th>
                    <th class="p-3 text-left text-xs font-medium">Tracking</th>
                    <th class="p-3 text-left text-xs font-medium">Tanggal</th>
                    <th class="p-3 text-left text-xs font-medium">Sesi</th>
                    <th class="p-3 text-left text-xs font-medium">Volume (L)</th>
                    <th class="p-3 text-left text-xs font-medium">Grade</th>
                    <th class="p-3 text-left text-xs font-medium">Status</th>
                    <th class="p-3 text-left text-xs font-medium">Transport</th>
                    <th class="p-3 text-left text-xs font-medium">Tujuan</th>
                    <th class="p-3 text-left text-xs font-medium">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="batches.data.length === 0">
                    <td colspan="10" class="p-8 text-center text-muted-foreground">
                      <Milk class="w-12 h-12 mx-auto mb-2 opacity-30" />
                      <p>Belum ada batch susu.</p>
                      <Link :href="route('milk-batches.create')">
                        <Button variant="link" class="mt-2">Buat batch pertama</Button>
                      </Link>
                    </td>
                  </tr>
                  <tr
                    v-for="batch in batches.data"
                    :key="batch.id"
                    class="border-b hover:bg-muted/30 transition-colors"
                  >
                    <td class="p-3 text-sm font-mono">{{ batch.batch_code }}</td>
                    <td class="p-3">
                      <div v-if="batch.tracking_number" class="flex items-center gap-1">
                        <Package class="w-3 h-3 text-muted-foreground" />
                        <span class="text-xs font-mono">{{ batch.tracking_number }}</span>
                      </div>
                      <span v-else class="text-xs text-muted-foreground">-</span>
                    </td>
                    <td class="p-3 text-sm">{{ formatDate(batch.collection_date) }}</td>
                    <td class="p-3 text-sm">{{ formatSession(batch.session) }}</td>
                    <td class="p-3 text-sm font-semibold">{{ batch.total_volume }}</td>
                    <td class="p-3">
                      <Badge v-if="batch.quality_grade" :variant="getGradeBadge(batch.quality_grade)?.variant">
                        Grade {{ batch.quality_grade }}
                      </Badge>
                      <span v-else class="text-xs text-muted-foreground">Belum diuji</span>
                    </td>
                    <td class="p-3">
                      <Badge :variant="getStatusBadge(batch.status).variant">
                        {{ getStatusBadge(batch.status).text }}
                      </Badge>
                    </td>
                    <td class="p-3">
                      <Badge v-if="batch.transport_status" :variant="getTransportStatusBadge(batch.transport_status)?.variant">
                        {{ getTransportStatusBadge(batch.transport_status)?.text }}
                      </Badge>
                      <span v-else class="text-xs text-muted-foreground">-</span>
                    </td>
                    <td class="p-3 text-sm">
                      {{ batch.destination_farm?.name || '-' }}
                    </td>
                    <td class="p-3">
                      <div class="flex items-center gap-2">
                        <Button
                          v-if="canDispatch(batch)"
                          variant="outline"
                          size="sm"
                          @click="openDispatchDialog(batch)"
                        >
                          <Truck class="w-3 h-3 mr-1" />
                          Kirim
                        </Button>
                        <Link :href="route('milk-batches.show', batch.id)">
                          <Button variant="ghost" size="sm">Detail</Button>
                        </Link>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="batches.links.length > 3" class="flex items-center justify-center gap-2 p-4 border-t">
              <Button
                v-for="(link, index) in batches.links"
                :key="index"
                :variant="link.active ? 'default' : 'ghost'"
                size="sm"
                :disabled="!link.url"
                @click="link.url && router.visit(link.url)"
                v-html="link.label"
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Dispatch Dialog -->
    <Dialog :open="showDispatchDialog" @update:open="closeDispatchDialog">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Kirim Batch ke Pabrik</DialogTitle>
          <DialogDescription>
            Batch: {{ selectedBatch?.batch_code }} - Volume: {{ selectedBatch?.total_volume }} L
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <!-- Destination Farm -->
          <div class="space-y-2">
            <Label for="destination_farm_id">Tujuan Pabrik <span class="text-red-500">*</span></Label>
            <Combobox v-model="selectedDestinationFarm">
              <div class="relative">
                <ComboboxButton
                  class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                  <span class="block truncate text-foreground">{{ selectedDestinationFarm ? getDestinationFarmDisplayValue(selectedDestinationFarm) : 'Pilih pabrik tujuan' }}</span>
                  <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <ChevronsUpDown class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                  </span>
                </ComboboxButton>
                <ComboboxOptions
                  class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-popover border border-border py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                  <div class="relative">
                    <ComboboxInput
                      class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                      :display-value="() => ''" @change="destinationFarmSearchQuery = $event.target.value"
                      placeholder="Cari pabrik..." />
                  </div>
                  <div v-if="filteredDestinationFarms.length === 0 && destinationFarmSearchQuery !== ''"
                    class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                    Pabrik tidak ditemukan.
                  </div>
                  <ComboboxOption v-for="farm in filteredDestinationFarms" :key="farm.id" v-slot="{ selected, active }"
                    :value="farm" as="template">
                    <li :class="[
                      active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                      'relative cursor-default select-none py-2 pl-10 pr-4',
                    ]">
                      <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                        {{ farm.name }}
                      </span>
                      <span v-if="farm.address"
                        :class="[active ? 'text-accent-foreground/70' : 'text-muted-foreground', 'block text-xs truncate']">
                        {{ farm.address }}
                      </span>
                      <span v-if="selected" :class="[
                        active ? 'text-accent-foreground' : 'text-primary',
                        'absolute inset-y-0 left-0 flex items-center pl-3',
                      ]">
                        <Check class="h-4 w-4" aria-hidden="true" />
                      </span>
                    </li>
                  </ComboboxOption>
                </ComboboxOptions>
              </div>
            </Combobox>
          </div>

          <!-- Courier Selection Type -->
          <div class="space-y-2">
            <Label>Jenis Kurir</Label>
            <div class="flex gap-4">
              <label class="flex items-center gap-2">
                <input
                  v-model="useCourierUser"
                  type="radio"
                  :value="true"
                  class="text-primary"
                />
                <span class="text-sm">Pengguna Sistem</span>
              </label>
              <label class="flex items-center gap-2">
                <input
                  v-model="useCourierUser"
                  type="radio"
                  :value="false"
                  class="text-primary"
                />
                <span class="text-sm">Kurir Eksternal</span>
              </label>
            </div>
          </div>

          <!-- System User Courier -->
          <div v-if="useCourierUser" class="space-y-2">
            <Label for="courier_user_id">Pilih Kurir <span class="text-red-500">*</span></Label>
            <Combobox v-model="selectedCourier">
              <div class="relative">
                <ComboboxButton
                  class="relative w-full cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                  <span class="block truncate text-foreground">{{ selectedCourier ? getCourierDisplayValue(selectedCourier) : 'Pilih kurir' }}</span>
                  <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <ChevronsUpDown class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                  </span>
                </ComboboxButton>
                <ComboboxOptions
                  class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-popover border border-border py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                  <div class="relative">
                    <ComboboxInput
                      class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0"
                      :display-value="() => ''" @change="courierSearchQuery = $event.target.value"
                      placeholder="Cari kurir..." />
                  </div>
                  <div v-if="filteredCouriers.length === 0 && courierSearchQuery !== ''"
                    class="relative cursor-default select-none px-4 py-2 text-muted-foreground">
                    Kurir tidak ditemukan.
                  </div>
                  <ComboboxOption v-for="courier in filteredCouriers" :key="courier.id" v-slot="{ selected, active }"
                    :value="courier" as="template">
                    <li :class="[
                      active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                      'relative cursor-default select-none py-2 pl-10 pr-4',
                    ]">
                      <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                        {{ courier.name }}
                      </span>
                      <span
                        :class="[active ? 'text-accent-foreground/70' : 'text-muted-foreground', 'block text-xs truncate']">
                        {{ courier.email }}
                      </span>
                      <span v-if="selected" :class="[
                        active ? 'text-accent-foreground' : 'text-primary',
                        'absolute inset-y-0 left-0 flex items-center pl-3',
                      ]">
                        <Check class="h-4 w-4" aria-hidden="true" />
                      </span>
                    </li>
                  </ComboboxOption>
                </ComboboxOptions>
              </div>
            </Combobox>
          </div>

          <!-- External Courier -->
          <div v-else class="space-y-4">
            <div class="space-y-2">
              <Label for="courier_name">Nama Kurir <span class="text-red-500">*</span></Label>
              <Input
                id="courier_name"
                v-model="dispatchForm.courier_name"
                type="text"
                placeholder="Nama lengkap kurir"
                :required="!useCourierUser"
              />
            </div>

            <div class="space-y-2">
              <Label for="courier_phone">Nomor Telepon</Label>
              <Input
                id="courier_phone"
                v-model="dispatchForm.courier_phone"
                type="tel"
                placeholder="08xx-xxxx-xxxx"
              />
            </div>
          </div>

          <!-- Vehicle Number -->
          <div class="space-y-2">
            <Label for="vehicle_number">Nomor Kendaraan</Label>
            <Input
              id="vehicle_number"
              v-model="dispatchForm.vehicle_number"
              type="text"
              placeholder="B 1234 XYZ"
            />
          </div>

          <!-- Expected Delivery -->
          <div class="space-y-2">
            <Label for="expected_delivery_at">Estimasi Waktu Tiba</Label>
            <Input
              id="expected_delivery_at"
              v-model="dispatchForm.expected_delivery_at"
              type="datetime-local"
            />
          </div>

          <!-- Transport Notes -->
          <div class="space-y-2">
            <Label for="transport_notes">Catatan Transportasi</Label>
            <Textarea
              id="transport_notes"
              v-model="dispatchForm.transport_notes"
              placeholder="Catatan tambahan untuk pengiriman..."
              rows="3"
            />
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <Button
            variant="outline"
            @click="closeDispatchDialog"
            :disabled="isSubmittingDispatch"
          >
            Batal
          </Button>
          <Button
            @click="submitDispatch"
            :disabled="isSubmittingDispatch || !selectedDestinationFarm || (useCourierUser && !selectedCourier) || (!useCourierUser && !dispatchForm.courier_name)"
          >
            {{ isSubmittingDispatch ? 'Mengirim...' : 'Kirim Batch' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
