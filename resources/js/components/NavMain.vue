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

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <DropdownMenu>
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
                        <DropdownMenuItem>
                            <Utensils class="mr-2 h-4 w-4" />
                            <span>Pakan</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <CreditCard class="mr-2 h-4 w-4" />
                            <span>Perah</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
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
</template>

<style scoped>
.text-xxs {
    font-size: 0.60rem;
    line-height: 1rem;
}
</style>
