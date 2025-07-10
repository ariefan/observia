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

    // Note: We check original file size here, but the processed file will be optimized to stay under 20MB
    if (file.size > MAX_FILE_SIZE) {
        console.warn(`Original file size ${(file.size / 1024 / 1024).toFixed(2)}MB exceeds 20MB, but will be processed and optimized`)
        // Don't reject here - let the processing handle size optimization
    }

    return true
}

const resizeAndCropImage = (file: File): Promise<File> => {
    console.log(`Starting resize for: ${file.name}, Original size: ${(file.size / 1024 / 1024).toFixed(2)}MB, Dimensions will be calculated...`)

    return new Promise((resolve, reject) => {
        const img = new Image()
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')

        if (!ctx) {
            reject(new Error('Canvas context not available'))
            return
        }

        img.onload = () => {
            console.log(`Image loaded: ${file.name}, Original dimensions: ${img.width}x${img.height}`)

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

            console.log(`Target dimensions: ${targetWidth}x${targetHeight}`)

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

            console.log(`Crop area: ${sourceX}, ${sourceY}, ${sourceWidth}x${sourceHeight}`)

            // Set canvas dimensions
            canvas.width = targetWidth
            canvas.height = targetHeight

            // Draw and crop the image
            ctx.drawImage(
                img,
                sourceX, sourceY, sourceWidth, sourceHeight,
                0, 0, targetWidth, targetHeight
            )

            // Convert canvas to blob and then to file with size optimization
            const createOptimizedFile = (quality: number = 0.9): Promise<File> => {
                return new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => {
                        if (blob) {
                            // Check if the blob size exceeds 20MB
                            if (blob.size > MAX_FILE_SIZE) {
                                console.warn(`File size ${(blob.size / 1024 / 1024).toFixed(2)}MB exceeds 20MB limit, reducing quality...`)

                                // If still too large and quality can be reduced, try lower quality
                                if (quality > 0.3) {
                                    createOptimizedFile(quality - 0.1).then(resolve).catch(reject)
                                    return
                                } else {
                                    reject(new Error(`Unable to reduce file size below 20MB even at lowest quality`))
                                    return
                                }
                            }

                            // Ensure we use a valid image MIME type and filename
                            let mimeType = 'image/jpeg'
                            let fileName = file.name

                            // Ensure the filename has the correct extension
                            if (!fileName.toLowerCase().endsWith('.jpg') && !fileName.toLowerCase().endsWith('.jpeg')) {
                                // Remove existing extension and add .jpg
                                fileName = fileName.replace(/\.[^/.]+$/, '') + '.jpg'
                            }

                            const resizedFile = new File([blob], fileName, {
                                type: mimeType,
                                lastModified: Date.now()
                            })

                            // Add properties that Laravel expects for file validation
                            Object.defineProperty(resizedFile, 'webkitRelativePath', { value: '' })

                            console.log(`Processed file: ${resizedFile.name}, Size: ${(resizedFile.size / 1024 / 1024).toFixed(2)}MB, Quality: ${(quality * 100).toFixed(0)}%, Type: ${resizedFile.type}`)
                            resolve(resizedFile)
                        } else {
                            reject(new Error('Failed to create blob'))
                        }
                    }, 'image/jpeg', quality) // Always use JPEG for consistency
                })
            }

            createOptimizedFile().then(resolve).catch(reject)
        }

        img.onerror = (error) => {
            console.error('Error loading image for resize:', file.name, error)
            reject(new Error(`Failed to load image: ${file.name}`))
        }
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

            // Convert canvas to blob and then to file with size optimization
            const createOptimizedFile = (quality: number = 0.9): Promise<File> => {
                return new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => {
                        if (blob) {
                            // Check if the blob size exceeds 20MB
                            if (blob.size > MAX_FILE_SIZE) {
                                console.warn(`Existing image file size ${(blob.size / 1024 / 1024).toFixed(2)}MB exceeds 20MB limit, reducing quality...`)

                                // If still too large and quality can be reduced, try lower quality
                                if (quality > 0.3) {
                                    createOptimizedFile(quality - 0.1).then(resolve).catch(reject)
                                    return
                                } else {
                                    reject(new Error(`Unable to reduce existing image file size below 20MB even at lowest quality`))
                                    return
                                }
                            }

                            // Ensure the filename has .jpg extension
                            let processedFileName = fileName
                            if (!processedFileName.toLowerCase().endsWith('.jpg') && !processedFileName.toLowerCase().endsWith('.jpeg')) {
                                processedFileName = processedFileName.replace(/\.[^/.]+$/, '') + '.jpg'
                            }

                            const resizedFile = new File([blob], processedFileName, {
                                type: 'image/jpeg', // Default to JPEG
                                lastModified: Date.now()
                            })

                            // Add properties that Laravel expects for file validation
                            Object.defineProperty(resizedFile, 'webkitRelativePath', { value: '' })

                            console.log(`Processed existing image: ${processedFileName}, Size: ${(resizedFile.size / 1024 / 1024).toFixed(2)}MB, Quality: ${(quality * 100).toFixed(0)}%, Type: ${resizedFile.type}`)
                            resolve(resizedFile)
                        } else {
                            reject(new Error('Failed to create blob'))
                        }
                    }, 'image/jpeg', quality)
                })
            }

            createOptimizedFile().then(resolve).catch(reject)
        }

        img.onerror = () => reject(new Error('Failed to load existing image'))
        img.src = imageUrl
    })
}

