<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Species {
  id: string;
  name: string;
  code: string;
  binomial_nomenclature: string;
}

interface Props {
  species: Species;
}

const props = defineProps<Props>();

const form = useForm({
  name: props.species.name,
  code: props.species.code,
  binomial_nomenclature: props.species.binomial_nomenclature,
});

const submit = () => {
  form.put(route('species.update', props.species.id));
};
</script>

<template>

  <Head title="Edit Spesies" />

  <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="sm" @click="$inertia.visit(route('species.index'))">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Spesies</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Perbarui informasi {{ props.species.name }}
          </p>
        </div>
      </div>

      <div class="max-w-2xl">
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
                <Input id="code" v-model="form.code" type="text" required
                  :class="{ 'border-red-500': form.errors.code }" />
                <div v-if="form.errors.code" class="text-sm text-red-600">
                  {{ form.errors.code }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="binomial_nomenclature">Nama Ilmiah</Label>
                <Input id="binomial_nomenclature" v-model="form.binomial_nomenclature" type="text"
                  :class="{ 'border-red-500': form.errors.binomial_nomenclature }" />
                <div v-if="form.errors.binomial_nomenclature" class="text-sm text-red-600">
                  {{ form.errors.binomial_nomenclature }}
                </div>
              </div>

              <div class="flex gap-4 pt-4">
                <Button type="submit" :disabled="form.processing">
                  {{ form.processing ? 'Memperbarui...' : 'Perbarui Spesies' }}
                </Button>
                <Button type="button" variant="outline" @click="$inertia.visit(route('species.index'))">
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