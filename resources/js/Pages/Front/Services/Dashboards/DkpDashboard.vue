<script setup>
import { ref, computed } from 'vue'

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

// Current date formatting (Tanggal Terkini)
const currentDateString = computed(() => {
    const now = new Date()
    return now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
})

// Date display from DPKP metadata or fallback
const displayDate = computed(() => {
    return props.dashboard?.metadata?.updated_at || currentDateString.value
})

const previewPdfUrl = computed(() => {
    return props.dashboard?.metadata?.preview_pdf || 'https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf'
})

const downloadPdfUrl = computed(() => {
    return props.dashboard?.metadata?.download_pdf || 'https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf'
})

// Active Tab state (1: Pabrik Kelapa Sawit, 2: Rerata Harga Perkebun Non Mitra)
const activeTab = ref(1)
const searchQuery = ref('')

// Factory prices from backend DPKP service or mock fallback
const factoryPrices = computed(() => {
    if (props.dashboard?.factory_prices && Array.isArray(props.dashboard.factory_prices) && props.dashboard.factory_prices.length > 0) {
        return props.dashboard.factory_prices
    }
    return [
        { id: 1, name: 'PT Putra Bangka Tani', location: 'Desa Belilik', today: 3100, yesterday: 3100 },
        { id: 2, name: 'PT Bangka Agro Mandiri', location: 'Desa Pinang Sebatang', today: 3050, yesterday: 3050 },
        { id: 3, name: 'PT Perlang Sawitindo Mas', location: 'Desa Perlang', today: 3030, yesterday: 3030 },
        { id: 4, name: 'PT Swarna Nusa Sentosa', location: 'Desa Kemingking', today: 3000, yesterday: 3000 },
    ]
})

// District prices from backend DPKP service or mock fallback
const districtPrices = computed(() => {
    if (props.dashboard?.district_prices && Array.isArray(props.dashboard.district_prices) && props.dashboard.district_prices.length > 0) {
        return props.dashboard.district_prices
    }
    return [
        { id: 1, district: 'Kecamatan Sungaiselan', today: 2750, yesterday: 2750 },
        { id: 2, district: 'Kecamatan Lubuk Besar', today: 2820, yesterday: 2820 },
        { id: 3, district: 'Kecamatan Simpangkatis', today: 2827, yesterday: 2827 },
        { id: 4, district: 'Kecamatan Pangkalan Baru', today: 2817, yesterday: 2817 },
        { id: 5, district: 'Kecamatan Koba', today: 2850, yesterday: 2850 },
        { id: 6, district: 'Kecamatan Namang', today: 2857, yesterday: 2857 },
    ]
})

// Format Rupiah helper
const formatRupiah = (val) => {
    if (val === undefined || val === null) return 'Rp 0'
    return 'Rp ' + val.toLocaleString('id-ID')
}

// Calculate difference (+/-)
const getDiff = (today, yesterday) => {
    return today - yesterday
}

// Filtered Tab 1 items
const filteredFactoryPrices = computed(() => {
    if (!searchQuery.value.trim()) return factoryPrices.value
    const q = searchQuery.value.toLowerCase()
    return factoryPrices.value.filter(item => 
        item.name.toLowerCase().includes(q) || 
        (item.location && item.location.toLowerCase().includes(q))
    )
})

// Filtered Tab 2 items
const filteredDistrictPrices = computed(() => {
    if (!searchQuery.value.trim()) return districtPrices.value
    const q = searchQuery.value.toLowerCase()
    return districtPrices.value.filter(item => item.district.toLowerCase().includes(q))
})

// Tab 1 Stats summary
const avgFactoryPrice = computed(() => {
    if (!factoryPrices.value || factoryPrices.value.length === 0) return 0
    const total = factoryPrices.value.reduce((acc, curr) => acc + curr.today, 0)
    return Math.round(total / factoryPrices.value.length)
})

const highestFactory = computed(() => {
    if (!factoryPrices.value || factoryPrices.value.length === 0) return { name: '-', today: 0 }
    return [...factoryPrices.value].sort((a, b) => b.today - a.today)[0]
})

// Tab 2 Stats summary
const avgDistrictPrice = computed(() => {
    if (!districtPrices.value || districtPrices.value.length === 0) return 0
    const total = districtPrices.value.reduce((acc, curr) => acc + curr.today, 0)
    return Math.round(total / districtPrices.value.length)
})

const highestDistrict = computed(() => {
    if (!districtPrices.value || districtPrices.value.length === 0) return { district: '-', today: 0 }
    return [...districtPrices.value].sort((a, b) => b.today - a.today)[0]
})

