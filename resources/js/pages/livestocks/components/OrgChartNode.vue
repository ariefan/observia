<template>
    <div class="flex flex-col items-center">
      <!-- Box for this node -->
      <div :class="['px-4 py-2 rounded shadow text-white', colorClass]">
        {{ node.name }}
      </div>
  
      <!-- Line to children -->
      <div v-if="hasChildren" class="h-6 w-px bg-gray-400"></div>
  
      <!-- Children -->
      <div v-if="hasChildren" class="flex space-x-6 mt-2">
        <OrgChartNode
          v-for="(child, index) in node.children"
          :key="index"
          :node="child"
        />
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
import { computed } from 'vue'

defineProps({
    node: {
      type: Object,
      required: true
    }
  })
  
  const hasChildren = computed(() => node.children && node.children.length > 0)
  
  const colorClass = computed(() => {
    if (node.role === 'CEO') return 'bg-blue-600'
    if (node.role?.includes('Manager')) return 'bg-green-600'
    return 'bg-yellow-500'
  })
  </script>
  