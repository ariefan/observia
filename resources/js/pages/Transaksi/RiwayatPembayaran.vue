<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TransaksiSidebar from '@/components/TransaksiSidebar.vue';
import { Receipt, CreditCard, Wallet, QrCode, Building2, CheckCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineOptions({
    layout: AppLayout,
});

interface Payment {
    id: string;
    invoice_number: string;
    plan_name: string;
    billing_cycle: 'monthly' | 'annual';
    total: number;
    payment_method: string;
    payment_reference: string | null;
    paid_at: string;
    paid_by: string | null;
}

interface Props {
    payments: Payment[];
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getPaymentMethodConfig = (method: string) => {
    switch (method) {
        case 'bank_transfer':
            return { label: 'Transfer Bank', icon: Building2 };
        case 'e_wallet':
            return { label: 'E-Wallet', icon: Wallet };
        case 'qris':
            return { label: 'QRIS', icon: QrCode };
        case 'virtual_account':
            return { label: 'Virtual Account', icon: CreditCard };
        default:
            return { label: method, icon: CreditCard };
    }
};

const totalPaid = props.payments.reduce((sum, p) => sum + p.total, 0);
</script>

<template>
    <div class="flex flex-col lg:flex-row">
        <TransaksiSidebar current-route="transaksi.riwayat-pembayaran" />

        <div class="flex-1 p-3 sm:p-4 lg:p-6">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Riwayat Pembayaran</h1>
                    <p class="text-muted-foreground">
                        Lihat riwayat pembayaran dan transaksi langganan Anda
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="payments.length === 0" class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 p-8 text-center">
                    <Receipt class="h-16 w-16 mx-auto mb-4 text-slate-300 dark:text-slate-600" />
                    <h3 class="text-xl font-semibold mb-2 dark:text-slate-100">Belum ada riwayat pembayaran</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Riwayat pembayaran Anda akan muncul di sini setelah melakukan transaksi.
                    </p>
                    <Button as-child>
                        <a href="/transaksi/paket-layanan">Lihat Paket</a>
                    </Button>
                </div>

                <!-- Payment Summary -->
                <div v-else class="space-y-6">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Card>
                            <CardHeader class="pb-2">
                                <CardDescription>Total Transaksi</CardDescription>
                                <CardTitle class="text-2xl">{{ payments.length }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-xs text-muted-foreground">
                                    Semua pembayaran berhasil
                                </p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="pb-2">
                                <CardDescription>Total Pembayaran</CardDescription>
                                <CardTitle class="text-2xl text-teal-600 dark:text-teal-400">{{ formatCurrency(totalPaid) }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-xs text-muted-foreground">
                                    Akumulasi seluruh pembayaran
                                </p>
                            </CardContent>
                        </Card>
                        <Card class="sm:col-span-2 lg:col-span-1">
                            <CardHeader class="pb-2">
                                <CardDescription>Pembayaran Terakhir</CardDescription>
                                <CardTitle class="text-lg">
                                    {{ payments[0] ? formatDate(payments[0].paid_at) : '-' }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-xs text-muted-foreground">
                                    {{ payments[0]?.plan_name || '-' }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Payments Table -->
                    <div class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 overflow-hidden">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No. Invoice</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead class="hidden md:table-cell">Periode</TableHead>
                                    <TableHead class="text-right">Jumlah</TableHead>
                                    <TableHead class="hidden lg:table-cell">Metode</TableHead>
                                    <TableHead>Tanggal</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="payment in payments" :key="payment.id">
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <CheckCircle class="h-4 w-4 text-green-500 dark:text-green-400" />
                                            <span class="font-mono text-sm">{{ payment.invoice_number }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="font-medium">{{ payment.plan_name }}</div>
                                    </TableCell>
                                    <TableCell class="hidden md:table-cell">
                                        <Badge variant="outline">
                                            {{ payment.billing_cycle === 'annual' ? 'Tahunan' : 'Bulanan' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right font-medium text-teal-600 dark:text-teal-400">
                                        {{ formatCurrency(payment.total) }}
                                    </TableCell>
                                    <TableCell class="hidden lg:table-cell">
                                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                            <component
                                                :is="getPaymentMethodConfig(payment.payment_method).icon"
                                                class="h-4 w-4"
                                            />
                                            {{ getPaymentMethodConfig(payment.payment_method).label }}
                                        </div>
                                        <div v-if="payment.payment_reference" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                            Ref: {{ payment.payment_reference }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ formatDateTime(payment.paid_at) }}</div>
                                        <div v-if="payment.paid_by" class="text-xs text-slate-400 dark:text-slate-500">
                                            oleh {{ payment.paid_by }}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Download Receipt Note -->
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4">
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Butuh bukti pembayaran atau invoice? Hubungi tim support kami untuk mendapatkan dokumen resmi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
