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

// Formatting current date fallback
const currentDateString = computed(() => {
    const now = new Date()
    return now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
})

const displayDate = computed(() => {
    return props.dashboard?.metadata?.updated_at || currentDateString.value
})

const searchQuery = ref('')

// Records array containing OPD personnel counts
const records = computed(() => {
    if (props.dashboard?.records && Array.isArray(props.dashboard.records) && props.dashboard.records.length > 0) {
        return props.dashboard.records
    }
    // Fallback static mock data
    return [
        { id: 1, nama_upd: 'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia Daerah', kode_upd: '19.07', jumlah_pns: 33, jumlah_p3k: 8, jumlah_p3k_pw: 9, total: 50 },
        { id: 2, nama_upd: 'Badan Kesatuan Bangsa dan Politik', kode_upd: '19.32', jumlah_pns: 17, jumlah_p3k: 5, jumlah_p3k_pw: 4, total: 26 },
        { id: 3, nama_upd: 'Badan Penanggulangan Bencana Daerah', kode_upd: '19.34', jumlah_pns: 11, jumlah_p3k: 21, jumlah_p3k_pw: 1, total: 33 },
        { id: 4, nama_upd: 'Badan Pengelolaan Keuangan dan Aset Daerah', kode_upd: '19.05', jumlah_pns: 39, jumlah_p3k: 5, jumlah_p3k_pw: 7, total: 51 },
        { id: 5, nama_upd: 'Badan Pengelolaan Pajak dan Retribusi Daerah', kode_upd: '19.06', jumlah_pns: 22, jumlah_p3k: 5, jumlah_p3k_pw: 22, total: 49 },
        { id: 6, nama_upd: 'Badan Perencanaan Pembangunan dan Penelitian Pengembangan Daerah', kode_upd: '19.04', jumlah_pns: 37, jumlah_p3k: 6, jumlah_p3k_pw: 10, total: 53 },
        { id: 7, nama_upd: 'Dinas Kearsipan dan Perpustakaan', kode_upd: '19.25', jumlah_pns: 19, jumlah_p3k: 10, jumlah_p3k_pw: 8, total: 37 },
        { id: 8, nama_upd: 'Dinas Kebudayaan, Pariwisata, Kepemudaan dan Olah Raga', kode_upd: '19.13', jumlah_pns: 42, jumlah_p3k: 3, jumlah_p3k_pw: 14, total: 59 },
        { id: 9, nama_upd: 'Dinas Kependudukan dan Pencatatan Sipil', kode_upd: '19.18', jumlah_pns: 21, jumlah_p3k: 14, jumlah_p3k_pw: 27, total: 62 },
        { id: 10, nama_upd: 'Dinas Kesehatan', kode_upd: '19.09', jumlah_pns: 44, jumlah_p3k: 16, jumlah_p3k_pw: 20, total: 80 },
        { id: 11, nama_upd: 'Dinas Komunikasi, Informatika dan Statistik', kode_upd: '19.21', jumlah_pns: 31, jumlah_p3k: 3, jumlah_p3k_pw: 12, total: 46 },
        { id: 12, nama_upd: 'Dinas Lingkungan Hidup', kode_upd: '19.12', jumlah_pns: 25, jumlah_p3k: 7, jumlah_p3k_pw: 35, total: 67 },
    ]
})

// Aggregate sums dynamically
const totalPns = computed(() => {
    return records.value.reduce((acc, r) => acc + r.jumlah_pns, 0)
})

const totalP3k = computed(() => {
    return records.value.reduce((acc, r) => acc + r.jumlah_p3k, 0)
})

const totalP3kPw = computed(() => {
    return records.value.reduce((acc, r) => acc + r.jumlah_p3k_pw, 0)
})

const grandTotal = computed(() => {
    return records.value.reduce((acc, r) => acc + r.total, 0)
})

