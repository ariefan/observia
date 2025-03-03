<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { ImageIcon, UploadIcon, TrashIcon, XIcon } from 'lucide-vue-next';

interface Props {
    initialImage: string;
    aspectRatio: number;
}

const props = withDefaults(defineProps<Props>(), {
    initialImage: '',
    aspectRatio: 1,
});

const emit = defineEmits<{
    (e: 'update:image', blob: Blob | null): void;
    (e: 'upload', payload: { blob: Blob; url: string; dimensions: { width: number; height: number } }): void;
    (e: 'remove'): void;
}>();

const previewUrl = ref<string>(props.initialImage);
const cropperSrc = ref<string>('');
const showCropper = ref<boolean>(false);
const isDragging = ref<boolean>(false);
const cropperInstance = ref<Cropper | null>(null);
const cropperImage = ref<HTMLImageElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = (): void => {
    fileInput.value?.click();
};

const handleFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files ? target.files[0] : null;
    if (file) processFile(file);
    target.value = '';
};

const handleDrop = (event: DragEvent): void => {
    isDragging.value = false;
    const file = event.dataTransfer?.files[0];
    if (file && file.type.startsWith('image/')) {
        processFile(file);
    } else {
        console.error("Oh for fuck's sake, that's not an image!");
    }
};

const processFile = (file: File): void => {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }

    const reader = new FileReader();
    reader.onload = (e: ProgressEvent<FileReader>) => {
        if (e.target?.result && typeof e.target.result === 'string') {
            cropperSrc.value = e.target.result;
            showCropper.value = true;
            nextTick(initCropper);
        }
    };
    reader.onerror = () => {
        console.error("Well, shit. Failed to read the file.");
    };
    reader.readAsDataURL(file);
};

const initCropper = (): void => {
    if (cropperInstance.value) cropperInstance.value.destroy();
    if (cropperImage.value) {
        cropperInstance.value = new Cropper(cropperImage.value, {
            aspectRatio: props.aspectRatio,
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

const cropImage = (): void => {
    if (!cropperInstance.value) return;

    const canvas = cropperInstance.value.getCroppedCanvas({
        width: 256,
        height: 256,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob((blob) => {
        if (!blob) {
            console.error("Holy shit, blob creation failed!");
            return;
        }
        const url = URL.createObjectURL(blob);
        previewUrl.value = url;
        emit('update:image', blob);
        emit('upload', { blob, url, dimensions: { width: 256, height: 256 } });
        closeCropper();
    }, 'image/jpeg', 0.9);
};

const removePhoto = (): void => {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = '';
    emit('update:image', null);
    emit('remove');
};

const closeCropper = (): void => {
    showCropper.value = false;
    if (cropperInstance.value) {
        cropperInstance.value.destroy();
        cropperInstance.value = null;
    }
};

onUnmounted(() => {
    if (cropperInstance.value) cropperInstance.value.destroy();
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

onMounted(() => {
    if (props.initialImage) previewUrl.value = props.initialImage;
});
</script>

<template>
    <div class="flex flex-col gap-4 max-w-md">
        <!-- Avatar Preview with Integrated Upload Overlay -->
        <div class="relative w-32 h-32 rounded-full overflow-hidden border-4 border-primary/10 dark:border-primary/90 bg-muted flex items-center justify-center"
            :class="{ 'border-primary/30': isDragging }" @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop">
            <img v-if="previewUrl" :src="previewUrl" alt="Profile photo" class="w-full h-full object-cover" />
            <ImageIcon v-else class="w-12 h-12 text-muted-foreground" />

            <!-- Upload Button Overlay (fits the circle) -->
            <label for="photo-upload"
                class="absolute inset-0 flex items-center justify-center cursor-pointer bg-background/80 backdrop-blur-sm opacity-0 hover:opacity-100 transition-opacity rounded-full">
                <UploadIcon class="w-5 h-5 text-primary" />
            </label>
            <input ref="fileInput" type="file" id="photo-upload" class="hidden" accept="image/*"
                @change="handleFileChange" />
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button @click="triggerFileInput"
                class="inline-flex items-center justify-center rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-8 px-3">
                <UploadIcon class="w-4 h-4 mr-1" />
                Upload
            </button>
            <button v-if="previewUrl" @click="removePhoto"
                class="inline-flex items-center justify-center rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-8 px-3">
                <TrashIcon class="w-4 h-4 mr-1" />
                Remove
            </button>
        </div>

        <!-- Cropper Modal -->
        <div v-if="showCropper"
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-card rounded-lg shadow-lg max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium">Crop Profile Photo</h3>
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
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        Cancel
                    </button>
                    <button type="button" @click="cropImage"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
