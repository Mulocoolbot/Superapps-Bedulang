<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import PerindagkopDashboard from './PerindagkopDashboard.vue'

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

const page = usePage()

// Active Sidebar Tab State ('bapok', 'sawit', 'umkm', 'utd')
const activeSidebarTab = ref('bapok')

// -------------------------------------------------------------
// SUB-VIEW 1: HARGA PASAR & STOK BAPOK
// -------------------------------------------------------------
const perindagData = computed(() => props.dashboard?.perindag || {})

// -------------------------------------------------------------
// SUB-VIEW 2: HARGA SAWIT (TBS PALM OIL)
// -------------------------------------------------------------
const sawitData = computed(() => props.dashboard?.sawit || {})

const displayDateSawit = computed(() => {
    return sawitData.value?.metadata?.updated_at || 'Terbaru'
})

const activeSawitTab = ref(1)
const searchSawitQuery = ref('')

const factoryPrices = computed(() => {
    if (sawitData.value?.factory_prices && Array.isArray(sawitData.value.factory_prices) && sawitData.value.factory_prices.length > 0) {
        return sawitData.value.factory_prices
    }
    return [
        { id: 1, name: 'PT Putra Bangka Tani', location: 'Desa Belilik', today: 3100, yesterday: 3100 },
        { id: 2, name: 'PT Bangka Agro Mandiri', location: 'Desa Pinang Sebatang', today: 3050, yesterday: 3050 },
        { id: 3, name: 'PT Perlang Sawitindo Mas', location: 'Desa Perlang', today: 3030, yesterday: 3030 },
        { id: 4, name: 'PT Swarna Nusa Sentosa', location: 'Desa Kemingking', today: 3000, yesterday: 3000 },
    ]
})

