<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    ipBlockedMessage: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
    captcha: '',
    captcha_num1: null,
    captcha_num2: null,
    captcha_operator: '+',
    captcha_input: '',
})

// Show / Hide Password State
const showPassword = ref(false)

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value
}

// Math Captcha State
const num1 = ref(0)
const num2 = ref(0)
const operator = ref('+')
const captchaAnswer = ref(0)
const captchaError = ref('')

const generateCaptcha = () => {
    const isAddition = Math.random() > 0.5
    const a = Math.floor(Math.random() * 12) + 2
    const b = Math.floor(Math.random() * 12) + 1

    if (isAddition) {
        num1.value = a
        num2.value = b
        operator.value = '+'
        captchaAnswer.value = a + b
    } else {
        num1.value = Math.max(a, b)
        num2.value = Math.min(a, b)
        operator.value = '-'
        captchaAnswer.value = num1.value - num2.value
    }
    form.captcha = ''
    form.captcha_num1 = num1.value
    form.captcha_num2 = num2.value
    form.captcha_operator = operator.value
    form.captcha_input = ''
    captchaError.value = ''
}

onMounted(() => {
    generateCaptcha()
})

const submit = () => {
    if (props.ipBlockedMessage) return

    form.captcha_input = form.captcha

    const userAnswer = parseInt(form.captcha, 10)
    if (isNaN(userAnswer) || userAnswer !== captchaAnswer.value) {
        captchaError.value = 'Jawaban captcha salah! Silakan hitung kembali.'
    }

    form.post(route('login'), {
        onFinish: () => {
            form.reset('password')
            generateCaptcha()
        },
        onError: () => {
            generateCaptcha()
        }
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

        <!-- IP Blocked Alert -->
        <div
            v-if="ipBlockedMessage"
            class="mt-6 rounded-2xl bg-red-50 border border-red-200 dark:bg-red-950/60 dark:border-red-900/80 p-4 text-xs font-bold text-red-700 dark:text-red-300 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div>
                <p class="font-extrabold text-sm mb-0.5">Akses Login Diblokir (24 Jam)</p>
                <p class="font-medium text-xs leading-relaxed">{{ ipBlockedMessage }}</p>
            </div>
        </div>

        <!-- Form -->

        <form
            @submit.prevent="submit"
            class="mt-8 space-y-5">

            <div>

                <InputLabel
                    for="email"
                    value="Email"></InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full"
                    v-model="form.email"
                    :disabled="!!ipBlockedMessage"
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

                <div class="relative mt-2">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-11"
                        v-model="form.password"
                        :disabled="!!ipBlockedMessage"
                        required
                        autocomplete="current-password"></TextInput>

                    <button
                        type="button"
                        @click="togglePasswordVisibility"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-150 focus:outline-none"
                        tabindex="-1"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        :title="showPassword ? 'Sembunyikan Password' : 'Tampilkan Password'"
                    >
                        <!-- Open Eye Icon (Tampilkan Password) -->
                        <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>

                        <!-- Slashed Eye Icon (Sembunyikan Password) -->
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <InputError
                    class="mt-2"
                    :message="form.errors.password"></InputError>

            </div>

            <!-- Modern Security Captcha Card -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50/80 to-indigo-50/80 dark:from-gray-800/80 dark:to-gray-800/50 border border-blue-100 dark:border-gray-700/80 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        Verifikasi Keamanan (Captcha)
                    </span>
                </div>

                <div class="grid grid-cols-12 gap-3 items-center">
                    <!-- Math Expression Box -->
                    <div class="col-span-5 px-3 py-2 rounded-xl bg-white dark:bg-gray-900 border border-blue-200/80 dark:border-gray-700 text-center shadow-inner">
                        <span class="font-mono font-extrabold text-sm tracking-wider text-blue-700 dark:text-blue-400 select-none">
                            {{ num1 }} {{ operator }} {{ num2 }} = ?
                        </span>
                    </div>

                    <!-- Answer Input -->
                    <div class="col-span-7">
                        <TextInput
                            id="captcha"
                            type="number"
                            class="block w-full font-mono text-center font-bold text-sm tracking-wider"
                            placeholder="Jawaban"
                            v-model="form.captcha"
                            :disabled="!!ipBlockedMessage"
                            required
                            autocomplete="off"
                        />
                    </div>
                </div>

                <!-- Captcha Input Error -->
                <InputError
                    class="mt-1"
                    :message="form.errors.captcha_input"></InputError>

                <!-- Error Alert -->
                <div v-if="captchaError" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs font-bold text-red-600 dark:text-red-400">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ captchaError }}</span>
                </div>
            </div>

            <!-- Remember -->

            <div
                class="flex items-center justify-between">

                <label
                    class="flex items-center cursor-pointer select-none">

                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"
                        :disabled="!!ipBlockedMessage"></Checkbox>

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
                :class="{ 'opacity-50 pointer-events-none': form.processing || !!ipBlockedMessage }"
                :disabled="form.processing || !!ipBlockedMessage"
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