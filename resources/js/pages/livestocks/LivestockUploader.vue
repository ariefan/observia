<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Plus, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import Example3 from '@/assets/example-3.jpg';
import Example4 from '@/assets/example-4.jpg';

interface UploadedImage {
    file: File | string
    originalFile: File | string
    url: string
}

const props = defineProps<{
    modelValue: (File | string)[]
}>()

const emit = defineEmits(['update:modelValue'])

const uploadedImages = ref<UploadedImage[]>([])
const fileInput = ref<HTMLInputElement>()
const errorMessage = ref('')

const MAX_FILE_SIZE = 20 * 1024 * 1024 // 20MB
const MAX_FILES = 5
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png']
const MAX_WIDTH = 1080
const ASPECT_RATIO = 16 / 9 // 16:9 aspect ratio

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

const resizeAndCropImage = (file: File): Promise<File> => {
    return new Promise((resolve, reject) => {
        const img = new Image()
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')

        if (!ctx) {
            reject(new Error('Canvas context not available'))
            return
        }

        img.onload = () => {
            // Calculate dimensions for 16:9 aspect ratio
            let { width, height } = img

            // Calculate the target width (max 1080px but maintain aspect ratio)
            let targetWidth = Math.min(width, MAX_WIDTH)
            let targetHeight = targetWidth / ASPECT_RATIO

            // If the calculated height exceeds the original image height,
            // use the original height and adjust width accordingly
            if (targetHeight > height) {
                targetHeight = height
                targetWidth = targetHeight * ASPECT_RATIO
            }

            // Calculate crop area (center crop)
            const sourceAspectRatio = width / height
            let sourceWidth, sourceHeight, sourceX, sourceY

            if (sourceAspectRatio > ASPECT_RATIO) {
                // Source is wider than 16:9, crop width
                sourceHeight = height
                sourceWidth = height * ASPECT_RATIO
                sourceX = (width - sourceWidth) / 2
                sourceY = 0
            } else {
                // Source is taller than 16:9, crop height
                sourceWidth = width
                sourceHeight = width / ASPECT_RATIO
                sourceX = 0
                sourceY = (height - sourceHeight) / 2
            }

            // Set canvas dimensions
            canvas.width = targetWidth
            canvas.height = targetHeight

            // Draw and crop the image
            ctx.drawImage(
                img,
                sourceX, sourceY, sourceWidth, sourceHeight,
                0, 0, targetWidth, targetHeight
            )

            // Convert canvas to blob and then to file
            canvas.toBlob((blob) => {
                if (blob) {
                    const resizedFile = new File([blob], file.name, {
                        type: file.type,
                        lastModified: Date.now()
                    })
                    resolve(resizedFile)
                } else {
                    reject(new Error('Failed to create blob'))
                }
            }, file.type, 0.9) // 90% quality
        }

        img.onerror = () => reject(new Error('Failed to load image'))
        img.src = URL.createObjectURL(file)
    })
}

// Function to convert existing image URLs to resized File objects
const processExistingImage = async (imageUrl: string, fileName: string): Promise<File> => {
    return new Promise((resolve, reject) => {
        const img = new Image()
        img.crossOrigin = 'anonymous' // Handle CORS if needed

        img.onload = () => {
            const canvas = document.createElement('canvas')
            const ctx = canvas.getContext('2d')

            if (!ctx) {
                reject(new Error('Canvas context not available'))
                return
            }

            // Use the same resizing logic as new uploads
            let { width, height } = img

            // Calculate the target width (max 1080px but maintain aspect ratio)
            let targetWidth = Math.min(width, MAX_WIDTH)
            let targetHeight = targetWidth / ASPECT_RATIO

            // If the calculated height exceeds the original image height,
            // use the original height and adjust width accordingly
            if (targetHeight > height) {
                targetHeight = height
                targetWidth = targetHeight * ASPECT_RATIO
            }

            // Calculate crop area (center crop)
            const sourceAspectRatio = width / height
            let sourceWidth, sourceHeight, sourceX, sourceY

            if (sourceAspectRatio > ASPECT_RATIO) {
                // Source is wider than 16:9, crop width
                sourceHeight = height
                sourceWidth = height * ASPECT_RATIO
                sourceX = (width - sourceWidth) / 2
                sourceY = 0
            } else {
                // Source is taller than 16:9, crop height
                sourceWidth = width
                sourceHeight = width / ASPECT_RATIO
                sourceX = 0
                sourceY = (height - sourceHeight) / 2
            }

            // Set canvas dimensions
            canvas.width = targetWidth
            canvas.height = targetHeight

            // Draw and crop the image
            ctx.drawImage(
                img,
                sourceX, sourceY, sourceWidth, sourceHeight,
                0, 0, targetWidth, targetHeight
            )

            // Convert canvas to blob and then to file
            canvas.toBlob((blob) => {
                if (blob) {
                    const resizedFile = new File([blob], fileName, {
                        type: 'image/jpeg', // Default to JPEG
                        lastModified: Date.now()
                    })
                    resolve(resizedFile)
                } else {
                    reject(new Error('Failed to create blob'))
                }
            }, 'image/jpeg', 0.9) // 90% quality
        }

        img.onerror = () => reject(new Error('Failed to load existing image'))
        img.src = imageUrl
    })
}

