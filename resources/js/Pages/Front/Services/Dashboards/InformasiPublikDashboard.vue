<script setup>
import { ref, computed } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
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
const activeSidebarTab = ref(new URLSearchParams(window.location.search).get('tab') || 'bapok')
const isSidebarOpen = ref(false)

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
}

const selectTab = (tab) => {
    activeSidebarTab.value = tab
}

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

const componentsList = computed(() => {
    return utdData.value?.components || ['Whole Blood (WB)', 'Packed Red Blood (PRC)']
})

const bloodTypesList = computed(() => {
    return utdData.value?.blood_types || ['A+', 'B+', 'O+', 'AB+']
})

const lastUpdatedText = computed(() => {
    return utdData.value?.last_updated || 'Belum diperbarui'
})

const stockMatrixMap = computed(() => {
    const map = {}
    const stocks = utdData.value?.stocks || []
    stocks.forEach(s => {
        const comp = s.component || 'Whole Blood (WB)'
        const bt = s.blood_type
        map[`${comp}_${bt}`] = s.amount || 0
    })
    return map
})

const getStockAmount = (component, bloodType) => {
    return stockMatrixMap.value[`${component}_${bloodType}`] ?? 0
}

const getComponentTotal = (component) => {
    return bloodTypesList.value.reduce((sum, bt) => sum + getStockAmount(component, bt), 0)
}

const getBloodTypeTotal = (bloodType) => {
    return componentsList.value.reduce((sum, comp) => sum + getStockAmount(comp, bloodType), 0)
}

