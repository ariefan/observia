<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Mars, Venus, ImageOff } from 'lucide-vue-next';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { differenceInYears, differenceInMonths, parseISO } from 'date-fns';


interface Livestock {
    id: string;
    name: string;
    aifarm_id: string;
    tag_id: string;
    sex: 'M' | 'F';
    birthdate: string; // Date string
    photo: string[]; // Array of photo paths
    breed: {
        name: string;
    };
}

defineProps<{
    livestocks: Livestock[];
    male_count: number;
    female_count: number;
}>();

const getPhotoUrl = (photoPath: string) => {
    if (photoPath.includes('firebasestorage.googleapis.com')) {
        return photoPath;
    }
    return photoPath ? `/storage/${photoPath}` : '';
};

const calculateAge = (birthdate: string): string => {
    if (!birthdate) return 'Unknown age';
    const birthDate = parseISO(birthdate);
    const now = new Date();
    const years = differenceInYears(now, birthDate);
    if (years > 0) {
        return `${years} tahun`;
    }
    const months = differenceInMonths(now, birthDate);
    return `${months} bulan`;
};

const show = (id: string) => router.visit(route('livestocks.show', { id }));

</script>

<template>

    <Head title="Populasi" />

    <AppLayout>
        <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 max-w-7xl mx-auto">
            <div>
                <h1 class="text-xl font-semibold text-primary">Populasi</h1>
            </div>

            <Card class="border-0 bg-primary">
                <CardContent class="pt-4">
                    <p class="text-white text-sm font-sans mb-2">Populasi ternak jantan dan betina di peternakan Anda:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-white font-sans">
                        <!-- GOAT SECTION -->
                        <div class="rounded-xl flex flex-col">
                            <!-- Header -->
                            <div
                                class="bg-teal-500 dark:bg-teal-200 text-white dark:text-black rounded-t-xl px-4 py-1 flex items-center gap-2">
                                <Mars class="size-4" />
                                <span>Jantan</span>
                            </div>

                            <!-- Big fat number -->
                            <div
                                class="bg-white dark:bg-zinc-800 dark:text-white text-teal-800 text-center py-2 rounded-b-xl text-2xl font-semibold">
                                {{ male_count }} <span class="text-sm font-normal">Ekor</span>
                            </div>
                        </div>

                        <!-- MILK SECTION -->
                        <div class="rounded-xl flex flex-col">
                            <!-- Header -->
                            <div
                                class="bg-cyan-500 dark:bg-cyan-200 text-white dark:text-black rounded-t-xl px-4 py-1 flex items-center gap-2">
                                <Venus class="size-4" />
                                <span>Betina</span>
                            </div>

                            <!-- Big fat number -->
                            <div
                                class="bg-white dark:bg-zinc-800 dark:text-white text-cyan-800 text-center py-2 rounded-b-xl text-2xl font-semibold">
                                {{ female_count }} <span class="text-sm font-normal">Ekor</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-6 gap-4 text-white font-sans">
                <Card v-for="livestock in livestocks" :key="livestock.id" @click="show(livestock.id)"
                    class="cursor-pointer border-0 rounded-lg overflow-hidden shadow-md p-0 transition-all duration-200 ease-in-out
                           hover:shadow-2xl hover:scale-[1.03] hover:-translate-y-1 hover:ring-4 hover:ring-primary/30 hover:bg-primary/20">
                    <!-- Image section with floating gender icon -->
                    <div class="relative">
                        <img v-if="livestock.photo && livestock.photo.length > 0" :src="getPhotoUrl(livestock.photo[0])"
                            :alt="livestock.name" class="w-full h-36 object-cover" />
                        <div v-else class="w-full h-36 object-cover bg-secondary flex items-center justify-center">
                            <ImageOff class="w-12 h-12 text-gray-400" />
                        </div>

                        <!-- Floating gender icon circle -->
                        <div
                            class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/25 backdrop-blur-md flex items-center justify-center shadow-sm">
                            <span v-if="livestock.sex === 'M'" class="text-blue-300">
                                <Mars />
                            </span>
                            <span v-else class="text-pink-300">
                                <Venus />
                            </span>
                        </div>

                        <Badge class="absolute bottom-2 right-2 bg-primary text-white rounded-full">{{
                            livestock.tag_id }}</Badge>
                    </div>

                    <!-- Goat info section -->
                    <CardContent class="px-4 py-2">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-base font-semibold text-primary">{{ livestock.name }}</h3>
                        </div>
                        <p class="text-sm">{{ livestock.breed.name }}</p>
                        <p class="text-sm">{{ calculateAge(livestock.birthdate) }}</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
