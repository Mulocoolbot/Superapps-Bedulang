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

// Date display from metadata or fallback
const displayDate = computed(() => {
    return props.dashboard?.metadata?.updated_at || currentDateString.value
})

const previewPdfUrl = computed(() => {
    return props.dashboard?.metadata?.preview_pdf || 'https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf'
})

const downloadPdfUrl = computed(() => {
    return props.dashboard?.metadata?.download_pdf || 'https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf'
})

// Active Tab state (1: Pabrik Kelapa Sawit, 2: Rerata Harga Pekebun Non Mitra)
const activeTab = ref(1)
const searchQuery = ref('')

// Factory prices from backend service or fallback
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

// District prices from backend service or fallback
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
    return 'Rp ' + Number(val).toLocaleString('id-ID')
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
    if (factoryPrices.value.length === 0) return 0
    const sum = factoryPrices.value.reduce((acc, item) => acc + item.today, 0)
    return Math.round(sum / factoryPrices.value.length)
})

const maxFactoryPrice = computed(() => {
    if (factoryPrices.value.length === 0) return 0
    return Math.max(...factoryPrices.value.map(i => i.today))
})

// Tab 2 Stats summary
const avgDistrictPrice = computed(() => {
    if (districtPrices.value.length === 0) return 0
    const sum = districtPrices.value.reduce((acc, item) => acc + item.today, 0)
    return Math.round(sum / districtPrices.value.length)
})
</script>

<template>
    <div class="space-y-8 mt-6">

        <!-- TOP CARD: SINGLE UNIFIED HEADER CARD -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 p-6 shadow-sm dark:border-slate-800 dark:from-emerald-950/40 dark:via-teal-950/40 dark:to-green-950/40 animate-fade-in-up">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">

                <!-- Title & Tanggal Badge -->
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-900/60 px-3 py-1 text-xs font-bold text-emerald-800 dark:text-emerald-300">
                        <span>📅 {{ displayDate }}</span>
                    </div>

                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                        Informasi Harga Pertanian & Hasil Panen TBS
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                        Data pemantauan harga Tandan Buah Segar (TBS) Kelapa Sawit di tingkat pabrik dan rerata pekebun non-mitra di Kabupaten Bangka Tengah.
                    </p>
                </div>

                <!-- Action PDF Buttons -->
                <div class="flex items-center gap-3 shrink-0">
                    <a
                        :href="previewPdfUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-600 dark:text-emerald-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span>Lihat PDF Report</span>
                    </a>

                    <a
                        :href="downloadPdfUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white transition shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Unduh PDF</span>
                    </a>
                </div>

            </div>
        </div>

        <!-- STATS METRIC BAR CARD -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            <!-- Metric 1: Rerata Pabrik -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Rerata Harga Pabrik
                </span>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ formatRupiah(avgFactoryPrice) }} / kg
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Daftar {{ factoryPrices.length }} Pabrik Kelapa Sawit
                </p>
            </div>

            <!-- Metric 2: Harga Pabrik Tertinggi -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Harga Tertinggi Pabrik
                </span>
                <div class="text-2xl font-extrabold text-teal-600 dark:text-teal-400 font-mono">
                    {{ formatRupiah(maxFactoryPrice) }} / kg
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Penetapan PKS Terbaik
                </p>
            </div>

            <!-- Metric 3: Rerata Pekebun Non-Mitra -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Rerata Pekebun (Kecamatan)
                </span>
                <div class="text-2xl font-extrabold text-green-600 dark:text-green-400 font-mono">
                    {{ formatRupiah(avgDistrictPrice) }} / kg
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Rerata 6 Kecamatan Bangka Tengah
                </p>
            </div>

        </div>

        <!-- MAIN TABLE SECTION WITH 2 TABS AND DEDICATED SELISIH COLUMN -->
        <div class="space-y-6">

            <!-- Tab Buttons Container -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <!-- 2 Main Tabs -->
                <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-xl w-full sm:w-auto">
                    <button
                        @click="activeTab = 1"
                        class="flex-1 sm:flex-initial px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer"
                        :class="activeTab === 1
                            ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    >
                        🏭 Pabrik Kelapa Sawit (PKS)
                    </button>
                    <button
                        @click="activeTab = 2"
                        class="flex-1 sm:flex-initial px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer"
                        :class="activeTab === 2
                            ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    >
                        👨‍🌾 Rerata Pekebun Non Mitra
                    </button>
                </div>
            </div>

            <!-- TAB 1: PABRIK KELAPA SAWIT TABLE WITH DEDICATED SELISIH COLUMN -->
            <div v-if="activeTab === 1" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans">
                            Harga di Pabrik Kelapa Sawit
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Tabel penetapan harga beli TBS di Pabrik Kelapa Sawit (PKS) Kabupaten Bangka Tengah.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Live Search Input -->
                        <div class="relative w-full sm:w-64">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari pabrik atau desa..."
                                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:outline-none"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>

                        <span class="px-3 py-1.5 rounded-xl text-xs font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 shrink-0 font-sans">
                            {{ factoryPrices.length }} Pabrik Active
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3.5 px-6">Nama Perusahaan / PKS</th>
                                <th class="py-3.5 px-6 text-right">Harga Hari Ini</th>
                                <th class="py-3.5 px-6 text-right">Harga Kemarin</th>
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
                                        <span class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                            🏭
                                        </span>
                                        <div>
                                            <span class="font-sans text-slate-900 dark:text-white block">{{ item.name }}</span>
                                            <span v-if="item.location" class="text-xs font-normal text-slate-400 dark:text-slate-500 block mt-0.5">
                                                📍 {{ item.location }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white font-sans">
                                    {{ formatRupiah(item.today) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
                                </td>
                                <td class="py-4 px-6 text-right font-medium text-slate-500 dark:text-slate-400">
                                    {{ formatRupiah(item.yesterday) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
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
                                        = Rp 0
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

            <!-- TAB 2: RERATA HARGA PERKEBUN NON MITRA TABLE WITH DEDICATED SELISIH COLUMN -->
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
                        <!-- Live Search Input -->
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
                                <th class="py-3.5 px-6 text-right">Harga Hari Ini</th>
                                <th class="py-3.5 px-6 text-right">Harga Kemarin</th>
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
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white font-sans">
                                    {{ formatRupiah(item.today) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
                                </td>
                                <td class="py-4 px-6 text-right font-medium text-slate-500 dark:text-slate-400">
                                    {{ formatRupiah(item.yesterday) }} <span class="text-xs text-slate-400 font-normal">/kg</span>
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
                                        = Rp 0
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

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
