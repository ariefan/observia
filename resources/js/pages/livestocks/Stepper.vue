<script>
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'


const stepIndex = ref(1)
const steps = [
    { step: 1, title: 'Data Ternak', description: '' },
    { step: 2, title: 'Data induk Jantan', description: '' },
    { step: 3, title: 'Data induk Betina', description: '' },
]
</script>
<template>

    <Stepper v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }" v-model="stepIndex" class="block w-full">
        <div class="flex w-full flex-start gap-2">
            <StepperItem v-for="step in steps" :key="step.step" v-slot="{ state }"
                class="relative flex w-full flex-col items-center justify-center" :step="step.step">
                <StepperSeparator v-if="step.step !== steps[steps.length - 1].step"
                    class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />

                <StepperTrigger as-child>
                    <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'" size="icon"
                        class="z-10 size-6 rounded-full shrink-0"
                        :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                        :disabled="state !== 'completed' && !meta.valid">
                        <Check v-if="state === 'completed'" class="size-3" />
                        <Circle v-if="state === 'active'" />
                        <Dot v-if="state === 'inactive'" />
                    </Button>
                </StepperTrigger>

                <div class="mt-2 flex flex-col items-center text-center">
                    <StepperTitle :class="[state === 'active' && 'text-primary']"
                        class="text-sm font-semibold transition lg:text-base">
                        {{ step.title }}
                    </StepperTitle>
                    <StepperDescription :class="[state === 'active' && 'text-primary']"
                        class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm">
                        {{ step.description }}
                    </StepperDescription>
                </div>
            </StepperItem>
        </div>

        <div class="flex flex-col gap-4 mt-4">
            <template v-if="stepIndex === 1">

            </template>

            <template v-if="stepIndex === 2">
                <!-- Todo page 2 -->
                <form ref="form_2">
                    <p class="font-semibold">Data Induk Jantan</p>
                    <p class="text-sm mb-6">Dengan mengisi informasi ini, kamu dapat melacak dan meningkatkan kualitas
                        keturunan serta mengoptimalkan hasil peternakan kambingmu.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                        <div>
                            <!-- TODO -->
                            <Label for="">ID Ternak</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Aifarm ID</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Nama Induk Jantan</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Ras</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-6">
                        <div>
                            <!-- TODO -->
                            <Label for="">Nomer Identitas Kakek</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Nomer Identitas Nenek</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">No Buku Registrasi</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <Button class="hidden" type="submit" :disabled="form.processing">
                            Simpan
                        </Button>
                    </div>
                </form>
            </template>

            <template v-if="stepIndex === 3">
                <!-- Todo page 3 -->
                <form ref="form_3">
                    <p class="font-semibold">Data Induk Betina</p>
                    <p class="text-sm mb-6">Dengan mengisi informasi ini, kamu dapat melacak dan meningkatkan kualitas
                        keturunan serta mengoptimalkan hasil peternakan kambingmu.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                        <div>
                            <!-- TODO -->
                            <Label for="">ID Ternak</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Aifarm ID</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Nama Induk Betina</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Ras</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-6">
                        <div>
                            <!-- TODO -->
                            <Label for="">Nomer Identitas Kakek</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">Nomer Identitas Nenek</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                        <div>
                            <!-- TODO -->
                            <Label for="">No Buku Registrasi</Label>
                            <Input id="" />
                            <InputError :message="''" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <Button class="hidden" type="submit" :disabled="form.processing">
                            Simpan
                        </Button>
                    </div>
                </form>
            </template>
        </div>

        <div class="flex items-center justify-between mt-4">
            <Button :disabled="isPrevDisabled" variant="outline" @click="prevStep()">
                Kembali
            </Button>
            <div class="flex items-center gap-3">

                <Button v-if="stepIndex === 1" type="button" :disabled="isNextDisabled || form.processing"
                    @click="() => { submitStep1(nextStep); }">
                    Lanjut
                </Button>
                <Button v-if="stepIndex === 2" :type="meta.valid ? 'button' : 'submit'"
                    :disabled="isSelanjutnyaDisabled" @click="meta.valid && nextStep()">
                    Lanjut
                </Button>
                <Button v-if="stepIndex === 3" type="submit">
                    Selesai
                </Button>
            </div>
        </div>
    </Stepper>
</template>