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
    Cross,
    Utensils,
} from 'lucide-vue-next';

import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogTrigger, DialogClose } from '@/components/ui/dialog';

defineProps<{
    items: NavItem[];
}>();


const feedDialogButton = ref<HTMLButtonElement | null>(null); const message = ref<string>('')

// Handler for the Edit Profile button
const handleEditProfile = (): void => {
    message.value = 'Edit Profile button was clicked!'
    console.log('Edit Profile clicked')
    // Add your edit profile logic here
}

// Function to programmatically click the Feed Dialog button
const clickFeedDialogButton = (): void => {
    if (feedDialogButton.value) {
        setTimeout(() => {
            feedDialogButton.value?.click()
        }, 200)
    }
}

const page = usePage<SharedData>();

function goToFeed(route: string) {
    setTimeout(() => { router.get(route); }, 200)
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
                            <PawPrint class="mr-2 h-4 w-4" />
                            <span>Ternak</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="clickFeedDialogButton">
                            <Utensils class="mr-2 h-4 w-4" />
                            <span>Pakan</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="router.get('/livestocks/milking')">
                            <CreditCard class="mr-2 h-4 w-4" />
                            <span>Perah</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="router.get('/livestocks/weight')">
                            <Anvil class="mr-2 h-4 w-4" />
                            <span>Bobot ternak</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <Heart class="mr-2 h-4 w-4" />
                            <span>Breeding</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <Cross class="mr-2 h-4 w-4" />
                            <span>Kesehatan</span>
                        </DropdownMenuItem>
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

    <!-- <Dialog :open="showRationDialog" @update:open="showRationDialog = $event"> -->
    <Dialog>
        <DialogTrigger as-child>
            <button ref="feedDialogButton" class="hidden">
                Edit Profile
            </button>
        </DialogTrigger>
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Data pakan apa yg akan Anda input?</DialogTitle>
                <DialogDescription>
                    Pilih yang ingin Anda lakukan untuk mengelola pakan ternak:
                </DialogDescription>
            </DialogHeader>
            <div class="flex gap-6 justify-center mt-4">
                <a href="/herds/feeding"
                    class="flex flex-col items-center p-6 rounded-xl border border-gray-300 shadow-sm hover:shadow-md hover:bg-teal-50 dark:hover:bg-teal-950 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-transform transform hover:scale-105 text-primary">
                    <Utensils class="mb-2 h-8 w-8" />
                    <span class="font-semibold">Pemberian Pakan</span>
                </a>
                <a href="/rations/leftover"
                    class="flex flex-col items-center p-6 rounded-xl border border-gray-300 shadow-sm hover:shadow-md hover:bg-teal-50 dark:hover:bg-teal-950 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-transform transform hover:scale-105 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 13h6m2 7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7h16v13zM9 7V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v3" />
                    </svg>
                    <span class="font-semibold">Catat Sisa Pakan</span>
                </a>
            </div>
        </DialogContent>
    </Dialog>

</template>

<style scoped>
.text-xxs {
    font-size: 0.60rem;
    line-height: 1rem;
}
</style>
