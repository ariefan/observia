<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { History, LogIn } from 'lucide-vue-next';
import { IconFileText, IconHorse, IconLock } from '@tabler/icons-vue';

interface Props {
  currentRoute?: string;
}

defineProps<Props>();

const navigationItems = [
  {
    title: 'Pakan',
    route: 'rations.index',
    icon: IconFileText,
  },
  {
    title: 'Kandang',
    route: 'herds.index',
    icon: IconHorse,
  },
  {
    title: 'Log Masuk',
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
  return route().current(routeName);
};
</script>

<template>
  <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
    <nav class="space-y-2">
      <Link v-for="item in navigationItems" :key="item.route" :href="route(item.route)" :class="[
        'flex items-center gap-2 text-sm font-semibold rounded-full px-4 py-2 transition-colors',
        isActiveRoute(item.route, currentRoute)
          ? 'text-white bg-primary'
          : 'hover:bg-primary hover:text-white'
      ]">
      <component :is="item.icon" class="h-4 w-4" />
      {{ item.title }}
      </Link>
    </nav>
  </aside>
</template>