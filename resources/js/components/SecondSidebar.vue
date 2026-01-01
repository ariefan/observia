<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
  History,
  TrendingUp,
  ArrowLeftRight,
  Settings,
  Milk,
  Microscope,
  BarChart3,
  Cake,
  Wallet,
  ChevronDown,
  PackageOpen,
  Video,
  FileText,
  Building2,
  MessageCircle,
  HardDrive,
  CreditCard
} from 'lucide-vue-next';
import { IconFileText, IconHorse, IconLock, IconDna, IconBug, IconFileAnalytics, IconStethoscope } from '@tabler/icons-vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import type { SharedData, Permissions } from '@/types';

interface Props {
  currentRoute?: string;
}

defineProps<Props>();

const page = usePage<SharedData>();

// Get permissions from backend (consistent with useNavigation)
const permissions = computed<Permissions>(() => {
  return page.props.auth.permissions ?? {
    isSuperUser: false,
    canAccessFinance: false,
    canModifyFinance: false,
    canAccessOperations: false,
    canModifyOperations: false,
    canManageMembers: false,
    canAccessSettings: false,
    isViewOnly: true,
  };
});

// Use consistent permission checks from backend
const hasFinanceAccess = computed(() => permissions.value.canAccessFinance);
const canAccessOperations = computed(() => permissions.value.canAccessOperations);
const canAccessSettings = computed(() => permissions.value.canAccessSettings);
const isSuperUser = computed(() => permissions.value.isSuperUser);
const isViewOnly = computed(() => permissions.value.isViewOnly);

// Track which sections are open (default open for commonly used sections)
const openSections = ref({
  coreData: true,
  content: false,
  inventory: false,
  supplyChain: false,
  superUser: false,
});

// Core data navigation items - filtered by permissions
const coreDataItems = computed(() => {
  const items = [
    {
      title: 'Laporan',
      href: '/laporan',
      icon: IconFileAnalytics,
    },
    {
      title: 'Kandang',
      route: 'herds.index',
      icon: IconHorse,
    },
    {
      title: 'Manajemen Peternakan',
      route: 'farms.index',
      icon: Building2,
    },
  ];

  // Operational items - only for users who can access operations (not investors)
  if (canAccessOperations.value) {
    items.splice(1, 0, {
      title: 'Pakan',
      route: 'rations.index',
      icon: IconFileText,
    });
    items.splice(2, 0, {
      title: 'Catatan Kesehatan',
      route: 'health-records.index',
      icon: IconStethoscope,
    });
  }

  // Admin items - login logs and audit trails
  if (canAccessSettings.value || isSuperUser.value) {
    items.push({
      title: 'Riwayat Login',
      route: 'login-logs.index',
      icon: IconLock,
    });
    items.push({
      title: 'Jejak Audit',
      route: 'audits.index',
      icon: History,
    });
  }

  return items;
});

// Content navigation items (accessible to all authenticated users)
const contentItems = [
  {
    title: 'Video Tutorial',
    route: 'videos.index',
    icon: Video,
  },
  {
    title: 'Artikel',
    route: 'articles.index',
    icon: FileText,
  },
];

// Inventory navigation items - only for operational users
const inventoryItems = computed(() => {
  // Investors don't see inventory (view-only)
  if (isViewOnly.value) return [];

  return [
    {
      title: 'Dashboard Inventaris',
      route: 'inventory.dashboard',
      icon: TrendingUp,
    },
    {
      title: 'Stok Barang',
      route: 'inventory.items.index',
      icon: PackageOpen,
    },
    {
      title: 'Transaksi Barang',
      route: 'inventory.transactions.index',
      icon: ArrowLeftRight,
    },
  ];
});

// Milk Supply Chain navigation items - filtered based on role
const supplyChainItems = computed(() => {
  const items = [
    {
      title: 'Dashboard Supply Chain',
      route: 'supply-chain.dashboard',
      icon: TrendingUp,
    },
    {
      title: 'Koleksi Batch Susu',
      route: 'milk-batches.index',
      icon: Milk,
    },
    {
      title: 'Quality Control',
      route: 'quality-control.index',
      icon: Microscope,
    },
    {
      title: 'Riwayat Grading',
      route: 'quality-control.history',
      icon: BarChart3,
    },
    {
      title: 'Produksi Keju',
      route: 'cheese-productions.index',
      icon: Cake,
    },
  ];

  // Finance-related items only for admin/owner/finance
  if (hasFinanceAccess.value) {
    items.push({
      title: 'Pembayaran Susu',
      route: 'payments.finance',
      icon: Wallet,
    });
  } else {
    // Regular farmers see their payment view
    items.push({
      title: 'Pembayaran Saya',
      route: 'payments.farmer',
      icon: CreditCard,
    });
  }

  return items;
});

// Super user only items
const superUserItems = [
  {
    title: 'Manajemen Billing',
    route: 'admin.billing.index',
    icon: Wallet,
    description: 'Kelola paket & langganan',
  },
  {
    title: 'Spesies',
    route: 'species.index',
    icon: IconDna,
  },
  {
    title: 'Ras',
    route: 'breeds.index',
    icon: IconDna,
  },
  {
    title: 'Manajemen Konten',
    route: 'content.management',
    icon: FileText,
  },
  {
    title: 'Pengaturan Telegram',
    href: '/admin/settings',
    icon: MessageCircle,
    description: 'Bot & notifikasi Telegram',
  },
  {
    title: 'Backup Database',
    href: '/admin/settings',
    icon: HardDrive,
    description: 'Backup & restore data',
  },
  {
    title: 'Riwayat Error',
    route: 'error-logs.index',
    icon: IconBug,
  },
  {
    title: 'Pengaturan Sistem',
    route: 'admin.settings.index',
    icon: Settings,
  },
];

