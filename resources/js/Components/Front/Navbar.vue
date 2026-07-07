<script setup>
import { ref, onMounted } from 'vue'

const open = ref(false)
const darkMode = ref(false)

onMounted(() => {
    darkMode.value = localStorage.theme === 'dark'

    if (darkMode.value) {
        document.documentElement.classList.add('dark')
    }
})

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value

    if (darkMode.value) {
        document.documentElement.classList.add('dark')
        localStorage.theme = 'dark'
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.theme = 'light'
    }
}
</script>

<template>
    <nav
        class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-50">

        <div class="container mx-auto px-6 h-20 flex justify-between items-center">

            <!-- LOGO -->
            <div class="flex items-center gap-3">

            <img
            src="/services/logobateng3.png"
            alt="logo bangka tengah"
            class="w-12 h-12 object-contain"
            >

                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                        BEDULANG
                    </h1>

                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Bangka Tengah
                    </p>
                </div>

            </div>

            <!-- MENU -->
            <div
                class="hidden md:flex items-center space-x-8 text-gray-700 dark:text-gray-200 font-medium">

                <a href="#" class="hover:text-blue-600 transition">
                    Beranda
                </a>

                <a href="#" class="hover:text-blue-600 transition">
                    Layanan
                </a>

                <a href="#" class="hover:text-blue-600 transition">
                    Berita
                </a>

                <a href="#" class="hover:text-blue-600 transition">
                    Kontak
                </a>

            </div>

            <!-- ACTION -->
            <div class="hidden md:flex items-center gap-5">

    <!-- Dark Mode Switch -->
    <div class="flex items-center">

    <button
        @click="toggleDarkMode"
        class="relative inline-flex h-7 w-14 items-center rounded-full transition-all duration-300"
        :class="darkMode ? 'bg-blue-600' : 'bg-gray-300'"
    >

        <span
            class="absolute flex h-6 w-6 items-center justify-center rounded-full shadow-md transform transition-all duration-300 text-sm"
            :class="darkMode ? 'translate-x-7' : 'translate-x-1'"
        >
            {{ darkMode ? '🌙' : '☀️' }}
        </span>

    </button>

</div>

    <!-- Login -->
    <a
        href="/login"
        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow hover:shadow-lg transition"
    >
        Login
    </a>

</div>

            <!-- MOBILE -->
            <button
                class="md:hidden text-3xl text-gray-700 dark:text-white"
                @click="open = !open"
            >
                ☰
            </button>

        </div>

        <!-- MOBILE MENU -->
        <div
            v-if="open"
            class="md:hidden border-t border-gray-200 dark:border-gray-700 px-6 py-4 space-y-3 bg-white dark:bg-gray-800"
        >

            <a href="#" class="block hover:text-blue-600">
                Beranda
            </a>

            <a href="#" class="block hover:text-blue-600">
                Layanan
            </a>

            <a href="#" class="block hover:text-blue-600">
                Berita
            </a>

            <a href="#" class="block hover:text-blue-600">
                Kontak
            </a>

            <button
                @click="toggleDarkMode"
                class="w-full mt-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg"
            >
                {{ darkMode ? '☀️ Light Mode' : '🌙 Dark Mode' }}
            </button>

            <a
                href="/login"
                class="block text-center mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg"
            >
                Login
            </a>

        </div>

    </nav>
</template>