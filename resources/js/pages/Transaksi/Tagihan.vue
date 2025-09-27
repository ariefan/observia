<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TransaksiSidebar from '@/components/TransaksiSidebar.vue';

defineOptions({
    layout: AppLayout,
});

interface Props {
    bills: Array<any>;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="flex">
        <TransaksiSidebar current-route="transaksi.tagihan" />

        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Tagihan</h1>
                    <p class="text-muted-foreground">
                        Kelola tagihan dan pembayaran Anda
                    </p>
                </div>

                <div class="bg-white rounded-lg border p-8 text-center">
                    <div class="text-6xl mb-4">📄</div>
                    <h3 class="text-xl font-semibold mb-2">Belum ada tagihan</h3>
                    <p class="text-gray-600">
                        Tagihan Anda akan muncul di sini setelah berlangganan paket.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>