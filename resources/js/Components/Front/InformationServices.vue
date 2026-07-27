<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const isAuthenticated = computed(() => !!page.props.auth?.user)

const activeTab = ref('info')

const apps = [
    {
        name: 'PEMERINTAHAN',
        slug: 'pemerintahan',
        logo: '/services/pemerintahan.svg',
    },
    {
        name: 'PENDIDIKAN',
        slug: 'pendidikan',
        logo: '/services/pendidikan.svg',
    },
    {
        name: 'KESEHATAN',
        slug: 'kesehatan',
        logo: '/services/kesehatan.svg',
    },
    {
        name: 'PERINDAGKOP-UKM',
        slug: 'perindagkop-ukm',
        logo: '/services/perindagkop.svg',
    },
    {
        name: 'KDMP',
        slug: 'kdmp',
        logo: '/services/kdmp.png',
    },
    {
        name: 'SOSIAL',
        slug: 'sosial',
        logo: '/services/sosial.svg',
    },
    {
        name: 'KEPENDUDUKAN',
        slug: 'kependudukan',
        logo: '/services/kependudukan.svg',
    },
    {
        name: 'KEPEGAWAIAN',
        slug: 'kepegawaian',
        logo: '/services/kepegawaian.svg',
    },
    {
        name: 'Pertanian dan Ketahanan Pangan',
        slug: 'pertanian',
        logo: '/services/pertanian.svg',
    },
    {
        name: 'Informasi Publik',
        slug: 'informasi-publik',
        logo: '/services/informasi publik.svg',
    },
    {
        name: 'PARIWISATA',
        slug: 'pariwisata',
        logo: '/services/pariwisata.svg',
    },
    {
        name: 'DATA SEKTORAL',
        slug: 'data-sektoral',
        logo: '/services/sektoral.svg',
    },
    {
        name: 'Makanan Bergizi Gratis',
        slug: 'makanan-bergizi-gratis',
        logo: '/services/bgn.png',
    },
]

const publicServices = [
    {
        name: 'PERIZINAN TERPADU',
        slug: 'perizinan-terpadu',
        logo: '/services/govern.svg',
        url: '/layanan/perizinan-terpadu'
    },
    {
        name: 'PELAYANAN KESEHATAN',
        slug: 'pelayanan-kesehatan',
        logo: '/services/health.svg',
        url: '/layanan/pelayanan-kesehatan'
    },
    {
        name: 'PENGADUAN PPID',
        slug: 'pengaduan-ppid',
        logo: '/services/lapor.png',
        url: '/layanan/pengaduan-ppid'
    },
    {
        name: 'PENGADUAN BEDULANG',
        slug: 'layanan-pengaduan-bedulang',
        logo: '/services/lapor.png',
        url: '/layanan/pengaduan-bedulang'
    },
    {
        name: 'MONITORING CCTV',
        slug: 'monitoring-cctv',
        logo: '/services/cctv.svg',
        url: '/cctv',
        allowGuest: true
    },
    {
        name: 'ADMINISTRASI WARGA',
        slug: 'administrasi-warga',
        logo: '/services/society.svg',
        url: '/layanan/administrasi-warga'
    },
]
const setTab = (tab) => {
    activeTab.value = tab
}

const handleServicesTabSelect = (event) => {
    const requestedTab = event.detail?.tab
    if (requestedTab === 'info' || requestedTab === 'publik') {
        activeTab.value = requestedTab
    }
}

onMounted(() => {
    window.addEventListener('select-services-tab', handleServicesTabSelect)

    const urlParams = new URLSearchParams(window.location.search)
    const tabParam = urlParams.get('tab')
    if (tabParam === 'info' || tabParam === 'publik') {
        activeTab.value = tabParam
        if (window.location.hash === '#layanan') {
            setTimeout(() => {
                const el = document.getElementById('layanan')
                if (el) el.scrollIntoView({ behavior: 'smooth' })
            }, 300)
        }
    }
})

onUnmounted(() => {
    window.removeEventListener('select-services-tab', handleServicesTabSelect)
})
</script>

