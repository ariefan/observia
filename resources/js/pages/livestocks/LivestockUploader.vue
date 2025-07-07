<script setup lang="ts">
import { ref, watch } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import Example3 from '@/assets/example-3.jpg';
import Example4 from '@/assets/example-4.jpg';

interface UploadedImage {
    file: File
    url: string
}

const props = defineProps<{
    modelValue: File[]
}>()

const emit = defineEmits(['update:modelValue'])

const uploadedImages = ref<UploadedImage[]>([])
const fileInput = ref<HTMLInputElement>()
const errorMessage = ref('')

const MAX_FILE_SIZE = 20 * 1024 * 1024 // 20MB
const MAX_FILES = 5
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png']

const triggerFileInput = () => {
    fileInput.value?.click()
}

const validateFile = (file: File): boolean => {
    if (!ALLOWED_TYPES.includes(file.type)) {
        errorMessage.value = 'Format file harus .jpg, .jpeg, atau .png'
        return false
    }

    if (file.size > MAX_FILE_SIZE) {
        errorMessage.value = 'Ukuran file maksimal 20MB'
        return false
    }

    return true
}

const addImages = (files: FileList | null) => {
    if (!files) return

    errorMessage.value = ''
    const fileArray = Array.from(files)

    if (uploadedImages.value.length + fileArray.length > MAX_FILES) {
        errorMessage.value = `Maksimal ${MAX_FILES} foto yang dapat diupload`
        return
    }

    fileArray.forEach(file => {
        if (validateFile(file)) {
            const imageUrl = URL.createObjectURL(file)
            uploadedImages.value.push({
                file,
                url: imageUrl
            })
        }
    })

    emit('update:modelValue', uploadedImages.value.map(img => img.file))
}

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    addImages(target.files)
    target.value = '' // Reset input
}

const handleDrop = (event: DragEvent) => {
    addImages(event.dataTransfer?.files || null)
}

const removeImage = (index: number) => {
    // Revoke object URL to prevent memory leaks
    URL.revokeObjectURL(uploadedImages.value[index].url)
    uploadedImages.value.splice(index, 1)
    emit('update:modelValue', uploadedImages.value.map(img => img.file))
}

watch(() => props.modelValue, (newVal) => {
    if (!newVal || newVal.length === 0) {
        uploadedImages.value.forEach(img => URL.revokeObjectURL(img.url))
        uploadedImages.value = []
    }
})
</script>

<template>
    <div class="max-w-7xl mx-auto mb-2">
        <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
            <!-- Left Side - Upload Guidelines -->
            <div class="space-y-2 col-span-3">
                <div class="bg-white rounded-lg p-6 shadow-sm border">
                    <h3 class="text-lg font-semibold mb-2">Panduan Unggah Foto</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                        <li>Foto dari dari berbagai sisi: depan, belakang, samping.</li>
                        <li>Pastikan pencahayaan cukup dan gambar tidak buram.</li>
                        <li>Ukuran file maksimal 20MB per foto.</li>
                        <li>Format file yang diizinkan: .jpg, .jpeg, .png.</li>
                        <li>Anda dapat mengunggah maksimal 5 foto.</li>
                    </ul>
                </div>

                <!-- Example Images -->
                <div class="bg-white rounded-lg p-6 shadow-sm border">
                    <h3 class="text-lg font-semibold mb-2">Contoh Foto yang Baik</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <img :src="Example3" alt="Contoh foto ternak 1" class="rounded-lg object-cover w-full h-32">
                        <img :src="Example4" alt="Contoh foto ternak 2" class="rounded-lg object-cover w-full h-32">
                    </div>
                </div>
            </div>

            <!-- Right Side - Upload Area / Carousel -->
            <div
                class="bg-white rounded-lg p-6 shadow-sm border flex flex-col justify-center items-center space-y-4 col-span-4">
                <div v-if="uploadedImages.length === 0"
                    class="upload-area w-full h-full flex flex-col justify-center items-center border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:bg-gray-50 transition-colors"
                    @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
                    <div class="text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="mt-2 text-sm">
                            Seret & Lepas atau
                            <span class="font-semibold text-primary">Klik untuk Unggah</span>
                        </p>
                        <p class="text-xs mt-1">JPG, JPEG, PNG (Maks. 20MB)</p>
                    </div>
                </div>

                <!-- Carousel -->
                <div v-else class="space-y-4 w-full">
                    <Carousel class="relative w-full">
                        <CarouselContent>
                            <CarouselItem v-for="(image, index) in uploadedImages" :key="index" class="relative">
                                <div class="p-1">
                                    <img :src="image.url" alt="Uploaded image"
                                        class="rounded-lg object-cover w-full h-80">
                                    <Button variant="destructive" size="icon"
                                        class="absolute top-2 right-2 h-7 w-7 rounded-full" @click="removeImage(index)">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </CarouselItem>
                        </CarouselContent>
                        <CarouselPrevious class="ml-10" v-if="uploadedImages.length > 1" />
                        <CarouselNext class="mr-10" v-if="uploadedImages.length > 1" />
                    </Carousel>
                    <Button v-if="uploadedImages.length < MAX_FILES" variant="outline" class="w-full"
                        @click="triggerFileInput">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Foto
                    </Button>
                </div>

                <!-- Hidden file input -->
                <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png" multiple class="hidden"
                    @change="handleFileSelect">
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700 text-sm">{{ errorMessage }}</p>
        </div>
    </div>
</template>