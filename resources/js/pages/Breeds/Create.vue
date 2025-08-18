<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
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

interface Props {
  species: Species[];
}

const props = defineProps<Props>();

const form = useForm({
  species_id: '',
  name: '',
  code: '',
  origin: '',
  description: '',
});

const submit = () => {
  form.post(route('breeds.store'));
};
</script>

<template>
  <Head title="Create Breed" />

  <AppHeaderLayout>
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="sm" @click="$inertia.visit(route('breeds.index'))">
        <ArrowLeft class="h-4 w-4 mr-2" />
        Back to Breeds
      </Button>
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Breed</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Add a new breed to the system
        </p>
      </div>
    </div>

    <div class="mt-6 max-w-2xl">
      <Card>
        <CardHeader>
          <CardTitle>Breed Information</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="species_id">Species *</Label>
              <Select v-model="form.species_id" required>
                <SelectTrigger :class="{ 'border-red-500': form.errors.species_id }">
                  <SelectValue placeholder="Select species" />
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
              <Label for="name">Breed Name *</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                required
                :class="{ 'border-red-500': form.errors.name }"
              />
              <div v-if="form.errors.name" class="text-sm text-red-600">
                {{ form.errors.name }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="code">Breed Code *</Label>
              <Input
                id="code"
                v-model="form.code"
                type="text"
                required
                placeholder="e.g., HF for Holstein Friesian"
                :class="{ 'border-red-500': form.errors.code }"
              />
              <div v-if="form.errors.code" class="text-sm text-red-600">
                {{ form.errors.code }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="origin">Origin</Label>
              <Input
                id="origin"
                v-model="form.origin"
                type="text"
                placeholder="e.g., Netherlands, Germany"
                :class="{ 'border-red-500': form.errors.origin }"
              />
              <div v-if="form.errors.origin" class="text-sm text-red-600">
                {{ form.errors.origin }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                rows="3"
                placeholder="Brief description of the breed characteristics..."
                :class="{ 'border-red-500': form.errors.description }"
              />
              <div v-if="form.errors.description" class="text-sm text-red-600">
                {{ form.errors.description }}
              </div>
            </div>

            <div class="flex gap-4 pt-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Creating...' : 'Create Breed' }}
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="$inertia.visit(route('breeds.index'))"
              >
                Cancel
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppHeaderLayout>
</template>