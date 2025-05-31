<template>
    <div class="flex items-center space-x-8">
      <!-- ── CHILD NODE ── -->
      <div class="relative flex flex-col items-center mr-8">
        <Card class="p-2 w-[200px] h-[200px] text-center shadow-md border border-gray-300">
          <CardContent class="card-content">
            <Avatar class="mx-auto mb-1">
              <AvatarImage :src="goat.image" :alt="goat.name" />
            </Avatar>
            <div class="font-semibold">ID {{ goat.id }}</div>
            <div class="text-xs text-muted-foreground">{{ goat.breed }}</div>
            <div class="text-sm mt-1">{{ goat.name }}</div>
          </CardContent>
        </Card>
  
        <!-- horizontal line to parents -->
        <div
          v-if="goat.father || goat.mother"
          class="absolute top-1/2 left-full w-16 h-[3px] bg-gray-300 transform -translate-y-1/2"
        ></div>
      </div>
  
      <!-- ── PARENTS ── -->
      <div
        v-if="goat.father || goat.mother"
        class="flex flex-col justify-between ml-12 space-y-8"
      >
        <!-- Father branch -->
        <div class="relative flex items-center">
          <div
            class="absolute -left-16 top-1/2 w-[3px] h-16 bg-gray-300 transform -translate-y-1/2"
          ></div>
          <GoatNode v-if="goat.father" :goat="goat.father" />
        </div>
  
        <!-- Mother branch -->
        <div class="relative flex items-center">
          <div
            class="absolute -left-16 bottom-1/2 w-[3px] h-16 bg-gray-300 transform translate-y-1/2"
          ></div>
          <GoatNode v-if="goat.mother" :goat="goat.mother" />
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineProps } from 'vue'
  import GoatNode from './GoatNode.vue'
  import { Card, CardContent } from '@/components/ui/card'
  import { Avatar, AvatarImage } from '@/components/ui/avatar'
  
  defineProps<{ goat: any }>()
  </script>
  
  <style scoped>
  .card-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
  }
  </style>
  