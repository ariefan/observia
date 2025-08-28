<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { NavItem, SharedData } from '@/types';
import { LayoutGrid, Home, Shield, TrendingUp, Heart } from 'lucide-vue-next';
import { IconFileText, IconHorse } from '@tabler/icons-vue';

const page = usePage<SharedData>();

// Same navigation logic as AppSidebar
const mainNavItems: NavItem[] = [
    // Add Super Dashboard for superusers at the top
    ...(page.props.auth.user?.is_super_user
        ? [{
            title: 'Super Dashboard',
            href: '/super-dashboard',
            icon: Shield,
        }]
        : []
    ),
    // Farm-dependent menu items (require current_farm_id)
    ...(page.props.auth.farms && page.props.auth.user.current_farm_id
        ? [{
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
        }]
        : []
    ),
    // Data menu - available for super users without farm context, or regular users with farm context
    ...(page.props.auth.user?.is_super_user || (page.props.auth.farms && page.props.auth.user.current_farm_id)
        ? [{
            title: 'Data',
            href: '/rations',
            icon: IconFileText,
        }]
        : []
    ),
    // Home menu for users without farm context (non-super users)
    ...(!page.props.auth.user?.is_super_user && (!page.props.auth.farms || !page.props.auth.user.current_farm_id)
        ? [{
            title: 'Home',
            href: '/home',
            icon: Home,
        }]
        : []
    ),
];

const isActiveRoute = (href: string) => {
    return page.url === href || page.url.startsWith(href.replace(/\/$/, ''));
};
</script>

<template>
    <!-- Bottom Navigation for Mobile -->
    <div class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 md:hidden">
        <div class="grid grid-cols-4 max-w-md mx-auto">
            <Link 
                v-for="item in mainNavItems.slice(0, 4)" 
                :key="item.title"
                :href="item.href"
                :class="[
                    'flex flex-col items-center justify-center py-2 px-1 text-xs transition-colors',
                    isActiveRoute(item.href)
                        ? 'text-primary bg-primary/10'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                ]"
            >
                <component :is="item.icon" class="h-5 w-5 mb-1" />
                <span class="truncate">{{ item.title }}</span>
            </Link>
        </div>
        
        <!-- If there are more than 4 items, show overflow indicator -->
        <div v-if="mainNavItems.length > 4" class="absolute top-1 right-1">
            <div class="w-2 h-2 bg-primary rounded-full"></div>
        </div>
    </div>
</template>