<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Search, Download, Eye, Calendar, User, Database, ChevronsUpDown, Check } from 'lucide-vue-next';
import {
  Combobox,
  ComboboxButton,
  ComboboxInput,
  ComboboxOption,
  ComboboxOptions,
} from '@headlessui/vue';
import SecondSidebar from '@/components/SecondSidebar.vue';

interface Audit {
  id: number;
  user_name: string | null;
  user_email: string | null;
  auditable_type: string;
  auditable_id: string;
  event: string;
  event_name: string;
  model_name: string;
  old_values: Record<string, any> | null;
  new_values: Record<string, any> | null;
  ip_address: string | null;
  created_at: string;
  user: {
    id: string;
    name: string;
    email: string;
  } | null;
}

interface Props {
  audits: {
    data: Audit[];
    links?: any[];
    meta?: any;
  };
  filters?: {
    model?: string;
    event?: string;
    user_id?: string;
    date_from?: string;
    date_to?: string;
    search?: string;
    farm_id?: string;
  };
  modelTypes?: Array<{ value: string; label: string }>;
  eventTypes?: Array<{ value: string; label: string }>;
  allFarms?: Array<{ id: string; name: string; address?: string }>;
  selectedFarmId?: string;
  isSuperUser?: boolean;
}

const props = defineProps<Props>();

const filters = ref({
  model: props.filters?.model || '',
  event: props.filters?.event || '',
  user_id: props.filters?.user_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
  search: props.filters?.search || '',
  farm_id: props.filters?.farm_id || 'all',
});

