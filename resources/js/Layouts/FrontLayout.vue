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
        <Navbar ></Navbar>

        <!-- CONTENT -->
        <main class="relative z-0 min-h-screen">
            <!-- Flash Message Banner -->
            <div v-if="$page.props.flash?.warning" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-2">
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium flex items-center gap-3 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-600 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ $page.props.flash.warning }}</span>
                </div>
            </div>
            <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 mb-2">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>
            <slot />
        </main>

    </div>

</template>