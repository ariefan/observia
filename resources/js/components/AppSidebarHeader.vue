<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Search, Bell, Settings, Plus, Trash2, Clock, AlertCircle } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import FarmMenuContent from '@/components/FarmMenuContent.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItemType, NavItem } from '@/types';
import type { Auth } from '@/types';
import { useAppearance } from '@/composables/useAppearance';
import { useNotifications } from '@/composables/useNotifications';
import { Sun, Moon, Building2 } from 'lucide-vue-next';
import type { SharedData } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
    description?: string;
}>();

const breadcrumbs = props.breadcrumbs ?? [];
const description = props.description ?? '';
const page = usePage<SharedData>();
const auth = computed<Auth>(() => page.props.auth as Auth);
const isSuperUser = computed(() => auth.value.user?.role === 'super_admin' || auth.value.user?.role === 'admin');

// Notification system
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

const rightNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits',
    //     icon: BookOpen,
    // },
];


// Appearance
type Appearance = 'light' | 'dark';
const { appearance, updateAppearance } = useAppearance();

function toggleAppearanceMode() {
    const nextMode: Appearance = appearance.value === 'light' ? 'dark' : 'light';
    updateAppearance(nextMode);
}

const getThemeIcon = computed(() => {
    return appearance.value === 'light' ? Sun : Moon;
});

const getThemeLabel = computed(() => {
    return appearance.value === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
});

// Notification management dialogs
const showNotificationDialog = ref(false);
const showCreateTemplateDialog = ref(false);
const showManageTemplatesDialog = ref(false);

// Notification creation form
const notificationForm = ref({
  title: '',
  description: '',
  type: 'info' as 'info' | 'success' | 'warning' | 'error' | 'reminder',
  priority: 'medium' as 'low' | 'medium' | 'high' | 'urgent',
});

// Template creation form
const templateForm = ref({
  name: '',
  title: '',
  description: '',
  type: 'info' as 'info' | 'success' | 'warning' | 'error' | 'reminder',
  priority: 'medium' as 'low' | 'medium' | 'high' | 'urgent',
  isActive: true,
});

// Functions
const createTestNotification = () => {
  if (!notificationForm.value.title || !notificationForm.value.description) return;
  
  addNotification({
    title: notificationForm.value.title,
    description: notificationForm.value.description,
    type: notificationForm.value.type,
    priority: notificationForm.value.priority,
  });
  
  // Reset form
  notificationForm.value = {
    title: '',
    description: '',
    type: 'info',
    priority: 'medium',
  };
  
  showNotificationDialog.value = false;
};

const createTemplate = () => {
  if (!templateForm.value.name || !templateForm.value.title) return;
  
  addTemplate({
    name: templateForm.value.name,
    title: templateForm.value.title,
    description: templateForm.value.description,
    type: templateForm.value.type,
    priority: templateForm.value.priority,
    isActive: templateForm.value.isActive,
  });
  
  // Reset form
  templateForm.value = {
    name: '',
    title: '',
    description: '',
    type: 'info',
    priority: 'medium',
    isActive: true,
  };
  
  showCreateTemplateDialog.value = false;
};

const createFeedingReminderNow = () => {
  createFeedingReminder([], 'Pengingat khusus: Saatnya memberi pakan ternak Anda. Pastikan untuk memberikan pakan berkualitas dan dalam jumlah yang tepat.');
};

const formatNotificationTime = (date: Date) => {
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const minutes = Math.floor(diff / (1000 * 60));
  const hours = Math.floor(diff / (1000 * 60 * 60));
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  
  if (minutes < 1) return 'Baru saja';
  if (minutes < 60) return `${minutes}m yang lalu`;
  if (hours < 24) return `${hours}j yang lalu`;
  return `${days}h yang lalu`;
};

