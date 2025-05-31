<script setup lang="ts">
import { computed } from 'vue'
import GoatNode from './GoatNode.vue'

const props = defineProps<{ goat: any }>()

function renderGoat(goat: any): any {
  if (!goat) return null
  return {
    id: goat.id,
    name: goat.name,
    breed: goat.breed,
    image: goat.image,
    father: renderGoat(goat.father),
    mother: renderGoat(goat.mother),
  }
}

const tree = computed(() => renderGoat(props.goat))
</script>

<template>
  <div class="overflow-auto p-4">
    <div class="flex justify-center">
      <GoatNode :goat="tree" />
    </div>
  </div>
</template>