const districtPrices = computed(() => {
    if (sawitData.value?.district_prices && Array.isArray(sawitData.value.district_prices) && sawitData.value.district_prices.length > 0) {
        return sawitData.value.district_prices
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

const filteredFactoryPrices = computed(() => {
    if (!searchSawitQuery.value.trim()) return factoryPrices.value
    const q = searchSawitQuery.value.toLowerCase()
    return factoryPrices.value.filter(item => 
        item.name.toLowerCase().includes(q) || 
        (item.location && item.location.toLowerCase().includes(q))
    )
})

const filteredDistrictPrices = computed(() => {
    if (!searchSawitQuery.value.trim()) return districtPrices.value
    const q = searchSawitQuery.value.toLowerCase()
    return districtPrices.value.filter(item => item.district.toLowerCase().includes(q))
})

const avgFactoryPrice = computed(() => {
    if (factoryPrices.value.length === 0) return 0
    const sum = factoryPrices.value.reduce((acc, item) => acc + item.today, 0)
    return Math.round(sum / factoryPrices.value.length)
})

const maxFactoryPrice = computed(() => {
    if (factoryPrices.value.length === 0) return 0
    return Math.max(...factoryPrices.value.map(i => i.today))
})

const avgDistrictPrice = computed(() => {
    if (districtPrices.value.length === 0) return 0
    const sum = districtPrices.value.reduce((acc, item) => acc + item.today, 0)
    return Math.round(sum / districtPrices.value.length)
})

// -------------------------------------------------------------
// SUB-VIEW 4: UNIT TRANSFUSI DARAH (UTD)
// -------------------------------------------------------------
const utdData = computed(() => props.dashboard?.utd || {})

const bloodStockList = computed(() => {
    if (utdData.value?.stocks && Array.isArray(utdData.value.stocks) && utdData.value.stocks.length > 0) {
        return utdData.value.stocks
    }
    return [
        { id: 1, blood_type: 'A', amount: 20, unit: 'Kantong' },
        { id: 2, blood_type: 'B', amount: 25, unit: 'Kantong' },
        { id: 3, blood_type: 'AB', amount: 12, unit: 'Kantong' },
        { id: 4, blood_type: 'O', amount: 32, unit: 'Kantong' },
    ]
})

const totalBloodStock = computed(() => {
    if (utdData.value?.total_stock !== undefined && utdData.value?.total_stock !== null) {
        return utdData.value.total_stock
    }
    return bloodStockList.value.reduce((acc, item) => acc + (item.amount || 0), 0)
})

const bloodLogs = computed(() => {
    return utdData.value?.logs || []
})

// UTD Admin Check & Form Modal State
const currentUser = computed(() => page.props.auth?.user || null)

const isUtdAdmin = computed(() => {
    if (!currentUser.value) return false
    if (currentUser.value.role === 'super_admin') return true
    if (currentUser.value.role === 'admin' && (
        !currentUser.value.service_slug ||
        ['informasi-publik', 'utd', 'kesehatan'].includes(currentUser.value.service_slug)
    )) {
        return true
    }
    return false
})

const isEditModalOpen = ref(false)
const isSubmitting = ref(false)

const editableStocks = ref([])

const openEditModal = () => {
    editableStocks.value = bloodStockList.value.map(s => ({
        blood_type: s.blood_type,
        amount: s.amount
    }))
    isEditModalOpen.value = true
}

const closeEditModal = () => {
    isEditModalOpen.value = false
}

const saveBloodStocks = () => {
    isSubmitting.value = true
    router.post('/admin/blood-stocks', {
        stocks: editableStocks.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitting.value = false
            isEditModalOpen.value = false
        },
        onError: () => {
            isSubmitting.value = false
        }
    })
}

// Formatting Helpers
const formatRupiah = (val) => {
    if (val === undefined || val === null) return 'Rp 0'
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

const getDiff = (today, yesterday) => {
    return today - yesterday
}

const getAvailabilityStatus = (amount) => {
    if (amount >= 15) return { label: 'Stok Aman', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' }
    if (amount >= 5) return { label: 'Stok Waspada', class: 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300' }
    return { label: 'Stok Kritis', class: 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300' }
}
</script>

<template>
    <div class="mt-6 flex flex-col lg:flex-row gap-8 min-h-[600px]">

        <!-- SIDEBAR NAVIGATION -->
        <aside class="w-full lg:w-72 shrink-0 space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-sm space-y-2">
                <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800 mb-2">
                    <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        Kategori Informasi
                    </h3>
                </div>

                <nav class="space-y-1.5">
                    <!-- Tab 1: Harga Pasar & Stok Bapok -->
                    <button
                        @click="activeSidebarTab = 'bapok'"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition-all text-left cursor-pointer"
                        :class="activeSidebarTab === 'bapok'
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-base">🛒</span>
                            <span>Harga Pasar & Stok Bapok</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                    <!-- Tab 2: Harga Sawit -->
                    <button
                        @click="activeSidebarTab = 'sawit'"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition-all text-left cursor-pointer"
                        :class="activeSidebarTab === 'sawit'
                            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-base">🌴</span>
                            <span>Harga Sawit</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                    <!-- Tab 3: Data UMKM -->
                    <button
                        @click="activeSidebarTab = 'umkm'"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition-all text-left cursor-pointer"
                        :class="activeSidebarTab === 'umkm'
                            ? 'bg-amber-600 text-white shadow-md shadow-amber-500/20'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-base">🏪</span>
                            <span>Data UMKM</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-semibold">
                            Pengembangan
                        </span>
                    </button>

                    <!-- Tab 4: Unit Transfusi Darah -->
                    <button
                        @click="activeSidebarTab = 'utd'"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition-all text-left cursor-pointer"
                        :class="activeSidebarTab === 'utd'
                            ? 'bg-rose-600 text-white shadow-md shadow-rose-500/20'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-base">🩸</span>
                            <span>Unit Transfusi Darah</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </nav>
            </div>
        </aside>

        <!-- MAIN DISPLAY AREA (DYNAMIC BY ACTIVE SIDEBAR TAB WITH SMOOTH TRANSITION) -->
        <main class="flex-1 min-w-0">
            <Transition name="tab-fade" mode="out-in">
                <!-- 🛒 SUB-VIEW 1: HARGA PASAR & STOK BAPOK -->
                <div v-if="activeSidebarTab === 'bapok'" key="bapok" class="space-y-8">

                    <!-- Header Card title matching original Perindagkop dashboard style -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-blue-50 via-indigo-50 to-slate-50 p-6 shadow-sm dark:border-slate-800 dark:from-blue-950/40 dark:via-indigo-950/40 dark:to-slate-900">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-blue-700 dark:text-blue-400 block mb-1">
                                    Disperindagkop & UKM Kab. Bangka Tengah
                                </span>
                                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                    Informasi Harga Pasar & Stok Bapok
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                                    Pantauan harian tren harga komoditas pangan pokok dan ketersediaan stok bahan pokok di berbagai pasar rakyat Kabupaten Bangka Tengah.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Full Perindagkop Dashboard Component -->
                    <PerindagkopDashboard :service="service" :dashboard="perindagData"></PerindagkopDashboard>

                    <!-- TAB 1 SPECIFIC DATA SOURCE FOOTER -->
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">Sumber Data:</span>
                            <a href="https://disperindagkop-ukm.bangkatengahkab.go.id/" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 underline">
                                Dinas Perindustrian, Perdagangan, Koperasi & UKM Kab. Bangka Tengah (disperindagkop-ukm.bangkatengahkab.go.id)
                            </a>
                        </div>
                    </div>

                </div>

                <!-- 🌴 SUB-VIEW 2: HARGA SAWIT (TBS PALM OIL) -->
                <div v-else-if="activeSidebarTab === 'sawit'" key="sawit" class="space-y-8">

                    <!-- Header Card -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 p-6 shadow-sm dark:border-slate-800 dark:from-emerald-950/40 dark:via-teal-950/40 dark:to-green-950/40">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="space-y-2">
                                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-900/60 px-3 py-1 text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                    <span>Per tanggal: {{ displayDateSawit }}</span>
                                </div>
                                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                                    Informasi Harga Sawit (Tandan Buah Segar - TBS)
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                                    Data pemantauan harga TBS Kelapa Sawit di tingkat Pabrik Kelapa Sawit (PKS) dan rerata pekebun non-mitra di Kabupaten Bangka Tengah.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Metric Bar Card -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Rerata Harga Pabrik</span>
                            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">{{ formatRupiah(avgFactoryPrice) }} / kg</div>
                            <p class="text-[11px] text-slate-400">Daftar {{ factoryPrices.length }} Pabrik Kelapa Sawit</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Harga Tertinggi Pabrik</span>
                            <div class="text-2xl font-extrabold text-teal-600 dark:text-teal-400 font-mono">{{ formatRupiah(maxFactoryPrice) }} / kg</div>
                            <p class="text-[11px] text-slate-400">Penetapan PKS Terbaik</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Rerata Pekebun (Kecamatan)</span>
                            <div class="text-2xl font-extrabold text-green-600 dark:text-green-400 font-mono">{{ formatRupiah(avgDistrictPrice) }} / kg</div>
                            <p class="text-[11px] text-slate-400">Rerata 6 Kecamatan Bangka Tengah</p>
                        </div>
                    </div>

                    <!-- 2 Tabs Table Full Width Navbar Menu -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-xl w-full">
                            <button @click="activeSawitTab = 1" class="flex-1 py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition cursor-pointer text-center" :class="activeSawitTab === 1 ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                Pabrik Kelapa Sawit (PKS)
                            </button>
                            <button @click="activeSawitTab = 2" class="flex-1 py-2.5 px-4 rounded-lg text-xs sm:text-sm font-bold transition cursor-pointer text-center" :class="activeSawitTab === 2 ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                Rerata Pekebun Non Mitra
                            </button>
                        </div>

                        <!-- SUB-TAB CONTENT WITH TRANSITION -->
                        <Transition name="subtab-fade" mode="out-in">
                            <!-- TAB 1: PABRIK KELAPA SAWIT -->
                            <div v-if="activeSawitTab === 1" key="sawit-pks" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
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
                                        <tr v-for="item in filteredFactoryPrices" :key="item.id" class="hover:bg-emerald-50/40 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-4 px-6 font-bold">
                                                <div>
                                                    <span class="text-slate-900 dark:text-white block">{{ item.name }}</span>
                                                    <span v-if="item.location" class="text-xs font-normal text-slate-400 block mt-0.5">{{ item.location }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white">{{ formatRupiah(item.today) }} /kg</td>
                                            <td class="py-4 px-6 text-right font-medium text-slate-500">{{ formatRupiah(item.yesterday) }} /kg</td>
                                            <td class="py-4 px-6 text-right font-bold">
                                                <span v-if="getDiff(item.today, item.yesterday) > 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                                                    ▲ +{{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                                </span>
                                                <span v-else-if="getDiff(item.today, item.yesterday) < 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400">
                                                    ▼ {{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                                </span>
                                                <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                    = Rp 0
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- TAB 2: RERATA PEKEBUN -->
                            <div v-else key="sawit-pekebun" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
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
                                        <tr v-for="item in filteredDistrictPrices" :key="item.id" class="hover:bg-teal-50/40 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-4 px-6 font-bold">
                                                <span class="text-slate-900 dark:text-white">{{ item.district }}</span>
                                            </td>
                                            <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white">{{ formatRupiah(item.today) }} /kg</td>
                                            <td class="py-4 px-6 text-right font-medium text-slate-500">{{ formatRupiah(item.yesterday) }} /kg</td>
                                            <td class="py-4 px-6 text-right font-bold">
                                                <span v-if="getDiff(item.today, item.yesterday) > 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                                                    ▲ +{{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                                </span>
                                                <span v-else-if="getDiff(item.today, item.yesterday) < 0" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400">
                                                    ▼ {{ formatRupiah(getDiff(item.today, item.yesterday)) }}
                                                </span>
                                                <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                    = Rp 0
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Transition>
                    </div>

                    <!-- TAB 2 SPECIFIC DATA SOURCE FOOTER -->
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">Sumber Data:</span>
                            <a href="https://dpkp.bangkatengahkab.go.id/" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 underline">
                                Dinas Pertanian Kab. Bangka Tengah (dpkp.bangkatengahkab.go.id)
                            </a>
                        </div>
                        <div v-if="displayDateSawit">
                            Terakhir diperbarui: {{ displayDateSawit }}
                        </div>
                    </div>

                </div>

                <!-- 🏪 SUB-VIEW 3: DATA UMKM (PLACEHOLDER EMPTY STATE) -->
                <div v-else-if="activeSidebarTab === 'umkm'" key="umkm" class="space-y-8">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-12 text-center shadow-sm space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mx-auto shadow-inner">
                            🏪
                        </div>
                        <div class="max-w-md mx-auto space-y-2">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                                Data UMKM Kabupaten Bangka Tengah
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Data Usaha Mikro, Kecil, dan Menengah (UMKM) sedang dalam tahap pengumpulan data dan pengintegrasian ke dalam Portal Informasi Publik.
                            </p>
                        </div>
                        <div class="pt-2">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Dalam Tahap Pengembangan
                            </span>
                        </div>
                    </div>

                    <!-- TAB 3 SPECIFIC DATA SOURCE FOOTER -->
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">Sumber Data:</span>
                            <a href="https://bangkatengahkab.go.id/" target="_blank" rel="noopener noreferrer" class="font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 underline">
                                Dinas Perindagkop & UMKM Kabupaten Bangka Tengah (bangkatengahkab.go.id)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 🩸 SUB-VIEW 4: UNIT TRANSFUSI DARAH (UTD) -->
                <div v-else-if="activeSidebarTab === 'utd'" key="utd" class="space-y-8">

                    <!-- Header Card -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-rose-50 via-red-50 to-pink-50 p-6 shadow-sm dark:border-slate-800 dark:from-rose-950/40 dark:via-red-950/40 dark:to-pink-950/40">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="space-y-2">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-400 block mb-1">
                                    Dinas Kesehatan & UTD Bangka Tengah
                                </span>
                                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
                                    Informasi Stok Darah Unit Transfusi Darah (UTD)
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                                    Informasi real-time ketersediaan stok darah per golongan darah di Unit Transfusi Darah (UTD) Kabupaten Bangka Tengah.
                                </p>
                            </div>

                            <!-- Admin UTD Edit Button (Visible to UTD Admin / Superadmin) -->
                            <div v-if="isUtdAdmin" class="shrink-0">
                                <button
                                    @click="openEditModal"
                                    class="inline-flex items-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2.5 text-xs font-bold text-white transition shadow-md shadow-rose-500/20 cursor-pointer"
                                >
                                    <span>⚙️ Input / Update Stok Darah</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAL STOK SUMMARY CARD -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-100 dark:border-slate-800 p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl shadow-inner shrink-0">
                                🩸
                            </div>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Stok Darah Tersedia</span>
                                <h3 class="text-3xl font-black text-rose-600 dark:text-rose-400 font-mono mt-0.5">
                                    {{ totalBloodStock }} <span class="text-sm font-semibold text-slate-500">Kantong / Unit</span>
                                </h3>
                            </div>
                        </div>
                        <div class="text-right sm:text-left border-t sm:border-t-0 sm:border-l border-slate-100 dark:border-slate-800 pt-3 sm:pt-0 sm:pl-6 text-xs text-slate-500 space-y-1">
                            <div>📍 Unit Transfusi Darah Kab. Bangka Tengah</div>
                            <div>⏱️ Pemantauan Stok Harian Terintegrasi</div>
                        </div>
                    </div>

                    <!-- BLOOD STOCK TABLE BY GOLONGAN DARAH -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Rincian Stok per Golongan Darah
                            </h4>
                            <span class="text-[11px] text-slate-400 font-medium">4 Golongan Darah</span>
                        </div>
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3.5 px-6">Golongan Darah</th>
                                    <th class="py-3.5 px-6 text-right">Jumlah (Kantong)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                <tr v-for="item in bloodStockList" :key="item.id || item.blood_type" class="hover:bg-rose-50/30 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-4 px-6 font-extrabold">
                                        <div class="inline-flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 font-black text-xs flex items-center justify-center border border-rose-200 dark:border-rose-900">
                                                {{ item.blood_type }}
                                            </span>
                                            <span class="text-slate-900 dark:text-white font-bold">Golongan {{ item.blood_type }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right font-extrabold text-slate-900 dark:text-white font-mono text-base">
                                        {{ item.amount }} <span class="text-xs font-normal text-slate-400">Kantong</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- BLOOD STOCK CHANGE HISTORY (FOR ADMIN UTD & SUPERADMIN ONLY) -->
                    <div v-if="isUtdAdmin" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm mt-6">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-base">📜</span>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Riwayat Perubahan Stok Darah (Khusus Admin)
                                </h4>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">Hanya Terlihat Oleh Admin UTD & Superadmin</span>
                        </div>
                        
                        <div v-if="bloodLogs.length === 0" class="p-6 text-center text-xs text-slate-400">
                            Belum ada riwayat perubahan stok darah yang tercatat.
                        </div>

                        <table v-else class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3.5 px-6">Waktu Update</th>
                                    <th class="py-3.5 px-6">Golongan Darah</th>
                                    <th class="py-3.5 px-6 text-center">Sebelumnya</th>
                                    <th class="py-3.5 px-6 text-center">Stok Baru</th>
                                    <th class="py-3.5 px-6 text-center">Perubahan</th>
                                    <th class="py-3.5 px-6 text-right">Petugas / Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                <tr v-for="log in bloodLogs" :key="log.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-6 text-xs text-slate-500 font-mono">
                                        {{ log.updated_at || '-' }}
                                    </td>
                                    <td class="py-3.5 px-6 font-bold">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 font-black text-xs">
                                            Gol. {{ log.blood_type }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 text-center font-mono text-slate-500">
                                        {{ log.old_amount }} Kantong
                                    </td>
                                    <td class="py-3.5 px-6 text-center font-mono font-bold text-slate-900 dark:text-white">
                                        {{ log.new_amount }} Kantong
                                    </td>
                                    <td class="py-3.5 px-6 text-center font-extrabold font-mono">
                                        <span :class="log.delta > 0 ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full text-xs' : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-2 py-0.5 rounded-full text-xs'">
                                            {{ log.delta > 0 ? '+' + log.delta : log.delta }} Kantong
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        👤 {{ log.updater_name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TAB 4 SPECIFIC DATA SOURCE FOOTER -->
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">Sumber Data:</span>
                            <a href="https://bangkatengahkab.go.id/" target="_blank" rel="noopener noreferrer" class="font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 underline">
                                Unit Transfusi Darah (UTD) Kabupaten Bangka Tengah (bangkatengahkab.go.id)
                            </a>
                        </div>
                    </div>

                </div>
            </Transition>
        </main>

        <!-- ADMIN UTD INPUT / EDIT MODAL -->
        <Teleport to="body">
            <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in-up">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-rose-50 to-pink-50 dark:from-rose-950/60 dark:to-slate-900">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                Update Data Stok Darah UTD
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Masukkan jumlah stok darah (kantong) terbaru per golongan darah.
                            </p>
                        </div>
                        <button @click="closeEditModal" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="saveBloodStocks" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4 max-h-[360px] overflow-y-auto pr-1">
                            <div v-for="(stock, idx) in editableStocks" :key="stock.blood_type" class="space-y-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Gol. Darah {{ stock.blood_type }}
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        min="0"
                                        v-model.number="stock.amount"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"
                                        required
                                    />
                                    <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-normal">Kantong</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-xs font-bold text-white transition shadow-md shadow-rose-500/20 cursor-pointer"
                            >
                                <span v-if="isSubmitting">Menyimpan...</span>
                                <span v-else>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

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
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Smooth Tab Transition Animations */
.tab-fade-enter-active,
.tab-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.tab-fade-enter-from {
    opacity: 0;
    transform: translateY(12px) scale(0.99);
}

.tab-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.99);
}

.subtab-fade-enter-active,
.subtab-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.subtab-fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.subtab-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
