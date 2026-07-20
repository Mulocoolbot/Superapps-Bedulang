<script setup>
import { ref, onMounted } from 'vue'
import Navbar from '@/Components/Front/Navbar.vue'
import WhatsAppWidget from '@/Components/Front/WhatsAppWidget.vue'

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

    <div class="min-h-screen bg-white dark:bg-gray-900 transition-colors duration-300">

        <!-- NAVBAR -->
        <Navbar />

        <!-- CONTENT -->
        <main class="pt-28 relative z-0 min-h-[calc(100vh-5.5rem)]">
            <slot />
        </main>

    </div>

</template>