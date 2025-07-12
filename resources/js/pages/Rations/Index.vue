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

const props = defineProps({
    rations: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const totalFeeds = (rationItems) => {
    return rationItems.length;
};

const totalCost = (rationItems) => {
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

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <Tabs default-value="ration" class="w-full">
                        <TabsList class="grid w-full grid-cols-6">
                            <TabsTrigger class="text-primary" value="ration">
                                Stok Ransum
                            </TabsTrigger>
                            <TabsTrigger class="text-primary" value="history">
                                Riwayat Ransum
                            </TabsTrigger>
                            <TabsTrigger class="text-primary" value="feed">
                                Pakan
                            </TabsTrigger>
                        </TabsList>
                        <TabsContent value="ration">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Stok Ransum</CardTitle>
                                    <CardDescription>
                                        Pastikan ketersediaan pakan selalu terjaga. Hindari kehabisan pakan yang dapat
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
                                                            <Link :href="route('rations.edit', ration.id)"
                                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                                            Edit
                                                            </Link>
                                                            <Link :href="route('rations.destroy', ration.id)"
                                                                method="delete" as="button"
                                                                class="font-medium text-red-600 dark:text-red-500 hover:underline ml-4">
                                                            Hapus
                                                            </Link>
                                                        </TableCell>
                                                    </TableRow>
                                                </template>
                                                <TableRow v-if="rations.length === 0">
                                                    <TableCell colspan="4" class="px-6 py-4 text-center">
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
                                        Selalu dapatkan informasi akurat tentang ketersediaan stok pakan ternak Anda,
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
                                        Hindari pemborosan pakan dengan melacak sisa pakan secara detail, sehingga Anda
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