const filteredRecords = computed(() => {
    if (!searchQuery.value.trim()) return records.value
    const q = searchQuery.value.toLowerCase()
    return records.value.filter(r => 
        r.nama_upd.toLowerCase().includes(q) || 
        r.kode_upd.toLowerCase().includes(q)
    )
})

// Formatting numbers helper
const formatNumber = (val) => {
    if (val === undefined || val === null) return '0'
    return val.toLocaleString('id-ID')
}

// Pagination logic
const currentPage = ref(1)
const perPage = ref(10)

const totalPages = computed(() => {
    return Math.ceil(filteredRecords.value.length / perPage.value) || 1
})

const paginatedRecords = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredRecords.value.slice(start, end)
})

// Sliding visible bubbles max 5
const visiblePages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    const maxVisible = 5
    
    if (total <= maxVisible) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }
    
    let start = current - 2
    if (start < 1) start = 1
    
    let end = start + maxVisible - 1
    if (end > total) {
        end = total
        start = end - maxVisible + 1
    }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

// Reset page on search
watch(searchQuery, () => {
    currentPage.value = 1
})
</script>

<template>
    <div class="mt-8 space-y-8 animate-fade-in-up">
        
        <!-- 1. FULLY DYNAMIC COLOR HEADER CARD -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 dark:from-indigo-950/80 dark:via-purple-950/80 dark:to-pink-950/80 border border-indigo-100 dark:border-indigo-900/60 p-6 sm:p-8 text-slate-800 dark:text-white shadow-xl transition-all duration-300">
            <!-- Background Glow & Pattern Decor -->
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-indigo-400/10 dark:bg-indigo-400/20 blur-3xl" />
            <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-pink-400/10 dark:bg-pink-400/20 blur-3xl" />

            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-3">
                    <!-- Date badge -->
                    <div class="inline-flex items-center gap-2 rounded-full bg-indigo-100 text-indigo-800 dark:bg-white/10 dark:text-indigo-200 backdrop-blur-md px-3.5 py-1 text-xs font-semibold tracking-wide border border-indigo-200/70 dark:border-white/10 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span>📅 {{ displayDate }}</span>
                    </div>

                    <!-- Main Title -->
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-sans text-slate-900 dark:text-white">
                        Dashboard Kepegawaian
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-indigo-100/90 max-w-xl font-medium leading-relaxed">
                        Data sebaran Aparatur Sipil Negara (ASN) di setiap UPD (Unit Perangkat Daerah) Kabupaten Bangka Tengah yang mencakup PNS, PPPK, dan PPPK Paruh Waktu.
                    </p>
                </div>

                <!-- Grand Total Highlight Pill -->
                <div class="flex items-center gap-4 bg-white/80 dark:bg-white/10 backdrop-blur-md border border-indigo-100 dark:border-white/15 p-4 rounded-2xl shrink-0 shadow-md">
                    <div class="p-3 bg-indigo-600 text-white dark:bg-indigo-500/30 dark:text-indigo-300 rounded-xl shadow-inner">
                        <span class="text-2xl">👥</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-200">Total Keseluruhan Pegawai</div>
                        <div class="text-xl sm:text-2xl font-black tracking-tight font-sans text-indigo-950 dark:text-white">
                            {{ formatNumber(grandTotal) }} <span class="text-xs font-normal text-slate-500 dark:text-indigo-200">Orang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. SUMMARY STATISTICS: SINGLE MEMANJANG CARD (ALL IN ONE HORIZONTAL ROW) -->
        <div class="animate-fade-in-up [animation-delay:100ms]">
            <div class="overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md border-t-4 border-t-indigo-600 dark:border-t-indigo-500 transition-all duration-300">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-800">
                    
                    <!-- Box 1: Total Unit Kerja -->
                    <div class="p-5 flex items-center gap-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="p-3.5 rounded-2xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 shrink-0 font-bold text-xl shadow-inner">
                            🏢
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unit Kerja</div>
                            <div class="text-2xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                {{ records.length }} <span class="text-xs font-medium text-slate-400">UPD</span>
                            </div>
                            <div class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 truncate">Badan & Dinas Terdata</div>
                        </div>
                    </div>

                    <!-- Box 2: Total PNS -->
                    <div class="p-5 flex items-center gap-4 hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="p-3.5 rounded-2xl bg-blue-100 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 shrink-0 font-bold text-xl shadow-inner">
                            👔
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total PNS</div>
                            <div class="text-2xl font-black text-blue-600 dark:text-blue-400 font-sans tracking-tight">
                                {{ formatNumber(totalPns) }} <span class="text-xs font-medium text-slate-400">Orang</span>
                            </div>
                            <div class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 truncate">Pegawai Negeri Sipil</div>
                        </div>
                    </div>

                    <!-- Box 3: Total PPPK (P3K) -->
                    <div class="p-5 flex items-center gap-4 hover:bg-purple-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="p-3.5 rounded-2xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 shrink-0 font-bold text-xl shadow-inner">
                            📜
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Total PPPK</div>
                            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 font-sans tracking-tight">
                                {{ formatNumber(totalP3k) }} <span class="text-xs font-medium text-slate-400">Orang</span>
                            </div>
                            <div class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 truncate">Perjanjian Kerja</div>
                        </div>
                    </div>

                    <!-- Box 4: Total PPPK PW -->
                    <div class="p-5 flex items-center gap-4 hover:bg-pink-50/40 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="p-3.5 rounded-2xl bg-pink-100 dark:bg-pink-950/80 text-pink-600 dark:text-pink-400 shrink-0 font-bold text-xl shadow-inner">
                            ⏳
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-pink-600 dark:text-pink-400">Total PPPK PW</div>
                            <div class="text-2xl font-black text-pink-600 dark:text-pink-400 font-sans tracking-tight">
                                {{ formatNumber(totalP3kPw) }} <span class="text-xs font-medium text-slate-400">Orang</span>
                            </div>
                            <div class="text-[11px] font-semibold text-pink-600 dark:text-pink-400 truncate">Paruh Waktu</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- 3. COLORFUL OPD TABLE CARD (BALANCED & PERFECTLY CENTERED METRICS WITH INNER PADDING) -->
        <div class="animate-fade-in-up [animation-delay:200ms]">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-md transition-all duration-300">
                
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans flex items-center gap-2">
                            <span>🏢</span> Daftar Pegawai Per UPD
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Tabel sebaran data jumlah pegawai per UPD (Unit Perangkat Daerah) Kabupaten Bangka Tengah.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search input -->
                        <div class="relative w-full sm:w-64">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari UPD / Dinas..."
                                class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:outline-none transition"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>

                        <span class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 shrink-0 font-sans">
                            Data OPD
                        </span>
                    </div>
                </div>

                <!-- Table Container with inner side padding to pull content away from outer card edges -->
                <div class="w-full min-h-[640px] flex flex-col justify-between px-3 sm:px-6 py-2">
                    <table class="w-full table-fixed text-left text-sm sm:text-base border-collapse">
                        <thead class="bg-gradient-to-r from-indigo-50/90 via-purple-50/70 to-pink-50/50 dark:from-indigo-950/80 dark:via-purple-950/60 dark:to-pink-950/40 text-indigo-950 dark:text-indigo-200 font-extrabold uppercase text-[11px] sm:text-xs tracking-wider border-b border-indigo-100 dark:border-slate-800 rounded-xl overflow-hidden">
                            <tr>
                                <th class="py-4 px-3 w-[8%] text-center rounded-l-xl">No</th>
                                <th class="py-4 px-4 w-[44%] text-left">Nama UPD</th>
                                <th class="py-4 px-2 w-[12%] text-center">PNS</th>
                                <th class="py-4 px-2 w-[12%] text-center">P3K</th>
                                <th class="py-4 px-2 w-[12%] text-center">P3K PW</th>
                                <th class="py-4 px-3 w-[12%] text-center rounded-r-xl">Total</th>
                            </tr>
                        </thead>

                        <!-- Animated Transition for Table Body on page switches -->
                        <Transition name="page-fade" mode="out-in">
                            <tbody :key="currentPage" class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                <tr 
                                    v-for="(item, idx) in paginatedRecords" 
                                    :key="item.id"
                                    class="hover:bg-indigo-50/40 dark:hover:bg-indigo-950/30 transition-colors duration-200"
                                >
                                    <td class="py-3.5 px-3 w-[8%] text-center text-slate-500 dark:text-slate-400 font-bold font-sans align-middle text-xs sm:text-sm">
                                        {{ (currentPage - 1) * perPage + idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4 w-[44%] font-bold align-middle">
                                        <div class="flex items-start sm:items-center gap-2.5">
                                            <span class="p-1.5 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 shrink-0 shadow-sm text-sm sm:text-base mt-0.5 sm:mt-0">
                                                🏢
                                            </span>
                                            <div class="font-sans text-slate-900 dark:text-white leading-normal text-xs sm:text-sm md:text-base whitespace-normal break-words">
                                                {{ item.nama_upd }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-2 w-[12%] text-center align-middle">
                                        <span class="inline-flex items-center justify-center px-2.5 sm:px-3 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-100 dark:border-blue-900/40 text-xs sm:text-sm font-extrabold min-w-[36px] sm:min-w-[44px]">
                                            {{ formatNumber(item.jumlah_pns) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-2 w-[12%] text-center align-middle">
                                        <span class="inline-flex items-center justify-center px-2.5 sm:px-3 py-1 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-100 dark:border-purple-900/40 text-xs sm:text-sm font-extrabold min-w-[36px] sm:min-w-[44px]">
                                            {{ formatNumber(item.jumlah_p3k) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-2 w-[12%] text-center align-middle">
                                        <span class="inline-flex items-center justify-center px-2.5 sm:px-3 py-1 rounded-full bg-pink-50 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300 border border-pink-100 dark:border-pink-900/40 text-xs sm:text-sm font-extrabold min-w-[36px] sm:min-w-[44px]">
                                            {{ formatNumber(item.jumlah_p3k_pw) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 w-[12%] text-center align-middle">
                                        <span class="inline-flex items-center justify-center px-3 sm:px-3.5 py-1 rounded-full bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white text-xs sm:text-sm font-black tracking-wide min-w-[40px] sm:min-w-[50px] shadow-sm">
                                            {{ formatNumber(item.total) }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="filteredRecords.length === 0">
                                    <td colspan="6" class="py-12 text-center text-slate-400 text-sm font-medium">
                                        Tidak ada data UPD yang sesuai.
                                    </td>
                                </tr>
                            </tbody>
                        </Transition>
                    </table>
                </div>

                <!-- Custom Pagination Controls -->
                <div class="p-4 sm:px-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                        Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ filteredRecords.length > 0 ? (currentPage - 1) * perPage + 1 : 0 }}</span> - 
                        <span class="font-bold text-slate-800 dark:text-white">{{ Math.min(currentPage * perPage, filteredRecords.length) }}</span> dari 
                        <span class="font-bold text-slate-800 dark:text-white">{{ filteredRecords.length }}</span> data
                    </div>
                    
                    <div class="flex items-center gap-1.5" v-if="totalPages > 1">
                        <!-- Left Arrow (disappears when currentPage === 1) -->
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
                        
                        <!-- Page numbers sliding bubbles -->
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="goToPage(page)"
                            class="h-8 w-8 rounded-lg text-xs font-bold transition duration-200 select-none cursor-pointer flex items-center justify-center"
                            :class="currentPage === page 
                                ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white shadow-md ring-1 ring-indigo-500/20' 
                                : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        >
                            {{ page }}
                        </button>
                        
                        <!-- Right Arrow (disappears when currentPage === totalPages) -->
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
        </div>

    </div>
</template>

<style scoped>
.page-fade-enter-active,
.page-fade-leave-active {
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.page-fade-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.page-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
