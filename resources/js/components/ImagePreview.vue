<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';
import { Button } from '@/components/ui/button';
import { Image } from 'lucide-vue-next';
import { usePhotoUrl } from '@/composables/usePhotoUrl';

interface Props {
  photos: string[];
  triggerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  triggerClass: ''
});


const open = ref(false);
const { getPhotoUrl } = usePhotoUrl();
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        :class="props.triggerClass"
        @click.stop="open = true"
      >
        <Image class="h-4 w-4" />
      </Button>
    </DialogTrigger>
    <DialogContent class="max-w-5xl w-full p-0">
      <div class="relative w-full">
        <Carousel class="w-full" :opts="{ align: 'center' }">
          <CarouselContent>
            <CarouselItem v-for="(photo, index) in props.photos" :key="index">
              <div class="flex justify-center p-4">
                <img
                  :src="getPhotoUrl(photo)"
                  :alt="`Image ${index + 1}`"
                  class="max-h-[80vh] max-w-full object-contain rounded-lg"
                />
              </div>
            </CarouselItem>
          </CarouselContent>
          <CarouselPrevious v-if="props.photos.length > 1" class="left-4" />
          <CarouselNext v-if="props.photos.length > 1" class="right-4" />
        </Carousel>
      </div>
    </DialogContent>
  </Dialog>
</template>