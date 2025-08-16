<script setup lang="ts">
import { getInitials } from '@/composables/useInitials';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Building2, Users, Plus, LogOut } from 'lucide-vue-next';
import type { SharedData } from '@/types';
import { computed } from 'vue';

const page = usePage<SharedData>();
const auth = computed(() => page.props.auth);

</script>

<template>
    <div class="overflow-hidden rounded-lg shadow-xl bg-white dark:bg-zinc-900 max-w-sm w-full">
        <!-- Top Info -->
        <div class="bg-teal-700 text-white px-4 py-4 flex flex-col items-center text-center"
            v-if="auth.user.current_farm">
            <Avatar class="size-20 overflow-hidden rounded-full flex items-center justify-center mb-2">
                <AvatarImage v-if="auth.user.current_farm.picture"
                    :src="auth.user.current_farm.picture" :alt="auth.user.current_farm.name" />
                <AvatarFallback class="text-2xl rounded-lg font-semibold text-black  dark:text-white">
                    {{ getInitials(auth.user.current_farm?.name) }}
                </AvatarFallback>
            </Avatar>
            <h2 class="text-lg font-bold">{{ auth.user.current_farm.name }}</h2>
            <p class="text-xs opacity-80 leading-tight mt-0.5">{{ auth.user.current_farm.address }}</p>

            <div class="mt-2 flex gap-2 w-full justify-center">
                <Link :href="route('farms.edit', { farm: auth.user.current_farm.id })">
                <Button variant="outline"
                    class="bg-white/10 text-white border-white/30 hover:bg-white/80 text-xs px-3 py-1 h-auto gap-1">
                    <Building2 class="w-4 h-4" /> Profile Peternakan
                </Button>
                </Link>
                <Link :href="route('farms.show', { farm: auth.user.current_farm.id })">
                <Button variant="outline"
                    class="bg-white/10 text-white border-white/30 hover:bg-white/80 text-xs px-3 py-1 h-auto gap-1"
                    title="Lihat anggota peternakan">
                    <Users class="w-4 h-4" /> {{ auth.user.current_farm.users_count }}
                </Button>
                </Link>
                <Link :href="route('farms.logout')">
                <Button variant="outline"
                    class="bg-white/10 text-white border-white/30 hover:bg-white/80 text-xs px-3 py-1 h-auto gap-1"
                    title="Log out peternakan ini">
                    <LogOut class="w-4 h-4" />
                </Button>
                </Link>
            </div>
        </div>

        <!-- Other Farms -->
        <div class="bg-teal-950 px-4 py-4 text-white text-sm">
            <h3 class="mb-2 font-semibold">Peternakan anda</h3>
            <ul class="space-y-1">
                <li v-for="(farm, i) in auth.farms.filter(
                    (farm) => farm.id !== (auth.user.current_farm?.id || '')
                )" :key="i">
                    <Link :href="route('farms.switch', { farm: farm.id })"
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

            <div class="mt-3 flex justify-between items-center">
                <Button variant="ghost" class="text-emerald-400 text-xs px-0 h-auto gap-1 hover:underline px-2"
                    @click="router.visit(route('farms.create'))">
                    <Plus class="w-4 h-4" /> Tambah Peternakan
                </Button>
            </div>
        </div>
    </div>
</template>
