<template>
  <Card class="border border-primary/20 dark:border-primary/80 flex flex-col h-full">
    <CardContent class="p-4 flex-1">
      <h3 class="font-semibold text-lg mb-4">Riwayat Kesehatan</h3>
      
      <ul v-if="history && history.length > 0" class="space-y-3">
        <li v-for="item in history" :key="`${item.id || item.date}-${item.status}`" class="pb-3">
          <div class="flex justify-between items-start">
            <div>
              <Badge v-if="item.status === 'sehat' || item.status === 'Sehat'" variant="default" class="rounded-full mb-1">Sehat</Badge>
              <Badge v-else variant="destructive" class="rounded-full mb-1">Sakit</Badge>
              
              <p v-if="item.diagnosis" class="text-sm font-medium mt-1">{{ item.diagnosis }}</p>
              <p v-if="item.treatment" class="text-sm text-muted-foreground mt-1">Treatment: {{ item.treatment }}</p>
              <p v-if="item.medicine_name" class="text-sm text-muted-foreground mt-1">Obat: {{ item.medicine_name }}
                <span v-if="item.medicine_type"> ({{ getMedicineTypeText(item.medicine_type) }})</span>
                <span v-if="item.medicine_quantity"> - {{ item.medicine_quantity }}</span>
              </p>
              <p v-if="item.notes" class="text-xs text-muted-foreground mt-1 italic">
                "{{ item.notes }}"
              </p>
              <p v-else-if="item.desc" class="text-sm mt-1">{{ item.desc }}</p>
            </div>
            <div class="text-right">
              <div class="text-sm">{{ formatDate(item.record_date || item.date) }}</div>
            </div>
          </div>
        </li>
      </ul>

      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada riwayat kesehatan</p>
        <p class="text-xs">Data pemeriksaan kesehatan akan muncul di sini</p>
      </div>
    </CardContent>

    <CardFooter v-if="history && history.length > 0" class="p-0 mt-auto">
      <Button @click="openMonthlyDialog" variant="ghost"
        class="w-full justify-center rounded-none text-primary hover:bg-primary/10 py-3" style="box-shadow: none;">
        <span class="font-semibold underline">Lihat bulan ini</span>
      </Button>
    </CardFooter>
  </Card>

  <!-- Monthly Health Dialog -->
  <Dialog v-model:open="showMonthlyDialog">
    <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Riwayat Kesehatan - {{ getMonthYearName(selectedMonth, selectedYear) }}</DialogTitle>
        <DialogDescription>
          Seluruh catatan kesehatan pada bulan {{ getMonthYearName(selectedMonth, selectedYear) }}
        </DialogDescription>
      </DialogHeader>

      <!-- Month/Year Filter -->
      <div class="flex gap-4 mb-4">
        <div class="flex-1">
          <Label for="month-select" class="text-sm font-medium">Bulan</Label>
          <Select v-model="selectedMonth">
            <SelectTrigger id="month-select">
              <SelectValue placeholder="Pilih bulan" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="(month, index) in months" :key="index" :value="index">
                {{ month }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex-1">
          <Label for="year-select" class="text-sm font-medium">Tahun</Label>
          <Select v-model="selectedYear">
            <SelectTrigger id="year-select">
              <SelectValue placeholder="Pilih tahun" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div v-if="filteredHealthRecords && filteredHealthRecords.length > 0" class="space-y-4">
        <div v-for="record in filteredHealthRecords" :key="record.id || `${record.date}-${record.status}`" 
             class="border-b border-border pb-4 last:border-b-0">
          <div class="flex justify-between items-start mb-2">
            <div class="flex items-center gap-2">
              <Badge v-if="record.health_status === 'sehat' || record.status === 'Sehat'" variant="default" class="rounded-full">Sehat</Badge>
              <Badge v-else variant="destructive" class="rounded-full">Sakit</Badge>
              <span class="font-medium text-sm">{{ formatDateLong(record.record_date || record.date) }}</span>
            </div>
          </div>
          
          <div class="ml-4 space-y-2">
            <div v-if="record.diagnosis" class="grid grid-cols-3 gap-4 text-sm">
              <span class="font-medium text-muted-foreground">Diagnosa:</span>
              <span class="col-span-2">{{ record.diagnosis }}</span>
            </div>
            
            <div v-if="record.treatment" class="grid grid-cols-3 gap-4 text-sm">
              <span class="font-medium text-muted-foreground">Treatment:</span>
              <span class="col-span-2">{{ record.treatment }}</span>
            </div>
            
            <div v-if="record.medicine_name" class="grid grid-cols-3 gap-4 text-sm">
              <span class="font-medium text-muted-foreground">Obat:</span>
              <span class="col-span-2">
                {{ record.medicine_name }}
                <span v-if="record.medicine_type"> ({{ getMedicineTypeText(record.medicine_type) }})</span>
                <span v-if="record.medicine_quantity"> - {{ record.medicine_quantity }}</span>
              </span>
            </div>
            
            <div v-if="record.notes" class="grid grid-cols-3 gap-4 text-sm">
              <span class="font-medium text-muted-foreground">Keterangan:</span>
              <span class="col-span-2 italic">{{ record.notes }}</span>
            </div>
            
            <div v-else-if="record.desc" class="grid grid-cols-3 gap-4 text-sm">
              <span class="font-medium text-muted-foreground">Keterangan:</span>
              <span class="col-span-2">{{ record.desc }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-muted-foreground">
        <p class="text-sm">Belum ada riwayat kesehatan pada {{ getMonthYearName(selectedMonth, selectedYear) }}</p>
      </div>

      <DialogFooter>
        <Button @click="showMonthlyDialog = false" variant="secondary">Tutup</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
  
<script setup lang="ts">
import { computed, ref } from 'vue';
import { Card, CardContent, CardFooter } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Label } from "@/components/ui/label";

// Type definitions
interface HealthRecord {
  id?: string;
  health_status?: 'sehat' | 'sakit';
  status?: string;
  diagnosis?: string;
  treatment?: string;
  notes?: string;
  desc?: string;
  medicine_name?: string;
  medicine_type?: string;
  medicine_quantity?: number;
  record_date?: string;
  date?: string;
  created_at?: string;
}

const props = defineProps<{
  history?: HealthRecord[];
}>();

// Dialog state
const showMonthlyDialog = ref(false);
const selectedMonth = ref(new Date().getMonth());
const selectedYear = ref(new Date().getFullYear());

const openMonthlyDialog = () => {
  selectedMonth.value = new Date().getMonth();
  selectedYear.value = new Date().getFullYear();
  showMonthlyDialog.value = true;
};

// Month names
const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Get month and year display name
const getMonthYearName = (month: number, year: number): string => {
  return `${months[month]} ${year}`;
};

// Get available years from health records
const availableYears = computed((): number[] => {
  if (!props.history || props.history.length === 0) {
    return [new Date().getFullYear()];
  }

  const years = new Set<number>();
  props.history.forEach((record: HealthRecord) => {
    const recordDate = new Date(record.record_date || record.date || new Date());
    years.add(recordDate.getFullYear());
  });

  const yearArray = Array.from(years).sort((a: number, b: number) => b - a); // Latest year first
  return yearArray.length > 0 ? yearArray : [new Date().getFullYear()];
});

// Format date
const formatDate = (dateStr: string): string => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const options: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    timeZone: 'Asia/Jakarta'
  };
  return date.toLocaleDateString('id-ID', options);
};

// Format date for monthly view
const formatDateLong = (dateStr: string): string => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const options: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'Asia/Jakarta'
  };
  return date.toLocaleDateString('id-ID', options);
};

// Get medicine type text
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

// Get filtered health records based on selected month/year
const filteredHealthRecords = computed((): HealthRecord[] => {
  if (!props.history || props.history.length === 0) {
    return [];
  }

  // Filter records for selected month/year
  const monthlyRecords = props.history.filter((record: HealthRecord) => {
    const recordDate = new Date(record.record_date || record.date || new Date());
    return recordDate.getMonth() === selectedMonth.value && recordDate.getFullYear() === selectedYear.value;
  });

  // Sort by date (newest first)
  return monthlyRecords.sort((a: HealthRecord, b: HealthRecord) => {
    const dateA = new Date(a.record_date || a.date || new Date()).getTime();
    const dateB = new Date(b.record_date || b.date || new Date()).getTime();
    return dateB - dateA;
  });
});
</script>
  