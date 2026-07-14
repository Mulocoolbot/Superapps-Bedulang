<script setup>
import { ref, onMounted } from 'vue'

const open = ref(false)
const darkMode = ref(false)

const infoServices = [
    { name: 'Pemerintahan', id: 'pemerintahan' },
    { name: 'Pendidikan', id: 'pendidikan' },
    { name: 'Kesehatan', id: 'kesehatan' },
    { name: 'Perindagkop-UKM', id: 'perindagkop-ukm' },
    { name: 'Hukum', id: 'hukum' },
    { name: 'Sosial', id: 'sosial' },
    { name: 'Kependudukan', id: 'kependudukan' },
    { name: 'Kepegawaian', id: 'kepegawaian' },
    { name: 'Lingkungan', id: 'lingkungan' },
    { name: 'Geografis', id: 'geografis' },
    { name: 'Pariwisata', id: 'pariwisata' },
    { name: 'Data Sektoral', id: 'data-sektoral' },
]

const regionalApps = [
    { name: 'SIPELAWAN', id: 'sipelawan' },
    { name: 'SIDOLPIN', id: 'sidolpin' },
    { name: 'SILAKSO', id: 'silakso' },
    { name: 'SIKOK DATA', id: 'sikok-data' },
    { name: 'LAPOR PPID', id: 'lapor-ppid' },
    { name: 'SIMENTARI', id: 'simentari' },
]

const publicServices = [
    { name: 'Perizinan Terpadu', id: 'perizinan-terpadu' },
    { name: 'Pelayanan Kesehatan', id: 'pelayanan-kesehatan' },
    { name: 'Pengaduan PPID', id: 'pengaduan-ppid' },
    { name: 'Administrasi Warga', id: 'administrasi-warga' },
]

const scrollToAndHighlight = (targetId) => {
    // If it's a layanan ID, toggle the correct tab first
    if (targetId.startsWith('layanan-')) {
        const publicIds = [
            'layanan-perizinan-terpadu',
            'layanan-pelayanan-kesehatan',
            'layanan-pengaduan-ppid',
            'layanan-administrasi-warga'
        ]
        const isPublic = publicIds.includes(targetId)
        
        // Find buttons in the #layanan section
        const buttons = document.querySelectorAll('#layanan button')
        if (buttons && buttons.length >= 2) {
            const targetBtn = isPublic ? buttons[1] : buttons[0]
            targetBtn.click()
        }
    }
    
    // Smooth scroll and highlight
    setTimeout(() => {
        const element = document.getElementById(targetId)
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' })
            
            // Add highlight classes
            element.classList.add('ring-4', 'ring-blue-500', 'scale-[1.03]', 'shadow-2xl', 'dark:ring-blue-400')
            
            // Remove after 2 seconds
            setTimeout(() => {
                element.classList.remove('ring-4', 'ring-blue-500', 'scale-[1.03]', 'shadow-2xl', 'dark:ring-blue-400')
            }, 2000)
        }
    }, 150)
}

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

                <a href="#beranda" class="hover:text-blue-600 transition">
                    Beranda
                </a>

                <!-- LAYANAN DROPDOWN -->
                <div class="relative group py-2">
                    <a href="#layanan" class="hover:text-blue-600 transition flex items-center gap-1">
                        Layanan
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </a>
                    
                    <!-- Level 1 Dropdown -->
                    <div class="absolute left-0 top-full hidden group-hover:block w-72 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl py-3 z-50">
                        <!-- Layanan Informasi dan Data (Nested Dropdown) -->
                        <div class="relative group/nested">
                            <button class="w-full text-left px-5 py-3 hover:bg-blue-50 dark:hover:bg-gray-700 flex justify-between items-center transition">
                                <span>Layanan Informasi & Data</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                            
                            <!-- Level 2 Flyout -->
                            <div class="absolute left-full top-0 hidden group-hover/nested:block w-64 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl py-2 z-50 ml-1 h-[320px] overflow-y-auto custom-scrollbar">
                                <a 
                                    v-for="app in infoServices" 
                                    :key="app.name" 
                                    href="javascript:void(0)"
                                    @click="scrollToAndHighlight('layanan-' + app.id)"
                                    class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                                >
                                    {{ app.name }}
                                </a>
                            </div>
                        </div>

                        <!-- Layanan Publik (Nested Dropdown) -->
                        <div class="relative group/nested2">
                            <button class="w-full text-left px-5 py-3 hover:bg-blue-50 dark:hover:bg-gray-700 flex justify-between items-center transition">
                                <span>Layanan Publik</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                            
                            <!-- Level 2 Flyout -->
                            <div class="absolute left-full top-0 hidden group-hover/nested2:block w-64 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl py-2 z-50 ml-1 h-[240px] overflow-y-auto custom-scrollbar">
                                <a 
                                    v-for="app in publicServices" 
                                    :key="app.name" 
                                    href="javascript:void(0)"
                                    @click="scrollToAndHighlight('layanan-' + app.id)"
                                    class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                                >
                                    {{ app.name }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- APLIKASI DROPDOWN -->
                <div class="relative group py-2">
                    <a href="#aplikasi" class="hover:text-blue-600 transition flex items-center gap-1">
                        Aplikasi
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </a>
                    
                    <!-- App Dropdown -->
                    <div class="absolute left-0 top-full hidden group-hover:block w-64 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl py-2 z-50">
                        <a 
                            v-for="app in regionalApps" 
                            :key="app.name" 
                            href="javascript:void(0)"
                            @click="scrollToAndHighlight('aplikasi-' + app.id)"
                            class="block px-5 py-3 hover:bg-blue-50 dark:hover:bg-gray-700 font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                        >
                            {{ app.name }}
                        </a>
                    </div>
                </div>

                <a href="#kontak" class="hover:text-blue-600 transition">
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
            {{ darkMode ? '⏾' : '☀︎' }}
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

            <a href="#beranda" class="block hover:text-blue-600">
                Beranda
            </a>

            <a href="#layanan" class="block hover:text-blue-600">
                Layanan
            </a>

            <a href="#aplikasi" class="block hover:text-blue-600">
                Aplikasi
            </a>

            <a href="#kontak" class="block hover:text-blue-600">
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