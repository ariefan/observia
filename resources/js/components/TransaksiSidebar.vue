<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CreditCard, FileText, Clock } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

interface Props {
  currentRoute?: string;
  pendingCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
  pendingCount: 0,
});

const navigationItems = [
  {
    title: 'Paket Layanan',
    route: 'transaksi.paket-layanan',
    icon: CreditCard,
    showBadge: false,
  },
  {
    title: 'Tagihan',
    route: 'transaksi.tagihan',
    icon: FileText,
    showBadge: true,
  },
  {
    title: 'Riwayat Pembayaran',
    route: 'transaksi.riwayat-pembayaran',
    icon: Clock,
    showBadge: false,
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
  <aside class="w-56 min-h-screen bg-teal-50 dark:bg-slate-900 p-2 shadow-xl dark:shadow-slate-950/50 -mt-2 border-r border-transparent dark:border-slate-800">
    <nav class="space-y-2">
      <Link
        v-for="item in navigationItems"
        :key="item.route"
        :href="route(item.route)"
        :class="[
          'flex items-center justify-between gap-2 text-sm font-semibold rounded-full px-4 py-2 transition-colors',
          isActiveRoute(item.route, currentRoute)
            ? 'text-white bg-primary dark:bg-teal-600'
            : 'text-slate-700 dark:text-slate-300 hover:bg-primary hover:text-white dark:hover:bg-teal-600'
        ]"
      >
        <div class="flex items-center gap-2">
          <component :is="item.icon" class="h-4 w-4" />
          {{ item.title }}
        </div>
        <Badge
          v-if="item.showBadge && pendingCount > 0"
          variant="destructive"
          class="h-5 min-w-5 flex items-center justify-center text-xs px-1.5"
        >
          {{ pendingCount }}
        </Badge>
      </Link>
    </nav>
  </aside>
</template>
