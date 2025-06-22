<script setup lang="ts">
import { reactive, ref, computed, PropType } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { BreadcrumbItem } from '@/types';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css'
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
import { getInitials } from '@/composables/useInitials';
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { Pencil, ChevronDown, Trash2, Building2, Users } from 'lucide-vue-next';


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Profil Peternakan', href: '/teams' },
];

const props = defineProps({
    farm: {
        type: Object as PropType<{
            id: string
            users: Array<{
                id: string
                name: string
                email: string
                phone: string
                avatar?: string | null
                editable?: boolean
                pivot: {
                    role: string
                }
            }>
        }>,
        required: true,
    },
})

const forms = ref<{ [key: string]: ReturnType<typeof useForm<any>> }>({})

// init form per user
props.farm.users.forEach((user) => {
    forms.value[user.id] = useForm({
        role: user.pivot.role ?? '',
    })
})

function updateRole(user: typeof props.farm.users[0], role: string) {
    const form = forms.value[user.id]
    if (!form) return

    if (form.role === role) return // No change, no cry

    form.role = role
    form.put(`/farms/${props.farm.id}/users/${user.id}/role`, {
        preserveScroll: true,
        onSuccess: () => {
            console.log(`Role updated for ${user.name} to ${role}`)
            toast.success(`Hak akses diubah menjadi ${role}`, {
                description: `Untuk user: ${user.name}`,
            })
        },
        onError: (err: any) => {
            console.error('Failed to update role:', err)
        },
    })
}

const back = () => window.history.back();
</script>

<template>

    <Head title="Peternakan" />
    <AppLayout>
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
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
                    <h1 class="text-xl font-semibold text-primary">
                        Anggota {{ $page.props.auth.user.current_farm.name }}
                    </h1>
                </div>
                <Card class="border-0">
                    <form @submit.prevent="submit">
                        <CardContent class="pt-2">
                            <div class="bg-teal-800 text-white p-4 rounded-lg mb-6">
                                <h2 class="text-lg font-bold mb-2">Undang anggota peternakan Anda</h2>
                                <p class="text-sm font-semibold mb-1">Fitur ini memungkinkan anda untuk:</p>
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
                                            <th class="px-4 py-2">Nama lengkap</th>
                                            <th class="px-4 py-2">No Hp</th>
                                            <th class="px-4 py-2">Email</th>
                                            <th class="px-4 py-2">Role</th>
                                            <th class="px-4 py-2">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="user in farm.users" :key="user.email"
                                            class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="flex items-center gap-3 px-4 py-3">
                                                <Avatar class="size-8 overflow-hidden rounded-full">
                                                    <AvatarImage v-if="user.avatar" :src="user.avatar"
                                                        :alt="user.name" />
                                                    <AvatarFallback
                                                        class="rounded-lg font-semibold text-black dark:text-white">
                                                        {{ getInitials(user?.name) }}
                                                    </AvatarFallback>
                                                </Avatar>
                                                {{ user.name }}
                                            </td>
                                            <td class="px-4 py-3">{{ user.phone }}</td>
                                            <td class="px-4 py-3">{{ user.email }}</td>
                                            <td class="px-4 py-3 gap-2">
                                                <DropdownMenu v-if="user.pivot.role !== 'ownera'">
                                                    <DropdownMenuTrigger>
                                                        <Button variant="secondary" size="sm">
                                                            {{
                                                                user.pivot.role.charAt(0).toUpperCase() +
                                                                user.pivot.role.slice(1)
                                                            }}
                                                            <ChevronDown class="ml-1 w-4 h-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent>
                                                        <DropdownMenuItem @click="updateRole(user, 'admin')">
                                                            Admin
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem @click="updateRole(user, 'abk')">
                                                            ABK
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem @click="updateRole(user, 'investor')">
                                                            Investor
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                                <span class="font-semibold" v-else>{{ user.pivot.role }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <Button v-if="user.pivot.role !== 'owner'" variant="destructive"
                                                    size="sm">
                                                    <Trash2 class="w-4 h-4 mr-1" /> Keluarkan
                                                </Button>
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