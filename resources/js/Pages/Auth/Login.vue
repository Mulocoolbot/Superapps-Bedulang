
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

<Head title="Login"></Head>

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
            class="mt-8 space-y-6">

            <div>

                <InputLabel
                    for="email"
                    value="Email"></InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"></TextInput>

                <InputError
                    class="mt-2"
                    :message="form.errors.email"></InputError>

            </div>

            <div>

                <InputLabel
                    for="password"
                    value="Password"></InputLabel>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"></TextInput>

                <InputError
                    class="mt-2"
                    :message="form.errors.password"></InputError>

            </div>

            <!-- Remember -->

            <div
                class="flex items-center justify-between">

                <label
                    class="flex items-center cursor-pointer select-none">

                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"></Checkbox>

                    <span
                        class="ml-2 text-sm text-gray-600 dark:text-gray-400">

                        Ingat Saya

                    </span>

                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">

                    Lupa Password?

                </Link>

            </div>

            <!-- Button -->

            <button
                type="submit"
                class="w-full flex justify-center items-center rounded-xl py-3 text-base font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] cursor-pointer"
                :class="{ 'opacity-50 pointer-events-none': form.processing }"
                :disabled="form.processing"
            >
                Masuk
            </button>

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