<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, } from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Home, Folder, BookOpen, Shield, History, LogIn, TrendingUp } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useSidebar } from '@/components/ui/sidebar/utils';
import type { SharedData } from '@/types';
import { IconFileText, IconHorse } from '@tabler/icons-vue';

const page = usePage<SharedData>();

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
            title: 'Data',
            href: '/rations',
            icon: IconFileText,
        },
            // {
            //     title: 'Transaksi',
            //     href: '/dashboard',
            //     icon: ArrowLeftRight,
            // },
            // {
            //     title: 'Breeding',
            //     href: '/dashboard',
            //     icon: Heart,
            // },
        ]
        :
        [{
            title: 'Home',
            href: '/home',
            icon: Home,
        }]
    ),
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];

if (useSidebar().state.value.toString() === 'expanded') {
    useSidebar().setOpen(false);
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem class="h-8">
                    <Link :href="route('dashboard')" class="flex items-center gap-x-2 hidden">
                    <SidebarTrigger />
                    </Link>
                    <SidebarMenuButton size="lg" as-child class="hidden">
                        <Link :href="route('dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="hidden">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