const getNotificationIcon = (type: string) => {
  switch (type) {
    case 'warning':
    case 'error':
      return AlertCircle;
    case 'reminder':
      return Clock;
    default:
      return Bell;
  }
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

<template>
    <header class="border-b border-sidebar-border/80">
        <div class="mx-auto flex h-12 items-center px-4 bg-card rounded-t-lg">

            <Link :href="route('dashboard')" class="flex items-center gap-x-2">
            <AppLogo class="hidden h-6 xl:block" />
            </Link>

            <div class="ml-auto flex items-center space-x-2">
                <div class="relative flex items-center space-x-1">
                    <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer hidden">
                        <Search class="size-5 opacity-80 group-hover:opacity-100" />
                    </Button>

                    <div class="hidden space-x-1 lg:flex">
                        <template v-for="item in rightNavItems" :key="item.title">
                            <TooltipProvider :delay-duration="0">
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button variant="ghost" size="icon" as-child
                                            class="group h-9 w-9 cursor-pointer">
                                            <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                <span class="sr-only">{{ item.title }}</span>
                                                <component :is="item.icon"
                                                    class="size-5 opacity-80 group-hover:opacity-100" />
                                            </a>
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ item.title }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </template>
                    </div>
                </div>

                <DropdownMenu v-if="auth.farms && auth.farms.length > 0">
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="outline" size="sm"
                            class="relative w-auto rounded-full focus-within:ring-2 focus-within:ring-primary">
                            <Building2 class="size-4" />
                            {{ auth.user.current_farm?.name || 'Pilih Peternakan' }}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-80 p-0 bg-teal-950 text-white rounded-xl">
                        <FarmMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>

                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button @click="toggleAppearanceMode" variant="ghost" size="icon"
                                class="group h-9 w-9 cursor-pointer">
                                <span class="sr-only">{{ getThemeLabel }}</span>
                                <component :is="getThemeIcon" class="size-5 opacity-80 group-hover:opacity-100" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{{ getThemeLabel }}</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer relative">
                            <Bell class="size-5 opacity-80 group-hover:opacity-100" />
                            <span v-if="unreadNotifications.length > 0"
                                class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs rounded-full">
                                {{ unreadNotifications.length > 9 ? '9+' : unreadNotifications.length }}
                            </span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-80 p-0">
                        <!-- Header -->
                        <div class="p-4 border-b bg-gray-50 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-sm">Notifikasi</h3>
                                <div class="flex items-center gap-2">
                                    <!-- Super User Controls -->
                                    <div v-if="isSuperUser" class="flex items-center gap-1">
                                        <Button @click="createFeedingReminderNow" variant="ghost" size="sm" class="h-7 px-2">
                                            <Clock class="h-3 w-3 mr-1" />
                                            Pakan
                                        </Button>
                                        <Dialog v-model:open="showNotificationDialog">
                                            <DialogTrigger as-child>
                                                <Button variant="ghost" size="sm" class="h-7 px-2">
                                                    <Plus class="h-3 w-3 mr-1" />
                                                    Test
                                                </Button>
                                            </DialogTrigger>
                                        </Dialog>
                                        <Button @click="showManageTemplatesDialog = true" variant="ghost" size="sm" class="h-7 px-2">
                                            <Settings class="h-3 w-3" />
                                        </Button>
                                    </div>
                                    <Button v-if="unreadNotifications.length > 0" @click="markAllAsRead" variant="ghost" size="sm" class="text-xs">
                                        Tandai semua dibaca
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications List -->
                        <div class="max-h-80 overflow-y-auto">
                            <div v-if="notificationsByPriority.length > 0" class="divide-y">
                                <div v-for="notification in notificationsByPriority.slice(0, 10)" :key="notification.id"
                                    @click="markAsRead(notification.id)"
                                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.isRead }">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <component :is="getNotificationIcon(notification.type)" class="h-4 w-4" :class="getPriorityColor(notification.priority)" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="text-sm font-medium truncate" :class="{ 'font-bold': !notification.isRead }">
                                                    {{ notification.title }}
                                                </h4>
                                                <Badge :variant="getNotificationBadgeVariant(notification.type)" class="text-xs">
                                                    {{ notification.type }}
                                                </Badge>
                                            </div>
                                            <p class="text-xs text-muted-foreground mt-1 line-clamp-2">
                                                {{ notification.description }}
                                            </p>
                                            <div class="flex items-center justify-between mt-2">
                                                <span class="text-xs text-muted-foreground">
                                                    {{ formatNotificationTime(notification.createdAt) }}
                                                </span>
                                                <Button v-if="isSuperUser" @click.stop="removeNotification(notification.id)" variant="ghost" size="sm" class="h-6 w-6 p-0 opacity-0 group-hover:opacity-100">
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-8 text-center text-muted-foreground">
                                <Bell class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">Tidak ada notifikasi</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-2 border-t bg-gray-50 dark:bg-gray-900 flex gap-2">
                            <Button v-if="notifications.length > 0" @click="clearAllNotifications" variant="ghost" size="sm" class="flex-1 text-xs">
                                Hapus Semua
                            </Button>
                            <Button @click="router.get(route('notification.page'))" variant="ghost" size="sm" class="flex-1 text-xs">
                                Lihat Semua
                            </Button>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="ghost" size="icon"
                            class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary">
                            <Avatar class="size-8 overflow-hidden rounded-full">
                                <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                <AvatarFallback class="rounded-lg font-semibold text-black  dark:text-white">
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
    <header
        class="max-h-16 items-center gap-2 border-sidebar-border/70 px-6 transition-[width,height] group-has-[[data-collapsible=icon]]/sidebar-wrapper:max-h-16 ease-linear md:px-4">
        <div class="flex flex-col mt-2">
            <!-- <SidebarTrigger class="-ml-1" /> -->
            <template v-if="breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
            <template v-if="description?.length > 0">
                <p class="text-sm">{{ description }}</p>
            </template>
        </div>
    </header>

    <!-- Create Test Notification Dialog -->
    <DialogContent v-if="showNotificationDialog" class="max-w-md">
        <DialogHeader>
            <DialogTitle>Buat Notifikasi Test</DialogTitle>
            <DialogDescription>
                Buat notifikasi untuk menguji sistem (hanya untuk super user).
            </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4">
            <div>
                <Label for="notification-title">Judul</Label>
                <Input id="notification-title" v-model="notificationForm.title" placeholder="Masukkan judul notifikasi" />
            </div>
            
            <div>
                <Label for="notification-description">Deskripsi</Label>
                <Textarea id="notification-description" v-model="notificationForm.description" 
                    placeholder="Masukkan deskripsi notifikasi" rows="3" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <Label>Tipe</Label>
                    <Select v-model="notificationForm.type">
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
                    <Select v-model="notificationForm.priority">
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
            <Button @click="showNotificationDialog = false" variant="outline">Batal</Button>
            <Button @click="createTestNotification">Buat Notifikasi</Button>
        </DialogFooter>
    </DialogContent>

    <!-- Manage Templates Dialog -->
    <Dialog v-model:open="showManageTemplatesDialog">
        <DialogContent class="max-w-2xl max-h-[80vh]">
            <DialogHeader>
                <DialogTitle>Kelola Template Notifikasi</DialogTitle>
                <DialogDescription>
                    Kelola template notifikasi untuk sistem pengingat otomatis.
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-medium">Template yang Tersedia</h4>
                    <Dialog v-model:open="showCreateTemplateDialog">
                        <DialogTrigger as-child>
                            <Button size="sm">
                                <Plus class="h-4 w-4 mr-1" />
                                Tambah Template
                            </Button>
                        </DialogTrigger>
                    </Dialog>
                </div>
                
                <div class="max-h-60 overflow-y-auto">
                    <div v-if="templates.length > 0" class="space-y-2">
                        <div v-for="template in templates" :key="template.id" 
                            class="p-3 border rounded-lg space-y-2">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h5 class="font-medium text-sm">{{ template.name }}</h5>
                                    <p class="text-xs text-muted-foreground">{{ template.title }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ template.description }}</p>
                                </div>
                                <div class="flex items-center gap-2 ml-2">
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
                                    {{ template.isActive ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <p class="text-sm">Belum ada template</p>
                    </div>
                </div>
            </div>
            
            <DialogFooter>
                <Button @click="showManageTemplatesDialog = false" variant="outline">Tutup</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Create Template Dialog -->
    <Dialog v-model:open="showCreateTemplateDialog">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Buat Template Baru</DialogTitle>
                <DialogDescription>
                    Buat template untuk notifikasi yang dapat digunakan kembali.
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-4">
                <div>
                    <Label for="template-name">Nama Template</Label>
                    <Input id="template-name" v-model="templateForm.name" placeholder="Masukkan nama template" />
                </div>
                
                <div>
                    <Label for="template-title">Judul Notifikasi</Label>
                    <Input id="template-title" v-model="templateForm.title" placeholder="Masukkan judul" />
                </div>
                
                <div>
                    <Label for="template-description">Deskripsi</Label>
                    <Textarea id="template-description" v-model="templateForm.description" 
                        placeholder="Masukkan deskripsi" rows="3" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Tipe</Label>
                        <Select v-model="templateForm.type">
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
                        <Select v-model="templateForm.priority">
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
                
                <div class="flex items-center space-x-2">
                    <Button 
                        @click="templateForm.isActive = !templateForm.isActive"
                        :variant="templateForm.isActive ? 'default' : 'outline'" 
                        size="sm" 
                        class="h-6 px-2 text-xs">
                        {{ templateForm.isActive ? 'ON' : 'OFF' }}
                    </Button>
                    <Label>Template aktif</Label>
                </div>
            </div>
            
            <DialogFooter>
                <Button @click="showCreateTemplateDialog = false" variant="outline">Batal</Button>
                <Button @click="createTemplate">Buat Template</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
