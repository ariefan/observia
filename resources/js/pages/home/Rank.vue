<script setup lang="ts">
import LivestockDefault from "@/assets/livestock-default.png";

defineProps<{
    name?: string;
}>();
</script>

<template>
    <Card>
        <template #title>
            <div class="flex justify-between items-center md:text-lg text-base lg:text-xl">
                <span>Produksi Susu Terbaik</span>
                <span class="text-sm">Perah pagi & sore</span>
            </div>
        </template>
        <template #subtitle>Rank Ternak</template>
        <template #content>
            <div class="flex flex-col items-center gap-6">
                <!-- Ranking Cards -->
                <div class="flex justify-center gap-12 items-end">
                    <!-- 2nd -->
                    <div class="flex flex-col items-center gap-2">
                        <Avatar :image="LivestockDefault" class="border border-2 border-primary mr-2" size="xlarge"
                            shape="circle" />
                        <Badge class="!rounded-full">ID 0007</Badge>
                        <div class="text-xs">Angel</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-silver-bg rounded-t-xl w-16 h-28 justify-center pb-2 shadow-lg">
                            <div class="text-2xl font-semibold">2</div>
                            <div class="text-xs">5 Liter</div>
                        </div>
                    </div>

                    <!-- 1st -->
                    <div class="flex flex-col items-center gap-2">
                        <Avatar :image="LivestockDefault" class="border border-2 border-primary mr-2" size="xlarge"
                            shape="circle" />
                        <Badge class="!rounded-full">ID 0007</Badge>
                        <div class="text-xs">Angel</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-gold-bg bg-gradient-to-t rounded-t-xl w-16 h-36 justify-center pb-2">
                            <div class="text-2xl font-semibold">1</div>
                            <div class="text-xs">6 Liter</div>
                        </div>
                    </div>

                    <!-- 3rd -->
                    <div class="flex flex-col items-center gap-2">
                        <Avatar :image="LivestockDefault" class="border border-2 border-primary mr-2" size="xlarge"
                            shape="circle" />
                        <Badge class="!rounded-full">ID 0007</Badge>
                        <div class="text-xs">Angel</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-bronze-bg bg-gradient-to-t rounded-t-xl w-16 h-24 justify-center pb-2">
                            <div class="text-2xl font-semibold">3</div>
                            <div class="text-xs">4 Liter</div>
                        </div>
                    </div>
                </div>

                <p class="text-sm">Ternak dengan produksi susu terbaik ini memiliki tingkat
                    produktivitas
                    yang tinggi dari seluruh ternak, memberikan susu berkualitas tinggi untuk bisnis Anda.
                </p>

                <!-- View All Link -->
                <a href="#" class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua ternak produksi susu terbaik
                </a>
            </div>

        </template>
    </Card>

    <Card>
        <template #title>
            <div class="flex justify-between items-center md:text-lg text-base lg:text-xl">
                <span>Bobot terbaik</span>
                <span class="text-sm">Pengecekan hari ini</span>
            </div>
        </template>
        <template #subtitle>Rank Ternak</template>

        <template #content>
            <div class="flex flex-col items-center gap-6">
                <!-- Ranking Cards -->
                <div class="flex justify-center gap-12 items-end">
                    <!-- 2nd -->
                    <div class="flex flex-col items-center gap-2">
                        <Avatar :image="LivestockDefault" class="border border-2 border-primary mr-2" size="xlarge"
                            shape="circle" />
                        <Badge class="!rounded-full">ID 0007</Badge>
                        <div class="text-xs">Angel</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-silver-bg rounded-t-xl w-16 h-4 justify-center pb-2 shadow-lg">
                            <div class="text-2xl font-semibold">2</div>
                            <div class="text-xs">50 Kg</div>
                        </div>
                    </div>

                    <!-- 1st -->
                    <div class="flex flex-col items-center gap-2">
                        <Avatar :image="LivestockDefault" class="border border-2 border-primary mr-2" size="xlarge"
                            shape="circle" />
                        <Badge class="!rounded-full">ID 0007</Badge>
                        <div class="text-xs">Angel</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-gold-bg bg-gradient-to-t rounded-t-xl w-16 h-36 justify-center pb-2">
                            <div class="text-2xl font-semibold">1</div>
                            <div class="text-xs">60 Kg</div>
                        </div>
                    </div>

                    <!-- 3rd -->
                    <div class="flex flex-col items-center gap-2">
                        <Avatar :image="LivestockDefault" class="border border-2 border-primary mr-2" size="xlarge"
                            shape="circle" />
                        <Badge class="!rounded-full">ID 0007</Badge>
                        <div class="text-xs">Angel</div>
                        <div
                            class="flex flex-col text-white text-shadow items-center rank-bronze-bg bg-gradient-to-t rounded-t-xl w-16 h-24 justify-center pb-2">
                            <div class="text-2xl font-semibold">3</div>
                            <div class="text-xs">40 Kg</div>
                        </div>
                    </div>
                </div>

                <p class="text-sm">Ternak dengan produksi susu terbaik ini memiliki tingkat produktivitas
                    yang tinggi dari seluruh ternak, memberikan susu berkualitas tinggi untuk bisnis Anda.
                </p>

                <!-- View All Link -->
                <a href="#" class="flex items-center gap-1 text-sm text-teal-900 font-semibold hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M9.29 6.71a1 1 0 011.42 0L16 12l-5.29 5.29a1 1 0 01-1.42-1.42L13.17 12l-3.88-3.88a1 1 0 010-1.41z" />
                    </svg>
                    Lihat semua bobot ternak terbaik
                </a>
            </div>

        </template>
    </Card>
</template>


<style scoped>
.rank-gold-bg {
    background: linear-gradient(158.09deg,
            #ae9614 -17.83%,
            #ffe81c 23.34%,
            #9e5908 136.37%);
    height: 150px;
}

.rank-silver-bg {
    background: linear-gradient(145.86deg,
            #a3aeaf -1.81%,
            #eceeee 25.28%,
            #888e8e 95.36%);
    height: 125px;
}

.rank-bronze-bg {
    background: linear-gradient(151.76deg,
            #9f7156 -9.52%,
            #f7bf6c 26.03%,
            #7e5137 115.1%);
    height: 100px;
}

.text-shadow {
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
}
</style>
