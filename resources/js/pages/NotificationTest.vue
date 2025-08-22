<template>
  <Head :title="isSuper ? 'Uji Sistem Notifikasi' : 'Notifikasi'" />

  <AppLayout>
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ isSuper ? 'Uji Sistem Notifikasi' : 'Notifikasi' }}</h1>
    
    <div class="space-y-6">
      <!-- Test Buttons - Only show for super admin -->
      <div v-if="isSuper" class="space-y-4">
        <h2 class="text-lg font-semibold">Uji Notifikasi</h2>
        <div class="flex flex-wrap gap-2">
          <Button @click="createTestNotification('info')" variant="outline">
            Buat Info
          </Button>
          <Button @click="createTestNotification('success')" variant="outline">
            Buat Sukses
          </Button>
          <Button @click="createTestNotification('warning')" variant="outline">
            Buat Peringatan
          </Button>
          <Button @click="createTestNotification('error')" variant="outline">
            Buat Error
          </Button>
          <Button @click="createTestNotification('reminder')" variant="outline">
            Buat Pengingat
          </Button>
          <Button @click="createFeedingReminder()" variant="default">
            Buat Pengingat Pakan
          </Button>
        </div>
      </div>

      <!-- Current Notifications -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Notifikasi Saat Ini ({{ notifications.length }})</h2>
          <div class="flex gap-2">
            <Button @click="markAllAsRead" size="sm" variant="outline">
              Tandai Sudah Dibaca
            </Button>
            <Button @click="clearAllNotifications" size="sm" variant="destructive">
              Hapus Semua
            </Button>
          </div>
        </div>
        
        <div v-if="notifications.length > 0" class="space-y-2 border rounded-lg p-4 max-h-80 overflow-y-auto">
          <div v-for="notification in notificationsByPriority" :key="notification.id" 
            class="p-3 border rounded-lg" 
            :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.isRead }">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-medium text-sm" :class="{ 'font-bold': !notification.isRead }">
                    {{ notification.title }}
                  </h3>
                  <Badge :variant="getNotificationBadgeVariant(notification.type)" class="text-xs">
                    {{ notification.type }}
                  </Badge>
                  <Badge variant="outline" class="text-xs" :class="getPriorityColor(notification.priority)">
                    {{ notification.priority }}
                  </Badge>
                </div>
                <p class="text-sm text-muted-foreground">{{ notification.message }}</p>
                <p class="text-xs text-muted-foreground mt-2">
                  {{ formatNotificationTime(new Date(notification.created_at)) }}
                </p>
              </div>
              <div class="flex gap-1">
                <Button @click="markAsRead(notification.id)" size="sm" variant="ghost" v-if="!notification.isRead">
                  Tandai Dibaca
                </Button>
                <Button @click="removeNotification(notification.id)" size="sm" variant="ghost">
                  <Trash2 class="h-3 w-3" />
                </Button>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-8 text-muted-foreground border rounded-lg">
          <Bell class="h-8 w-8 mx-auto mb-2 opacity-50" />
          <p>Tidak ada notifikasi</p>
        </div>
      </div>

      <!-- Templates Management - Only show for super admin -->
      <div v-if="isSuper" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Template Notifikasi ({{ templates.length }})</h2>
          <Button @click="showCreateTemplate = true" size="sm">
            <Plus class="h-3 w-3 mr-1" />
            Tambah Template
          </Button>
        </div>
        
        <div v-if="templates.length > 0" class="space-y-2 border rounded-lg p-4">
          <div v-for="template in templates" :key="template.id" 
            class="p-3 border rounded-lg space-y-2">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="font-medium text-sm">{{ template.name }}</h3>
                <p class="text-xs text-muted-foreground">{{ template.title }}</p>
                <p class="text-xs text-muted-foreground mt-1">{{ template.description }}</p>
              </div>
              <div class="flex items-center gap-2">
                <Button 
                  @click="updateTemplate(template.id, { isActive: !template.isActive })"
                  :variant="template.isActive ? 'default' : 'outline'" 
                  size="sm" 
                  class="h-6 px-2 text-xs">
                  {{ template.isActive ? 'ON' : 'OFF' }}
                </Button>
                <Button @click="removeTemplate(template.id)" variant="ghost" size="sm" class="h-6 w-6 p-0">
                  <Trash2 class="h-3 w-3 text-red-500" />
                </Button>
              </div>
            </div>
            <div class="flex items-center gap-2 text-xs">
              <Badge :variant="getNotificationBadgeVariant(template.type)">{{ template.type }}</Badge>
              <Badge variant="outline">{{ template.priority }}</Badge>
              <Badge :variant="template.isActive ? 'default' : 'secondary'">
                {{ template.isActive ? 'Aktif' : 'Tidak Aktif' }}
              </Badge>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Template Dialog - Only for super admin -->
    <Dialog v-if="isSuper" v-model:open="showCreateTemplate">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Buat Template Baru</DialogTitle>
          <DialogDescription>
            Buat template notifikasi yang dapat digunakan kembali.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4">
          <div>
            <Label for="template-name">Nama Template</Label>
            <Input id="template-name" v-model="newTemplate.name" placeholder="Masukkan nama template" />
          </div>
          
          <div>
            <Label for="template-title">Judul Notifikasi</Label>
            <Input id="template-title" v-model="newTemplate.title" placeholder="Masukkan judul" />
          </div>
          
          <div>
            <Label for="template-description">Deskripsi</Label>
            <Textarea id="template-description" v-model="newTemplate.description" 
              placeholder="Masukkan deskripsi" rows="3" />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Tipe</Label>
              <Select v-model="newTemplate.type">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih tipe" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="info">Info</SelectItem>
                  <SelectItem value="success">Sukses</SelectItem>
                  <SelectItem value="warning">Peringatan</SelectItem>
                  <SelectItem value="error">Error</SelectItem>
                  <SelectItem value="reminder">Pengingat</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div>
              <Label>Prioritas</Label>
              <Select v-model="newTemplate.priority">
                <SelectTrigger>
                  <SelectValue placeholder="Pilih prioritas" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="low">Rendah</SelectItem>
                  <SelectItem value="medium">Sedang</SelectItem>
                  <SelectItem value="high">Tinggi</SelectItem>
                  <SelectItem value="urgent">Mendesak</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
        
        <DialogFooter>
          <Button @click="showCreateTemplate = false" variant="outline">Batal</Button>
          <Button @click="createNewTemplate">Buat Template</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Bell, Plus, Trash2 } from 'lucide-vue-next';
