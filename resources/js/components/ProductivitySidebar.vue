<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Droplets, Weight } from 'lucide-vue-next';

interface Props {
  currentRoute?: string;
}

defineProps<Props>();

const navigationItems = [
  {
    title: 'Susu',
    route: 'productivity.milk',
    icon: Droplets,
  },
  {
    title: 'Bobot',
    route: 'productivity.weight',
    icon: Weight,
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