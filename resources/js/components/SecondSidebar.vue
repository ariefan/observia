<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { History, LogIn, Database, FileText, ClipboardPlus } from 'lucide-vue-next';
import { IconFileText, IconHorse, IconLock, IconDna, IconBug, IconFileAnalytics, IconStethoscope } from '@tabler/icons-vue';
import type { SharedData } from '@/types';

interface Props {
  currentRoute?: string;
}

defineProps<Props>();

const page = usePage<SharedData>();

const navigationItems = [
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
  // Super user only items
  ...(page.props.auth.user?.is_super_user
    ? [
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
        title: 'Laporan',
        href: '/laporan',
        icon: IconFileAnalytics,
      },
      {
        title: 'Riwayat Error',
        route: 'error-logs.index',
        icon: IconBug,
      },
    ]
    : []
  ),
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
      <Link v-for="item in navigationItems" :key="item.route || item.href" :href="item.href || route(item.route || '')"
        :class="[
          'flex items-center gap-2 text-sm font-semibold rounded-full px-4 py-2 transition-colors',
          isActiveRoute(item.route || '', currentRoute)
            ? 'text-white bg-primary'
            : 'hover:bg-primary hover:text-white'
        ]">
      <component :is="item.icon" class="h-4 w-4" />
      {{ item.title }}
      </Link>
    </nav>
  </aside>
</template>