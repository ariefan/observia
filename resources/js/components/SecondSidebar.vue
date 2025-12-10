<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { History, LogIn, Database, FileText, ClipboardPlus, Package, PackageOpen, TrendingUp, ArrowLeftRight, Settings, Milk, Microscope, BarChart3, Cake, Wallet } from 'lucide-vue-next';
import { IconFileText, IconHorse, IconLock, IconDna, IconBug, IconFileAnalytics, IconStethoscope } from '@tabler/icons-vue';
import type { SharedData } from '@/types';

interface Props {
  currentRoute?: string;
}

defineProps<Props>();

const page = usePage<SharedData>();

const navigationItems = [
  {
    title: 'Laporan',
    href: '/laporan',
    icon: IconFileAnalytics,
  },
  {
    title: 'Pakan',
    route: 'rations.index',
    icon: IconFileText,
  },
  {
    title: 'Catatan Kesehatan',
    route: 'health-records.index',
    icon: ClipboardPlus,
  },
  {
    title: 'Kandang',
    route: 'herds.index',
    icon: IconHorse,
  },
  {
    title: 'Riwayat Login',
    route: 'login-logs.index',
    icon: IconLock,
  },
  {
    title: 'Jejak Audit',
    route: 'audits.index',
    icon: History,
  },
  // Inventory section separator
  {
    separator: true,
    title: 'Inventaris'
  },
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
  // Milk Supply Chain section separator
  {
    separator: true,
    title: 'Rantai Pasok Susu'
  },
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
  {
    title: 'Pembayaran Susu',
    route: 'payments.finance',
    icon: Wallet,
  },
  // Super user only items
  ...(page.props.auth.user?.is_super_user
    ? [
      {
        separator: true,
        title: 'Super User'
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
        title: 'Riwayat Error',
        route: 'error-logs.index',
        icon: IconBug,
      },
      {
        title: 'Pengaturan',
        route: 'admin.settings.index',
        icon: Settings,
      },
    ]
    : []
  ),
];

const isActiveRoute = (routeName: string, currentRoute?: string) => {
  if (currentRoute) {
    return currentRoute === routeName;
  }
  return route().current(routeName || '');
};
</script>

<template>
  <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
    <nav class="space-y-2">
      <template v-for="item in navigationItems" :key="item.route || item.href || item.title">
        <!-- Separator -->
        <div v-if="item.separator" class="pt-2">
          <div class="border-t border-teal-200 dark:border-teal-800 pt-2">
            <h3 class="text-xs font-semibold text-teal-600 dark:text-teal-400 px-4 py-1 uppercase tracking-wider">
              {{ item.title }}
            </h3>
          </div>
        </div>
        <!-- Navigation Link -->
        <Link v-else :href="item.href || route(item.route || '')" :class="[
          'flex items-center gap-2 text-sm font-semibold rounded-full px-4 py-2 transition-colors',
          isActiveRoute(item.route || '', currentRoute)
            ? 'text-white bg-primary'
            : 'hover:bg-primary hover:text-white'
        ]">
        <component :is="item.icon" class="h-4 w-4" />
        {{ item.title }}
        </Link>
      </template>
    </nav>
  </aside>
</template>