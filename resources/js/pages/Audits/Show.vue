<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import { 
  ArrowLeft, 
  Calendar, 
  User, 
  Database, 
  Globe, 
  Monitor,
  FileText
} from 'lucide-vue-next';

interface Audit {
  id: number;
  user_name: string | null;
  user_email: string | null;
  auditable_type: string;
  auditable_id: string;
  event: string;
  event_name?: string;
  model_name?: string;
  old_values: Record<string, any> | null;
  new_values: Record<string, any> | null;
  ip_address: string | null;
  user_agent: string | null;
  url: string | null;
  created_at: string;
  metadata: Record<string, any> | null;
  formatted_changes?: Record<string, { old: any; new: any }>;
  user: {
    id: string;
    name: string;
    email: string;
  } | null;
}

interface Props {
  audit: Audit;
}

const props = defineProps<Props>();

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

const formatValue = (value: any): string => {
  if (value === null || value === undefined) {
    return 'kosong';
  }
  if (typeof value === 'boolean') {
    return value ? 'ya' : 'tidak';
  }
  if (typeof value === 'string') {
    // Handle photo URLs with ellipsis
    if (value.includes('/storage/') || value.includes('http')) {
      if (value.length > 50) {
        return value.substring(0, 30) + '...' + value.substring(value.length - 15);
      }
    }
    return value;
  }
  if (typeof value === 'object') {
    return JSON.stringify(value, null, 2);
  }
  return String(value);
};

const getFieldDisplayName = (field: string): string => {
  // Indonesian field name translations
  const fieldTranslations: Record<string, string> = {
    'name': 'Nama',
    'email': 'Email',
    'password': 'Kata Sandi',
    'birth_date': 'Tanggal Lahir',
    'birthdate': 'Tanggal Lahir',
    'purchase_date': 'Tanggal Pembelian',
    'entry_date': 'Tanggal Masuk',
    'herd_entry_date': 'Tanggal Masuk Kandang',
    'sex': 'Jenis Kelamin',
    'weight': 'Berat',
    'birth_weight': 'Berat Lahir',
    'breed_id': 'Ras',
    'herd_id': 'Kandang',
    'farm_id': 'Peternakan',
    'status': 'Status',
    'origin': 'Asal',
    'tag_type': 'Jenis Tag',
    'tag_id': 'ID Tag',
    'purchase_price': 'Harga Beli',
    'purchase_from': 'Dibeli Dari',
    'grant_from': 'Hibah Dari',
    'grant_date': 'Tanggal Hibah',
    'borrowed_from': 'Pinjam Dari',
    'borrowed_date': 'Tanggal Pinjam',
    'barter_from': 'Tukar Dari',
    'barter_date': 'Tanggal Tukar',
    'photo': 'Foto',
    'description': 'Deskripsi',
    'capacity': 'Kapasitas',
    'type': 'Jenis',
    'created_at': 'Dibuat Pada',
    'updated_at': 'Diperbarui Pada',
    'price': 'Harga',
    'quantity': 'Jumlah',
    'unit': 'Satuan',
    'protein': 'Protein',
    'energy': 'Energi',
    'fiber': 'Serat',
    'fat': 'Lemak',
    'calcium': 'Kalsium',
    'phosphorus': 'Fosfor',
    'current_farm_id': 'Peternakan Aktif',
    'is_super_user': 'Super User',
    'email_verified_at': 'Email Terverifikasi',
  };

  return fieldTranslations[field] || field
    .replace(/_/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase());
};

const shouldTruncate = (value: any): boolean => {
  if (typeof value === 'string') {
    return value.length > 50 || value.includes('/storage/') || value.includes('http');
  }
  return false;
};

const getTruncatedValue = (value: any): string => {
  if (typeof value === 'string' && value.length > 50) {
    if (value.includes('/storage/') || value.includes('http')) {
      return value.substring(0, 30) + '...' + value.substring(value.length - 15);
    }
    return value.substring(0, 47) + '...';
  }
  return formatValue(value);
};
</script>