// Average price difference across active tab
const avgDiff = computed(() => {
    const list = activeTab.value === 1 ? factoryPrices.value : districtPrices.value
    if (!list || list.length === 0) return 0
    const sum = list.reduce((acc, curr) => acc + (curr.today - curr.yesterday), 0)
    return Math.round(sum / list.length)
})
</script>

<template>
    <div class="mt-8 space-y-8 animate-fade-in-up">
        
        <!-- 1. MAIN HEADER CARD: TANGGAL TERKINI & UPDATE HARGA TBS -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-900 p-6 sm:p-8 text-white shadow-2xl transition-all duration-300 hover:shadow-emerald-900/20">
            <!-- Background Glow & Pattern Decor -->
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-emerald-400/20 blur-3xl" />
            <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-teal-300/20 blur-3xl" />

            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-3">
                    <!-- Tanggal Terkini -->
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-md px-3.5 py-1 text-xs font-semibold tracking-wide text-emerald-200 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span>📅 {{ displayDate }}</span>
                    </div>

                    <!-- Teks Utama Update Harga TBS -->
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-sans drop-shadow-sm text-white">
                        Update Harga TBS Kelapa Sawit
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-100/90 max-w-xl font-medium leading-relaxed">
                        Dinas Pertanian & Ketahanan Pangan Kabupaten Bangka Tengah — Pemantauan harga Tandan Buah Segar (TBS) Kelapa Sawit real-time di tingkat Pabrik & Pekebun Non Mitra.
                    </p>
                </div>

                <!-- Live Price Highlight Pill -->
                <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md border border-white/15 p-4 rounded-2xl shrink-0 shadow-inner">
                    <div class="p-3 bg-emerald-500/30 rounded-xl">
                        <span class="text-2xl">🌴</span>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-emerald-200">Rata-rata Harga Hari Ini</div>
                        <div class="text-xl sm:text-2xl font-extrabold tracking-tight font-sans">
                            {{ activeTab === 1 ? formatRupiah(avgFactoryPrice) : formatRupiah(avgDistrictPrice) }} <span class="text-xs font-normal text-emerald-200">/ kg</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. SUMMARY STATS GRID (JUMLAH PABRIK / KECAMATAN DLL) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 animate-fade-in-up [animation-delay:100ms]">
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-1 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-800">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Unit Terdata</span>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-sans">
                    {{ activeTab === 1 ? factoryPrices.length + ' Pabrik' : districtPrices.length + ' Kecamatan' }}
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-1 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-800">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Harga Tertinggi Hari Ini</span>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-sans">
                    {{ activeTab === 1 ? formatRupiah(highestFactory.today) : formatRupiah(highestDistrict.today) }}
                </div>
                <div class="text-[11px] text-slate-400 truncate font-medium">
                    {{ activeTab === 1 ? highestFactory.name : highestDistrict.district }}
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-1 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-800">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rata-rata Selisih Harga</span>
                <div class="text-2xl font-extrabold font-sans" :class="avgDiff >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400'">
                    {{ avgDiff >= 0 ? '+' : '' }}{{ formatRupiah(avgDiff) }} <span class="text-xs font-medium text-slate-400">/ kg</span>
                </div>
                <div class="text-[11px] font-semibold" :class="avgDiff > 0 ? 'text-emerald-600 dark:text-emerald-400' : avgDiff < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500'">
                    <span v-if="avgDiff > 0">▲ Tren Kenaikan Positif</span>
                    <span v-else-if="avgDiff < 0">▼ Tren Penurunan Harga</span>
                    <span v-else>= Harga Stabil</span>
                </div>
            </div>
        </div>

        <!-- 3. CARD MENU PILIHAN (DIPINDAHKAN KE BAWAH STAT CARDS - PANJANG KE SAMPING) -->
        <div class="animate-fade-in-up [animation-delay:200ms]">
            <div class="w-full p-2 rounded-2xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row gap-2">
                <button
                    @click="activeTab = 1"
                    type="button"
                    class="flex-1 py-3.5 px-6 rounded-xl text-xs sm:text-sm font-extrabold font-sans transition-all duration-300 flex items-center justify-center gap-2.5 cursor-pointer"
                    :class="activeTab === 1 
                        ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 shadow-md shadow-slate-200/60 dark:shadow-none ring-1 ring-emerald-500/20' 
                        : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-200'"
                >
                    <span class="p-1 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs">🏭</span>
                    <span>Harga Di Pabrik Kelapa Sawit</span>
                </button>

                <button
                    @click="activeTab = 2"
                    type="button"
                    class="flex-1 py-3.5 px-6 rounded-xl text-xs sm:text-sm font-extrabold font-sans transition-all duration-300 flex items-center justify-center gap-2.5 cursor-pointer"
                    :class="activeTab === 2 
                        ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 shadow-md shadow-slate-200/60 dark:shadow-none ring-1 ring-emerald-500/20' 
                        : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-200'"
                >
                    <span class="p-1 rounded-lg bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-xs">📍</span>
                    <span>Rerata Harga Perkebun Non Mitra</span>
                </button>
            </div>
        </div>

        <!-- 4. TABEL DATA HARGA TBS & CARI DATA -->
        <div class="animate-fade-in-up [animation-delay:300ms]">
            
            <!-- TAB 1: HARGA DI PABRIK KELAPA SAWIT -->
            <div v-if="activeTab === 1" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans">
                            Harga Di Pabrik Kelapa Sawit
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Tabel perbandingan harga beli Tandan Buah Segar (TBS) di Pabrik Kelapa Sawit Kabupaten Bangka Tengah.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search input -->
                        <div class="relative w-full sm:w-64">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari pabrik atau lokasi..."
                                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:outline-none"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>

                        <span class="px-3 py-1.5 rounded-xl text-xs font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 shrink-0 font-sans">
                            Data Pabrik
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3.5 px-6">Pabrik Kelapa Sawit</th>
                                <th class="py-3.5 px-6 text-right">Harga Kemarin</th>
                                <th class="py-3.5 px-6 text-right">Harga Hari Ini</th>
                                <th class="py-3.5 px-6 text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                            <tr 
                                v-for="item in filteredFactoryPrices" 
                                :key="item.id"
                                class="hover:bg-emerald-50/40 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="py-4 px-6 font-bold">
                                    <div class="flex items-center gap-2.5">
                                        <span class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 shrink-0">
                                            🏭
                                        </span>
                                        <div>
                                            <div class="font-sans text-slate-900 dark:text-white">{{ item.name }}</div>
                                            <div v-if="item.location" class="text-[11px] text-slate-400 font-normal font-sans">📍 {{ item.location }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right font-medium text-slate-500 dark:text-slate-400">
                                    {{ formatRupiah(item.yesterday) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white font-sans">
                                    {{ formatRupiah(item.today) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
                                </td>
                                <td class="py-4 px-6 text-right font-bold font-sans">
                                    <span 
                                        v-if="getDiff(item.today, item.yesterday) > 0"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 shadow-sm"
                                    >
                                        ▲ +{{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                    </span>
                                    <span 
                                        v-else-if="getDiff(item.today, item.yesterday) < 0"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400 shadow-sm"
                                    >
                                        ▼ {{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                    </span>
                                    <span 
                                        v-else
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        — Rp 0
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="filteredFactoryPrices.length === 0">
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs font-medium">
                                    Tidak ada data pabrik kelapa sawit yang sesuai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: RERATA HARGA PERKEBUN NON MITRA -->
            <div v-else class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans">
                            Rerata Harga Perkebun Non Mitra
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Tabel rerata harga beli TBS bagi petani/pekebun kelapa sawit non-mitra per kecamatan.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search input -->
                        <div class="relative w-full sm:w-64">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari kecamatan..."
                                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:outline-none"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>

                        <span class="px-3 py-1.5 rounded-xl text-xs font-extrabold bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-400 shrink-0 font-sans">
                            Data Kecamatan
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3.5 px-6">Kecamatan</th>
                                <th class="py-3.5 px-6 text-right">Harga Kemarin</th>
                                <th class="py-3.5 px-6 text-right">Harga Hari Ini</th>
                                <th class="py-3.5 px-6 text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                            <tr 
                                v-for="item in filteredDistrictPrices" 
                                :key="item.id"
                                class="hover:bg-teal-50/40 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="py-4 px-6 font-bold flex items-center gap-2.5">
                                    <span class="p-1.5 rounded-lg bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                                        📍
                                    </span>
                                    <span class="font-sans text-slate-900 dark:text-white">{{ item.district }}</span>
                                </td>
                                <td class="py-4 px-6 text-right font-medium text-slate-500 dark:text-slate-400">
                                    {{ formatRupiah(item.yesterday) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white font-sans">
                                    {{ formatRupiah(item.today) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
                                </td>
                                <td class="py-4 px-6 text-right font-bold font-sans">
                                    <span 
                                        v-if="getDiff(item.today, item.yesterday) > 0"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 shadow-sm"
                                    >
                                        ▲ +{{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                    </span>
                                    <span 
                                        v-else-if="getDiff(item.today, item.yesterday) < 0"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400 shadow-sm"
                                    >
                                        ▼ {{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                    </span>
                                    <span 
                                        v-else
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        — Rp 0
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="filteredDistrictPrices.length === 0">
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs font-medium">
                                    Tidak ada data kecamatan yang sesuai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</template>
