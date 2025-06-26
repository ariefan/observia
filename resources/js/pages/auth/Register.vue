<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { FloatingInput } from '@/components/ui/floating-input';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { Separator } from '@/components/ui/separator';
import LogoGoogle from '@/assets/google.png';
import LogoFacebook from '@/assets/facebook.png';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Buat akun baru" description="Masukkan data anda dibawah untuk membuat akun baru anda">

        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6 border border-primary-500 rounded-xl p-4">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <FloatingInput label="Name" id="name" type="text" required autofocus :tabindex="1"
                        autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <FloatingInput label="Email address" id="email" type="email" required :tabindex="2"
                        autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <FloatingInput label="Password" id="password" type="password" required :tabindex="3"
                        autocomplete="new-password" v-model="form.password" placeholder="Password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <FloatingInput label="Confirm password" id="password_confirmation" type="password" required
                        :tabindex="4" autocomplete="new-password" v-model="form.password_confirmation"
                        placeholder="Confirm password" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
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
