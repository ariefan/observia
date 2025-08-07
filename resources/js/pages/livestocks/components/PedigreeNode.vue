<template>
  <div :class="nodeClasses" class="relative">
    <Card :class="cardClasses" class="text-center shadow-md border border-gray-300 dark:border-gray-600">
      <CardContent class="p-3 flex flex-col items-center justify-center h-full">
        <div :class="avatarClasses" class="rounded-full overflow-hidden mb-2 flex items-center justify-center">
          <img 
            v-if="animal.photo && animal.photo.length > 0" 
            :src="getPhotoUrl(animal.photo[0])" 
            :alt="animal.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="bg-gradient-to-br from-primary/20 to-primary/10 w-full h-full flex items-center justify-center">
            <span class="text-primary font-semibold" :class="animal.sex === 'M' ? 'text-blue-600' : 'text-pink-600'">
              {{ animal.name?.charAt(0) || '?' }}
            </span>
          </div>
        </div>
        
        <div :class="textClasses">
          <div class="font-semibold truncate" :class="nameClasses">{{ animal.name || 'Tidak diketahui' }}</div>
          <div class="text-muted-foreground truncate" :class="breedClasses">{{ animal.breed_name || 'Unknown breed' }}</div>
          <div v-if="showDetails" class="flex items-center justify-center gap-1 mt-1">
            <Mars v-if="animal.sex === 'M'" class="text-blue-500 w-3 h-3" />
            <Venus v-else-if="animal.sex === 'F'" class="text-pink-500 w-3 h-3" />
            <span :class="idClasses">ID: {{ animal.id }}</span>
          </div>
        </div>
      </CardContent>
    </Card>
    
    <!-- Current animal indicator -->
    <div v-if="isCurrent" class="absolute -top-2 -right-2 bg-primary text-primary-foreground rounded-full w-6 h-6 flex items-center justify-center">
      <Award class="w-3 h-3" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Card, CardContent } from "@/components/ui/card";
import { Mars, Venus, Award } from "lucide-vue-next";

const props = defineProps({
  animal: {
    type: Object,
    required: true
  },
  isCurrent: {
    type: Boolean,
    default: false
  },
  generation: {
    type: Number,
    default: 0
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
});

const showDetails = computed(() => props.size !== 'sm');

const nodeClasses = computed(() => {
  const base = 'flex flex-col items-center';
  return base;
});

const cardClasses = computed(() => {
  const sizes = {
    sm: 'w-24 h-28',
    md: 'w-32 h-36', 
    lg: 'w-40 h-44'
  };
  return sizes[props.size] || sizes.md;
});

const avatarClasses = computed(() => {
  const sizes = {
    sm: 'w-8 h-8',
    md: 'w-12 h-12',
    lg: 'w-16 h-16'
  };
  return sizes[props.size] || sizes.md;
});

const textClasses = computed(() => {
  const sizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  };
  return sizes[props.size] || sizes.md;
});

const nameClasses = computed(() => {
  const sizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  };
  return sizes[props.size] || sizes.md;
});

const breedClasses = computed(() => {
  const sizes = {
    sm: 'text-xs',
    md: 'text-xs',
    lg: 'text-sm'
  };
  return sizes[props.size] || sizes.md;
});

const idClasses = computed(() => {
  const sizes = {
    sm: 'text-xs',
    md: 'text-xs',
    lg: 'text-xs'
  };
  return sizes[props.size] || sizes.md;
});

const getPhotoUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('public/')) {
    return `/storage/${path.substring(7)}`;
  }
  return `/storage/${path}`;
};
</script>