// Function to get all images as File objects (processing existing ones if needed)
const getAllImagesAsFiles = async (): Promise<File[]> => {
    console.log('Processing all images for submission...')
    console.log('Current uploadedImages:', uploadedImages.value.map(img => ({
        file: typeof img.file === 'string' ? img.file : `File: ${img.file.name} (${(img.file.size / 1024 / 1024).toFixed(2)}MB)`,
        url: img.url,
        isString: typeof img.file === 'string'
    })))

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
            // Always re-process uploaded files to ensure they have correct MIME type and properties
            const fileSize = image.file.size
            const fileSizeMB = fileSize / 1024 / 1024

            console.log(`Processing uploaded file: ${image.file.name}, Size: ${fileSizeMB.toFixed(2)}MB`)

            try {
                // Always process uploaded files to ensure standardization
                const processedFile = await resizeAndCropImage(image.file)
                processedFiles.push(processedFile)
                console.log(`Successfully processed uploaded file: ${image.file.name} -> ${processedFile.name}`)
            } catch (error) {
                console.error('Error processing uploaded file:', error)
                // Skip this image if processing fails
            }
        }
    }

    console.log('Final processed files count:', processedFiles.length)

    // Final validation: ensure all files are under 20MB
    const oversizedFiles = processedFiles.filter(file => file.size > MAX_FILE_SIZE)
    if (oversizedFiles.length > 0) {
        console.error('Some processed files still exceed 20MB:', oversizedFiles.map(f => ({
            name: f.name,
            size: `${(f.size / 1024 / 1024).toFixed(2)}MB`
        })))
        throw new Error(`${oversizedFiles.length} file(s) still exceed 20MB after processing`)
    }

    console.log('All files validated - sizes:', processedFiles.map(f => ({
        name: f.name,
        size: `${(f.size / 1024 / 1024).toFixed(2)}MB`
    })))

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
        console.log(`Processing new upload: ${originalFile.name}, Size: ${(originalFile.size / 1024 / 1024).toFixed(2)}MB`)

        if (validateFile(originalFile)) {
            try {
                const resizedFile = await resizeAndCropImage(originalFile)
                const imageUrl = URL.createObjectURL(resizedFile)
                console.log(`Resized ${originalFile.name} from ${(originalFile.size / 1024 / 1024).toFixed(2)}MB to ${(resizedFile.size / 1024 / 1024).toFixed(2)}MB`)

                uploadedImages.value.push({
                    file: resizedFile,
                    originalFile,
                    url: imageUrl
                })
            } catch (error) {
                console.error('Error processing image:', originalFile.name, error)
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
                        <li>Ukuran file akan otomatis dioptimalkan maksimal 20MB per foto.</li>
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