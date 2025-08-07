<template>
  <div class="p-8 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 mb-8 text-center">Pedigree</h1>

      <div class="flex items-start justify-center overflow-x-auto">
        <!-- Level 1 - Root -->
        <div class="relative flex items-center">
          <!-- Root Node Card -->
          <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24 flex items-center space-x-1">
            <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center">
              <img v-if="orgData.avatar" :src="getPhotoUrl(orgData.avatar)" :alt="orgData.name"
                class="size-10 rounded-full object-cover" @error="handleImageError" />
              <ImageOff v-else class="w-6 h-6 text-gray-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 
                :class="[
                  'text-xs font-semibold truncate',
                  orgData.isClickable 
                    ? 'text-blue-600 hover:text-blue-800 cursor-pointer hover:underline' 
                    : 'text-gray-900'
                ]"
                @click="navigateToLivestock(orgData)"
              >
                {{ orgData.name }}
              </h3>
              <p class="text-xs text-gray-600 truncate">{{ orgData.title }}</p>
            </div>
          </div>

          <template v-if="orgData.children && orgData.children.length > 0">
            <!-- Horizontal line from root to level 2 -->
            <div class="w-8 h-px bg-gray-300"></div>

            <!-- Vertical connector for level 2 nodes -->
            <div v-if="orgData.children.length > 1" class="absolute bg-gray-300 w-px" :style="{
              left: '224px',
              height: `${(orgData.children.length - 1) * 248}px`,
              top: `${108}px`
            }"></div>

            <!-- Level 2 nodes -->
            <div class="ml-0">
              <div class="flex flex-col space-y-8">
                <div v-for="(level2Node, index) in orgData.children" :key="level2Node.id"
                  class="relative flex items-center">
                  <!-- Horizontal connector from vertical line to level 2 node -->
                  <div class="w-8 h-px bg-gray-300"></div>

                  <!-- Level 2 Node Card -->
                  <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24 flex items-center space-x-1">
                    <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center">
                      <img v-if="level2Node.avatar" :src="getPhotoUrl(level2Node.avatar)" :alt="level2Node.name"
                        class="size-10 rounded-full object-cover" @error="handleImageError" />
                      <ImageOff v-else class="w-6 h-6 text-gray-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 
                        :class="[
                          'text-xs font-semibold truncate',
                          level2Node.isClickable 
                            ? 'text-blue-600 hover:text-blue-800 cursor-pointer hover:underline' 
                            : 'text-gray-900'
                        ]"
                        @click="navigateToLivestock(level2Node)"
                      >
                        {{ level2Node.name }}
                      </h3>
                      <p class="text-xs text-gray-600 truncate">{{ level2Node.title }}</p>
                    </div>
                  </div>

                  <template v-if="level2Node.children && level2Node.children.length > 0">
                    <!-- Horizontal line from level 2 to level 3 -->
                    <div class="w-8 h-px bg-gray-300"></div>

                    <!-- Vertical connector for multiple level 3 nodes -->
                    <div v-if="level2Node.children.length > 1" class="absolute bg-gray-300 w-px" :style="{
                      left: '256px',
                      height: `${(level2Node.children.length - 1) * 120}px`,
                      top: `${48}px`
                    }"></div>

                    <!-- Level 3 nodes -->
                    <div class="flex flex-col space-y-6">
                      <div v-for="level3Node in level2Node.children" :key="level3Node.id"
                        class="relative flex items-center">
                        <!-- Horizontal connector from vertical line to level 3 node -->
                        <div class="w-8 h-px bg-gray-300"></div>

                        <!-- Level 3 Node Card -->
                        <div
                          class="bg-white border border-gray-200 rounded-lg shadow-sm p-2 w-48 h-24 flex items-center space-x-1">
                          <div class="size-10 bg-blue-100 rounded-full flex-shrink-0 flex items-center justify-center">
                            <img v-if="level3Node.avatar" :src="getPhotoUrl(level3Node.avatar)" :alt="level3Node.name"
                              class="size-10 rounded-full object-cover" @error="handleImageError" />
                            <ImageOff v-else class="w-6 h-6 text-gray-400" />
                          </div>
                          <div class="flex-1 min-w-0">
                            <h3 
                              :class="[
                                'text-xs font-semibold truncate',
                                level3Node.isClickable 
                                  ? 'text-blue-600 hover:text-blue-800 cursor-pointer hover:underline' 
                                  : 'text-gray-900'
                              ]"
                              @click="navigateToLivestock(level3Node)"
                            >
                              {{ level3Node.name }}
                            </h3>
                            <p class="text-xs text-gray-600 truncate">{{ level3Node.title }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { ImageOff } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface OrgNode {
  id: string
  name: string
  title: string
  avatar?: string
  children?: OrgNode[]
  isClickable?: boolean
}

const props = defineProps<{
  pedigreeData?: any[]
}>()

const orgData = computed<OrgNode>(() => {
  // Find current animal (depth 0)
  const currentAnimal = props.pedigreeData?.find(animal => animal.depth === 0)

  // Find parents (depth 1)  
  const parents = props.pedigreeData?.filter(animal => animal.depth === 1) || []

  // Find grandparents (depth 2)
  const grandparents = props.pedigreeData?.filter(animal => animal.depth === 2) || []

  // Always create 2 parents (even if data doesn't exist)
  const parentNodes = [0, 1].map(index => {
    const parent = parents[index]

    // Always create 2 grandparents per parent (4 total)
    const grandparentNodes = [0, 1].map(gpIndex => {
      const actualGpIndex = index * 2 + gpIndex
      const grandparent = grandparents[actualGpIndex]

      return {
        id: grandparent?.id || `gp-${actualGpIndex}`,
        name: grandparent?.name || `Kakek/Nenek ${actualGpIndex + 1}`,
        title: grandparent?.breed_name || 'Unknown',
        avatar: grandparent?.photo && grandparent.photo.length > 0 ? grandparent.photo[0] : '',
        isClickable: !!grandparent
      }
    })

    return {
      id: parent?.id || `parent-${index}`,
      name: parent?.name || (index === 0 ? 'Ayah' : 'Ibu'),
      title: parent?.breed_name || 'Unknown',
      avatar: parent?.photo && parent.photo.length > 0 ? parent.photo[0] : '',
      children: grandparentNodes,
      isClickable: !!parent
    }
  })

  return {
    id: currentAnimal?.id || 'current',
    name: currentAnimal?.name || 'Current Animal',
    title: currentAnimal?.breed_name || 'Unknown',
    avatar: currentAnimal?.photo && currentAnimal.photo.length > 0 ? currentAnimal.photo[0] : '',
    children: parentNodes,
    isClickable: !!currentAnimal
  }
})

const getPhotoUrl = (path: string) => {
  if (!path) return ''
  if (path.startsWith('public/')) {
    return `/storage/${path.substring(7)}`
  }
  return `/storage/${path}`
}

const navigateToLivestock = (node: OrgNode) => {
  if (node.isClickable && node.id) {
    router.visit(`/livestocks/${node.id}`)
  }
}

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCA0OCA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjQiIGN5PSIyNCIgcj0iMjQiIGZpbGw9IiNGM0Y0RjYiLz4KPGNpcmNsZSBjeD0iMjQiIGN5PSIyMCIgcj0iOCIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMTAgMzZjMC02LjYyNyA1LjM3My0xMiAxMi0xMmg0YzYuNjI3IDAgMTIgNS4zNzMgMTIgMTJ2NCIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K'
}
</script>

<style scoped>
/* Additional styles if needed */
</style>