<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { NavItem, SharedData } from '@/types';
import { LayoutGrid, Home, Shield, TrendingUp, Heart } from 'lucide-vue-next';
import { IconFileText, IconHorse } from '@tabler/icons-vue';
import { computed } from 'vue';

const page = usePage<SharedData>();

// Same navigation logic as AppSidebar
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (page.props.auth.user?.is_super_user) {
        items.push({
            title: 'Super Dashboard',
            href: '/super-dashboard',
            icon: Shield,
        });
    }

    if (page.props.auth.farms && page.props.auth.user?.current_farm_id) {
        items.push(
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Populasi',
                href: '/livestocks',
                icon: IconHorse,
            },
            {
                title: 'Produktivitas',
                href: '/productivity',
                icon: TrendingUp,
            },
            {
                title: 'Kesehatan',
                href: '/health-records',
                icon: Heart,
            },
        );
    }

    if (
        page.props.auth.user?.is_super_user ||
        (page.props.auth.farms && page.props.auth.user?.current_farm_id)
    ) {
        items.push({
            title: 'Data',
            href: '/rations',
            icon: IconFileText,
        });
    }

    if (
        !page.props.auth.user?.is_super_user &&
        (!page.props.auth.farms || !page.props.auth.user?.current_farm_id)
    ) {
        items.push({
            title: 'Home',
            href: '/home',
            icon: Home,
        });
    }

    return items;
});

const hasOverflowItems = computed(() => mainNavItems.value.length > 5);

const isActiveRoute = (href: string) => {
    return page.url === href || page.url.startsWith(href.replace(/\/$/, ''));
};
</script>

<template>
    <!-- Bottom Navigation for Mobile -->
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-900/95 md:hidden pb-[env(safe-area-inset-bottom)]">
        <div class="mx-auto w-full max-w-xl px-2">
            <div
                :class="[
                    'flex items-stretch gap-1',
                    hasOverflowItems ? 'overflow-x-auto pb-1 -mx-2 px-2 snap-x snap-mandatory' : 'justify-between'
                ]"
            >
                <Link
                    v-for="item in mainNavItems"
                    :key="item.title"
                    :href="item.href"
                    :class="[
                        'flex flex-col items-center justify-center gap-1 rounded-lg px-2 py-2 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent',
                        isActiveRoute(item.href)
                            ? 'text-primary bg-primary/10'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                        hasOverflowItems ? 'flex-none basis-20 snap-center' : 'flex-1'
                    ]"
                    :aria-current="isActiveRoute(item.href) ? 'page' : undefined"
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    <span class="truncate text-[11px] font-medium leading-tight">
                        {{ item.title }}
                    </span>
                </Link>
            </div>
        </div>
    </nav>
</template>
