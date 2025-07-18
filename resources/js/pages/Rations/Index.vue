<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, Building2, Pencil, Trash } from 'lucide-vue-next';

import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogClose
} from '@/components/ui/dialog';

const props = defineProps({
    rations: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const totalFeeds = (rationItems = []) => rationItems.length;

const totalCost = (rationItems = []) => {
    return rationItems.reduce((acc, item) => acc + (item.quantity * item.price), 0);
};

</script>

<template>

    <Head title="Ransum" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Ransum
            </h2>
        </template>

        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-56 bg-teal-50 dark:bg-teal-950 p-2 shadow-xl -mt-2">
                <nav class="space-y-2">
                    <Link :href="route('rations.index')"
                        class="flex items-center gap-2 text-sm font-semibold text-white bg-primary rounded-full px-4 py-2 transition-colors">
                    Pakan
                    </Link>
                    <Link :href="route('herds.index')"
                        class="flex items-center gap-2 text-sm font-semibold hover:bg-primary hover:text-white rounded-full px-4 py-2 transition-colors">
                    Kandang
                    </Link>
                </nav>
            </aside>

            <div class="flex-1 flex flex-col gap-4 p-4 max-w-7xl mx-auto">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Tabs default-value="ration" class="w-full">
                        <TabsList class="grid w-full grid-cols-6">
                            <TabsTrigger class="text-primary font-semibold" value="ration">
                                Stok Ransum
                            </TabsTrigger>
                            <TabsTrigger class="text-primary font-semibold" value="history">
                                Riwayat Ransum
                            </TabsTrigger>
                            <TabsTrigger class="text-primary font-semibold" value="feed">
                                Pakan
                            </TabsTrigger>
                        </TabsList>
                        <TabsContent value="ration">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Stok Ransum</CardTitle>
                                    <CardDescription>
                                        Pastikan ketersediaan pakan selalu terjaga. Hindari kehabisan pakan yang
                                        dapat
                                        mengganggu kesehatan ternak dan produktivitas peternakan
                                    </CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    <div class="flex justify-end mb-4">

                                    </div>
                                    <div class="relative overflow-x-auto sm:rounded-lg">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Ransum</TableHead>
                                                    <TableHead>Komposisi</TableHead>
                                                    <TableHead>Jumlah</TableHead>
                                                    <TableHead>Total Harga</TableHead>
                                                    <TableHead class="text-right">
                                                        <Link :href="route('rations.create')">
                                                        <Button size="small">Tambah Ransum</Button>
                                                        </Link>
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <template v-for="ration in rations" :key="ration.id">
                                                    <TableRow>
                                                        <TableCell
                                                            class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                            {{ ration.name }}
                                                        </TableCell>
                                                        <TableCell>
                                                            {{ totalFeeds(ration.ration_items) }} Jenis Pakan
                                                        </TableCell>
                                                        <TableCell>
                                                            {{ formatCurrency(totalCost(ration.ration_items)) }}
                                                        </TableCell>
                                                        <TableCell class="text-right">
                                                            <Link :href="route('rations.edit', ration.id)">
                                                            <Button variant="ghost" size="icon">
                                                                <Pencil class="w-4 h-4" />
                                                            </Button>
                                                            </Link>
                                                            <Dialog>
                                                                <DialogTrigger as-child>
                                                                    <Button variant="destructive" size="icon">
                                                                        <Trash class="w-4 h-4" />
                                                                    </Button>
                                                                </DialogTrigger>
                                                                <DialogContent>
                                                                    <DialogHeader>
                                                                        <DialogTitle>Hapus Ransum?</DialogTitle>
                                                                        <div class="text-sm text-muted-foreground">
                                                                            Apakah Anda yakin ingin menghapus ransum
                                                                            ini? Tindakan ini tidak dapat dibatalkan.
                                                                        </div>
                                                                    </DialogHeader>
                                                                    <div class="flex justify-end gap-2">
                                                                        <DialogClose as-child>
                                                                            <Button variant="outline">Batal</Button>
                                                                        </DialogClose>
                                                                        <Link
                                                                            :href="route('rations.destroy', ration.id)"
                                                                            method="delete" as="button">
                                                                        <Button variant="destructive">Hapus</Button>
                                                                        </Link>
                                                                    </div>
                                                                </DialogContent>
                                                            </Dialog>
                                                        </TableCell>
                                                    </TableRow>
                                                </template>
                                                <TableRow v-if="rations.length === 0">
                                                    <TableCell colspan="5" class="px-6 py-4 text-center">
                                                        Belum ada data ransum.
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="history">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Riwayat Ransum</CardTitle>
                                    <CardDescription>
                                        Selalu dapatkan informasi akurat tentang ketersediaan stok pakan ternak
                                        Anda,
                                        sehingga Anda dapat membuat keputusan pembelian yang tepat.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    Belum ada riwayat ransum.
                                </CardContent>
                            </Card>
                        </TabsContent>
                        <TabsContent value="feed">
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        <div class="flex items-center justify-between">
                                            Pakan
                                            <div>
                                                <Link :href="route('herds.index')">
                                                <Button size="small" class="mr-4">Kandang</Button>
                                                </Link>
                                                <Link :href="route('rations.create')">
                                                <Button size="small">Catat sisa pakan</Button>
                                                </Link>
                                            </div>
                                        </div>
                                    </CardTitle>
                                    <CardDescription>
                                        Hindari pemborosan pakan dengan melacak sisa pakan secara detail, sehingga
                                        Anda
                                        dapat menghemat biaya dan meningkatkan efisiensi peternakan.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    Belum ada Pakan.
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
