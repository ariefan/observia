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
  ArrowLeft, 
  Calendar, 
  User, 
  Globe, 
  Monitor,
  Bug,
  FileText,
  AlertTriangle
} from 'lucide-vue-next';

interface ErrorLog {
  id: number;
  level: string;
  level_name: string;
  message: string;
  context: Record<string, any> | null;
  file: string | null;
  line: number | null;
  stack_trace: string | null;
  url: string | null;
  ip_address: string | null;
  user_agent: string | null;
  created_at: string;
  user: {
    id: string;
    name: string;
    email: string;
  } | null;
  farm: {
    id: string;
    name: string;
    address?: string;
  } | null;
}

interface Props {
  errorLog: ErrorLog;
}

const props = defineProps<Props>();

const getLevelBadgeVariant = (level: string) => {
  switch (level) {
    case 'emergency':
    case 'alert':
    case 'critical':
    case 'error':
      return 'destructive';
    case 'warning':
      return 'outline';
    case 'notice':
    case 'info':
      return 'secondary';
    case 'debug':
      return 'default';
    default:
      return 'secondary';
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleString();
};
</script>

<template>
  <Head :title="`Riwayat Error #${errorLog.id}`" />

  <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link :href="route('error-logs.index')">
            <Button variant="outline" size="icon">
              <ArrowLeft class="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 class="text-2xl font-bold tracking-tight">
              Riwayat Error #{{ errorLog.id }}
            </h1>
            <p class="text-muted-foreground">
              {{ errorLog.level_name }} 
              pada {{ formatDate(errorLog.created_at) }}
            </p>
          </div>
        </div>
        <Badge :variant="getLevelBadgeVariant(errorLog.level)" class="text-sm">
          {{ errorLog.level_name }}
        </Badge>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Error Message -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Bug class="h-5 w-5" />
                <span>Pesan Error</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="bg-muted p-4 rounded-lg">
                <p class="text-sm font-mono whitespace-pre-wrap">{{ errorLog.message }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- File Information -->
          <Card v-if="errorLog.file">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <FileText class="h-5 w-5" />
                <span>Lokasi Error</span>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 gap-4">
                <div>
                  <Label class="text-sm font-medium text-muted-foreground">File</Label>
                  <p class="text-sm font-mono break-all">{{ errorLog.file }}</p>
                </div>
                <div v-if="errorLog.line">
                  <Label class="text-sm font-medium text-muted-foreground">Baris</Label>
                  <p class="text-sm font-mono">{{ errorLog.line }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Stack Trace -->
          <Card v-if="errorLog.stack_trace">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <AlertTriangle class="h-5 w-5" />
                <span>Stack Trace</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <pre class="text-xs bg-muted p-3 rounded overflow-auto whitespace-pre-wrap">{{ errorLog.stack_trace }}</pre>
            </CardContent>
          </Card>

          <!-- Context -->
          <Card v-if="errorLog.context">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Monitor class="h-5 w-5" />
                <span>Context Data</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <pre class="text-xs bg-muted p-3 rounded overflow-auto">{{ JSON.stringify(errorLog.context, null, 2) }}</pre>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Error Level -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Bug class="h-5 w-5" />
                <span>Level Error</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <Badge :variant="getLevelBadgeVariant(errorLog.level)" class="text-sm">
                {{ errorLog.level_name }}
              </Badge>
              <p class="text-xs text-muted-foreground mt-2">{{ errorLog.level }}</p>
            </CardContent>
          </Card>

          <!-- User Information -->
          <Card v-if="errorLog.user">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <User class="h-5 w-5" />
                <span>Pengguna</span>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Nama</Label>
                <p class="text-sm">{{ errorLog.user.name }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Email</Label>
                <p class="text-sm">{{ errorLog.user.email }}</p>
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
              <p class="text-sm">{{ formatDate(errorLog.created_at) }}</p>
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
              <div v-if="errorLog.url">
                <Label class="text-sm font-medium text-muted-foreground">URL</Label>
                <p class="text-sm break-all">{{ errorLog.url }}</p>
              </div>
              <div v-if="errorLog.ip_address">
                <Label class="text-sm font-medium text-muted-foreground">Alamat IP</Label>
                <p class="text-sm font-mono">{{ errorLog.ip_address }}</p>
              </div>
              <div v-if="errorLog.user_agent">
                <Label class="text-sm font-medium text-muted-foreground">User Agent</Label>
                <p class="text-xs text-muted-foreground break-all">{{ errorLog.user_agent }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Farm Information -->
          <Card v-if="errorLog.farm">
            <CardHeader>
              <CardTitle class="flex items-center space-x-2">
                <Monitor class="h-5 w-5" />
                <span>Peternakan</span>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Nama Peternakan</Label>
                <p class="text-sm">{{ errorLog.farm.name }}</p>
              </div>
              <div v-if="errorLog.farm.address">
                <Label class="text-sm font-medium text-muted-foreground">Alamat</Label>
                <p class="text-sm">{{ errorLog.farm.address }}</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>