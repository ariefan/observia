<script setup lang="ts">
import { ref, PropType, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose,
} from '@/components/ui/dialog'
import { getInitials } from '@/composables/useInitials';
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { ChevronDown, Trash2, Building2, Users } from 'lucide-vue-next';


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
    invites: Array as PropType<Array<{
        id: string
        farm_id: string
        email: string
        role: string
    }>>,
})

const page = usePage<SharedData>();
const auth = computed(() => page.props.auth);

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

function updateRoleInvite(email: string, role: string) {
    console.log(`Updating role for invite: ${email} to ${role}`)
    const forms = ref<{ [key: string]: ReturnType<typeof useForm<any>> }>({})

    // init form per user
    props.invites?.forEach((user) => {
        forms.value[email] = useForm({
            email: user.email ?? '',
            role: user.role ?? '',
        })
    })
    const form = forms.value[email]
    if (!form) return

    if (form.role === role) return // No change, no cry
    console.log(form)

    form.role = role
    form.put(`/farms/${props.farm.id}/email/${email}/role-invite`, {
        preserveScroll: true,
        onSuccess: () => {
            console.log(`Role updated for ${email} to ${role}`)
            toast.success(`Hak akses diubah menjadi ${role}`, {
                description: `Untuk email: ${email}`,
            })
        },
        onError: (err: any) => {
            console.error('Failed to update role:', err)
        },
    })
}

