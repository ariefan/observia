<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Species {
  id: string;
  name: string;
}

interface Breed {
  id: string;
  species_id: string;
  name: string;
  code: string;
  origin: string;
  description: string;
  species: Species;
}

interface Props {
  breed: Breed;
  species: Species[];
}

const props = defineProps<Props>();

const form = useForm({
  species_id: props.breed.species_id,
  name: props.breed.name,
  code: props.breed.code,
  origin: props.breed.origin || '',
  description: props.breed.description || '',
});

const submit = () => {
  form.put(route('breeds.update', props.breed.id));
};
</script>

<template>

  <Head title="Edit Ras" />

  <AppLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="sm" @click="$inertia.visit(route('breeds.index'))">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Ras</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Perbarui informasi {{ props.breed.name }}
          </p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Informasi Ras</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="species_id">Spesies *</Label>
              <Select v-model="form.species_id" required>
                <SelectTrigger :class="{ 'border-red-500': form.errors.species_id }">
                  <SelectValue placeholder="Pilih spesies" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="species in props.species" :key="species.id" :value="species.id">
                    {{ species.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.species_id" class="text-sm text-red-600">
                {{ form.errors.species_id }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="name">Nama Ras *</Label>
              <Input id="name" v-model="form.name" type="text" required
                :class="{ 'border-red-500': form.errors.name }" />
              <div v-if="form.errors.name" class="text-sm text-red-600">
                {{ form.errors.name }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="code">Kode Ras *</Label>
              <Input id="code" v-model="form.code" type="text" required
                :class="{ 'border-red-500': form.errors.code }" />
              <div v-if="form.errors.code" class="text-sm text-red-600">
                {{ form.errors.code }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="origin">Asal</Label>
              <Input id="origin" v-model="form.origin" type="text" :class="{ 'border-red-500': form.errors.origin }" />
              <div v-if="form.errors.origin" class="text-sm text-red-600">
                {{ form.errors.origin }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="description">Deskripsi</Label>
              <Textarea id="description" v-model="form.description" rows="3"
                :class="{ 'border-red-500': form.errors.description }" />
              <div v-if="form.errors.description" class="text-sm text-red-600">
                {{ form.errors.description }}
              </div>
            </div>

            <div class="flex gap-4 pt-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Memperbarui...' : 'Perbarui Ras' }}
              </Button>
              <Button type="button" variant="outline" @click="$inertia.visit(route('breeds.index'))">
                Batal
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>