<script setup lang="ts">
import { getInitials } from '@/composables/useInitials';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Combobox,
    ComboboxButton,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
} from '@headlessui/vue';
import { Building2, Users, Plus, LogOut, Check, ChevronsUpDown } from 'lucide-vue-next';
import type { SharedData } from '@/types';
import { computed, ref } from 'vue';

const page = usePage<SharedData>();
const auth = computed(() => page.props.auth);

const farmQuery = ref('');
const selectedFarmId = ref('');
const isComboboxOpen = ref(false);

const otherFarms = computed(() =>
    auth.value.farms.filter(farm => farm.id !== (auth.value.user.current_farm?.id || ''))
);

const shouldUseCombobox = computed(() =>
    auth.value.user.is_super_user && otherFarms.value.length > 5
);

const filteredFarms = computed(() => {
    let farms = otherFarms.value;

    if (farmQuery.value) {
        farms = farms.filter(farm =>
            farm.name.toLowerCase().replace(/\s+/g, '').includes(farmQuery.value.toLowerCase().replace(/\s+/g, ''))
        );
    }

    // Limit to 100 items
    return farms.slice(0, 100);
});

const selectedFarmObject = computed(() => {
    return otherFarms.value.find(farm => farm.id === selectedFarmId.value) || null;
});

const handleFarmChange = (farmId: string) => {
    selectedFarmId.value = farmId;
    router.visit(route('farms.switch', { farm: farmId }));
};

</script>

<template>
    <div class="overflow-hidden rounded-lg shadow-xl bg-white dark:bg-zinc-900 max-w-sm w-full">
        <!-- Top Info -->
        <div class="bg-teal-700 text-white px-4 py-4 flex flex-col items-center text-center"
            v-if="auth.user.current_farm">
            <Avatar class="size-20 overflow-hidden rounded-full flex items-center justify-center mb-2">
                <AvatarImage v-if="auth.user.current_farm.picture" :src="auth.user.current_farm.picture"
                    :alt="auth.user.current_farm.name" />
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
        <div class="bg-teal-950 px-4 py-4 text-white text-sm transition-all duration-300"
            :class="isComboboxOpen && shouldUseCombobox ? 'h-[350px]' : ''" id="other-farms">
            <h3 class="mb-2 font-semibold">Peternakan anda</h3>

            <!-- Combobox for super users with > 5 farms -->
            <div v-if="shouldUseCombobox" class="mb-2">
                <Combobox v-model="selectedFarmId" @update:model-value="handleFarmChange" v-slot="{ open }">
                    <!-- Update the reactive variable when open state changes -->
                    <div v-show="false">{{ isComboboxOpen = open }}</div>
                    <div class="relative">
                        <ComboboxButton
                            class="relative w-full cursor-default rounded-lg bg-teal-900 border border-teal-700 py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 sm:text-sm">
                            <span class="block truncate text-white">{{ selectedFarmObject?.name || 'Pilih peternakan...'
                            }}</span>
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                <ChevronsUpDown class="h-4 w-4 text-teal-300" aria-hidden="true" />
                            </span>
                        </ComboboxButton>
                        <ComboboxOptions
                            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-teal-900 border border-teal-700 py-1 text-base shadow-lg focus:outline-none sm:text-sm">
                            <div class="relative">
                                <ComboboxInput
                                    class="w-full border-none bg-transparent py-2 pl-3 pr-10 text-sm leading-5 text-white placeholder:text-teal-300 focus:outline-none focus:ring-0"
                                    :display-value="() => ''" @change="farmQuery = $event.target.value"
                                    placeholder="Cari peternakan..." />
                            </div>
                            <div v-if="filteredFarms.length === 0 && farmQuery !== ''"
                                class="relative cursor-default select-none px-4 py-2 text-teal-300">
                                Tidak ada peternakan ditemukan.
                            </div>
                            <ComboboxOption v-for="farm in filteredFarms" :key="farm.id" v-slot="{ selected, active }"
                                :value="farm.id" as="template">
                                <li :class="[
                                    active ? 'bg-teal-800 text-white' : 'text-white',
                                    'relative cursor-default select-none py-2 pl-10 pr-4',
                                ]">
                                    <div class="flex items-center gap-2">
                                        <Avatar class="size-6 overflow-hidden rounded-full">
                                            <AvatarImage v-if="farm.picture" :src="farm.picture" :alt="farm.name" />
                                            <AvatarFallback class="rounded-lg font-semibold text-black text-xs">
                                                {{ getInitials(farm?.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate']">
                                            {{ farm.name }}
                                        </span>
                                    </div>
                                    <span v-if="selected" :class="[
                                        active ? 'text-white' : 'text-teal-400',
                                        'absolute inset-y-0 left-0 flex items-center pl-3',
                                    ]">
                                        <Check class="h-4 w-4" aria-hidden="true" />
                                    </span>
                                </li>
                            </ComboboxOption>
                        </ComboboxOptions>
                    </div>
                </Combobox>
            </div>

            <!-- Regular list for non-super users or <= 5 farms -->
            <ul v-else class="space-y-1">
                <li v-for="(farm, i) in otherFarms" :key="i">
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
