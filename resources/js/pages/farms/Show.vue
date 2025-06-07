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
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { Pencil, ChevronDown } from 'lucide-vue-next';


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Profil Peternakan', href: '/teams' },
];

const props = defineProps<{
    provinces: { id: number; code: string; name: string }[];
    cities: { id: number; province_id: number; code: string; name: string }[];
    name?: string;
}>();

const users = ref([
    {
        name: 'Ferdi Sambo',
        phone: '08123456789',
        email: 'Ferdi@gmail.com',
        role: 'Farm Owner & Admin',
        avatar: 'https://i.pravatar.cc/100?u=ferdi',
        editable: false,
        permission: 'Editor',
    },
    {
        name: 'Yayan',
        phone: '08123456789',
        email: 'Yayan@gmail.com',
        role: 'ABK',
        avatar: 'https://i.pravatar.cc/100?u=yayan',
        editable: true,
        permission: 'Lihat',
    }
])

function setPermission(user, permission) {
    user.permission = permission
}

const form = useForm({
    name: '',
    owner: '',
    phone: '',
    email: '',
    picture: '',
    picture_blob: null,
    province_id: '',
    city_id: '',
    latlong: { latitude: 0, longitude: 0 },
});

const filteredCities = computed(() => {
    if (!form.province_id) return [];
    return props.cities.filter(city => city.province_id === parseInt(form.province_id));
});

function submit() {
    console.log(form);
    form.post(route('farms.store'), {
        onSuccess: () => {
            console.log('Form submitted successfully');
        },
        onError: () => {
            console.error('Form submission failed', form.errors);
        },
    });
}

const back = () => window.history.back();
</script>

<template>

    <Head title="Peternakan" />
    <AppLayout>
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-100 dark:bg-teal-900 p-2 shadow-xl -mt-2">
                <nav class="space-y-2">
                    <Link :href="route('farms.edit', $page.props.auth.user.current_farm.id)"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    <Building2 class="size-4" /> Profil Peternakan
                    </Link>
                    <Link href="#"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
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
                    <h1 class="text-xl font-semibold text-primary">Anggota {{ $page.props.auth.user.current_farm.name }}
                    </h1>
                </div>

                <Card class="border-0">
                    <form @submit.prevent="submit">
                        <CardContent class="pt-6">
                            <div class="bg-teal-800 text-white p-6 rounded-lg mb-6">
                                <h2 class="text-xl font-bold mb-2">Undang anggota peternakan Anda</h2>
                                <p class="text-sm font-semibold mb-2">Fitur ini memungkinkan anda untuk:</p>
                                <ul class="list-disc pl-5 space-y-1 text-sm">
                                    <li>Menambahkan anggota baru ke peternakan Anda.</li>
                                    <li>Memberikan akses ke data dan fitur peternakan kepada anggota.</li>
                                    <li>Berkolaborasi dengan anggota lain untuk mengelola peternakan.</li>
                                </ul>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left border-t">
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                        <tr>
                                            <th class="px-4 py-2">User name</th>
                                            <th class="px-4 py-2">No Hp</th>
                                            <th class="px-4 py-2">Email</th>
                                            <th class="px-4 py-2">Role</th>
                                            <th class="px-4 py-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="user in users" :key="user.email"
                                            class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="flex items-center gap-3 px-4 py-3">
                                                <img :src="user.avatar" class="w-8 h-8 rounded-full" alt="Avatar" />
                                                {{ user.name }}
                                            </td>
                                            <td class="px-4 py-3">{{ user.phone }}</td>
                                            <td class="px-4 py-3">{{ user.email }}</td>
                                            <td class="px-4 py-3">{{ user.role }}</td>
                                            <td class="px-4 py-3 flex items-center justify-center gap-2">
                                                <Button v-if="user.editable" variant="outline" size="sm">
                                                    <Pencil class="w-4 h-4 mr-1" /> Edit
                                                </Button>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger>
                                                        <Button variant="secondary" size="sm">
                                                            {{ user.permission }}
                                                            <ChevronDown class="ml-1 w-4 h-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent>
                                                        <DropdownMenuItem @click="setPermission(user, 'Editor')">
                                                            Editor</DropdownMenuItem>
                                                        <DropdownMenuItem @click="setPermission(user, 'Lihat')">
                                                            Lihat</DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>

                        <CardFooter class="flex justify-between px-6 pb-6">
                            <Button variant="outline" @click="back">Undang anggota</Button>
                            <div></div>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>