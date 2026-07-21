<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const open = ref(false)
const profileOpen = ref(false)
const darkMode = ref(false)
const isScrolled = ref(false)
const animateNavbar = ref(false)

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20
}

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const isAdmin = computed(() => authUser.value && ['admin', 'super_admin', 'pimpinan'].includes(authUser.value.role))

const isHomePage = () => {
    return !!document.getElementById('beranda')
}

const navigateMenu = (e, targetHash) => {
    if (isHomePage()) {
        e.preventDefault()
        const element = document.getElementById(targetHash.replace('#', ''))
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' })
        }
    }
}

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

    // Attach scroll listener
    window.addEventListener('scroll', handleScroll)

    // Trigger navbar entrance transition
    setTimeout(() => {
        animateNavbar.value = true
    }, 100)

    // Close profile dropdown on click outside
    window.addEventListener('click', (e) => {
        const button = document.querySelector('[aria-label="Profile Menu"]')
        const dropdown = button?.nextElementSibling
        if (profileOpen.value && button && dropdown && !button.contains(e.target) && !dropdown.contains(e.target)) {
            profileOpen.value = false
        }
    })
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})

const selectAboutSlide = (index) => {
    if (isHomePage()) {
        const tentangSection = document.getElementById('tentang')
        if (tentangSection) {
            tentangSection.scrollIntoView({ behavior: 'smooth' })
        }
        window.dispatchEvent(new CustomEvent('select-about-slide', { detail: { index } }))
    } else {
        window.location.href = `/?slide=${index}#tentang`
    }
}

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
        class="fixed top-0 left-0 right-0 z-50 bg-transparent transition-all duration-[1200ms] transform ease-out"
        :class="animateNavbar ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-8'"
    >
        <div
            class="mx-auto transition-all duration-500 ease-in-out"
            :class="isScrolled 
                ? 'max-w-5xl rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg shadow-lg border border-gray-200/50 dark:border-gray-700/50 px-6 py-2 mt-4 mx-4 md:mx-auto' 
                : 'max-w-full rounded-none bg-white/20 dark:bg-gray-900/20 backdrop-blur-sm border-b border-t-transparent border-x-transparent border-white/20 dark:border-gray-800/20 shadow-sm px-6 py-3 mt-0 mx-0'"
        >
            <div 
                class="container mx-auto flex justify-between items-center transition-all duration-500 ease-in-out"
                :class="isScrolled ? 'h-12' : 'h-14'"
            >
                <!-- LOGO -->
                <div class="flex items-center gap-3">
                    <img
                        src="/services/logobateng3.png"
                        alt="logo bangka tengah"
                        class="transition-all duration-500 ease-in-out object-contain"
                        :class="isScrolled ? 'w-9 h-9' : 'w-11 h-11'"
                    >
                    <div 
                        class="flex flex-col transition-all duration-500 ease-in-out overflow-hidden"
                        :class="isScrolled ? 'max-w-0 opacity-0 pointer-events-none' : 'max-w-[200px] opacity-100'"
                    >
                        <h1 class="text-xl font-bold text-gray-800 dark:text-white whitespace-nowrap leading-none">
                            BEDULANG
                        </h1>
                        <p class="text-[10px] text-gray-500 dark:text-gray-300 whitespace-nowrap leading-none mt-1">
                            Beragam Layanan Dalam Satu Wadah
                        </p>
                    </div>
                </div>

                <!-- MENU -->
                <div
                    class="hidden md:flex items-center space-x-8 text-gray-700 dark:text-gray-200 font-medium"
                >
                    <a href="/#beranda" @click="navigateMenu($event, '#beranda')" class="hover:text-blue-600 transition">
                        Beranda
                    </a>
                    
                    <!-- TENTANG DROPDOWN -->
                    <div class="relative group py-2">
                        <a href="/#tentang" @click="navigateMenu($event, '#tentang')" class="hover:text-blue-600 transition flex items-center gap-1">
                            Tentang
                            <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </a>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute left-0 top-full hidden group-hover:block w-48 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl py-2 z-50">
                            <a 
                                href="javascript:void(0)"
                                @click="selectAboutSlide(1)"
                                class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                            >
                                Filosofi
                            </a>
                            <a 
                                href="javascript:void(0)"
                                @click="selectAboutSlide(2)"
                                class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                            >
                                Visi
                            </a>
                            <a 
                                href="javascript:void(0)"
                                @click="selectAboutSlide(3)"
                                class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                            >
                                Misi
                            </a>
                        </div>
                    </div>

                <!-- LAYANAN DROPDOWN -->
                <div class="relative group py-2">
                    <a href="/#layanan" @click="navigateMenu($event, '#layanan')" class="hover:text-blue-600 transition flex items-center gap-1">
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
                                <Link 
                                    v-for="app in infoServices" 
                                    :key="app.name" 
                                    :href="'/layanan/' + app.id"
                                    class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                                >
                                    {{ app.name }}
                                </Link>
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
                                <Link 
                                    v-for="app in publicServices" 
                                    :key="app.name" 
                                    :href="'/layanan/' + app.id"
                                    class="block px-5 py-2.5 hover:bg-blue-50 dark:hover:bg-gray-700 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                                >
                                    {{ app.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- APLIKASI DROPDOWN -->
                <div class="relative group py-2">
                    <a href="/#aplikasi" @click="navigateMenu($event, '#aplikasi')" class="hover:text-blue-600 transition flex items-center gap-1">
                        Aplikasi
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </a>
                    
                    <!-- App Dropdown -->
                    <div class="absolute left-0 top-full hidden group-hover:block w-64 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl py-2 z-50">
                        <Link 
                            v-for="app in regionalApps" 
                            :key="app.name" 
                            href="/login"
                            class="block px-5 py-3 hover:bg-blue-50 dark:hover:bg-gray-700 font-medium hover:text-blue-600 dark:hover:text-blue-400 transition"
                        >
                            {{ app.name }}
                        </Link>
                    </div>
                </div>

                <a href="/#kontak" @click="navigateMenu($event, '#kontak')" class="hover:text-blue-600 transition">
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

                <div v-if="authUser" class="relative">
                    <button
                        @click="profileOpen = !profileOpen"
                        class="inline-flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 p-2.5 shadow transition-all duration-200"
                        aria-label="Profile Menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </button>

                    <div
                        v-show="profileOpen"
                        class="absolute right-0 mt-3 w-60 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:bg-gray-800 dark:border-gray-700 z-50 transition-all"
                    >
                        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Signed in as</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">{{ authUser.name }}</p>
                        </div>

                        <Link
                            href="/profile"
                            class="block w-full px-5 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 transition"
                        >
                            Profile
                        </Link>

                        <template v-if="isAdmin">
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            
                            <Link
                                href="/admin/cctv"
                                class="block w-full px-5 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 font-medium transition"
                            >
                                CCTV
                            </Link>

                            <Link
                                href="/admin/users"
                                class="block w-full px-5 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700 font-medium transition"
                            >
                                Kelola Admin
                            </Link>
                        </template>

                        <div class="border-t border-gray-100 dark:border-gray-700"></div>

                        <Link
                            method="post"
                            href="/logout"
                            as="button"
                            class="block w-full px-5 py-3 text-left text-sm text-red-600 hover:bg-red-50/30 dark:hover:bg-red-950/20 font-semibold transition"
                        >
                            Logout
                        </Link>
                    </div>
                </div>

                <Link
                    v-else
                    href="/login"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow hover:shadow-lg transition"
                >
                    Login
                </Link>

            </div>

            <!-- MOBILE -->
            <button
                class="md:hidden text-3xl text-gray-700 dark:text-white"
                @click="open = !open"
            >
                ☰
            </button>

        </div>
        </div>

        <!-- MOBILE MENU -->
        <div
            v-if="open"
            class="md:hidden border-t border-gray-200 dark:border-gray-700 px-6 py-4 space-y-3 bg-white dark:bg-gray-800"
        >

            <a href="/#beranda" @click="open = false; navigateMenu($event, '#beranda')" class="block hover:text-blue-600">
                Beranda
            </a>

            <div class="space-y-1">
                <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mt-2 px-1">Tentang</span>
                <a 
                    href="javascript:void(0)" 
                    @click="open = false; selectAboutSlide(1)" 
                    class="block pl-3 py-1 hover:text-blue-600 transition text-sm text-gray-700 dark:text-gray-300"
                >
                    • Filosofi
                </a>
                <a 
                    href="javascript:void(0)" 
                    @click="open = false; selectAboutSlide(2)" 
                    class="block pl-3 py-1 hover:text-blue-600 transition text-sm text-gray-700 dark:text-gray-300"
                >
                    • Visi
                </a>
                <a 
                    href="javascript:void(0)" 
                    @click="open = false; selectAboutSlide(3)" 
                    class="block pl-3 py-1 hover:text-blue-600 transition text-sm text-gray-700 dark:text-gray-300"
                >
                    • Misi
                </a>
            </div>

            <a href="/#layanan" @click="open = false; navigateMenu($event, '#layanan')" class="block hover:text-blue-600">
                Layanan
            </a>

            <a href="/#aplikasi" @click="open = false; navigateMenu($event, '#aplikasi')" class="block hover:text-blue-600">
                Aplikasi
            </a>

            <a href="/#kontak" @click="open = false; navigateMenu($event, '#kontak')" class="block hover:text-blue-600">
                Kontak
            </a>

            <button
                @click="toggleDarkMode"
                class="w-full mt-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg"
            >
                {{ darkMode ? '☀️ Light Mode' : '🌙 Dark Mode' }}
            </button>

            <template v-if="authUser">
                <Link
                    href="/profile"
                    class="block text-center mt-2 px-4 py-2 bg-white text-gray-800 rounded-lg border border-gray-200 hover:bg-gray-50"
                >
                    Profile
                </Link>

                <Link
                    method="post"
                    href="/logout"
                    as="button"
                    class="block text-center mt-2 px-4 py-2 bg-red-600 text-white rounded-lg"
                >
                    Logout
                </Link>

                <template v-if="isAdmin">
                    <Link
                        href="/admin/cctv"
                        class="block text-center mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg"
                    >
                        CCTV
                    </Link>

                    <Link
                        href="/admin/users"
                        class="block text-center mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg"
                    >
                        Kelola Admin
                    </Link>
                </template>
            </template>

            <Link
                v-else
                href="/login"
                class="block text-center mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg"
            >
                Login
            </Link>
        </div>

    </nav>
</template>
