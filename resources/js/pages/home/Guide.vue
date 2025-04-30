<script setup lang="ts">
import IconFeed from "@/assets/icon-feed.svg";
import IconBreed from "@/assets/icon-breed.svg";
import IconHealth from "@/assets/icon-health.svg";

defineProps<{
    name?: string;
}>();
</script>

<template>
    <Card>
        <template #title>
            <div class="flex justify-between items-center md:text-lg text-base lg:text-xl">
                Panduan Harian Peternak
            </div>
        </template>
        <template #content>
            <p class="mb-3">Hari ini, kami ingin membantu Anda memaksimalkan
                kesehatan dan produktivitas kambing Anda dengan daftar tugas harian yang
                terpersonalisasi.</p>
            <Divider />
            <div class="grid gap-2">
                <div class="flex flex-row text-sm">
                    <div class="flex justify-center items-center">
                        <Avatar shape="circle" class="!bg-teal-800 mr-2 !size-10">
                            <IconBreed class="w-6 h-6 text-white" />
                        </Avatar>
                    </div>
                    <div class="p-1 flex-auto">ID perkawinan KWN001 sudah masuk hari ke-61,
                        lakukan USG pada kambing betina di ID perkawinan tersebut</div>
                    <div class="p-1 text-primary font-semibold">08 March 2025</div>
                </div>
                <div class="flex flex-row text-sm">
                    <div class="flex justify-center items-center">
                        <Avatar shape="circle" class="!bg-teal-800 mr-2 !size-10">
                            <IconFeed class="w-6 h-6 text-white" />
                        </Avatar>
                    </div>
                    <div class="p-1 flex-auto">Waktunya untuk memberi pakan di kandang 1P.
                        Jangan
                        sampai ternakmu kekurangan gizi</div>
                    <div class="p-1 text-primary font-semibold">08 March 2025</div>
                </div>
                <div class="flex flex-row text-sm">
                    <div class="flex justify-center items-center">
                        <Avatar shape="circle" class="!bg-teal-800 mr-2 !size-10">
                            <IconHealth class="w-6 h-6 text-white" />
                        </Avatar>
                    </div>
                    <div class="p-1 flex-auto">Kambing AM0020 mengalami penurunan bobot 2Kg.
                        Segera lakukan cek kesehatan kambing tersebut</div>
                    <div class="p-1 text-primary font-semibold">08 March 2025</div>
                </div>
            </div>
        </template>
    </Card>
</template>
