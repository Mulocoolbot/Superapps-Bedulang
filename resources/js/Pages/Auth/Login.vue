
<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}


</script>

<template>

<Head title="Login" />

<section
    class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-blue-50 via-white to-slate-100 dark:from-gray-950 dark:via-gray-900 dark:to-black px-6">

    <!-- Background Blur -->

    <div
        class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-blue-300/20 blur-3xl">
    </div>

    <div
        class="absolute bottom-0 right-0 w-80 h-80 rounded-full bg-cyan-300/20 blur-3xl">
    </div>

    <!-- Card -->

    <div
        class="relative z-10 w-full max-w-md rounded-3xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl shadow-2xl p-8">

        <!-- Logo -->

        <div class="flex justify-center">

            <img
                src="/services/logobateng3.png"
                alt="BEDULANG"
                class="w-20 h-20 object-contain"
            >

        </div>

        <!-- Heading -->

        <div class="mt-5 text-center">

            <h1
                class="text-3xl font-bold text-gray-900 dark:text-white">

                BEDULANG

            </h1>

            <p
                class="mt-1 text-blue-600 font-medium">

                Bangka Tengah Super App

            </p>

        </div>

        <!-- Status -->

        <div
            v-if="status"
            class="mt-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">

            {{ status }}

        </div>

        <!-- Form -->

        <form
            @submit.prevent="submit"
            class="mt-8 space-y-5">

            <div>

                <InputLabel
                    for="email"
                    value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-xl"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.email" />

            </div>

            <div>

                <InputLabel
                    for="password"
                    value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-xl"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password" />

            </div>

            <!-- Remember -->

            <div
                class="flex items-center justify-between">

                <label
                    class="flex items-center">

                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember" />

                    <span
                        class="ml-2 text-sm text-gray-600 dark:text-gray-400">

                        Ingat Saya

                    </span>

                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-700">

                    Lupa Password?

                </Link>

            </div>

            <!-- Button -->

            <PrimaryButton
                class="w-full justify-center rounded-xl py-3 text-base font-semibold shadow-md"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing">

                Masuk

            </PrimaryButton>

        </form>

        <!-- Footer -->

        <div
            class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-5 text-center">

            <Link
                href="/"
                class="text-sm text-gray-500 hover:text-blue-600 transition">

                ← Kembali ke Beranda

            </Link>

        </div>

    </div>

</section>

</template>