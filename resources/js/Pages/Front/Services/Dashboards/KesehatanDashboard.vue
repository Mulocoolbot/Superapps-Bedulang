<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    dashboard: {
        type: Object,
        default: () => ({}),
    },
})

// Data references
const faskesList = computed(() => props.dashboard?.faskes_list ?? [])
const summary = computed(() => props.dashboard?.summary ?? {
    total_faskes: 0,
    total_rsud: 0,
    total_puskesmas: 0,
    total_rs_swasta: 0,
})

const availableJenisFaskes = computed(() => props.dashboard?.available_jenis_faskes ?? [])
const availableJenisPelayanan = computed(() => props.dashboard?.available_jenis_pelayanan ?? [])

// State filters
const selectedJenisFaskes = ref('all')
const selectedJenisPelayanan = ref('all')
const searchQuery = ref('')
const activeTab = ref('daftar-faskes')

// Pagination state
const currentPage = ref(1)
const perPage = ref(5)

// Watch filters and search to reset pagination to page 1
watch([selectedJenisFaskes, selectedJenisPelayanan, searchQuery, activeTab], () => {
    currentPage.value = 1
})

// Filter logic
const filteredFaskes = computed(() => {
    return faskesList.value.filter(item => {
        // Filter: Jenis Faskes
        if (selectedJenisFaskes.value !== 'all' && item.jenis !== selectedJenisFaskes.value) {
            return false
        }
        // Filter: Jenis Pelayanan
        if (selectedJenisPelayanan.value !== 'all' && !item.pelayanan.includes(selectedJenisPelayanan.value)) {
            return false
        }
        // Search query (Nama, Kecamatan, Alamat)
        if (searchQuery.value.trim() !== '') {
            const query = searchQuery.value.toLowerCase()
            const matchNama = item.nama.toLowerCase().includes(query)
            const matchKecamatan = item.kecamatan.toLowerCase().includes(query)
            const matchAlamat = item.alamat.toLowerCase().includes(query)
            if (!matchNama && !matchKecamatan && !matchAlamat) {
                return false
            }
        }
        return true
    })
})

// Pagination calculations
const totalPages = computed(() => {
    return Math.ceil(filteredFaskes.value.length / perPage.value) || 1
})

const paginatedFaskes = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredFaskes.value.slice(start, end)
})

// Navigation methods
const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const goToPage = (page) => {
    currentPage.value = page
}

const visiblePages = computed(() => {
    const pages = []
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i)
    }
    return pages
})

// Helper colors for badges based on facility type
const getBadgeStyles = (jenis) => {
    switch (jenis) {
        case 'Rumah Sakit Daerah':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-100 dark:border-blue-900/40'
        case 'Puskesmas':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-100 dark:border-emerald-900/40'
        case 'Rumah Sakit Swasta':
            return 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-100 dark:border-purple-900/40'
        default:
            return 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-100 dark:border-slate-700'
    }
}
</script>

