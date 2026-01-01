<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TransaksiSidebar from '@/components/TransaksiSidebar.vue';
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { FileText, Clock, CheckCircle, AlertCircle, XCircle, Upload, CreditCard } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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

defineOptions({
    layout: AppLayout,
});

interface Invoice {
    id: string;
    invoice_number: string;
    plan_name: string;
    billing_cycle: 'monthly' | 'annual';
    subtotal: number;
    discount: number;
    tax: number;
    total: number;
    status: 'pending' | 'paid' | 'cancelled' | 'refunded' | 'overdue';
    due_date: string;
    paid_at: string | null;
    is_overdue: boolean;
    created_at: string;
}

interface Props {
    invoices: Invoice[];
}

const props = defineProps<Props>();

const showPaymentDialog = ref(false);
const selectedInvoice = ref<Invoice | null>(null);
const paymentProofFile = ref<File | null>(null);

const form = useForm({
    payment_method: '',
    payment_reference: '',
    payment_proof: null as File | null,
});

const pendingInvoices = computed(() =>
    props.invoices.filter(inv => inv.status === 'pending' || inv.is_overdue)
);

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

const getStatusConfig = (invoice: Invoice) => {
    if (invoice.is_overdue) {
        return { label: 'Jatuh Tempo', variant: 'destructive' as const, icon: AlertCircle };
    }
    switch (invoice.status) {
        case 'pending':
            return { label: 'Menunggu Pembayaran', variant: 'warning' as const, icon: Clock };
        case 'paid':
            return { label: 'Lunas', variant: 'success' as const, icon: CheckCircle };
        case 'cancelled':
            return { label: 'Dibatalkan', variant: 'secondary' as const, icon: XCircle };
        case 'refunded':
            return { label: 'Dikembalikan', variant: 'outline' as const, icon: XCircle };
        default:
            return { label: invoice.status, variant: 'secondary' as const, icon: Clock };
    }
};

const openPaymentDialog = (invoice: Invoice) => {
    selectedInvoice.value = invoice;
    form.reset();
    paymentProofFile.value = null;
    showPaymentDialog.value = true;
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        paymentProofFile.value = target.files[0];
        form.payment_proof = target.files[0];
    }
};

const submitPayment = () => {
    if (!selectedInvoice.value) return;

    form.post(`/transaksi/invoices/${selectedInvoice.value.id}/pay`, {
        forceFormData: true,
        onSuccess: () => {
            showPaymentDialog.value = false;
            selectedInvoice.value = null;
            form.reset();
        },
    });
};

const paymentMethods = [
    { value: 'bank_transfer', label: 'Transfer Bank' },
    { value: 'e_wallet', label: 'E-Wallet (GoPay, OVO, Dana)' },
    { value: 'qris', label: 'QRIS' },
    { value: 'virtual_account', label: 'Virtual Account' },
];
</script>

