<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
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
  <Head title="Create Species" />

  <AppHeaderLayout>
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="sm" @click="$inertia.visit(route('species.index'))">
        <ArrowLeft class="h-4 w-4 mr-2" />
        Back to Species
      </Button>
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Species</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Add a new species to the system
        </p>
      </div>
    </div>

    <div class="mt-6 max-w-2xl">
      <Card>
        <CardHeader>
          <CardTitle>Species Information</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="name">Species Name *</Label>
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
              <Label for="code">Species Code *</Label>
              <Input
                id="code"
                v-model="form.code"
                type="text"
                required
                placeholder="e.g., BOV for Bovine"
                :class="{ 'border-red-500': form.errors.code }"
              />
              <div v-if="form.errors.code" class="text-sm text-red-600">
                {{ form.errors.code }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="binomial_nomenclature">Binomial Nomenclature</Label>
              <Input
                id="binomial_nomenclature"
                v-model="form.binomial_nomenclature"
                type="text"
                placeholder="e.g., Bos taurus"
                :class="{ 'border-red-500': form.errors.binomial_nomenclature }"
              />
              <div v-if="form.errors.binomial_nomenclature" class="text-sm text-red-600">
                {{ form.errors.binomial_nomenclature }}
              </div>
            </div>

            <div class="flex gap-4 pt-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Creating...' : 'Create Species' }}
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="$inertia.visit(route('species.index'))"
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