const totalBloodStock = computed(() => {
    if (utdData.value?.total_stock !== undefined && utdData.value?.total_stock !== null) {
        return utdData.value.total_stock
    }
    return componentsList.value.reduce((sum, comp) => sum + getComponentTotal(comp), 0)
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
    const list = []
    componentsList.value.forEach(comp => {
        bloodTypesList.value.forEach(bt => {
            list.push({
                component: comp,
                blood_type: bt,
                amount: getStockAmount(comp, bt)
            })
        })
    })
    editableStocks.value = list
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
    <div class="relative mt-6 min-h-[600px]">

        <!-- COMPACT FLUSH SIDEBAR NAVIGATION (PERMANENTLY STUCK TO SCREEN LEFT EDGE) -->
        <aside
            class="fixed left-0 top-32 z-30 w-44 sm:w-48 transition-all duration-300"
        >
            <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-r-2xl rounded-l-none border-y border-r border-slate-200/80 dark:border-slate-800 p-2.5 shadow-xl space-y-1">
                <div class="px-2 py-1 mb-1 border-b border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        Kategori
                    </h3>
                </div>

                <nav class="space-y-1">
                    <!-- Tab 1: Bahan Pokok -->
                    <button
                        @click="selectTab('bapok')"
                        class="w-full flex items-center gap-2.5 text-left px-3 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                        :class="activeSidebarTab === 'bapok'
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <span class="text-sm shrink-0">🛒</span>
                        <span>Bahan Pokok</span>
                    </button>

                    <!-- Tab 2: Harga Sawit -->
                    <button
                        @click="selectTab('sawit')"
                        class="w-full flex items-center gap-2.5 text-left px-3 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                        :class="activeSidebarTab === 'sawit'
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <span class="text-sm shrink-0">🌴</span>
                        <span>Harga Sawit</span>
                    </button>

                    <!-- Tab 3: Stok Darah -->
                    <button
                        @click="selectTab('utd')"
                        class="w-full flex items-center gap-2.5 text-left px-3 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                        :class="activeSidebarTab === 'utd'
                            ? 'bg-rose-600 text-white shadow-sm'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <span class="text-sm shrink-0">🩸</span>
                        <span>Stok Darah</span>
                    </button>
                </nav>
            </div>
        </aside>

        <!-- MAIN DISPLAY AREA (WITH LEFT PADDING FOR SIDEBAR) -->
        <main class="w-full min-w-0 pl-48 sm:pl-52 transition-all duration-300">
            <Transition name="tab-fade" mode="out-in">
                <!-- 🛒 SUB-VIEW 1: HARGA PASAR & STOK BAPOK -->
                <div v-if="activeSidebarTab === 'bapok'" key="bapok" class="space-y-8">

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
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">Sumber Data:</span>
                            <a href="https://dpkp.bangkatengahkab.go.id/" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 underline">
                                Dinas Pertanian Kab. Bangka Tengah (dpkp.bangkatengahkab.go.id)
                            </a>
                        </div>
                        <div v-if="displayDateSawit" class="flex flex-wrap items-center gap-3">
                            <a v-if="sawitData.metadata?.download_pdf" :href="sawitData.metadata.download_pdf" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-xs transition text-[11px]">
                                <span>📄</span> Unduh Dokumen PDF
                            </a>
                            <span>Terakhir diperbarui: {{ displayDateSawit }}</span>
                        </div>
                    </div>

                </div>



                <!-- 🩸 SUB-VIEW 4: UNIT TRANSFUSI DARAH (UTD) -->
                <div v-else-if="activeSidebarTab === 'utd'" key="utd" class="space-y-8">

                    <!-- TOTAL STOK SUMMARY & LAST UPDATED CARD -->
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

                        <!-- FITUR LAST UPDATED -->
                        <div class="text-right sm:text-left border-t sm:border-t-0 sm:border-l border-slate-100 dark:border-slate-800 pt-3 sm:pt-0 sm:pl-6 text-xs space-y-1.5">
                            <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-bold">
                                <span>⏱️ Last Updated:</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-mono font-black text-xs">
                                    {{ lastUpdatedText }}
                                </span>
                            </div>
                            <div class="text-slate-400 text-[11px]">📍 Unit Transfusi Darah Kab. Bangka Tengah</div>
                        </div>
                    </div>

                    <!-- Admin UTD Controls (Visible only to Admin UTD) -->
                    <div v-if="isUtdAdmin" class="flex items-center gap-3 justify-end animate-fade-in-up">
                        <Link
                            href="/utd/history"
                            class="inline-flex items-center gap-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-2.5 text-xs font-extrabold text-rose-700 dark:text-rose-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm cursor-pointer"
                        >
                            <span>📜 Riwayat Transfusi</span>
                        </Link>
                        <button
                            @click="openEditModal"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2.5 text-xs font-bold text-white transition shadow-md shadow-rose-500/20 cursor-pointer"
                        >
                            <span>⚙️ Update Stok Darah</span>
                        </button>
                    </div>

                    <!-- MATRIX TABLE STOK DARAH (X: Gol. Darah, Y: Komponen, Corner: Komponen) -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    <span>🩸 Matrix Stok Darah Per Komponen & Golongan</span>
                                </h4>
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                    Jumlah kantong darah berdasarkan kombinasi jenis komponen dan rhesus golongan darah.
                                </p>
                            </div>
                        </div>

                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-100/80 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 font-extrabold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <!-- Corner Header Cell (Top-Left): Komponen -->
                                    <th class="py-4 px-6 bg-slate-200/60 dark:bg-slate-800 text-slate-900 dark:text-white font-black border-r border-slate-200 dark:border-slate-700">
                                        <div class="flex items-center justify-between">
                                            <span>Komponen</span>
                                            <span class="text-[10px] text-slate-400 font-normal">Gol. Darah ➔</span>
                                        </div>
                                    </th>

                                    <!-- X-Axis Headers: Golongan Darah (A+, B+, O+, AB+) -->
                                    <th v-for="bt in bloodTypesList" :key="bt" class="py-4 px-3 text-center border-r border-slate-200/60 dark:border-slate-800 font-black">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-900 font-black text-xs">
                                            {{ bt }}
                                        </span>
                                    </th>

                                    <!-- Row Total Header -->
                                    <th class="py-4 px-4 text-center bg-slate-200/40 dark:bg-slate-800/60 font-black">
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                <!-- Y-Axis Rows: Components (Whole Blood (WB), Packed Red Blood (PRC)) -->
                                <tr v-for="comp in componentsList" :key="comp" class="hover:bg-rose-50/20 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-4 px-6 font-extrabold bg-slate-50/70 dark:bg-slate-800/40 text-slate-900 dark:text-white border-r border-slate-200/60 dark:border-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span>{{ comp }}</span>
                                        </div>
                                    </td>

                                    <td v-for="bt in bloodTypesList" :key="bt" class="py-4 px-3 text-center font-mono font-extrabold border-r border-slate-100 dark:border-slate-800 text-sm">
                                        <span :class="getStockAmount(comp, bt) > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-slate-600'">
                                            {{ getStockAmount(comp, bt) }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-center font-mono font-black text-rose-600 dark:text-rose-400 bg-slate-50/50 dark:bg-slate-800/30 text-sm">
                                        {{ getComponentTotal(comp) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- KETERANGAN KOMPONEN DARAH -->
                        <div class="p-3.5 bg-slate-50/80 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-6">
                            <span class="font-bold text-slate-700 dark:text-slate-300">ℹ️ Keterangan:</span>
                            <div class="flex flex-wrap items-center gap-4">
                                <div><strong class="text-slate-800 dark:text-slate-200">Whole Blood (WB)</strong> = Darah Lengkap</div>
                                <div><strong class="text-slate-800 dark:text-slate-200">Packed Red Cell (PRC)</strong> = Darah Merah Pekat</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4 SPECIFIC DATA SOURCE FOOTER -->
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium">Sumber Data:</span>
                            <a href="https://bangkatengahkab.go.id/" target="_blank" rel="noopener noreferrer" class="font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 underline">
                                Unit Transfusi Darah (UTD) Kabupaten Bangka Tengah (bangkatengahkab.go.id)
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span>Last Updated: <strong class="text-rose-600 dark:text-rose-400">{{ lastUpdatedText }}</strong></span>
                        </div>
                    </div>

                </div>
            </Transition>
        </main>
    </div>

        <!-- ADMIN UTD INPUT / EDIT MODAL (GRID BY COMPONENT & BLOOD TYPE) -->
        <Teleport to="body">
            <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in-up">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-3xl w-full overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-rose-50 to-pink-50 dark:from-rose-950/60 dark:to-slate-900">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                Update Data Stok Darah UTD (Matrix Form)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Masukkan jumlah stok darah (kantong) terbaru per komponen dan golongan darah.
                            </p>
                        </div>
                        <button @click="closeEditModal" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            ✕
                        </button>
                    </div>

                    <form @submit.prevent="saveBloodStocks" class="p-6 space-y-6">
                        <div class="max-h-[420px] overflow-y-auto pr-1 space-y-6">
                            <div v-for="comp in componentsList" :key="comp" class="space-y-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                                <h4 class="text-xs font-black uppercase tracking-wider text-rose-700 dark:text-rose-400 flex items-center gap-2">
                                    <span>🩸 {{ comp }}</span>
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div
                                        v-for="stock in editableStocks.filter(s => s.component === comp)"
                                        :key="`${stock.component}_${stock.blood_type}`"
                                        class="space-y-1"
                                    >
                                        <label class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300">
                                            Gol. Darah {{ stock.blood_type }}
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                min="0"
                                                v-model.number="stock.amount"
                                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"
                                                required
                                            />
                                            <span class="absolute right-2.5 top-2 text-[10px] text-slate-400">Ktg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                            <Link href="/utd/history" @click="closeEditModal" class="text-xs font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 underline">
                                📜 Buka Halaman Riwayat Perubahan
                            </Link>

                            <div class="flex items-center gap-3">
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
                                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-xs font-extrabold text-white transition shadow-md shadow-rose-500/20 cursor-pointer"
                                >
                                    <span v-if="isSubmitting">Menyimpan...</span>
                                    <span v-else>Simpan Perubahan</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(16px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Fixed Sidebar Screen-Locked Drawer Transition */
.sidebar-fade-enter-active,
.sidebar-fade-leave-active {
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
}

.sidebar-fade-enter-from,
.sidebar-fade-leave-to {
    opacity: 0;
    transform: translateX(-100%);
}

/* Smooth Main Content Tab Transition Animations */
.tab-fade-enter-active {
    transition: opacity 0.35s ease-out, transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.tab-fade-leave-active {
    transition: opacity 0.2s ease-in, transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.tab-fade-enter-from {
    opacity: 0;
    transform: translateY(14px) scale(0.99);
}

.tab-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.99);
}

/* Sub-Tab Fade Transitions */
.subtab-fade-enter-active {
    transition: opacity 0.3s ease-out, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.subtab-fade-leave-active {
    transition: opacity 0.18s ease-in, transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.subtab-fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.subtab-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
