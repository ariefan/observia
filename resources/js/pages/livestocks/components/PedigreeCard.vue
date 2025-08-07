<template>
  <Card class="border border-primary/20 dark:border-primary/80">
    <CardContent class="p-4">
      <h3 class="font-semibold text-lg mb-4">Silsilah Keturunan (Pedigree)</h3>
      
      <div v-if="pedigreeData && pedigreeData.length > 0" class="overflow-x-auto">
        <div class="min-w-max">
          <!-- Current livestock (depth 0) -->
          <div class="flex justify-center mb-8">
            <PedigreeNode 
              v-for="animal in getCurrentGeneration(0)" 
              :key="animal.id" 
              :animal="animal" 
              :is-current="true" 
            />
          </div>
          
          <!-- Parents (depth 1) -->
          <div v-if="hasGeneration(1)" class="flex justify-center mb-8 space-x-16">
            <div class="text-center">
              <p class="text-xs text-muted-foreground mb-2">Induk Jantan</p>
              <PedigreeNode 
                v-for="animal in getParentsByGender(1, 'M')" 
                :key="animal.id" 
                :animal="animal" 
                :generation="1"
              />
            </div>
            <div class="text-center">
              <p class="text-xs text-muted-foreground mb-2">Induk Betina</p>
              <PedigreeNode 
                v-for="animal in getParentsByGender(1, 'F')" 
                :key="animal.id" 
                :animal="animal" 
                :generation="1"
              />
            </div>
          </div>
          
          <!-- Grandparents (depth 2) -->
          <div v-if="hasGeneration(2)" class="flex justify-center mb-8 space-x-8">
            <div class="text-center">
              <p class="text-xs text-muted-foreground mb-2">Kakek</p>
              <div class="grid grid-cols-2 gap-4">
                <PedigreeNode 
                  v-for="animal in getGrandparentsByType(2, 'paternal', 'M')" 
                  :key="animal.id" 
                  :animal="animal" 
                  :generation="2"
                  :size="'sm'"
                />
                <PedigreeNode 
                  v-for="animal in getGrandparentsByType(2, 'maternal', 'M')" 
                  :key="animal.id" 
                  :animal="animal" 
                  :generation="2"
                  :size="'sm'"
                />
              </div>
            </div>
            <div class="text-center">
              <p class="text-xs text-muted-foreground mb-2">Nenek</p>
              <div class="grid grid-cols-2 gap-4">
                <PedigreeNode 
                  v-for="animal in getGrandparentsByType(2, 'paternal', 'F')" 
                  :key="animal.id" 
                  :animal="animal" 
                  :generation="2"
                  :size="'sm'"
                />
                <PedigreeNode 
                  v-for="animal in getGrandparentsByType(2, 'maternal', 'F')" 
                  :key="animal.id" 
                  :animal="animal" 
                  :generation="2"
                  :size="'sm'"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-8 text-muted-foreground">
        <TreeDeciduous class="h-12 w-12 mx-auto mb-4 opacity-50" />
        <p class="text-sm">Data silsilah tidak tersedia</p>
        <p class="text-xs">Hubungan keturunan belum diatur</p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import { Card, CardContent } from "@/components/ui/card";
import { TreeDeciduous } from "lucide-vue-next";
import PedigreeNode from './PedigreeNode.vue';

const props = defineProps({
  pedigreeData: {
    type: Array,
    default: () => []
  }
});

const getCurrentGeneration = (depth) => {
  return props.pedigreeData.filter(animal => animal.depth === depth);
};

const hasGeneration = (depth) => {
  return props.pedigreeData.some(animal => animal.depth === depth);
};

const getParentsByGender = (depth, gender) => {
  return props.pedigreeData.filter(animal => 
    animal.depth === depth && animal.sex === gender
  );
};

const getGrandparentsByType = (depth, parentType, gender) => {
  // This is a simplified approach - in a real implementation you'd need 
  // more complex logic to determine paternal vs maternal grandparents
  return props.pedigreeData.filter(animal => 
    animal.depth === depth && animal.sex === gender
  ).slice(0, 1); // Just show one for now
};
</script>