<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TransaksiSidebar from '@/components/TransaksiSidebar.vue';

defineOptions({
    layout: AppLayout,
});

interface Props {
    payments: Array<any>;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="flex">
        <TransaksiSidebar current-route="transaksi.riwayat-pembayaran" />

        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Riwayat Pembayaran</h1>
                    <p class="text-muted-foreground">
                        Lihat riwayat pembayaran dan transaksi Anda
                    </p>
                </div>

                <div class="bg-white rounded-lg border p-8 text-center">
                    <div class="text-6xl mb-4">🧾</div>
                    <h3 class="text-xl font-semibold mb-2">Belum ada riwayat pembayaran</h3>
                    <p class="text-gray-600">
                        Riwayat pembayaran Anda akan muncul di sini setelah melakukan transaksi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>