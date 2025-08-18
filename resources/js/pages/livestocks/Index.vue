<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Mars, Venus, ImageOff, Search, Filter } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

// Composables
import { usePhotoUrl } from '@/composables/usePhotoUrl';
import { useAgeCalculation } from '@/composables/useAgeCalculation';

// Components
import ImagePreview from '@/components/ImagePreview.vue';

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
        species?: {
            name: string;
        };
    };
}

interface Props {
    livestocks: {
        data: Livestock[];
        links: any[];
        total: number;
    };
    male_count: number;
    female_count: number;
    filters: {
        search?: string;
        gender?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

// Composables
const { getPhotoUrl } = usePhotoUrl();
const { calculateAge } = useAgeCalculation();

// Filters
const filters = ref({
    search: props.filters.search || '',
    gender: props.filters.gender || 'all',
    status: props.filters.status || 'active',
});

const search = () => {
    const queryParams = { ...filters.value };

    // Convert "all" to empty string for backend
    if (queryParams.gender === 'all') {
        queryParams.gender = '';
    }

    router.get(route('livestocks.index'), queryParams, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filters.value = { search: '', gender: 'all', status: 'active' };
    search();
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
                    <p class="text-white text-sm font-sans mb-2">Populasi ternak aktif di peternakan Anda:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-white font-sans">
                        <!-- MALE SECTION -->
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

                        <!-- FEMALE SECTION -->
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

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                <!-- Search -->
                <div>
                    <label class="text-xs font-medium">Cari</label>
                    <div class="relative">
                        <Search class="absolute left-2 top-2 h-3.5 w-3.5 text-muted-foreground" />
                        <Input v-model="filters.search" placeholder="Nama, ID, Tag..." class="h-8 pl-8 text-xs"
                            @keyup.enter="search" />
                    </div>
                </div>

                <!-- Gender Filter -->
                <div>
                    <label class="text-xs font-medium">Jenis Kelamin</label>
                    <Select v-model="filters.gender" class="h-8 text-xs">
                        <SelectTrigger class="h-8 text-xs">
                            <SelectValue placeholder="Semua" class="text-xs" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all" class="text-xs">Semua</SelectItem>
                            <SelectItem value="M" class="text-xs">Jantan</SelectItem>
                            <SelectItem value="F" class="text-xs">Betina</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="text-xs font-medium">Status</label>
                    <Select v-model="filters.status" class="h-8 text-xs">
                        <SelectTrigger class="h-8 text-xs">
                            <SelectValue placeholder="Aktif" class="text-xs" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active" class="text-xs">Aktif</SelectItem>
                            <SelectItem value="ended" class="text-xs">Sudah Diakhiri</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <Button @click="search" class="h-8 flex-1 text-xs">
                        Cari
                    </Button>
                    <Button @click="clearFilters" variant="outline" class="h-8 text-xs">
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Results -->
            <div>
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ livestocks.data?.length || 0 }} dari {{ livestocks.total || 0 }} ternak
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-6 gap-4 text-white font-sans">
                <Card v-for="livestock in livestocks.data" :key="livestock.id" @click="show(livestock.id)"
                    class="cursor-pointer border-0 rounded-lg overflow-hidden shadow-md p-0 transition-all duration-200 ease-in-out
                           hover:shadow-2xl hover:scale-[1.03] hover:-translate-y-1 hover:ring-4 hover:ring-primary/30 hover:bg-primary/20">
                    <!-- Image section with floating gender icon -->
                    <div class="relative">
                        <img v-if="livestock.photo && livestock.photo.length > 0" :src="getPhotoUrl(livestock.photo[0])"
                            :alt="livestock.name" class="w-full h-36 object-cover" />
                        <div v-else class="w-full h-36 object-cover bg-secondary flex items-center justify-center">
                            <ImageOff class="w-12 h-12 text-gray-400" />
                        </div>

                        <!-- Image preview and gender icons -->
                        <div class="absolute top-2 right-2 flex gap-1">
                            <ImagePreview v-if="livestock.photo && livestock.photo.length > 0" :photos="livestock.photo"
                                trigger-class="w-8 h-8 rounded-full bg-white/25 backdrop-blur-md text-white hover:bg-white/40" />
                            <div
                                class="w-8 h-8 rounded-full bg-white/25 backdrop-blur-md flex items-center justify-center shadow-sm">
                                <span v-if="livestock.sex === 'M'" class="text-blue-300">
                                    <Mars />
                                </span>
                                <span v-else class="text-pink-300">
                                    <Venus />
                                </span>
                            </div>
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

            <!-- No results -->
            <div v-if="!livestocks.data || livestocks.data.length === 0" class="text-center py-8">
                <p class="text-muted-foreground">Tidak ada ternak yang ditemukan</p>
            </div>

            <!-- Pagination -->
            <div v-if="livestocks.links && livestocks.links.length > 3" class="mt-6 flex justify-center">
                <div class="flex space-x-1">
                    <Link v-for="link in livestocks.links" :key="link.label" :href="link.url" :class="[
                        'px-3 py-2 text-sm rounded-md',
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent',
                        !link.url ? 'pointer-events-none opacity-50' : ''
                    ]">
                    {{link.label.replace(/&laquo;|&raquo;/g, (match) => match === '&laquo;' ? '«' : '»')}}
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
