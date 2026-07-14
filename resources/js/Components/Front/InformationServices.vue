<script setup>
import { ref, onMounted, nextTick } from 'vue'

const activeTab = ref('info')

const apps = [
    {
        name: 'PEMERINTAHAN',
        logo: '/services/govern.svg',
        url: '/login'
    },
    {
        name: 'PENDIDIKAN',
        logo: '/services/education.svg',
        url: '/login'
    },
    {
        name: 'KESEHATAN',
        logo: '/services/health.svg',
        url: '/login'
    },
    {
        name: 'PERINDAGKOP-UKM',
        logo: '/services/culture.svg',
        url: '/login'
    },
    {
        name: 'HUKUM',
        logo: '/services/law.svg',
        url: '/login'
    },
    {
        name: 'SOSIAL',
        logo: '/services/social.svg',
        url: '/login'
    },
    {
        name: 'KEPENDUDUKAN',
        logo: '/services/society.svg',
        url: '/login'
    },
    {
        name: 'KEPEGAWAIAN',
        logo: '/services/work.svg',
        url: '/login'
    },
    {
        name: 'LINGKUNGAN',
        logo: '/services/environment.svg',
        url: '/login'
    },
    {
        name: 'GEOGRAFIS',
        logo: '/services/geo.svg',
        url: '/login'
    },
    {
        name: 'PARIWISATA',
        logo: '/services/vacation.svg',
        url: '/login'
    },
    {
        name: 'DATA SEKTORAL',
        logo: '/services/voting.svg',
        url: '/login'
    },
]

const publicServices = [
    {
        name: 'PERIZINAN TERPADU',
        logo: '/services/govern.svg',
        url: '/login'
    },
    {
        name: 'PELAYANAN KESEHATAN',
        logo: '/services/health.svg',
        url: '/login'
    },
    {
        name: 'PENGADUAN PPID',
        logo: '/services/lapor.png',
        url: '/login'
    },
    {
        name: 'ADMINISTRASI WARGA',
        logo: '/services/society.svg',
        url: '/login'
    },
]
const setTab = (tab) => {
    activeTab.value = tab
}

onMounted(() => {
    // No scroll check needed
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
            <h2 class="text-5xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight">
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

                    <div class="flex flex-wrap justify-center gap-5">
                        <!-- TAB: INFORMASI & DATA -->
                        <template v-if="activeTab === 'info'">
                            <a
                                v-for="(app, index) in apps"
                                :key="app.name"
                                :href="app.url"
                                class="group w-[180px] flex-shrink-0 transition-all duration-300"
                            >
                                <div 
                                    :id="'layanan-' + app.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                                    class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-500"
                                >
                                    <!-- Thumbnail -->
                                    <div class="h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-900/70">
                                        <img :src="app.logo" :alt="app.name" class="w-[65%] h-[65%] object-contain transition-transform duration-500 ease-out group-hover:scale-105" />
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
                            </a>
                        </template>

                        <!-- TAB: LAYANAN PUBLIK -->
                        <template v-else>
                            <a
                                v-for="(app, index) in publicServices"
                                :key="app.name"
                                :href="app.url"
                                class="group w-[180px] flex-shrink-0 transition-all duration-300"
                            >
                                <div 
                                    :id="'layanan-' + app.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                                    class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-500"
                                >
                                    <!-- Thumbnail -->
                                    <div class="h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-900/70">
                                        <img :src="app.logo" :alt="app.name" class="w-[65%] h-[65%] object-contain transition-transform duration-500 ease-out group-hover:scale-105" />
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
                            </a>
                        </template>
                    </div>
                </div>
            </Transition>

        </div>

    </div>
</section>
</template>