<template>
    <div class="flex flex-col lg:flex-row">
        <TransaksiSidebar current-route="transaksi.tagihan" :pending-count="pendingInvoices.length" />

        <div class="flex-1 p-3 sm:p-4 lg:p-6">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Tagihan</h1>
                    <p class="text-muted-foreground">
                        Kelola tagihan dan pembayaran langganan Anda
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="invoices.length === 0" class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 p-8 text-center">
                    <FileText class="h-16 w-16 mx-auto mb-4 text-slate-300 dark:text-slate-600" />
                    <h3 class="text-xl font-semibold mb-2 dark:text-slate-100">Belum ada tagihan</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Tagihan Anda akan muncul di sini setelah berlangganan paket berbayar.
                    </p>
                    <Button as-child>
                        <a href="/transaksi/paket-layanan">Lihat Paket</a>
                    </Button>
                </div>

                <!-- Invoices Table -->
                <div v-else class="bg-white dark:bg-slate-900 rounded-lg border dark:border-slate-800 overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No. Invoice</TableHead>
                                <TableHead>Paket</TableHead>
                                <TableHead class="hidden md:table-cell">Periode</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="hidden lg:table-cell">Jatuh Tempo</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="invoice in invoices" :key="invoice.id">
                                <TableCell class="font-mono text-sm">
                                    {{ invoice.invoice_number }}
                                </TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ invoice.plan_name }}</div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <Badge variant="outline">
                                        {{ invoice.billing_cycle === 'annual' ? 'Tahunan' : 'Bulanan' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right font-medium">
                                    <div>{{ formatCurrency(invoice.total) }}</div>
                                    <div v-if="invoice.discount > 0" class="text-xs text-teal-600 dark:text-teal-400">
                                        Hemat {{ formatCurrency(invoice.discount) }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="getStatusConfig(invoice).variant"
                                        class="flex items-center gap-1 w-fit"
                                    >
                                        <component :is="getStatusConfig(invoice).icon" class="h-3 w-3" />
                                        {{ getStatusConfig(invoice).label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="hidden lg:table-cell text-sm text-slate-600 dark:text-slate-400">
                                    {{ formatDate(invoice.due_date) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button
                                        v-if="invoice.status === 'pending' || invoice.is_overdue"
                                        size="sm"
                                        @click="openPaymentDialog(invoice)"
                                        class="bg-teal-600 hover:bg-teal-700"
                                    >
                                        <CreditCard class="h-4 w-4 mr-1" />
                                        Bayar
                                    </Button>
                                    <span v-else-if="invoice.status === 'paid'" class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ formatDate(invoice.paid_at!) }}
                                    </span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Payment Info -->
                <div v-if="invoices.length > 0" class="mt-6 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/50 p-4">
                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Informasi Pembayaran</h4>
                    <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                        <p>Transfer ke rekening berikut:</p>
                        <ul class="list-disc list-inside ml-2">
                            <li>Bank BCA: 1234567890 a.n. PT Aifarm Indonesia</li>
                            <li>Bank Mandiri: 0987654321 a.n. PT Aifarm Indonesia</li>
                        </ul>
                        <p class="mt-2">Atau scan QRIS yang tersedia di halaman pembayaran.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Dialog -->
    <Dialog v-model:open="showPaymentDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Konfirmasi Pembayaran</DialogTitle>
                <DialogDescription>
                    Masukkan detail pembayaran untuk invoice {{ selectedInvoice?.invoice_number }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitPayment" class="space-y-4">
                <!-- Invoice Summary -->
                <div v-if="selectedInvoice" class="rounded-lg border dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Paket</span>
                        <span class="font-medium dark:text-slate-200">{{ selectedInvoice.plan_name }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-slate-600 dark:text-slate-400">Total</span>
                        <span class="font-bold text-teal-600 dark:text-teal-400">{{ formatCurrency(selectedInvoice.total) }}</span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="space-y-2">
                    <Label>Metode Pembayaran</Label>
                    <Select v-model="form.payment_method">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih metode pembayaran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="method in paymentMethods"
                                :key="method.value"
                                :value="method.value"
                            >
                                {{ method.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.payment_method" class="text-sm text-red-500">
                        {{ form.errors.payment_method }}
                    </p>
                </div>

                <!-- Payment Reference -->
                <div class="space-y-2">
                    <Label>Nomor Referensi (Opsional)</Label>
                    <Input
                        v-model="form.payment_reference"
                        placeholder="Contoh: nomor transfer atau ID transaksi"
                    />
                </div>

                <!-- Payment Proof -->
                <div class="space-y-2">
                    <Label>Bukti Pembayaran (Opsional)</Label>
                    <div class="flex items-center gap-2">
                        <Input
                            type="file"
                            accept="image/*,.pdf"
                            @change="handleFileChange"
                            class="flex-1"
                        />
                    </div>
                    <p v-if="paymentProofFile" class="text-sm text-slate-600 dark:text-slate-400">
                        File: {{ paymentProofFile.name }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Format: JPG, PNG, PDF. Maks 5MB</p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showPaymentDialog = false">
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.payment_method"
                        class="bg-teal-600 hover:bg-teal-700"
                    >
                        {{ form.processing ? 'Memproses...' : 'Konfirmasi Pembayaran' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
