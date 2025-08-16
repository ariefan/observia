<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import FarmMenuContent from '@/components/FarmMenuContent.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItemType, NavItem } from '@/types';
import type { Auth } from '@/types';
import { useAppearance } from '@/composables/useAppearance';
import { Sun, Moon, Building2 } from 'lucide-vue-next';
import type { SharedData } from '@/types';

const props = defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
    description?: string;
}>();

const breadcrumbs = props.breadcrumbs ?? [];
const description = props.description ?? '';
const page = usePage<SharedData>();
const auth = computed<Auth>(() => page.props.auth as Auth);

const rightNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits',
    //     icon: BookOpen,
    // },
];


// Appearance
type Appearance = 'light' | 'dark';
const { appearance, updateAppearance } = useAppearance();

function toggleAppearanceMode() {
    const nextMode: Appearance = appearance.value === 'light' ? 'dark' : 'light';
    updateAppearance(nextMode);
}

const getThemeIcon = computed(() => {
    return appearance.value === 'light' ? Sun : Moon;
});

const getThemeLabel = computed(() => {
    return appearance.value === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
});

</script>

<template>
    <header class="border-b border-sidebar-border/80">
        <div class="mx-auto flex h-12 items-center px-4 bg-card rounded-t-lg">

            <Link :href="route('dashboard')" class="flex items-center gap-x-2">
            <AppLogo class="hidden h-6 xl:block" />
            </Link>

            <div class="ml-auto flex items-center space-x-2">
                <div class="relative flex items-center space-x-1">
                    <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer hidden">
                        <Search class="size-5 opacity-80 group-hover:opacity-100" />
                    </Button>

                    <div class="hidden space-x-1 lg:flex">
                        <template v-for="item in rightNavItems" :key="item.title">
                            <TooltipProvider :delay-duration="0">
                                <Tooltip>
                                    <TooltipTrigger>
                                        <Button variant="ghost" size="icon" as-child
                                            class="group h-9 w-9 cursor-pointer">
                                            <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                <span class="sr-only">{{ item.title }}</span>
                                                <component :is="item.icon"
                                                    class="size-5 opacity-80 group-hover:opacity-100" />
                                            </a>
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{{ item.title }}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </template>
                    </div>
                </div>

                <DropdownMenu v-if="auth.farms && auth.farms.length > 0">
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="outline" size="sm"
                            class="relative w-auto rounded-full focus-within:ring-2 focus-within:ring-primary">
                            <Building2 class="size-4" />
                            {{ auth.user.current_farm?.name || 'Pilih Peternakan' }}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-80 p-0 bg-teal-950 text-white rounded-xl">
                        <FarmMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>

                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button @click="toggleAppearanceMode" variant="ghost" size="icon"
                                class="group h-9 w-9 cursor-pointer">
                                <span class="sr-only">{{ getThemeLabel }}</span>
                                <component :is="getThemeIcon" class="size-5 opacity-80 group-hover:opacity-100" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{{ getThemeLabel }}</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <!-- <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer relative">
                            <Bell class="size-5 opacity-80 group-hover:opacity-100" />
                            <span
                                class="absolute top-1.5 right-1.5 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-80 p-0">
                        <ul>
                            <li v-for="(notification, idx) in [
                                { id: 1, title: 'New message', desc: 'You have a new message from John', time: '2m ago' },
                                { id: 2, title: 'Farm updated', desc: 'Farm profile was updated', time: '10m ago' },
                                { id: 3, title: 'Reminder', desc: 'Check your livestock health', time: '1h ago' },
                                { id: 4, title: 'Task assigned', desc: 'A new task has been assigned to you', time: '3h ago' },
                                { id: 5, title: 'System alert', desc: 'System maintenance scheduled', time: '1d ago' }
                            ]" :key="notification.id"
                                class="px-4 py-3 bg-gray-50 dark:bg-gray-900 hover:bg-teal-100 dark:hover:bg-teal-900 cursor-pointer">
                                <div class="text-sm font-medium">{{ notification.title }}</div>
                                <div class="text-xs">{{ notification.desc }}</div>
                                <div class="text-xs mt-1">{{ notification.time }}</div>
                            </li>
                        </ul>
                        <div class="p-2 text-center bg-gray-50 dark:bg-gray-900">
                            <Button variant="ghost" size="sm" class="w-full">View all</Button>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu> -->

                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="ghost" size="icon"
                            class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary">
                            <Avatar class="size-8 overflow-hidden rounded-full">
                                <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                <AvatarFallback class="rounded-lg font-semibold text-black  dark:text-white">
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
    <header
        class="max-h-16 items-center gap-2 border-sidebar-border/70 px-6 transition-[width,height] group-has-[[data-collapsible=icon]]/sidebar-wrapper:max-h-16 ease-linear md:px-4">
        <div class="flex flex-col mt-2">
            <!-- <SidebarTrigger class="-ml-1" /> -->
            <template v-if="breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
            <template v-if="description?.length > 0">
                <p class="text-sm">{{ description }}</p>
            </template>
        </div>
    </header>
</template>
