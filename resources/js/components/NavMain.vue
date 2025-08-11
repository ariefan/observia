<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import type { NavItem, SharedData } from '@/types';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    PawPrint,
    Anvil,
    CreditCard,
    Keyboard,
    Plus,
    Settings,
    User,
    Users,
    Heart,
    Wheat,
    Droplets,
    Utensils,
} from 'lucide-vue-next';
import { IconScaleOutline, IconHorse, IconScale, IconMilk } from '@tabler/icons-vue';

import { ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const showFeedDialog = ref(false);

// Function to open the feed dialog
const openFeedDialog = (): void => {
    showFeedDialog.value = true;
}

const page = usePage<SharedData>();

function goToFeed(route: string) {
    showFeedDialog.value = false;
    router.get(route);
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <DropdownMenu v-if="page.props.auth.farms && page.props.auth.user.current_farm_id">
                <DropdownMenuTrigger as-child>
                    <Button size="icon" class="mb-4 rounded-lg">
                        <Plus class="w-4 h-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-56 ml-8">
                    <DropdownMenuGroup>
                        <DropdownMenuItem @click="router.get('/livestocks/create')">
                            <IconHorse class="mr-2 h-4 w-4" />
                            <span>Ternak</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="openFeedDialog">
                            <Wheat class="mr-2 h-4 w-4" />
                            <span>Pakan</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="router.get('/livestocks/milking')">
                            <IconMilk class="mr-2 h-4 w-4" />
                            <span>Perah</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="router.get('/livestocks/weight')">
                            <IconScaleOutline class="mr-2 h-4 w-4" />
                            <span>Bobot ternak</span>
                        </DropdownMenuItem>
                        <!-- <DropdownMenuItem>
                            <Heart class="mr-2 h-4 w-4" />
                            <span>Breeding</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <Cross class="mr-2 h-4 w-4" />
                            <span>Kesehatan</span>
                        </DropdownMenuItem> -->
                    </DropdownMenuGroup>
                </DropdownMenuContent>
            </DropdownMenu>

            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>

    <!-- Custom Dialog -->
    <Teleport to="body">
        <div v-if="showFeedDialog" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showFeedDialog = false"></div>

            <!-- Dialog Content -->
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <!-- Close button -->
                <button @click="showFeedDialog = false"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Data pakan apa yg akan Anda input?
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Pilih yang ingin Anda lakukan untuk mengelola pakan ternak:
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-6 justify-center">
                    <button @click="goToFeed('/herds/feeding')"
                        class="flex flex-col items-center p-6 rounded-xl border border-gray-300 shadow-sm hover:shadow-md hover:bg-teal-50 dark:hover:bg-teal-950 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-transform transform hover:scale-105 text-primary">
                        <Wheat class="mb-2 h-8 w-8" />
                        <span class="font-semibold">Pemberian Pakan</span>
                    </button>
                    <button @click="goToFeed(route('rations.index', { tab: 'feed' }))"
                        class="flex flex-col items-center p-6 rounded-xl border border-gray-300 shadow-sm hover:shadow-md hover:bg-teal-50 dark:hover:bg-teal-950 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-transform transform hover:scale-105 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 13h6m2 7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7h16v13zM9 7V4a2 2 0 0 1-2-2h2a2 2 0 0 1 2 2v3" />
                        </svg>
                        <span class="font-semibold">Catat Sisa Pakan</span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

</template>

<style scoped>
.text-xxs {
    font-size: 0.60rem;
    line-height: 1rem;
}
</style>
