<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { ImageIcon, UploadIcon, TrashIcon, XIcon } from 'lucide-vue-next';

const props = defineProps<{
    imageUrl?: string;
    imageBlob?: Blob | null;
    aspectRatio?: number;
}>();

const emit = defineEmits<{
    'update:imageBlob': [value: Blob | null];
    'update:imageUrl': [value: string];
}>();

const previewUrl = ref(props.imageUrl || '');
const cropperSrc = ref('');
const showCropper = ref(false);
const isDragging = ref(false);
const cropperInstance = ref<Cropper | null>(null);
const cropperImage = ref<HTMLImageElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = () => fileInput.value?.click();

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) processFile(file);
    target.value = '';
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files[0];
    if (file?.type.startsWith('image/')) processFile(file);
};

const processFile = (file: File) => {
    if (previewUrl.value.startsWith('blob:')) URL.revokeObjectURL(previewUrl.value);

    const reader = new FileReader();
    reader.onload = (e) => {
        if (typeof e.target?.result === 'string') {
            cropperSrc.value = e.target.result;
            showCropper.value = true;
            nextTick(initCropper);
        }
    };
    reader.readAsDataURL(file);
};

const initCropper = () => {
    cropperInstance.value?.destroy();
    if (cropperImage.value) {
        cropperInstance.value = new Cropper(cropperImage.value, {
            aspectRatio: props.aspectRatio || 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }
};

const cropImage = () => {
    if (!cropperInstance.value) return;

    const canvas = cropperInstance.value.getCroppedCanvas({
        width: 256,
        height: 256,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob((blob) => {
        if (!blob) return;
        const url = URL.createObjectURL(blob);
        previewUrl.value = url;
        emit('update:imageBlob', blob);
        emit('update:imageUrl', url);
        closeCropper();
    }, 'image/jpeg', 0.9);
};

const removePhoto = () => {
    if (previewUrl.value.startsWith('blob:')) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = '';
    emit('update:imageBlob', null);
    emit('update:imageUrl', '');
};

const closeCropper = () => {
    showCropper.value = false;
    cropperInstance.value?.destroy();
    cropperInstance.value = null;
};

watch(() => props.imageUrl, (val) => {
    if (val) previewUrl.value = val;
});

onUnmounted(() => {
    cropperInstance.value?.destroy();
    if (previewUrl.value.startsWith('blob:')) URL.revokeObjectURL(previewUrl.value);
});
</script>

<template>
    <div class="flex flex-col gap-4 max-w-md">
        <!-- Avatar -->
        <div class="relative w-32 h-32 rounded-full overflow-hidden border-4 border-primary/10 bg-muted flex items-center justify-center group"
            :class="{ 'border-primary/30': isDragging }" @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop">
            <img v-if="previewUrl" :src="previewUrl" alt="Profile photo" class="w-full h-full object-cover" />
            <ImageIcon v-else class="w-12 h-12 text-muted-foreground" />

            <!-- Hover Overlay -->
            <div
                class="absolute inset-0 flex items-center justify-center bg-background/80 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity rounded-full gap-2">
                <button type="button" @click="triggerFileInput" class="text-primary hover:scale-110 transition">
                    <UploadIcon class="w-5 h-5" />
                </button>
                <button type="button" v-if="previewUrl" @click="removePhoto"
                    class="text-destructive hover:scale-110 transition">
                    <TrashIcon class="w-5 h-5" />
                </button>
            </div>
            <input ref="fileInput" type="file" class="hidden" accept="image/*" @change="handleFileChange" />
        </div>

        <!-- Cropper Modal -->
        <div v-if="showCropper"
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-card rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium">Crop Image</h3>
                    <button type="button" @click="closeCropper" class="text-muted-foreground hover:text-foreground">
                        <XIcon class="w-5 h-5" />
                    </button>
                </div>
                <div class="relative aspect-square overflow-hidden bg-muted rounded-md">
                    <img v-if="cropperSrc" ref="cropperImage" :src="cropperSrc" alt="Image to crop"
                        class="max-w-full" />
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeCropper"
                        class="bg-muted px-4 py-2 rounded-md hover:bg-muted/50">Cancel</button>
                    <button type="button" @click="cropImage"
                        class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary/90">Apply</button>
                </div>
            </div>
        </div>
    </div>
</template>