<template>
    <div class="space-y-8">
        <!-- 1. SUMMARY STATISTICS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 animate-fade-in-up [animation-delay:100ms]">
            <!-- Total Faskes Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-600 dark:bg-emerald-500" />
                <div class="pl-2 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Faskes</span>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                            {{ summary.total_faskes }} <span class="text-xs font-medium text-slate-400">Unit</span>
                        </p>
                        <p class="mt-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Fasilitas Kesehatan Terdaftar</p>
                    </div>
                    
                </div>
            </div>

            <!-- RSUD Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600 dark:bg-blue-500" />
                <div class="pl-2 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">RS Daerah (RSUD)</span>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                            {{ summary.total_rsud }} <span class="text-xs font-medium text-slate-400">Faskes</span>
                        </p>
                        <p class="mt-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400">Rumah Sakit Milik Daerah</p>
                    </div>
                    
                </div>
            </div>

            <!-- Puskesmas Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-teal-600 dark:bg-teal-500" />
                <div class="pl-2 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Puskesmas</span>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                            {{ summary.total_puskesmas }} <span class="text-xs font-medium text-slate-400">Puskesmas</span>
                        </p>
                        <p class="mt-1 text-[11px] font-semibold text-teal-600 dark:text-teal-400">Pusat Kesehatan Masyarakat</p>
                    </div>
                    
                </div>
            </div>

            <!-- RS Swasta/Klinik Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-purple-600 dark:bg-purple-500" />
                <div class="pl-2 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">RS Swasta / Swasta</span>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                            {{ summary.total_rs_swasta }} <span class="text-xs font-medium text-slate-400">Unit</span>
                        </p>
                        <p class="mt-1 text-[11px] font-semibold text-purple-600 dark:text-purple-400">Rumah Sakit & Klinik Swasta</p>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- 2. FILTERS & SEARCH CONTAINER -->
        <div class="animate-fade-in-up [animation-delay:150ms]">
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <!-- Search Input -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Pencarian Faskes</label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama faskes, kecamatan, alamat..."
                                class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 dark:focus:ring-emerald-950/40 focus:outline-none transition"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Filter Jenis Faskes -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jenis Faskes</label>
                        <div class="relative">
                            <select
                                v-model="selectedJenisFaskes"
                                class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 dark:focus:ring-emerald-950/40"
                            >
                                <option value="all">Semua Jenis Faskes</option>
                                <option v-for="jenis in availableJenisFaskes" :key="jenis" :value="jenis">
                                    {{ jenis }}
                                </option>
                            </select>
                            <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>

                    <!-- Filter Jenis Pelayanan -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jenis Pelayanan</label>
                        <div class="relative">
                            <select
                                v-model="selectedJenisPelayanan"
                                class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 dark:focus:ring-emerald-950/40"
                            >
                                <option value="all">Semua Pelayanan</option>
                                <option v-for="pelayanan in availableJenisPelayanan" :key="pelayanan" :value="pelayanan">
                                    {{ pelayanan }}
                                </option>
                            </select>
                            <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TABS SWITCHER -->
        <div class="animate-fade-in-up [animation-delay:200ms] flex justify-center">
            <div class="inline-flex p-1 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/80 shadow-sm max-w-md w-full">
                <button
                    @click="activeTab = 'daftar-faskes'"
                    class="flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer text-center flex items-center justify-center gap-2 select-none"
                    :class="activeTab === 'daftar-faskes'
                        ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-md ring-1 ring-emerald-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    <span>📋</span> Daftar Faskes
                </button>
                <button
                    @click="activeTab = 'jenis-pelayanan'"
                    class="flex-1 py-3 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer text-center flex items-center justify-center gap-2 select-none"
                    :class="activeTab === 'jenis-pelayanan'
                        ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-md ring-1 ring-emerald-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    <span>🏥</span> Jenis Pelayanan
                </button>
            </div>
        </div>

        <!-- 4. TAB CONTENT WRAPPERS -->
        <div class="animate-fade-in-up [animation-delay:250ms]">
            <!-- TAB 1: DAFTAR FASKES -->
            <div v-if="activeTab === 'daftar-faskes'" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-md transition-all duration-300">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans flex items-center gap-2">
                             Tabel Daftar Fasilitas Kesehatan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Daftar fasilitas kesehatan Kabupaten Bangka Tengah berdasarkan kriteria pencarian dan filter terpilih.
                        </p>
                    </div>
                </div>

                <div class="w-full px-3 sm:px-6 py-4">
                    <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                        <table class="w-full text-left text-xs sm:text-sm border-collapse min-w-[700px]">
                            <thead class="bg-gradient-to-r from-emerald-50/90 via-teal-50/70 to-emerald-50/50 dark:from-emerald-950/80 dark:via-teal-950/60 dark:to-emerald-950/40 text-emerald-950 dark:text-emerald-200 font-extrabold uppercase text-[11px] tracking-wider border-b border-emerald-100 dark:border-slate-800">
                                <tr>
                                    <th class="py-4 px-3 w-[8%] text-center">No</th>
                                    <th class="py-4 px-4 w-[35%] text-left">Nama Faskes</th>
                                    <th class="py-4 px-3 w-[22%] text-center">Jenis</th>
                                    <th class="py-4 px-3 w-[15%] text-center">Kecamatan</th>
                                    <th class="py-4 px-4 w-[20%] text-left">Alamat</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                <tr
                                    v-for="(item, idx) in paginatedFaskes"
                                    :key="item.id"
                                    class="hover:bg-emerald-50/20 dark:hover:bg-emerald-950/20 transition-colors duration-200"
                                >
                                    <!-- Number -->
                                    <td class="py-3.5 px-3 text-center text-slate-500 dark:text-slate-400 font-bold font-sans">
                                        {{ (currentPage - 1) * perPage + idx + 1 }}
                                    </td>

                                    <!-- Facility Name -->
                                    <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2.5">
                                            
                                            <div>
                                                {{ item.nama }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Facility Type Badge -->
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full border text-[11px] font-extrabold tracking-wide" :class="getBadgeStyles(item.jenis)">
                                            {{ item.jenis }}
                                        </span>
                                    </td>

                                    <!-- Kecamatan -->
                                    <td class="py-3.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                        {{ item.kecamatan }}
                                    </td>

                                    <!-- Address -->
                                    <td class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-sans max-w-[200px] truncate hover:text-clip hover:whitespace-normal" :title="item.alamat">
                                        {{ item.alamat }}
                                    </td>
                                </tr>

                                <tr v-if="filteredFaskes.length === 0">
                                    <td colspan="5" class="py-12 text-center text-slate-400 text-sm font-medium">
                                        Tidak ada data fasilitas kesehatan yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Custom Pagination Controls -->
                <div class="p-4 sm:px-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                        Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ filteredFaskes.length > 0 ? (currentPage - 1) * perPage + 1 : 0 }}</span> - 
                        <span class="font-bold text-slate-800 dark:text-white">{{ Math.min(currentPage * perPage, filteredFaskes.length) }}</span> dari 
                        <span class="font-bold text-slate-800 dark:text-white">{{ filteredFaskes.length }}</span> data
                    </div>
                    
                    <div class="flex items-center gap-1.5" v-if="totalPages > 1">
                        <!-- Left Arrow -->
                        <button
                            v-if="currentPage > 1"
                            @click="prevPage"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95 cursor-pointer select-none"
                            aria-label="Halaman Sebelumnya"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        
                        <!-- Page Numbers -->
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="goToPage(page)"
                            class="h-8 w-8 rounded-lg text-xs font-bold transition duration-200 select-none cursor-pointer flex items-center justify-center"
                            :class="currentPage === page 
                                ? 'bg-emerald-600 text-white dark:bg-emerald-500 dark:text-white shadow-md ring-1 ring-emerald-500/20' 
                                : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        >
                            {{ page }}
                        </button>
                        
                        <!-- Right Arrow -->
                        <button
                            v-if="currentPage < totalPages"
                            @click="nextPage"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95 cursor-pointer select-none"
                            aria-label="Halaman Selanjutnya"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 2: JENIS PELAYANAN -->
            <div v-else class="space-y-6">
                <!-- Instructions / info banner -->
                <div class="rounded-2xl border border-emerald-100 dark:border-emerald-950/40 bg-emerald-50/50 dark:bg-emerald-950/20 p-5 shadow-sm">
                    <div class="flex gap-3">
                        <span class="text-xl">🩺</span>
                        <div>
                            <h4 class="text-sm font-bold text-emerald-950 dark:text-emerald-200">Lokasi Faskes & Jenis Pelayanan Terdaftar</h4>
                            <p class="text-xs text-emerald-800/80 dark:text-emerald-400/80 mt-1">
                                Klik filter Jenis Pelayanan di bagian atas untuk menyoroti pelayanan kesehatan spesifik pada daftar di bawah ini. Pelayanan yang dicari akan otomatis disorot dengan warna hijau pekat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grid of Faskes with services details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div 
                        v-for="item in filteredFaskes" 
                        :key="item.id"
                        class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-md flex flex-col justify-between hover:shadow-lg transition-all duration-300"
                    >
                        <div>
                            <!-- Header Info -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h4 class="text-base font-extrabold text-slate-900 dark:text-white leading-snug">
                                        {{ item.nama }}
                                    </h4>
                                    <span class="inline-block mt-1.5 px-2.5 py-0.5 text-[10px] font-extrabold tracking-wide uppercase rounded-full border" :class="getBadgeStyles(item.jenis)">
                                        {{ item.jenis }}
                                    </span>
                                </div>
                                <span class="px-3 py-1 rounded-xl bg-slate-50 dark:bg-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400 border border-slate-100 dark:border-slate-700/60 shrink-0 select-none">
                                    📍 Kec. {{ item.kecamatan }}
                                </span>
                            </div>

                            <!-- Address -->
                            <div class="flex items-start gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4 font-sans">
                                <span class="shrink-0 mt-0.5">📌</span>
                                <p class="leading-relaxed">{{ item.alamat }}</p>
                            </div>
                        </div>

                        <!-- Services List -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">Layanan Tersedia</span>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="serv in item.pelayanan" 
                                    :key="serv"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold tracking-wide border transition-all duration-200"
                                    :class="selectedJenisPelayanan === serv 
                                        ? 'bg-emerald-600 text-white border-emerald-600 dark:bg-emerald-500 dark:border-emerald-500 shadow-sm scale-[1.03]' 
                                        : 'bg-slate-50 text-slate-700 border-slate-100 dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/80'"
                                >
                                    {{ serv }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredFaskes.length === 0" class="col-span-full py-12 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-center text-slate-400 text-sm font-medium">
                        Tidak ada fasilitas kesehatan yang cocok dengan filter yang dipilih.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