import { useNotifications } from '@/composables/useNotifications';

// Get user information from Inertia page props
const page = usePage();
const user = computed(() => page.props.auth?.user);
const isSuper = computed(() => user.value?.is_super_user === true);

// Use notification system
const {
  notifications,
  unreadNotifications,
  notificationsByPriority,
  templates,
  addNotification,
  markAsRead,
  markAllAsRead,
  removeNotification,
  clearAllNotifications,
  addTemplate,
  updateTemplate,
  removeTemplate,
  createFeedingReminder,
} = useNotifications();

// Dialog state
const showCreateTemplate = ref(false);

// New template form
const newTemplate = ref({
  name: '',
  title: '',
  description: '',
  type: 'info' as 'info' | 'success' | 'warning' | 'error' | 'reminder',
  priority: 'medium' as 'low' | 'medium' | 'high' | 'urgent',
  isActive: true,
});

// Functions
const createTestNotification = (type: 'info' | 'success' | 'warning' | 'error' | 'reminder') => {
  const messages = {
    info: {
      title: 'Informasi',
      description: 'Ini adalah pesan informasi untuk tujuan pengujian.',
      priority: 'low' as const,
    },
    success: {
      title: 'Berhasil!',
      description: 'Operasi berhasil diselesaikan.',
      priority: 'medium' as const,
    },
    warning: {
      title: 'Peringatan',
      description: 'Harap perhatikan pesan peringatan ini.',
      priority: 'high' as const,
    },
    error: {
      title: 'Error Terjadi',
      description: 'Terjadi error yang memerlukan perhatian Anda.',
      priority: 'urgent' as const,
    },
    reminder: {
      title: 'Pengingat',
      description: 'Ini adalah pengingat ramah tentang tugas yang akan datang.',
      priority: 'medium' as const,
    },
  };

  const message = messages[type];
  addNotification({
    title: message.title,
    message: message.description,
    type: `test-${type}`,
  });
};

const createNewTemplate = () => {
  if (!newTemplate.value.name || !newTemplate.value.title) return;
  
  addTemplate({
    name: newTemplate.value.name,
    title: newTemplate.value.title,
    description: newTemplate.value.description,
    type: newTemplate.value.type,
    priority: newTemplate.value.priority,
    isActive: newTemplate.value.isActive,
  });
  
  // Reset form
  newTemplate.value = {
    name: '',
    title: '',
    description: '',
    type: 'info',
    priority: 'medium',
    isActive: true,
  };
  
  showCreateTemplate.value = false;
};

const formatNotificationTime = (date: Date) => {
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const minutes = Math.floor(diff / (1000 * 60));
  const hours = Math.floor(diff / (1000 * 60 * 60));
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  
  if (minutes < 1) return 'Baru saja';
  if (minutes < 60) return `${minutes} menit lalu`;
  if (hours < 24) return `${hours} jam lalu`;
  return `${days} hari lalu`;
};

const getNotificationBadgeVariant = (type: string) => {
  switch (type) {
    case 'error':
      return 'destructive';
    case 'warning':
      return 'secondary';
    case 'success':
      return 'default';
    case 'reminder':
      return 'outline';
    default:
      return 'secondary';
  }
};

const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'urgent':
      return 'text-red-600';
    case 'high':
      return 'text-orange-500';
    case 'medium':
      return 'text-blue-500';
    default:
      return 'text-gray-500';
  }
};
</script>