<template>
<section
    id="layanan"
    data-aos="fade-up"
    class="py-16 bg-gradient-to-b from-white via-blue-50/20 to-gray-100 dark:from-gray-950 dark:via-gray-900 dark:to-gray-800 transition-colors duration-300">

    <div class="w-full max-w-[1400px] mx-auto px-6 lg:px-8">

        <!-- Header -->
        <div class="max-w-2xl mx-auto text-center mb-8">
            <h2 class="text-5xl font-bold leading-tight text-gray-900 dark:text-white tracking-tight">
                Layanan <span class="text-blue-600 dark:text-blue-400">Bedulang</span>
            </h2>
        </div>

        <!-- Switch Tab Selector -->
        <div class="flex justify-center mb-12">
            <div class="relative flex p-1.5 bg-gray-200/80 dark:bg-gray-800/80 rounded-2xl backdrop-blur-sm border border-gray-300/30 dark:border-gray-700/50">
                <!-- Sliding Background -->
                <div 
                    class="absolute top-1.5 bottom-1.5 left-1.5 rounded-xl bg-white dark:bg-gray-700 shadow-md transition-all duration-300 ease-out"
                    :style="{
                        width: '220px',
                        transform: activeTab === 'info' ? 'translateX(0px)' : 'translateX(220px)'
                    }"
                ></div>
                
                <button 
                    @click="setTab('info')"
                    class="relative z-10 w-[220px] py-4 text-base font-bold rounded-xl transition duration-300 text-center cursor-pointer"
                    :class="activeTab === 'info' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                >
                    Informasi & Data
                </button>
                
                <button 
                    @click="setTab('publik')"
                    class="relative z-10 w-[220px] py-4 text-base font-bold rounded-xl transition duration-300 text-center cursor-pointer"
                    :class="activeTab === 'publik' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200'"
                >
                    Layanan Publik
                </button>
            </div>
        </div>

        <!-- Main Outer Box Wrapper -->
        <div class="rounded-3xl border border-gray-200/60 dark:border-gray-700/50 bg-white/40 dark:bg-gray-900/30 backdrop-blur-md shadow-xl p-8 md:p-10 transition duration-300">
            
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform scale-[0.99] opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-[0.99] opacity-0"
                mode="out-in"
            >
                <div :key="activeTab">
                    <!-- Dynamic Title & Description -->
                    <div class="text-center mb-8 border-b border-gray-200/40 dark:border-gray-700/40 pb-6">
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                            {{ activeTab === 'info' ? 'Layanan Informasi & Data' : 'Layanan Publik' }}
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            {{ activeTab === 'info' ? 'Pilih kategori untuk melihat informasi dan data Kabupaten Bangka Tengah.' : 'Akses berbagai portal pelayanan publik terpadu Kabupaten Bangka Tengah.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:flex md:flex-wrap md:justify-center md:gap-5 max-h-[385px] md:max-h-none overflow-y-auto md:overflow-visible pr-1">
                        <!-- TAB: INFORMASI & DATA -->
                        <template v-if="activeTab === 'info'">
                            <Link
                                v-for="(app, index) in apps"
                                :key="app.name"
                                :href="isAuthenticated ? '/layanan/' + app.slug : '/login'"
                                class="group w-full md:w-[180px] flex-shrink-0 transition-all duration-300"
                            >
                                <div 
                                    :id="'layanan-' + app.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                                    class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-500"
                                >
                                    <!-- Lock Icon Badge for Guest User -->
                                    <div 
                                        v-if="!isAuthenticated" 
                                        class="absolute top-2.5 right-2.5 z-10 p-1.5 rounded-full bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 shadow-xs backdrop-blur-xs flex items-center justify-center"
                                        title="Login diperlukan untuk mengakses pelayanan"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                    </div>

                                    <!-- Thumbnail -->
                                    <div class="h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-900/70 relative">
                                        <img :src="app.logo" :alt="app.name" class="w-[90%] h-[90%] object-contain transition-transform duration-500 ease-out group-hover:scale-105" :class="{ 'opacity-80': !isAuthenticated }" />
                                    </div>
                                    <!-- Footer -->
                                    <div class="flex items-center justify-between px-3.5 py-3.5">
                                        <h3 class="text-[12px] font-bold uppercase tracking-wider text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                                            {{ app.name }}
                                        </h3>
                                        <svg class="w-4 h-4 text-gray-400 transition-all duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </Link>
                        </template>

                        <!-- TAB: LAYANAN PUBLIK -->
                        <template v-else>
                            <Link
                                v-for="(app, index) in publicServices"
                                :key="app.name"
                                :href="isAuthenticated || app.allowGuest ? app.url : '/login'"
                                class="group w-full md:w-[180px] flex-shrink-0 transition-all duration-300"
                            >
                                <div 
                                    :id="'layanan-' + app.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                                    class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-500"
                                >
                                    <!-- Lock Icon Badge for Guest User -->
                                    <div 
                                        v-if="!isAuthenticated && !app.allowGuest" 
                                        class="absolute top-2.5 right-2.5 z-10 p-1.5 rounded-full bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 shadow-xs backdrop-blur-xs flex items-center justify-center"
                                        title="Login diperlukan untuk mengakses pelayanan"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                    </div>

                                    <!-- Thumbnail -->
                                    <div class="h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-900/70 relative">
                                        <img :src="app.logo" :alt="app.name" class="w-[70%] h-[70%] object-contain transition-transform duration-500 ease-out group-hover:scale-105" :class="{ 'opacity-80': !isAuthenticated && !app.allowGuest }" />
                                    </div>
                                    <!-- Footer -->
                                    <div class="flex items-center justify-between px-3.5 py-3.5">
                                        <h3 class="text-[12px] font-bold uppercase tracking-wider text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                                            {{ app.name }}
                                        </h3>
                                        <svg class="w-4 h-4 text-gray-400 transition-all duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </Link>
                        </template>
                    </div>
                </div>
            </Transition>

        </div>

    </div>
</section>
</template>