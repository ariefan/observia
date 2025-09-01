<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const form = useForm({
  name: '',
  code: '',
  binomial_nomenclature: '',
});

const submit = () => {
  form.post(route('species.store'));
};
</script>

<template>

  <Head title="Tambah Spesies" />

  <AppLayout>
    <div class="max-w-5xl space-y-6">
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="sm" @click="router.visit(route('species.index'))">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tambah Spesies</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Tambahkan spesies baru ke sistem
          </p>
        </div>
      </div>

      <div>
        <Card>
          <CardHeader>
            <CardTitle>Informasi Spesies</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <div class="space-y-2">
                <Label for="name">Nama Spesies *</Label>
                <Input id="name" v-model="form.name" type="text" required
                  :class="{ 'border-red-500': form.errors.name }" />
                <div v-if="form.errors.name" class="text-sm text-red-600">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="code">Kode Spesies *</Label>
                <Input id="code" v-model="form.code" type="text" required placeholder="contoh: BOV untuk Bovine"
                  :class="{ 'border-red-500': form.errors.code }" />
                <div v-if="form.errors.code" class="text-sm text-red-600">
                  {{ form.errors.code }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="binomial_nomenclature">Nama Ilmiah</Label>
                <Input id="binomial_nomenclature" v-model="form.binomial_nomenclature" type="text"
                  placeholder="contoh: Bos taurus" :class="{ 'border-red-500': form.errors.binomial_nomenclature }" />
                <div v-if="form.errors.binomial_nomenclature" class="text-sm text-red-600">
                  {{ form.errors.binomial_nomenclature }}
                </div>
              </div>

              <div class="flex gap-4 pt-4">
                <Button type="submit" :disabled="form.processing">
                  {{ form.processing ? 'Menambahkan...' : 'Tambah Spesies' }}
                </Button>
                <Button type="button" variant="outline" @click="router.visit(route('species.index'))">
                  Batal
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>