<script setup>
import { ref, onMounted } from 'vue'
import Navbar from '@/Components/Front/Navbar.vue'

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

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

        <!-- NAVBAR -->
        <Navbar />

        <!-- CONTENT -->
        <main>
            <slot />
        </main>

        <!-- FOOTER -->
        <footer class="bg-white dark:bg-gray-800 mt-10">

            <div class="container mx-auto px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">

                © 2026 Super App Bangka Tengah

            </div>

        </footer>

    </div>

</template>