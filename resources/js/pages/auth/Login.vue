<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { Separator } from '@/components/ui/separator';
import LogoGoogle from '@/assets/google.png';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Selamat datang!" description="Masukkan username dan password anda dibawah untuk masuk">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-2 border border-primary-500 rounded-xl p-4">
            <div class="grid gap-4">

                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" required autofocus v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid w-full max-w-sm items-center gap-1.5">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required v-model="form.password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between" :tabindex="3">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" />
                        <span>Ingat saya</span>
                    </Label>
                    <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                        Lupa password?
                    </TextLink>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Log in
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Belum punya akun?
                <TextLink :href="route('register')" :tabindex="5">Daftar</TextLink>
            </div>
        </form>

        <Separator class="my-4" label="Atau login dengan" />

        <div class="flex justify-between">
            <Button as="a" variant="outline" class="w-full mr-2" :href="route('google.redirect')">
                <img :src="LogoGoogle" alt="Logo Google" class="size-4"> Google
            </Button>
            <!-- <Button variant="outline" class="w-full ml-2">
                <img :src="LogoFacebook" alt="Logo Facebook" class="size-4"> Facebook
            </Button> -->
        </div>
    </AuthBase>
</template>