<template>
  <Head :title="`Catatan Audit #${audit.id}`" />

  <TooltipProvider>
    <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link :href="route('audits.index')">
            <Button variant="outline" size="icon">
              <ArrowLeft class="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 class="text-2xl font-bold tracking-tight">
              Catatan Audit #{{ audit.id }}
            </h1>
            <p class="text-muted-foreground">
              {{ audit.model_name }} {{ audit.event_name?.toLowerCase() || audit.event }} 
              pada {{ formatDate(audit.created_at) }}
            </p>
          </div>
        </div>
        <Badge :variant="getEventBadgeVariant(audit.event)" class="text-sm">
          {{ audit.event_name || audit.event }}
        </Badge>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Database class="h-5 w-5" />
                <span>Informasi Model</span>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-muted-foreground">Jenis Model</Label>
                  <p class="text-sm font-mono">{{ audit.model_name || 'Model Tidak Diketahui' }}</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-muted-foreground">ID Model</Label>
                  <p class="text-sm font-mono">{{ audit.auditable_id }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Changes -->
          <Card v-if="audit.formatted_changes && Object.keys(audit.formatted_changes).length > 0">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <FileText class="h-5 w-5" />
                <span>Perubahan</span>
              </CardTitle>
              <CardDescription>
                Field yang diubah dalam {{ audit.event_name?.toLowerCase() || audit.event }} ini
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <div 
                  v-for="(change, field) in (audit.formatted_changes || {})" 
                  :key="field"
                  class="border rounded-lg p-3"
                >
                  <h4 class="font-medium text-sm mb-2">{{ getFieldDisplayName(field) }}</h4>
                  
                  <div class="flex items-center text-xs space-x-2 flex-wrap">
                    <span class="text-muted-foreground">Nilai Lama:</span>
                    <Tooltip v-if="shouldTruncate(change.old)">
                      <TooltipTrigger as-child>
                        <code class="px-2 py-1 bg-red-50 text-red-800 rounded text-xs max-w-xs truncate cursor-help">{{ getTruncatedValue(change.old) }}</code>
                      </TooltipTrigger>
                      <TooltipContent class="max-w-md break-all">
                        <p>{{ formatValue(change.old) }}</p>
                      </TooltipContent>
                    </Tooltip>
                    <code v-else class="px-2 py-1 bg-red-50 text-red-800 rounded text-xs">{{ formatValue(change.old) }}</code>
                    
                    <span class="text-muted-foreground">→</span>
                    
                    <span class="text-muted-foreground">Nilai Baru:</span>
                    <Tooltip v-if="shouldTruncate(change.new)">
                      <TooltipTrigger as-child>
                        <code class="px-2 py-1 bg-green-50 text-green-800 rounded text-xs max-w-xs truncate cursor-help">{{ getTruncatedValue(change.new) }}</code>
                      </TooltipTrigger>
                      <TooltipContent class="max-w-md break-all">
                        <p>{{ formatValue(change.new) }}</p>
                      </TooltipContent>
                    </Tooltip>
                    <code v-else class="px-2 py-1 bg-green-50 text-green-800 rounded text-xs">{{ formatValue(change.new) }}</code>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Full Data for Create/Delete Events -->
          <Card v-if="audit.event === 'created' && audit.new_values">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <FileText class="h-5 w-5" />
                <span>Data yang Dibuat</span>
              </CardTitle>
              <CardDescription>
                Semua data yang ditetapkan saat record ini dibuat
              </CardDescription>
            </CardHeader>
            <CardContent>
              <pre class="text-sm bg-muted p-4 rounded-lg overflow-auto">{{ JSON.stringify(audit.new_values, null, 2) }}</pre>
            </CardContent>
          </Card>

          <Card v-if="audit.event === 'deleted' && audit.old_values">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <FileText class="h-5 w-5" />
                <span>Deleted Data</span>
              </CardTitle>
              <CardDescription>
                All data that was present when this record was deleted
              </CardDescription>
            </CardHeader>
            <CardContent>
              <pre class="text-sm bg-muted p-4 rounded-lg overflow-auto">{{ JSON.stringify(audit.old_values, null, 2) }}</pre>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- User Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <User class="h-5 w-5" />
                <span>Pengguna</span>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Nama</Label>
                <p class="text-sm">{{ audit.user_name || 'Sistem' }}</p>
              </div>
              <div v-if="audit.user_email">
                <Label class="text-sm font-medium text-muted-foreground">Email</Label>
                <p class="text-sm">{{ audit.user_email }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Timestamp -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Calendar class="h-5 w-5" />
                <span>Waktu</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm">{{ formatDate(audit.created_at) }}</p>
            </CardContent>
          </Card>

          <!-- Request Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Globe class="h-5 w-5" />
                <span>Info Request</span>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div v-if="audit.ip_address">
                <Label class="text-sm font-medium text-muted-foreground">Alamat IP</Label>
                <p class="text-sm font-mono">{{ audit.ip_address }}</p>
              </div>
              <div v-if="audit.url">
                <Label class="text-sm font-medium text-muted-foreground">URL</Label>
                <p class="text-sm break-all">{{ audit.url }}</p>
              </div>
              <div v-if="audit.user_agent">
                <Label class="text-sm font-medium text-muted-foreground">User Agent</Label>
                <p class="text-xs text-muted-foreground break-all">{{ audit.user_agent }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Metadata -->
          <Card v-if="audit.metadata">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Monitor class="h-5 w-5" />
                <span>Metadata</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <pre class="text-xs bg-muted p-3 rounded overflow-auto">{{ JSON.stringify(audit.metadata, null, 2) }}</pre>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
    </AppLayout>
  </TooltipProvider>
</template>