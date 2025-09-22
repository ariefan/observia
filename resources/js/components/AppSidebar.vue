<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, } from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Home, Folder, BookOpen, Shield, History, LogIn, TrendingUp, Bell, FileText, Settings, Heart, FolderOpen, Layers } from 'lucide-vue-next';
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
        },
        {
            title: 'Manajemen Konten',
            href: '/content-management',
            icon: Layers,
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
        ]
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
