<script setup lang="ts">
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Building2, Users, Plus } from 'lucide-vue-next';

interface Farm {
    name: string;
    location: string;
    members: number;
    avatar?: string;
}
</script>

<template>
    <div class="overflow-hidden rounded-lg shadow-xl bg-white dark:bg-zinc-900 max-w-sm w-full">
        <!-- Top Info -->
        <div class="bg-teal-700 text-white px-4 py-4 flex flex-col items-center text-center"
            v-if="$page.props.auth.user.current_farm">
            <Avatar class="size-20 overflow-hidden rounded-full flex items-center justify-center mb-2">
                <AvatarImage v-if="$page.props.auth.user.current_farm.picture"
                    :src="$page.props.auth.user.current_farm.picture" :alt="$page.props.auth.user.current_farm.name" />
                <AvatarFallback class="text-2xl rounded-lg font-semibold text-black  dark:text-white">
                    {{ getInitials($page.props.auth.user.current_farm?.name) }}
                </AvatarFallback>
            </Avatar>
            <h2 class="text-lg font-bold">{{ $page.props.auth.user.current_farm.name }}</h2>
            <p class="text-xs opacity-80 leading-tight mt-0.5">{{ $page.props.auth.user.current_farm.address }}</p>

            <div class="mt-2 flex gap-2 w-full justify-center">
                <Link :href="route('farms.edit', $page.props.auth.user.current_farm.id)">
                <Button variant="outline"
                    class="bg-white/10 text-white border-white/30 hover:bg-white/80 text-xs px-3 py-1 h-auto gap-1">
                    <Building2 class="w-4 h-4" /> Profile Peternakan
                </Button>
                </Link>
                <Link :href="route('farms.show', $page.props.auth.user.current_farm.id)">
                <Button variant="outline"
                    class="bg-white/10 text-white border-white/30 hover:bg-white/80 text-xs px-3 py-1 h-auto gap-1">
                    <Users class="w-4 h-4" /> {{ $page.props.auth.user.current_farm.users_count }}
                </Button>
                </Link>
            </div>
        </div>

        <!-- Other Farms -->
        <div class="bg-teal-950 px-4 py-4 text-white text-sm">
            <h3 class="mb-2 font-semibold">Peternakan lain</h3>
            <ul class="space-y-1">
                <li v-for="(farm, i) in $page.props.auth.farms.filter(
                    (farm) => farm.id !== ($page.props.auth.user.current_farm?.id || '')
                )" :key="i">
                    <Link :href="route('farms.switch', farm.id)"
                        class="flex items-center gap-2 cursor-pointer hover:bg-teal-800 rounded-full px-2 py-2 transition-all duration-150">
                    <Avatar class="size-8 overflow-hidden rounded-full">
                        <AvatarImage v-if="farm.picture" :src="farm.picture" :alt="farm.name" />
                        <AvatarFallback class="rounded-lg font-semibold text-black  dark:text-white">
                            {{ getInitials(farm?.name) }}
                        </AvatarFallback>
                    </Avatar>
                    {{ farm?.name }}
                    </Link>
                </li>
            </ul>

            <div class="mt-3">
                <Link :href="route('farms.create')">
                <Button variant="ghost" class="text-emerald-400 text-xs px-0 h-auto gap-1 hover:underline px-2">
                    <Plus class="w-4 h-4" /> Tambah Peternakan
                </Button>
                </Link>
            </div>
        </div>
    </div>
</template>
