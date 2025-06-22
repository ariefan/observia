<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { FloatingInput } from '@/components/ui/floating-input';
import { MapInput } from '@/components/ui/map-input';
import { LocationInput } from '@/components/ui/location-input';
import { ImageUpload } from '@/components/ui/image-upload';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Building2, Users, Trash2 } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Profil Peternakan', href: '/teams' },
];

const props = defineProps<{
    provinces: { id: number; code: string; name: string }[];
    cities: { id: number; province_id: number; code: string; name: string }[];
    farm?: {
        id: number;
        name: string;
        address: string;
        owner: string;
        phone: string;
        email: string;
        picture: string | null;
        picture_blob: Blob | null;
        city: {
            id: number;
            name: string;
            province_id: number;
            code: string;
        };
        city_id: number;
        latlong: { latitude: number; longitude: number };
    }
}>();

const selectedCoordinates = ref<{ latitude: number; longitude: number } | null>(null);
const profileImage = ref(null);

const form = useForm({
    _method: props.farm?.id ? 'put' : 'post',
    name: props.farm?.name || '',
    address: props.farm?.address || '',
    owner: props.farm?.owner || '',
    phone: props.farm?.phone || '',
    email: props.farm?.email || '',
    picture: props.farm?.picture || '',
    picture_blob: null,
    province_id: props.farm?.city.province_id || '',
    city_id: props.farm?.city_id || '',
    latlong: { latitude: 0, longitude: 0 },
});

const filteredCities = computed(() => {
    if (!form.province_id) return [];
    return props.cities.filter(city => city.province_id === form.province_id);
});

function submit() {
    console.log(form);
    if (props.farm?.id) {
        form.post(route('farms.update', { farm: props.farm.id }), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Form updated successfully');
            },
            onError: () => {
                console.error('Form update failed', form.errors);
            },
        });
        return;
    } else {
        form.post(route('farms.store'), {
            onSuccess: () => {
                console.log('Form submitted successfully');
            },
            onError: () => {
                console.error('Form submission failed', form.errors);
            },
        });
    }
}

const back = () => window.history.back();
</script>

<template>

    <Head title="Peternakan" />
    <AppLayout>
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-100 dark:bg-teal-900 p-2 shadow-xl -mt-2" v-if="farm?.id">
                <nav class="space-y-2">
                    <Link href="#"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    <Building2 class="size-4" /> Profil Peternakan
                    </Link>
                    <Link :href="route('farms.show', { farm: farm.id })"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    <Users class="size-4" /> Anggota Peternakan
                    </Link>
                    <Link href="#"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    <Trash2 class="size-4" /> Hapus Peternakan
                    </Link>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
                <div>
                    <h1 class="text-xl font-semibold text-primary">Peternakan</h1>
                    <p class="text-sm">Pastikan data Anda akurat dan terkini untuk pengalaman Aifarm yang optimal.</p>
                </div>

                <Card class="border-0">
                    <form @submit.prevent="submit">
                        <CardContent class="pt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div>
                                    <Label for="name">Logo Peternakan</Label>
                                    <ImageUpload v-model:imageBlob="form.picture_blob"
                                        v-model:imageUrl="form.picture" />
                                </div>

                                <div>
                                    <Label for="name">Nama peternakan</Label>
                                    <Input id="name" type="text" required v-model="form.name" />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div>
                                    <Label for="owner">Pemilik peternakan</Label>
                                    <Input id="owner" type="text" required v-model="form.owner" />
                                    <InputError :message="form.errors.owner" />
                                </div>

                                <div>
                                    <Label for="phone">Nomor telepon peternakan</Label>
                                    <Input id="phone" type="text" required v-model="form.phone" />
                                    <InputError :message="form.errors.phone" />
                                </div>

                                <div>
                                    <Label for="email">Email peternakan</Label>
                                    <Input id="email" type="email" required v-model="form.email" />
                                    <InputError :message="form.errors.email" />
                                </div>

                                <div>
                                    <Label for="province_id">Provinsi</Label>
                                    <Select v-model="form.province_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih Provinsi" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="province in provinces" :key="province.id"
                                                    :value="province.id">
                                                    {{ province.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <!-- <InputError :message="form.errors.province_id" /> -->
                                </div>

                                <div>
                                    <Label for="city_id">Kota / Kabupaten</Label>
                                    <Select v-model="form.city_id" :disabled="!form.province_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih Kota/Kabupaten" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup v-if="filteredCities.length > 0">
                                                <SelectItem v-for="city in filteredCities" :key="city.id"
                                                    :value="city.id">
                                                    {{ city.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                            <div v-else class="text-xs text-gray-500 px-2 py-1">Pilih provinsi terlebih
                                                dahulu</div>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.city_id" />
                                </div>

                                <div>
                                    <Label for="address">Alamat Peternakan</Label>
                                    <Input id="address" type="text" required v-model="form.address" />
                                    <InputError :message="form.errors.address" />
                                </div>
                            </div>

                            <div>
                                <Label for="latlong">Lokasi peta peternakan</Label>
                                <LocationInput v-model="form.latlong" />
                                <InputError :message="form.errors.latlong" />
                            </div>
                        </CardContent>

                        <CardFooter class="flex justify-between px-6 pb-6">
                            <Button variant="outline" @click="back">Batal</Button>
                            <Button type="submit">Simpan</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>