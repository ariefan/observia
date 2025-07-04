<script setup lang="ts">
import { ref } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Carousel } from '@/components/ui/carousel'

interface UploadedImage {
    file: File
    url: string
}

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
}
</script>

<template>
    <div class="max-w-6xl mx-auto p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Side - Upload Guidelines -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg p-6 shadow-sm border">
                    <h2 class="text-lg font-semibold mb-4">Panduan Upload Foto Ternak</h2>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Format foto <strong>.jpg, .jpeg, dan png</strong> ukuran maks
                                <strong>20MB</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Pilih foto lanscape yang jelas, simetris dan dapat diidentifikasi</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Pastikan hanya ada <strong>1 ternak</strong> di dalam foto</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mt-2 flex-shrink-0"></span>
                            <span>Anda dapat mengupload maks <strong>5 foto</strong></span>
                        </li>
                    </ul>
                </div>

                <!-- Example Images -->
                <div class="bg-white rounded-lg p-6 shadow-sm border">
                    <h3 class="text-sm font-medium mb-4">Contoh gambar seperti di bawah:</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <img src="https://images.pexels.com/photos/422220/pexels-photo-422220.jpeg?auto=compress&cs=tinysrgb&w=300&h=200&fit=crop"
                            alt="Contoh foto ternak 1" class="w-full h-24 object-cover rounded-lg" />
                        <img src="https://images.pexels.com/photos/1300375/pexels-photo-1300375.jpeg?auto=compress&cs=tinysrgb&w=300&h=200&fit=crop"
                            alt="Contoh foto ternak 2" class="w-full h-24 object-cover rounded-lg" />
                    </div>
                </div>
            </div>

            <!-- Right Side - Upload Area / Carousel -->
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div v-if="uploadedImages.length === 0" class="upload-area">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors"
                        @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                <Plus class="w-8 h-8 text-gray-400" />
                            </div>
                            <div>
                                <p class="text-lg font-medium text-gray-700">Tambah foto</p>
                                <p class="text-sm text-gray-500 mt-1">Klik atau drag & drop file di sini</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carousel -->
                <div v-else class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Foto Ternak ({{ uploadedImages.length }}/5)</h3>
                        <Button variant="outline" size="sm" @click="triggerFileInput"
                            :disabled="uploadedImages.length >= 5">
                            <Plus class="w-4 h-4 mr-2" />
                            Tambah Foto
                        </Button>
                    </div>

                    <div class="relative">
                        <Carousel :slides="uploadedImages">
                            <div v-for="(image, index) in uploadedImages" :key="index"
                                class="embla__slide flex-shrink-0 w-full relative">
                                <div class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <img :src="image.url" :alt="`Uploaded image ${index + 1}`"
                                        class="w-full h-full object-cover" />
                                    <button @click="removeImage(index)"
                                        class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </Carousel>
                    </div>
                </div>

                <!-- Hidden file input -->
                <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png" multiple class="hidden"
                    @change="handleFileSelect" />
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700 text-sm">{{ errorMessage }}</p>
        </div>
    </div>
</template>