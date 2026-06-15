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
    <nav class="bg-white dark:bg-gray-800 shadow">

        <div class="container mx-auto px-6 py-4 flex justify-between items-center">

            <!-- LOGO -->
            <div class="text-xl font-bold text-gray-800 dark:text-white">
                BEDULANG Bangka Tengah
            </div>

            <!-- MENU DESKTOP -->
            <div class="hidden md:flex space-x-6 text-gray-700 dark:text-gray-200">
                <a href="#" class="hover:text-blue-500">Beranda</a>
                <a href="#" class="hover:text-blue-500">Layanan</a>
                <a href="#" class="hover:text-blue-500">Berita</a>
                <a href="#" class="hover:text-blue-500">Kontak</a>
            </div>

            <!-- ACTIONS -->
            <div class="hidden md:flex items-center space-x-3">

                <!-- DARK MODE BUTTON -->
                <button
                    @click="toggleDarkMode"
                    class="px-3 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-sm text-gray-800 dark:text-white"
                >
                    {{ darkMode ? 'Light' : 'Dark' }}
                </button>

                <!-- LOGIN -->
                <a href="/login" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Login
                </a>

            </div>

            <!-- MOBILE BUTTON -->
            <button class="md:hidden text-2xl" @click="open = !open">
                ☰
            </button>

        </div>

        <!-- MOBILE MENU -->
        <div v-if="open" class="md:hidden px-6 pb-4 space-y-2">

            <a href="#" class="block">Beranda</a>
            <a href="#" class="block">Layanan</a>
            <a href="#" class="block">Berita</a>
            <a href="#" class="block">Kontak</a>

            <button
                @click="toggleDarkMode"
                class="block mt-3 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg"
            >
                {{ darkMode ? 'Light Mode' : 'Dark Mode' }}
            </button>

            <a href="/login" class="block mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg">
                Login
            </a>

        </div>

    </nav>
</template>