const search = () => {
  router.get(route('audits.index'), filters.value, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  filters.value = {
    model: '',
    event: '',
    user_id: '',
    date_from: '',
    date_to: '',
    search: '',
  };
  search();
};

const clearModelFilter = () => {
  filters.value.model = '';
  search();
};

const clearEventFilter = () => {
  filters.value.event = '';
  search();
};

const exportAudits = () => {
  window.open(route('audits.export', filters.value), '_blank');
};

const getEventBadgeVariant = (event: string) => {
  switch (event) {
    case 'created':
      return 'default';
    case 'updated':
      return 'secondary';
    case 'deleted':
      return 'destructive';
    case 'restored':
      return 'outline';
    default:
      return 'secondary';
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleString();
};

const getChangesCount = (audit: Audit) => {
  if (!audit.old_values || !audit.new_values) return 0;
  return Object.keys(audit.new_values).length;
};

// Farm selection for super users
const farmQuery = ref('');

const filteredFarms = computed(() => {
  if (!props.allFarms) return [];
  
  const allOption = { id: 'all', name: 'Semua Peternakan', address: 'Lihat semua data peternakan' };
  const farms = [allOption, ...props.allFarms];
  
  return farmQuery.value === ''
    ? farms
    : farms.filter((farm) =>
        farm.name
          .toLowerCase()
          .replace(/\s+/g, '')
          .includes(farmQuery.value.toLowerCase().replace(/\s+/g, ''))
      );
});

const selectedFarmObject = computed(() => {
  if (filters.value.farm_id === 'all') {
    return { id: 'all', name: 'Semua Peternakan', address: 'Lihat semua data peternakan' };
  }
  return props.allFarms?.find(farm => farm.id === filters.value.farm_id) || null;
});

const handleFarmChange = (farmId: string) => {
  filters.value.farm_id = farmId;
  search();
};
</script>

<template>
  <Head title="Jejak Audit" />

  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Jejak Audit
      </h2>
    </template>

    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <SecondSidebar current-route="audits.index" />

      <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Jejak Audit</h1>
          <p class="text-muted-foreground">
            {{ isSuperUser && selectedFarmObject?.name === 'Semua Peternakan' 
                ? 'Lacak semua perubahan di seluruh peternakan' 
                : isSuperUser 
                  ? `Lacak perubahan di ${selectedFarmObject?.name}` 
                  : 'Lacak semua perubahan yang dilakukan pada data peternakan Anda' }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Farm Selection Combobox for Super Users -->
          <Combobox v-if="isSuperUser" v-model="filters.farm_id" @update:model-value="handleFarmChange">
            <div class="relative">
              <ComboboxButton
                class="relative w-64 cursor-default rounded-lg bg-background border border-input py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:text-sm">
                <span class="block truncate text-foreground">{{ selectedFarmObject?.name || 'Pilih Peternakan' }}</span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                  <ChevronsUpDown class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                </span>
              </ComboboxButton>
              <ComboboxOptions
                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-popover border border-border py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                <div class="relative">
                  <ComboboxInput
                    class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-foreground placeholder:text-muted-foreground focus:ring-0"
                    :display-value="(farm: any) => farm?.name"
                    @change="farmQuery = $event.target.value"
                    placeholder="Cari peternakan..."
                  />
                </div>
                <ComboboxOption
                  v-for="farm in filteredFarms"
                  :key="farm.id"
                  v-slot="{ selected, active }"
                  :value="farm.id"
                  as="template"
                >
                  <li
                    :class="[
                      'relative cursor-default select-none py-2 pl-10 pr-4',
                      active ? 'bg-accent text-accent-foreground' : 'text-foreground',
                    ]"
                  >
                    <span :class="['block truncate', selected ? 'font-medium' : 'font-normal']">
                      {{ farm.name }}
                    </span>
                    <span v-if="farm.address" :class="['block text-xs', active ? 'text-accent-foreground/70' : 'text-muted-foreground']">
                      {{ farm.address }}
                    </span>
                    <span
                      v-if="selected"
                      :class="[
                        'absolute inset-y-0 left-0 flex items-center pl-3',
                        active ? 'text-accent-foreground' : 'text-primary',
                      ]"
                    >
                      <Check class="h-4 w-4" aria-hidden="true" />
                    </span>
                  </li>
                </ComboboxOption>
              </ComboboxOptions>
            </div>
          </Combobox>
          
          <Button @click="exportAudits" variant="outline">
            <Download class="mr-2 h-4 w-4" />
            Ekspor CSV
          </Button>
        </div>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">Filter</CardTitle>
          <CardDescription>
            Filter catatan audit berdasarkan model, event, tanggal, atau kata kunci
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="space-y-2">
              <Label for="search">Cari</Label>
              <div class="relative">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  id="search"
                  v-model="filters.search"
                  placeholder="Cari pengguna, model, event..."
                  class="pl-8"
                  @keyup.enter="search"
                />
              </div>
            </div>

            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label for="model">Jenis Model</Label>
                <Button 
                  v-if="filters.model" 
                  @click="clearModelFilter" 
                  variant="ghost" 
                  size="sm"
                  class="h-auto p-1 text-xs"
                >
                  Hapus
                </Button>
              </div>
              <Select v-model="filters.model">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih jenis model" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="modelType in (modelTypes || [])" 
                    :key="modelType.value"
                    :value="modelType.value"
                  >
                    {{ modelType.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <Label for="event">Jenis Event</Label>
                <Button 
                  v-if="filters.event" 
                  @click="clearEventFilter" 
                  variant="ghost" 
                  size="sm"
                  class="h-auto p-1 text-xs"
                >
                  Hapus
                </Button>
              </div>
              <Select v-model="filters.event">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih jenis event" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="eventType in (eventTypes || [])" 
                    :key="eventType.value"
                    :value="eventType.value"
                  >
                    {{ eventType.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="date_from">Tanggal Dari</Label>
              <Input
                id="date_from"
                v-model="filters.date_from"
                type="date"
              />
            </div>

            <div class="space-y-2">
              <Label for="date_to">Tanggal Sampai</Label>
              <Input
                id="date_to"
                v-model="filters.date_to"
                type="date"
              />
            </div>

            <div class="flex items-end space-x-2">
              <Button @click="search" class="flex-1">
                Cari
              </Button>
              <Button @click="clearFilters" variant="outline">
                Hapus Semua
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Results -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">
            Catatan Audit
            <span class="text-sm font-normal text-muted-foreground ml-2">
              ({{ audits.data?.length || 0 }} catatan)
            </span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Tanggal</TableHead>
                  <TableHead>Pengguna</TableHead>
                  <TableHead>Model</TableHead>
                  <TableHead>Event</TableHead>
                  <TableHead>Perubahan</TableHead>
                  <TableHead>Alamat IP</TableHead>
                  <TableHead class="w-24">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="audit in (audits.data || [])" :key="audit.id">
                  <TableCell class="font-medium">
                    <div class="flex items-center space-x-2">
                      <Calendar class="h-4 w-4 text-muted-foreground" />
                      <span class="text-sm">{{ formatDate(audit.created_at) }}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center space-x-2">
                      <User class="h-4 w-4 text-muted-foreground" />
                      <div>
                        <div class="text-sm font-medium">
                          {{ audit.user_name || 'System' }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                          {{ audit.user_email }}
                        </div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center space-x-2">
                      <Database class="h-4 w-4 text-muted-foreground" />
                      <div>
                        <div class="text-sm font-medium">{{ audit.model_name }}</div>
                        <div class="text-xs text-muted-foreground">
                          ID: {{ audit.auditable_id }}
                        </div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getEventBadgeVariant(audit.event)">
                      {{ audit.event_name }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm">
                      {{ getChangesCount(audit) }} field berubah
                    </div>
                  </TableCell>
                  <TableCell>
                    <span class="text-sm text-muted-foreground">
                      {{ audit.ip_address || '-' }}
                    </span>
                  </TableCell>
                  <TableCell>
                    <Link 
                      :href="route('audits.show', audit.id)"
                      class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8"
                    >
                      <Eye class="h-4 w-4" />
                    </Link>
                  </TableCell>
                </TableRow>
                <TableRow v-if="!audits.data || audits.data.length === 0">
                  <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                    Tidak ada catatan audit ditemukan
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div v-if="audits.links && audits.links.length > 3" class="mt-4 flex justify-center">
            <div class="flex space-x-1">
              <Link
                v-for="link in (audits.links || [])"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm',
                  link.active 
                    ? 'bg-primary text-primary-foreground rounded-md' 
                    : 'text-muted-foreground hover:text-foreground',
                  !link.url ? 'pointer-events-none opacity-50' : ''
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </CardContent>
      </Card>
      </div>
    </div>
  </AppLayout>
</template>