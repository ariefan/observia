<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, Search, Plus } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import FarmMenuContent from '@/components/FarmMenuContent.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItemType, NavItem } from '@/types';
import type { Auth } from '@/types';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun, SunMoon, Building2, Bell } from 'lucide-vue-next';
import type { SharedData, User } from '@/types';

const props = defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
    description?: string;
}>();

const breadcrumbs = props.breadcrumbs ?? [];
const description = props.description ?? '';
const page = usePage<SharedData>();
const auth = computed<Auth>(() => page.props.auth as Auth);
const user = page.props.auth.user as User;

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
type Appearance = 'light' | 'dark' | 'system';
const { appearance, updateAppearance } = useAppearance();

const getNextAppearance = (current: Appearance): Appearance => {
    switch (current) {
        case 'dark': return 'system';
        case 'system': return 'light';
        default: return 'dark';
    }
};

const nextAppearance = computed(() => getNextAppearance(appearance.value));
const appearanceIcon = computed(() => {
    switch (appearance.value) {
        case 'dark': return Monitor;
        case 'system': return Sun;
        default: return Moon;
    }
});

function updateCurrentAppearance(newAppearance?: Appearance) {
    updateAppearance(newAppearance ?? nextAppearance.value);
}

const appearanceMenu = ref();
const appearanceItems = ref([
    { label: 'System', value: 'system', icon: Monitor },
    { label: 'Light', value: 'light', icon: Sun },
    { label: 'Dark', value: 'dark', icon: Moon },
]);
const toggleAppearance = (event: Event) => {
    appearanceMenu.value.toggle(event);
};

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

                <DropdownMenu v-if="$page.props.auth.farms && $page.props.auth.farms.length > 0">
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="outline" size="sm"
                            class="relative w-auto rounded-full focus-within:ring-2 focus-within:ring-primary">
                            <Building2 class="size-4" />
                            {{ $page.props.auth.user.current_farm?.name || 'Pilih Peternakan' }}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-80 p-0 bg-teal-950 text-white rounded-xl">
                        <FarmMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>

                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button @click="toggleAppearance" variant="ghost" size="icon" as-child
                                class="group h-9 w-9 cursor-pointer">
                                <span rel="noopener noreferrer">
                                    <span class="sr-only">{{ nextAppearance }}</span>
                                    <component :is="SunMoon" class="size-5 opacity-80 group-hover:opacity-100" />
                                </span>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Tampilan</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <Menu ref="appearanceMenu" id="overlay_menu" :model="appearanceItems" :popup="true">
                    <template #item="{ item, props }">
                        <a v-ripple class="flex items-center" v-bind="props.action"
                            @click="updateCurrentAppearance(item.value)">
                            <component :is="item.icon" class="size-4 opacity-80 group-hover:opacity-100" />
                            <span class="text-sm ms-2">{{ item.label }}</span>
                            <span class="ml-auto">{{ appearance === item.value ? '✓' : '' }}</span>
                        </a>
                    </template>
                </Menu>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer relative">
                            <Bell class="size-5 opacity-80 group-hover:opacity-100" />
                            <span class="absolute top-1 right-1 inline-block w-2 h-2 bg-red-500 rounded-full"></span>
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
                </DropdownMenu>

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