// Function to get all images as File objects (processing existing ones if needed)
const getAllImagesAsFiles = async (): Promise<File[]> => {
    console.log('Processing all images for submission...')
    const processedFiles: File[] = []
    
    for (let i = 0; i < uploadedImages.value.length; i++) {
        const image = uploadedImages.value[i]
        
        if (typeof image.file === 'string') {
            // Process existing image URL to File object
            try {
                console.log('Processing existing image:', image.file)
                const fileName = `image_${i + 1}.jpg`
                const processedFile = await processExistingImage(image.url, fileName)
                processedFiles.push(processedFile)
                console.log('Successfully processed existing image:', fileName)
            } catch (error) {
                console.error('Error processing existing image:', error)
                // Skip this image if processing fails
            }
        } else {
            // Already a File object (new upload)
            console.log('Using already processed file:', image.file.name)
            processedFiles.push(image.file)
        }
    }
    
    console.log('Final processed files count:', processedFiles.length)
    return processedFiles
}

// Expose the function to parent component
defineExpose({
    getAllImagesAsFiles
})

const addImages = async (files: FileList | null) => {
    if (!files) return

    errorMessage.value = ''
    const fileArray = Array.from(files)

    if (uploadedImages.value.length + fileArray.length > MAX_FILES) {
        errorMessage.value = `Maksimal ${MAX_FILES} foto yang dapat diupload`
        return
    }

    for (const originalFile of fileArray) {
        if (validateFile(originalFile)) {
            try {
                const resizedFile = await resizeAndCropImage(originalFile)
                const imageUrl = URL.createObjectURL(resizedFile)
                uploadedImages.value.push({
                    file: resizedFile,
                    originalFile,
                    url: imageUrl
                })
            } catch (error) {
                console.error('Error processing image:', error)
                errorMessage.value = 'Gagal memproses gambar'
            }
        }
    }

    emit('update:modelValue', uploadedImages.value.map(img => img.file))
}

const handleFileSelect = async (event: Event) => {
    const target = event.target as HTMLInputElement
    await addImages(target.files)
    target.value = '' // Reset input
}

const handleDrop = async (event: DragEvent) => {
    await addImages(event.dataTransfer?.files || null)
}

const removeImage = (index: number) => {
    const image = uploadedImages.value[index]

    // Only revoke object URL if it was created from a File object
    if (typeof image.file !== 'string') {
        URL.revokeObjectURL(image.url)
    }

    uploadedImages.value.splice(index, 1)
    emit('update:modelValue', uploadedImages.value.map(img => img.file))
}

const initializeExistingImages = async (files: (File | string)[]) => {
    console.log('Initializing existing images:', files)

    // Clear existing images first
    uploadedImages.value.forEach(img => {
        if (typeof img.file !== 'string') {
            URL.revokeObjectURL(img.url)
        }
    })
    uploadedImages.value = []

    for (const file of files) {
        try {
            let imageUrl: string

            if (typeof file === 'string') {
                // Existing photo path from database
                imageUrl = `/storage/${file}`
                console.log('Adding existing photo:', file, 'URL:', imageUrl)
                uploadedImages.value.push({
                    file,
                    originalFile: file,
                    url: imageUrl
                })
            } else {
                // File object (new upload)
                imageUrl = URL.createObjectURL(file)
                console.log('Adding file object:', file.name, 'URL:', imageUrl)
                uploadedImages.value.push({
                    file,
                    originalFile: file,
                    url: imageUrl
                })
            }
        } catch (error) {
            console.error('Error initializing existing image:', error)
        }
    }

    console.log('Final uploadedImages:', uploadedImages.value)
}

watch(() => props.modelValue, async (newVal, oldVal) => {
    // Only process if the modelValue actually changed and is not just an internal update
    if (newVal && newVal.length > 0 && newVal !== oldVal) {
        // Check if this is different from our current uploadedImages
        const currentFiles = uploadedImages.value.map(img => img.file)
        const filesAreDifferent = newVal.length !== currentFiles.length ||
            newVal.some((file, index) => file !== currentFiles[index])

        if (filesAreDifferent) {
            console.log('ModelValue has changed, reinitializing images.')
            await initializeExistingImages(newVal)
        }
    } else if (!newVal || newVal.length === 0) {
        console.warn('ModelValue is empty, clearing uploaded images.')
        uploadedImages.value.forEach(img => {
            // Only revoke object URL if it was created from a File object
            if (typeof img.file !== 'string') {
                URL.revokeObjectURL(img.url)
            }
        })
        uploadedImages.value = []
    }
})

onMounted(async () => {
    console.log('LivestockUploader mounted. Props modelValue:', props.modelValue)

    // Initialize with existing images if any
    if (props.modelValue && props.modelValue.length > 0) {
        await initializeExistingImages(props.modelValue);
    } else {
        console.warn('No existing images found in modelValue during initialization.');
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
                        <li>Gambar akan otomatis diubah ke rasio 16:9 dengan lebar maksimal 1080px.</li>
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
                        <p class="text-xs mt-1">JPG, JPEG, PNG (Maks. 20MB) - Otomatis diubah ke 16:9</p>
                    </div>
                </div>

                <!-- Carousel -->
                <div v-else class="space-y-4 w-full">
                    <Carousel class="relative w-full">
                        <CarouselContent>
                            <CarouselItem v-for="(image, index) in uploadedImages" :key="index" class="relative">
                                <div class="p-1">
                                    <div class="relative w-full" style="aspect-ratio: 16/9;">
                                        <img :src="image.url" alt="Uploaded image"
                                            class="rounded-lg object-cover w-full h-full">
                                        <Button type="button" variant="destructive" size="icon"
                                            class="absolute top-2 right-2 h-7 w-7 rounded-full"
                                            @click="removeImage(index)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CarouselItem>
                        </CarouselContent>
                        <CarouselPrevious type="button" class="ml-10" v-if="uploadedImages.length > 1" />
                        <CarouselNext type="button" class="mr-10" v-if="uploadedImages.length > 1" />
                    </Carousel>
                    <Button type="button" v-if="uploadedImages.length < MAX_FILES" variant="outline" class="w-full"
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