const isActiveRoute = (routeName: string, currentRoute?: string) => {
  if (currentRoute) {
    return currentRoute === routeName;
  }
  try {
    return route().current(routeName || '');
  } catch {
    return false;
  }
};

const getItemHref = (item: { href?: string; route?: string }) => {
  if (item.href) return item.href;
  if (item.route) {
    try {
      return route(item.route);
    } catch {
      return '#';
    }
  }
  return '#';
};
</script>

<template>
  <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2 overflow-y-auto max-h-[calc(100vh-4rem)]">
    <nav class="space-y-1">
      <!-- Core Data Section -->
      <Collapsible v-model:open="openSections.coreData" class="group">
        <CollapsibleTrigger class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900 transition-colors">
          <span>Data Inti</span>
          <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openSections.coreData }" />
        </CollapsibleTrigger>
        <CollapsibleContent class="space-y-1 pt-1">
          <Link
            v-for="item in coreDataItems"
            :key="item.route || item.href"
            :href="getItemHref(item)"
            :class="[
              'flex items-center gap-2 text-sm font-medium rounded-full px-4 py-2 transition-colors ml-2',
              isActiveRoute(item.route || '', currentRoute)
                ? 'text-white bg-primary'
                : 'text-teal-600 dark:text-teal-400 hover:bg-primary hover:text-white'
            ]"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.title }}
          </Link>
        </CollapsibleContent>
      </Collapsible>

      <!-- Content Section (Videos & Articles) -->
      <Collapsible v-model:open="openSections.content" class="group">
        <CollapsibleTrigger class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900 transition-colors">
          <span>Konten & Tutorial</span>
          <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openSections.content }" />
        </CollapsibleTrigger>
        <CollapsibleContent class="space-y-1 pt-1">
          <Link
            v-for="item in contentItems"
            :key="item.route"
            :href="getItemHref(item)"
            :class="[
              'flex items-center gap-2 text-sm font-medium rounded-full px-4 py-2 transition-colors ml-2',
              isActiveRoute(item.route || '', currentRoute)
                ? 'text-white bg-primary'
                : 'text-teal-600 dark:text-teal-400 hover:bg-primary hover:text-white'
            ]"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.title }}
          </Link>
        </CollapsibleContent>
      </Collapsible>

      <!-- Inventory Section - Hidden for investors (view-only) -->
      <Collapsible v-if="inventoryItems.length > 0" v-model:open="openSections.inventory" class="group">
        <CollapsibleTrigger class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900 transition-colors">
          <span>Inventaris</span>
          <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openSections.inventory }" />
        </CollapsibleTrigger>
        <CollapsibleContent class="space-y-1 pt-1">
          <Link
            v-for="item in inventoryItems"
            :key="item.route"
            :href="getItemHref(item)"
            :class="[
              'flex items-center gap-2 text-sm font-medium rounded-full px-4 py-2 transition-colors ml-2',
              isActiveRoute(item.route || '', currentRoute)
                ? 'text-white bg-primary'
                : 'text-teal-600 dark:text-teal-400 hover:bg-primary hover:text-white'
            ]"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.title }}
          </Link>
        </CollapsibleContent>
      </Collapsible>

      <!-- Supply Chain Section -->
      <Collapsible v-model:open="openSections.supplyChain" class="group">
        <CollapsibleTrigger class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900 transition-colors">
          <span>Rantai Pasok Susu</span>
          <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openSections.supplyChain }" />
        </CollapsibleTrigger>
        <CollapsibleContent class="space-y-1 pt-1">
          <Link
            v-for="item in supplyChainItems"
            :key="item.route"
            :href="getItemHref(item)"
            :class="[
              'flex items-center gap-2 text-sm font-medium rounded-full px-4 py-2 transition-colors ml-2',
              isActiveRoute(item.route || '', currentRoute)
                ? 'text-white bg-primary'
                : 'text-teal-600 dark:text-teal-400 hover:bg-primary hover:text-white'
            ]"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.title }}
          </Link>
        </CollapsibleContent>
      </Collapsible>

      <!-- Super User Section - Only visible to super users -->
      <Collapsible v-if="isSuperUser" v-model:open="openSections.superUser" class="group">
        <CollapsibleTrigger class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-sm font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900 transition-colors">
          <span>Super User</span>
          <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openSections.superUser }" />
        </CollapsibleTrigger>
        <CollapsibleContent class="space-y-1 pt-1">
          <Link
            v-for="item in superUserItems"
            :key="item.route || item.href"
            :href="getItemHref(item)"
            :class="[
              'flex items-center gap-2 text-sm font-medium rounded-full px-4 py-2 transition-colors ml-2',
              isActiveRoute(item.route || '', currentRoute)
                ? 'text-white bg-amber-600'
                : 'text-amber-600 dark:text-amber-400 hover:bg-amber-600 hover:text-white'
            ]"
          >
            <component :is="item.icon" class="h-4 w-4" />
            {{ item.title }}
          </Link>
        </CollapsibleContent>
      </Collapsible>
    </nav>
  </aside>
</template>