function removeUser(user: typeof props.farm.users[0]) {
    if (!confirm(`Apakah Anda yakin ingin menghapus anggota ${user.name}?`)) return;

    useForm({}).delete(`/farms/${props.farm.id}/users/${user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            console.log(`User ${user.name} removed successfully`)
            toast.success(`Anggota ${user.name} berhasil dihapus`, {
                description: 'Anggota telah dihapus dari peternakan.',
            });
        },
        onError: (err: any) => {
            console.error('Failed to remove user:', err);
            toast.error('Gagal menghapus anggota', {
                description: err.message || 'Terjadi kesalahan saat menghapus anggota.',
            });
        },
    })
}

function removeUserInvite(email: string) {
    if (!confirm(`Apakah Anda yakin ingin menghapus anggota ${email}?`)) return;

    useForm({}).delete(`/farms/${props.farm.id}/email/${email}`, {
        preserveScroll: true,
        onSuccess: () => {
            console.log(`Email ${email} removed successfully`)
            toast.success(`Email ${email} berhasil dihapus`, {
                description: 'Email telah dihapus dari peternakan.',
            });
        },
        onError: (err: any) => {
            console.error('Failed to remove email:', err);
            toast.error('Gagal menghapus email', {
                description: err.message || 'Terjadi kesalahan saat menghapus email.',
            });
        },
    })
}

const formInvite = useForm({
    email: '',
    role: 'abk',
})

function invite() {
    console.log('Inviting member with form:', formInvite)
    formInvite.post(`/farms/${props.farm.id}/invite`, {
        preserveScroll: true,
        onSuccess: () => {
            formInvite.reset();
            toast.success('Anggota berhasil diundang', {
                description: 'Email undangan telah dikirim.',
            });
        },
        onError: (err: any) => {
            console.error('Failed to invite member:', err);
            toast.error('Gagal mengundang anggota', {
                description: err.message || 'Terjadi kesalahan. Pastikan email belum terdaftar.',
            });
        },
    })
}
</script>

<template>

    <Head title="Peternakan" />
    <AppLayout>
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
                <nav class="space-y-2">
                    <Link :href="route('farms.edit', auth.user.current_farm.id)"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    <Building2 class="size-4" /> Profil Peternakan
                    </Link>
                    <Link href="#"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    <Users class="size-4" /> Anggota Peternakan
                    </Link>

                    <Dialog>
                        <DialogTrigger as-child>
                            <a href="#"
                                class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                                <Trash2 class="size-4" /> Hapus Peternakan
                            </a>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[450px]">
                            <DialogHeader>
                                <DialogTitle>Hapus Peternakan</DialogTitle>
                                <DialogDescription>
                                    Berikan akses ke data dan fitur peternakan kepada anggota.
                                </DialogDescription>
                            </DialogHeader>
                            Silahkan hubungi Aifarm untuk melakukan permohonan penghapusan peternakan.
                            <DialogFooter>
                                <DialogClose as-child>
                                    <a href="https://wa.me/6282323294488" target="_blank" class="w-full">
                                        <Button type="submit" class="w-full" v-on:click="invite">
                                            Hubungi Aifarm
                                        </Button>
                                    </a>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
                <div>
                    <h1 class="text-xl font-semibold text-primary">
                        {{ auth.user.role }}
                        Anggota {{ auth.user.current_farm.name }}
                    </h1>
                </div>
                <Card class="border-0">
                    <CardContent class="pt-2">
                        <form @submit.prevent="submit">
                            <div class="bg-teal-800 text-white p-4 rounded-lg mb-6">
                                <h2 class="text-lg font-bold mb-2">Undang anggota peternakan Anda</h2>
                                <p class="text-sm font-semibold mb-1">Fitur ini memungkinkan anda untuk:</p>
                                <ul class="list-disc pl-5 space-y-1 text-sm">
                                    <li>Menambahkan anggota baru ke peternakan Anda.</li>
                                    <li>Memberikan akses ke data dan fitur peternakan kepada anggota.</li>
                                    <li>Berkolaborasi dengan anggota lain untuk mengelola peternakan.</li>
                                </ul>
                            </div>

                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Nama lengkap</TableHead>
                                            <TableHead>No Hp</TableHead>
                                            <TableHead>Email</TableHead>
                                            <TableHead>Role</TableHead>
                                            <TableHead>Action</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <template v-for="user in farm.users" :key="user.email">
                                            <TableRow>
                                                <TableCell>
                                                    <Avatar class="size-8 overflow-hidden rounded-full">
                                                        <AvatarImage v-if="user.avatar" :src="user.avatar"
                                                            :alt="user.name" />
                                                        <AvatarFallback
                                                            class="rounded-lg font-semibold text-black dark:text-white">
                                                            {{ getInitials(user?.name) }}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                    {{ user.name }}
                                                </TableCell>
                                                <TableCell>{{ user.phone }}</TableCell>
                                                <TableCell>{{ user.email }}</TableCell>
                                                <TableCell>
                                                    <DropdownMenu
                                                        v-if="user.pivot.role !== 'owner' &&
                                                            ['owner', 'admin'].includes(auth.user.current_farm.role)">
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
                                                            <DropdownMenuItem @click="updateRole(user, 'admin')">Admin
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem @click="updateRole(user, 'abk')">ABK
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem @click="updateRole(user, 'investor')">
                                                                Investor</DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                    <span class="font-semibold" v-else>{{ user.pivot.role }}</span>
                                                </TableCell>
                                                <TableCell>
                                                    <Button
                                                        v-if="user.pivot.role !== 'owner' &&
                                                            ['owner', 'admin'].includes(auth.user.current_farm.role)"
                                                        variant="destructive" size="sm" @click="removeUser(user)">
                                                        <Trash2 class="w-4 h-4 mr-1" /> Keluarkan
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </template>
                                        <template v-for="user in invites" :key="user.email">
                                            <TableRow>
                                                <TableCell class="italic">Menunggu konfirmasi</TableCell>
                                                <TableCell></TableCell>
                                                <TableCell>{{ user.email }}</TableCell>
                                                <TableCell>
                                                    <DropdownMenu v-if="user.role !== 'owner'">
                                                        <DropdownMenuTrigger>
                                                            <Button variant="secondary" size="sm">
                                                                {{
                                                                    user.role.charAt(0).toUpperCase() +
                                                                    user.role.slice(1)
                                                                }}
                                                                <ChevronDown class="ml-1 w-4 h-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent>
                                                            <DropdownMenuItem
                                                                @click="updateRoleInvite(user.email, 'admin')">Admin
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                @click="updateRoleInvite(user.email, 'abk')">ABK
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                @click="updateRoleInvite(user.email, 'investor')">
                                                                Investor</DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                    <span class="font-semibold" v-else>{{ user.role }}</span>
                                                </TableCell>
                                                <TableCell>
                                                    <Button v-if="user.role !== 'owner'" variant="destructive" size="sm"
                                                        @click="removeUserInvite(user.email)">
                                                        <Trash2 class="w-4 h-4 mr-1" /> Keluarkan
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </template>
                                    </TableBody>
                                </Table>
                            </div>
                        </form>
                    </CardContent>

                    <CardFooter class="flex justify-between px-6 pb-6">
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="outline">
                                    Undang anggota
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-[450px]">
                                <DialogHeader>
                                    <DialogTitle>Tambah Anggota</DialogTitle>
                                    <DialogDescription>
                                        Berikan akses ke data dan fitur peternakan kepada anggota.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-4 py-4">
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="email" class="text-right">
                                            Email
                                        </Label>
                                        <Input id="email" type="email" class="col-span-3" v-model="formInvite.email" />
                                    </div>
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="name" class="text-right">
                                            Role
                                        </Label>
                                        <!-- <Input id="name" value="Pedro Duarte" class="col-span-3" /> -->
                                        <Select v-model="formInvite.role">
                                            <SelectTrigger class="col-span-3">
                                                <SelectValue placeholder="Pilih role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="admin">
                                                    Admin
                                                </SelectItem>
                                                <SelectItem value="abk">
                                                    ABK
                                                </SelectItem>
                                                <SelectItem value="investor">
                                                    Investor
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button type="submit" class="w-full" v-on:click="invite">
                                            Undang
                                        </Button>
                                    </DialogClose>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </CardFooter>
                </Card>
            </div>
        </div>

    </AppLayout>
</template>