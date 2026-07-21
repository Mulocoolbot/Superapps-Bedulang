<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

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

const dashboardData = computed(() => props.dashboard ?? {})

const serviceTypes = computed(() =>
    props.service?.filters?.length > 0
        ? props.service.filters
        : [{ label: 'Semua Jenis', value: 'all' }]
)

const selectedStartDate = ref(props.dashboard?.metadata?.start_date ?? '')
const selectedEndDate = ref(props.dashboard?.metadata?.end_date ?? '')
const selectedPasar = ref(props.dashboard?.metadata?.pasar_id ?? '')

const pasarMap = {
    1: 'Pasar Modern Koba',
    2: 'Pasar Namang',
    3: 'Pasar Air Mesu',
    4: 'Pasar Sungaiselan',
    5: 'Pasar Kayu Besi',
    6: 'Pasar Simpang Katis',
    7: 'Pasar Lubuk Besar'
}

const chartData = computed(() => {
    if (props.dashboard?.commodity_chart?.labels?.length || 0) {
        return props.dashboard.commodity_chart
    }
    return dashboardData.value.chart ?? {
        labels: [],
        series: [],
    }
})

const basicChartData = computed(() => {
    const labels = []
    const series_i = []
    const series_ii = []
    const sourceLabels = chartData.value.labels ?? []
    const sourceSeriesI = chartData.value.series_i ?? []
    const sourceSeriesII = chartData.value.series_ii ?? []

    sourceLabels.forEach((label, idx) => {
        if (label !== 'Gas LPG 3 Kg') {
            labels.push(label)
            series_i.push(sourceSeriesI[idx] ?? 0)
            series_ii.push(sourceSeriesII[idx] ?? 0)
        }
    })
    return { labels, series_i, series_ii }
})

const importantChartData = computed(() => {
    const labels = []
    const series_i = []
    const series_ii = []
    const sourceLabels = chartData.value.labels ?? []
    const sourceSeriesI = chartData.value.series_i ?? []
    const sourceSeriesII = chartData.value.series_ii ?? []

    sourceLabels.forEach((label, idx) => {
        if (label === 'Gas LPG 3 Kg') {
            labels.push(label)
            series_i.push(sourceSeriesI[idx] ?? 0)
            series_ii.push(sourceSeriesII[idx] ?? 0)
        }
    })
    return { labels, series_i, series_ii }
})

const hasBasicChart = computed(() => basicChartData.value.labels.length > 0)
const hasImportantChart = computed(() => importantChartData.value.labels.length > 0)

const priceTrends = computed(() => props.dashboard?.price_trends ?? [])

const basicPriceTrends = computed(() => {
    return priceTrends.value.filter(item => item.commodity !== 'Gas LPG 3 Kg')
})

const importantPriceTrends = computed(() => {
    return priceTrends.value.filter(item => item.commodity === 'Gas LPG 3 Kg')
})

const hasBasicPriceTrends = computed(() => basicPriceTrends.value.length > 0)
const hasImportantPriceTrends = computed(() => importantPriceTrends.value.length > 0)

const latestDateLimit = computed(() => props.dashboard?.metadata?.latest_date ?? '')

const maxStartDateLimit = computed(() => {
    const dates = []
    if (latestDateLimit.value) {
        const latest = new Date(latestDateLimit.value)
        latest.setDate(latest.getDate() - 1)
        dates.push(latest.toISOString().split('T')[0])
    }
    if (selectedEndDate.value) {
        const end = new Date(selectedEndDate.value)
        end.setDate(end.getDate() - 1)
        dates.push(end.toISOString().split('T')[0])
    }
    if (dates.length > 0) {
        return dates.reduce((a, b) => a < b ? a : b)
    }
    return ''
})

const minEndDateLimit = computed(() => {
    if (selectedStartDate.value) {
        const start = new Date(selectedStartDate.value)
        start.setDate(start.getDate() + 1)
        return start.toISOString().split('T')[0]
    }
    return ''
})

const formatDateDMY = (dateStr) => {
    if (!dateStr) return ''
    const parts = dateStr.split('-')
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`
    }
    return dateStr
}

const formattedStartDate = computed(() => formatDateDMY(selectedStartDate.value))
const formattedEndDate = computed(() => formatDateDMY(selectedEndDate.value))

const maxPriceValue = computed(() => {
    const seriesI = chartData.value.series_i ?? []
    const seriesII = chartData.value.series_ii ?? []
    return Math.max(...seriesI, ...seriesII, 1)
})

const getBarWidth = (val) => {
    return Math.max(0, Math.min(100, Math.ceil((val / maxPriceValue.value) * 100)))
}

const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

const applyFilters = () => {
    if (selectedStartDate.value && latestDateLimit.value) {
        if (selectedStartDate.value >= latestDateLimit.value) {
            alert(`Tanggal Pembanding (Mulai) harus minimal H-1 sebelum Tanggal Terkini (${latestDateLimit.value})!`)
            return
        }
    }
    if (selectedStartDate.value && selectedEndDate.value) {
        if (selectedStartDate.value > selectedEndDate.value) {
            alert('Tanggal Pembanding (Mulai) tidak boleh lebih dari Tanggal Acuan (Selesai)!')
            return
        }
        if (selectedStartDate.value === selectedEndDate.value) {
            alert('Tanggal Acuan (Selesai) tidak boleh sama dengan Tanggal Pembanding (Mulai)!')
            return
        }
    }
    if (selectedEndDate.value && latestDateLimit.value) {
        if (selectedEndDate.value > latestDateLimit.value) {
            alert(`Tanggal Acuan (Selesai) tidak boleh melebihi Tanggal Terkini (${latestDateLimit.value})!`)
            return
        }
    }
    router.get(`/layanan/${props.service.slug}`, {
        start_date: selectedStartDate.value || undefined,
        end_date: selectedEndDate.value || undefined,
        pasar_id: selectedPasar.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    selectedStartDate.value = ''
    selectedEndDate.value = ''
    selectedPasar.value = ''
    applyFilters()
}

const urlParams = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null
const activeTab = ref(urlParams ? (urlParams.get('tab') || 'harga') : 'harga')

const todayStr = '2026-07-20'

const isMonday = (dateStr) => {
    if (!dateStr) return false
    const date = new Date(dateStr)
    return date.getDay() === 1
}

const getNearestMonday = (dateStr) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    const day = date.getDay()
    if (day === 1) return dateStr
    
    const diff = 1 - day
    let adjustDays = diff
    if (day === 0) adjustDays = -6
    else if (day > 1) adjustDays = 1 - day
    
    date.setDate(date.getDate() + adjustDays)
    
    if (date > new Date(todayStr)) {
        date.setDate(date.getDate() - 7)
    }
    
    return date.toISOString().split('T')[0]
}

const stokDate = ref(props.dashboard?.metadata?.stok_end_date || getNearestMonday(todayStr))
const stokCompDate = ref(props.dashboard?.metadata?.stok_start_date || '')
const selectedPenyedia = ref(props.dashboard?.metadata?.penyedia || '1')

watch(() => props.dashboard?.metadata?.stok_end_date, (newVal) => {
    if (newVal) stokDate.value = newVal
})
watch(() => props.dashboard?.metadata?.stok_start_date, (newVal) => {
    if (newVal) stokCompDate.value = newVal
})
watch(() => props.dashboard?.metadata?.penyedia, (newVal) => {
    if (newVal) selectedPenyedia.value = String(newVal)
})

const initStokCompDate = () => {
    if (props.dashboard?.metadata?.stok_start_date) {
        stokCompDate.value = props.dashboard.metadata.stok_start_date
    } else if (stokDate.value) {
        const date = new Date(stokDate.value)
        date.setDate(date.getDate() - 7)
        stokCompDate.value = date.toISOString().split('T')[0]
    }
}
initStokCompDate()

const applyStokFilters = () => {
    router.get(`/layanan/${props.service.slug}`, {
        start_date: selectedStartDate.value || undefined,
        end_date: selectedEndDate.value || undefined,
        pasar_id: selectedPasar.value || undefined,
        pasar_date: pasarSelectedDate.value || undefined,
        pasar_commodity: pasarSelectedCommodity.value || undefined,
        stok_start_date: stokCompDate.value || undefined,
        stok_end_date: stokDate.value || undefined,
        penyedia: selectedPenyedia.value || undefined,
        tab: 'stok'
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const handleStokDateChange = () => {
    if (!stokDate.value) return
    
    if (stokDate.value > todayStr) {
        alert('Tanggal Stok tidak boleh melebihi tanggal hari ini!')
        stokDate.value = getNearestMonday(todayStr)
        return
    }
    
    if (!isMonday(stokDate.value)) {
        const adjusted = getNearestMonday(stokDate.value)
        alert(`Tanggal Stok hanya boleh hari Senin. Tanggal disesuaikan ke hari Senin terdekat: ${formatDateDMY(adjusted)}.`);
        stokDate.value = adjusted
    }
    
    if (stokDate.value === stokCompDate.value) {
        alert('Tanggal Pembanding tidak boleh sama dengan Tanggal Stok!');
        initStokCompDate()
    }

    applyStokFilters()
}

const handleStokCompDateChange = () => {
    if (!stokCompDate.value) return
    
    if (stokCompDate.value > todayStr) {
        alert('Tanggal Pembanding tidak boleh melebihi tanggal hari ini!')
        initStokCompDate()
        return
    }
    
    if (!isMonday(stokCompDate.value)) {
        const adjusted = getNearestMonday(stokCompDate.value)
        alert(`Tanggal Pembanding hanya boleh hari Senin. Tanggal disesuaikan ke hari Senin terdekat: ${formatDateDMY(adjusted)}.`);
        stokCompDate.value = adjusted
    }
    
    if (stokCompDate.value === stokDate.value) {
        alert('Tanggal Pembanding tidak boleh sama dengan Tanggal Stok!');
        initStokCompDate()
    }

    applyStokFilters()
}

const pasarSelectedCommodity = ref(props.dashboard?.pasar_commodity || 'Daging Sapi')
const pasarSelectedDate = ref(props.dashboard?.pasar_date || todayStr)

watch(() => props.dashboard?.pasar_commodity, (newVal) => {
    if (newVal) pasarSelectedCommodity.value = newVal
})
watch(() => props.dashboard?.pasar_date, (newVal) => {
    if (newVal) pasarSelectedDate.value = newVal
})

const applyPasarFilters = () => {
    router.get(`/layanan/${props.service.slug}`, {
        start_date: selectedStartDate.value || undefined,
        end_date: selectedEndDate.value || undefined,
        pasar_id: selectedPasar.value || undefined,
        pasar_date: pasarSelectedDate.value || undefined,
        pasar_commodity: pasarSelectedCommodity.value || undefined,
        tab: 'pasar'
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const handlePasarDateChange = () => {
    if (!pasarSelectedDate.value) return
    if (pasarSelectedDate.value > latestDateLimit.value) {
        alert(`Tanggal tidak boleh melebihi tanggal terkini (${latestDateLimit.value})!`)
        pasarSelectedDate.value = latestDateLimit.value
        return
    }
    applyPasarFilters()
}

const handlePasarCommodityChange = () => {
    applyPasarFilters()
}

const seedRandom = (str) => {
    let hash = 0
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash)
    }
    return Math.abs(hash)
}

const getMockStock = (commodity, dateStr) => {
    const seed = seedRandom(commodity + dateStr)
    const baseStocks = {
        'Beras Premium': 150,
        'Beras Medium': 200,
        'Gula Pasir': 80,
        'Minyak Goreng': 120,
        'Tepung Terigu': 60,
        'Cabai Merah': 25,
        'Daging Sapi': 15,
        'Telur Ayam': 40,
        'Bawang Merah': 30,
        'Daging Ayam': 35
    }
    const base = baseStocks[commodity] ?? 50
    const variation = ((seed % 30) - 15) / 100
    return Math.round(base * (1 + variation))
}

const getMockPrice = (commodity, pasarId, dateStr) => {
    const seed = seedRandom(commodity + pasarId + dateStr)
    const basePrices = {
        'Cabai Merah': 45000,
        'Minyak Goreng': 17500,
        'Tepung Terigu': 12500,
        'Telur Ayam': 28000,
        'Beras Premium': 14500,
        'Beras Medium': 12500,
        'Daging Sapi': 130000,
        'Gula Pasir': 16000,
        'Bawang Merah': 38000,
        'Daging Ayam': 34000
    }
    const base = basePrices[commodity] ?? 20000
    const marketSeed = seedRandom(commodity + pasarId)
    const dateSeed = seedRandom(commodity + dateStr)
    const mVar = ((marketSeed % 20) - 10) / 100
    const dVar = ((dateSeed % 10) - 5) / 100
    
    const isReporting = (seed % 100) >= 15
    if (!isReporting) return 0
    
    return Math.round((base * (1 + mVar + dVar)) / 100) * 100
}

const stokCommodities = [
    'Beras Premium', 'Beras Medium', 'Gula Pasir', 'Minyak Goreng', 
    'Tepung Terigu', 'Cabai Merah', 'Daging Sapi', 'Telur Ayam', 
    'Bawang Merah', 'Daging Ayam'
]

const pasarCommodities = computed(() => props.dashboard?.pasar_commodities || [
    'Daging Sapi', 'Cabe Merah Besar', 'Cabe Rawit Merah', 'Bawang Merah', 'Bawang Putih', 
    'Daging Ayam Ras', 'Telur Ayam Ras', 'Minyak Goreng Kemasan Premium', 'Gula Pasir', 
    'Beras Premium', 'Beras Medium', 'Tepung Terigu'
])

const stokChartData = computed(() => props.dashboard?.stok_data ?? [])

const maxStokValue = computed(() => {
    let maxVal = 1
    stokChartData.value.forEach(item => {
        if (item.qtyStok > maxVal) maxVal = item.qtyStok
        if (item.qtyComp > maxVal) maxVal = item.qtyComp
    })
    return maxVal
})

const getStokBarWidth = (val) => {
    return Math.max(0, Math.min(100, Math.ceil((val / maxStokValue.value) * 100)))
}

const pasarChartData = computed(() => props.dashboard?.pasar_chart_data || [])

const maxPasarPrice = computed(() => {
    let maxVal = 1
    pasarChartData.value.forEach(item => {
        if (item.price > maxVal) maxVal = item.price
    })
    return maxVal
})

const yAxisTicks = computed(() => {
    const maxVal = maxPasarPrice.value
    return [
        maxVal,
        Math.round(maxVal * 0.8),
        Math.round(maxVal * 0.6),
        Math.round(maxVal * 0.4),
        Math.round(maxVal * 0.2),
        0
    ]
})

const getPasarBarWidth = (val) => {
    return Math.max(0, Math.min(100, Math.ceil((val / maxPasarPrice.value) * 100)))
}

const getStokBarPercent = (val) => {
    return Math.max(1, Math.min(100, Math.ceil((val / maxStokValue.value) * 100)))
}

const getPasarBarPercent = (val) => {
    return Math.max(1, Math.min(100, Math.ceil((val / maxPasarPrice.value) * 100)))
}
</script>

<template>
    <!-- Menu Bar Section (only for Perindagkop) -->
    <div class="mt-6 flex flex-wrap gap-2 rounded-xl bg-slate-100 p-1.5 dark:bg-slate-800/60 w-full animate-fade-in-up [animation-delay:100ms]">
        <button
            @click="activeTab = 'harga'"
            type="button"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition-all duration-200 select-none cursor-pointer"
            :class="activeTab === 'harga' 
                ? 'bg-white text-cyan-600 shadow-sm dark:bg-slate-700 dark:text-cyan-400' 
                : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200'"
        >
            Perbandingan Harga Barang
        </button>
        <button
            @click="activeTab = 'stok'"
            type="button"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition-all duration-200 select-none cursor-pointer"
            :class="activeTab === 'stok' 
                ? 'bg-white text-cyan-600 shadow-sm dark:bg-slate-700 dark:text-cyan-400' 
                : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200'"
        >
            Stok Bapok
        </button>
        <button
            @click="activeTab = 'pasar'"
            type="button"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition-all duration-200 select-none cursor-pointer"
            :class="activeTab === 'pasar' 
                ? 'bg-white text-cyan-600 shadow-sm dark:bg-slate-700 dark:text-cyan-400' 
                : 'text-slate-600 hover:bg-white/50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200'"
        >
            Harga Per Pasar
        </button>
    </div>

    <!-- Main Grid -->
    <div class="mt-6 grid gap-6 lg:grid-cols-[2fr_1fr]">

        <!-- Chart Section (Tab 1 / Harga) -->
        <section v-if="activeTab === 'harga'" :key="'harga-charts-' + selectedStartDate + '-' + selectedEndDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
            <!-- Filter Controls -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Visualisasi</p>
                    <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                        Grafik Perbandingan Harga
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Date Range -->
                    <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800">
                        <input
                            type="date"
                            v-model="selectedStartDate"
                            :max="maxStartDateLimit"
                            @change="applyFilters"
                            class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100 dark:text-slate-300 dark:focus:ring-cyan-950/50"
                            placeholder="Mulai"
                        />
                        <span class="text-xs text-slate-400 dark:text-slate-500">→</span>
                        <input
                            type="date"
                            v-model="selectedEndDate"
                            :min="minEndDateLimit"
                            :max="latestDateLimit"
                            @change="applyFilters"
                            class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100 dark:text-slate-300 dark:focus:ring-cyan-950/50"
                            placeholder="Selesai"
                        />
                    </div>

                    <!-- Pasar Selector -->
                    <div class="relative">
                        <select
                            v-model="selectedPasar"
                            @change="applyFilters"
                            class="h-9 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:focus:border-cyan-500 dark:focus:ring-cyan-950/50"
                        >
                            <option value="">Semua Pasar</option>
                            <option v-for="(name, id) in pasarMap" :key="id" :value="id">{{ name }}</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Charts Wrapper -->
            <div class="mt-5 space-y-6">
                <!-- 1. Kebutuhan Pokok Chart -->
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                    <h4 class="text-xs font-bold uppercase tracking-[0.16em] text-slate-700 mb-3 border-b border-slate-200/60 pb-2 dark:text-slate-300 dark:border-slate-800/80">
                        GRAFIK PERBANDINGAN HARGA BARANG KEBUTUHAN POKOK {{ selectedPasar ? 'DI ' + pasarMap[selectedPasar].toUpperCase() : 'DI SEMUA PASAR' }}
                    </h4>
                    <div v-if="!hasBasicChart" class="flex min-h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500">
                        Belum ada data barang kebutuhan pokok untuk ditampilkan
                    </div>
                    <div v-else class="space-y-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-200/40 pb-2 dark:text-slate-400 dark:border-slate-800/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Komoditas Pokok</span>
                            <div class="flex gap-4">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded bg-slate-400"></span>
                                    {{ formattedStartDate }} (Awal)
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded bg-blue-600"></span>
                                    {{ formattedEndDate }} (Akhir)
                                </span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(label, idx) in basicChartData.labels" :key="idx" class="space-y-2">
                                <div class="flex justify-between text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    <span>{{ label }}</span>
                                </div>
                                <div class="grid grid-cols-1 gap-2 bg-slate-100/50 dark:bg-slate-900/40 p-2.5 rounded-lg border border-slate-200/40 dark:border-slate-800">
                                    <!-- Bar Price I -->
                                    <div class="flex items-center gap-3">
                                        <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedStartDate }}</span>
                                        <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'basic-i-' + idx + '-' + basicChartData.series_i[idx]"
                                                    class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${getBarWidth(basicChartData.series_i[idx])}%` }"
                                                ></div>
                                            </div>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                    Nominal: Rp {{ formatNumber(basicChartData.series_i[idx]) }}
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                        <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            {{ basicChartData.series_i[idx] > 0 ? 'Rp ' + formatNumber(basicChartData.series_i[idx]) : '-' }}
                                        </span>
                                    </div>
                                    <!-- Bar Price II -->
                                    <div class="flex items-center gap-3">
                                        <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedEndDate }}</span>
                                        <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'basic-ii-' + idx + '-' + basicChartData.series_ii[idx]"
                                                    class="bg-blue-600 dark:bg-blue-500 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${getBarWidth(basicChartData.series_ii[idx])}%` }"
                                                ></div>
                                            </div>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                    Nominal: Rp {{ formatNumber(basicChartData.series_ii[idx]) }}
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                        <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            {{ basicChartData.series_ii[idx] > 0 ? 'Rp ' + formatNumber(basicChartData.series_ii[idx]) : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Barang Penting Chart -->
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                    <h4 class="text-xs font-bold uppercase tracking-[0.16em] text-slate-700 mb-3 border-b border-slate-200/60 pb-2 dark:text-slate-300 dark:border-slate-800/80">
                        GRAFIK PERBANDINGAN HARGA BARANG PENTING {{ selectedPasar ? 'DI ' + pasarMap[selectedPasar].toUpperCase() : 'DI SEMUA PASAR' }}
                    </h4>
                    <div v-if="!hasImportantChart" class="flex min-h-[120px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500">
                        Belum ada data barang penting untuk ditampilkan
                    </div>
                    <div v-else class="space-y-6">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-200/40 pb-2 dark:text-slate-400 dark:border-slate-800/60">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Barang Penting</span>
                            <div class="flex gap-4">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded bg-slate-400"></span>
                                    {{ formattedStartDate }} (Awal)
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded bg-blue-600"></span>
                                    {{ formattedEndDate }} (Akhir)
                                </span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div v-for="(label, idx) in importantChartData.labels" :key="idx" class="space-y-2">
                                <div class="flex justify-between text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    <span>{{ label }}</span>
                                </div>
                                <div class="grid grid-cols-1 gap-2 bg-slate-100/50 dark:bg-slate-900/40 p-2.5 rounded-lg border border-slate-200/40 dark:border-slate-800">
                                    <!-- Bar Price I -->
                                    <div class="flex items-center gap-3">
                                        <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedStartDate }}</span>
                                        <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'important-i-' + idx + '-' + importantChartData.series_i[idx]"
                                                    class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${getBarWidth(importantChartData.series_i[idx])}%` }"
                                                ></div>
                                            </div>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                    Nominal: Rp {{ formatNumber(importantChartData.series_i[idx]) }}
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                        <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            {{ importantChartData.series_i[idx] > 0 ? 'Rp ' + formatNumber(importantChartData.series_i[idx]) : '-' }}
                                        </span>
                                    </div>
                                    <!-- Bar Price II -->
                                    <div class="flex items-center gap-3">
                                        <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedEndDate }}</span>
                                        <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'important-ii-' + idx + '-' + importantChartData.series_ii[idx]"
                                                    class="bg-blue-600 dark:bg-blue-500 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${getBarWidth(importantChartData.series_ii[idx])}%` }"
                                                ></div>
                                            </div>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                    Nominal: Rp {{ formatNumber(importantChartData.series_ii[idx]) }}
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                        <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            {{ importantChartData.series_ii[idx] > 0 ? 'Rp ' + formatNumber(importantChartData.series_ii[idx]) : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sidebar (Tab 1 / Harga Tables) -->
        <aside v-if="activeTab === 'harga'" :key="'harga-tables-' + selectedStartDate + '-' + selectedEndDate" class="space-y-6 animate-fade-in-up [animation-delay:300ms]">
            <!-- 1. Perbandingan Harga Barang Kebutuhan Pokok -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tabel Data</p>
                    <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Perbandingan Harga Barang Kebutuhan Pokok</h3>
                </div>
                <div v-if="!hasBasicPriceTrends" class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
                    Belum ada data kebutuhan pokok untuk filter saat ini.
                </div>
                <div v-else class="overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 sticky top-0 z-10 dark:bg-slate-800">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Komoditas</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Awal</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Akhir</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Selisih</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                <tr v-for="item in basicPriceTrends" :key="item.commodity" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-3 py-2 font-medium text-slate-800 dark:text-slate-200">{{ item.commodity }}</td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ item.price_i }}</td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ item.price_ii }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap" :class="item.selisih === '-' ? 'text-slate-500' : (item.selisih > 0 ? 'text-emerald-600' : (item.selisih < 0 ? 'text-rose-600' : 'text-slate-500'))">
                                        {{ item.selisih === '-' ? '-' : (item.selisih > 0 ? '+' : '') + formatNumber(item.selisih) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="font-bold text-lg">
                                            <span v-if="item.status === 'naik'" class="text-emerald-600" title="Naik">▲</span>
                                            <span v-else-if="item.status === 'turun'" class="text-rose-600" title="Turun">▼</span>
                                            <span v-else-if="item.status === 'tetap'" class="text-slate-400" title="Tetap">=</span>
                                            <span v-else class="text-slate-400" title="Tidak Tersedia">-</span>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Perbandingan Harga Barang Penting -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tabel Data</p>
                    <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Perbandingan Harga Barang Penting</h3>
                </div>
                <div v-if="!hasImportantPriceTrends" class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
                    Belum ada data barang penting untuk filter saat ini.
                </div>
                <div v-else class="overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto max-h-[200px] overflow-y-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 sticky top-0 z-10 dark:bg-slate-800">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Komoditas</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Awal</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Akhir</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Selisih</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                <tr v-for="item in importantPriceTrends" :key="item.commodity" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-3 py-2 font-medium text-slate-800 dark:text-slate-200">{{ item.commodity }}</td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ item.price_i }}</td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ item.price_ii }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap" :class="item.selisih === '-' ? 'text-slate-500' : (item.selisih > 0 ? 'text-emerald-600' : (item.selisih < 0 ? 'text-rose-600' : 'text-slate-500'))">
                                        {{ item.selisih === '-' ? '-' : (item.selisih > 0 ? '+' : '') + formatNumber(item.selisih) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="font-bold text-lg">
                                            <span v-if="item.status === 'naik'" class="text-emerald-600" title="Naik">▲</span>
                                            <span v-else-if="item.status === 'turun'" class="text-rose-600" title="Turun">▼</span>
                                            <span v-else-if="item.status === 'tetap'" class="text-slate-400" title="Tetap">=</span>
                                            <span v-else class="text-slate-400" title="Tidak Tersedia">-</span>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Stok Bapok Chart Section (Tab 2 / Stok) -->
        <section v-if="activeTab === 'stok'" :key="'stok-charts-' + stokDate + '-' + stokCompDate + '-' + selectedPenyedia" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 space-y-6 animate-fade-in-up [animation-delay:200ms]">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b border-slate-200/60 pb-5 dark:border-slate-800/80">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Visualisasi Stok</p>
                    <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">Tren Stok Bahan Pokok (Ton)</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800">
                        <input
                            type="date"
                            v-model="stokCompDate"
                            @change="handleStokCompDateChange"
                            class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100 dark:text-slate-300 dark:focus:ring-cyan-950/50"
                            placeholder="Mulai"
                        />
                        <span class="text-xs text-slate-400 dark:text-slate-500">→</span>
                        <input
                            type="date"
                            v-model="stokDate"
                            @change="handleStokDateChange"
                            class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100 dark:text-slate-300 dark:focus:ring-cyan-950/50"
                            placeholder="Selesai"
                        />
                    </div>
                    <div class="relative">
                        <select
                            v-model="selectedPenyedia"
                            @change="applyStokFilters"
                            class="h-9 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:focus:border-cyan-500 dark:focus:ring-cyan-950/50"
                        >
                            <option value="1">Distributor</option>
                            <option value="2">Retail Modern / Sub-Distributor</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Graphic bar chart for Stok -->
            <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                <div v-if="stokChartData.length === 0" class="flex min-h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500">
                    Belum ada data stok bapok untuk ditampilkan
                </div>
                <div v-else class="space-y-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-200/40 pb-2 dark:text-slate-400 dark:border-slate-800/60">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Komoditas Pokok</span>
                        <div class="flex gap-4">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded bg-slate-400"></span>
                                {{ formatDateDMY(stokCompDate) }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded bg-cyan-600"></span>
                                {{ formatDateDMY(stokDate) }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <div v-for="item in stokChartData" :key="item.commodity" class="space-y-2">
                            <div class="flex justify-between text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <span>{{ item.commodity }}</span>
                            </div>
                            <div class="grid grid-cols-1 gap-2 bg-slate-100/50 dark:bg-slate-900/40 p-2.5 rounded-lg border border-slate-200/40 dark:border-slate-800">
                                <div class="flex items-center gap-3">
                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formatDateDMY(stokCompDate) }}</span>
                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                            <div 
                                                class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out" 
                                                :style="{ width: `${getStokBarWidth(item.qtyComp)}%` }"
                                            ></div>
                                        </div>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                            <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                Nominal: {{ formatNumber(item.qtyComp) }} Ton
                                            </div>
                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                        </div>
                                    </div>
                                    <span class="w-20 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        {{ item.qtyComp > 0 ? formatNumber(item.qtyComp) + ' Ton' : '-' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formatDateDMY(stokDate) }}</span>
                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                            <div 
                                                class="bg-cyan-600 dark:bg-cyan-500 h-full rounded-r transition-all duration-700 ease-out" 
                                                :style="{ width: `${getStokBarWidth(item.qtyStok)}%` }"
                                            ></div>
                                        </div>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                            <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                Nominal: {{ formatNumber(item.qtyStok) }} Ton
                                            </div>
                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                        </div>
                                    </div>
                                    <span class="w-20 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        {{ item.qtyStok > 0 ? formatNumber(item.qtyStok) + ' Ton' : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sidebar for Stok (Tab 2 / Stok Tables) -->
        <aside v-if="activeTab === 'stok'" :key="'stok-tables-' + stokDate + '-' + stokCompDate + '-' + selectedPenyedia" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900 space-y-4 animate-fade-in-up [animation-delay:300ms]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Rincian Stok</p>
                <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Tabel Selisih Stok Komoditas</h3>
            </div>
            <div v-if="stokChartData.length === 0" class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
                Belum ada data stok bapok untuk filter saat ini.
            </div>
            <div v-else class="overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 sticky top-0 z-10 dark:bg-slate-800">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Komoditas</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Awal</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Akhir</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            <tr v-for="item in stokChartData" :key="item.commodity" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-3 py-2 font-medium text-slate-800 dark:text-slate-200 text-xs">{{ item.commodity }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-300 text-xs">{{ formatNumber(item.qtyComp) }} T</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-300 text-xs">{{ formatNumber(item.qtyStok) }} T</td>
                                <td class="px-3 py-2 text-right text-xs font-semibold whitespace-nowrap" :class="item.selisih > 0 ? 'text-emerald-600' : (item.selisih < 0 ? 'text-rose-600' : 'text-slate-500')">
                                    {{ item.selisih > 0 ? '+' : '' }}{{ formatNumber(item.selisih) }} T
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </aside>

        <!-- Harga Per Pasar (Tab 3 / Pasar Charts) -->
        <section v-if="activeTab === 'pasar'" :key="'pasar-charts-' + pasarSelectedCommodity + '-' + pasarSelectedDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 space-y-6 animate-fade-in-up [animation-delay:200ms]">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b border-slate-200/60 pb-5 dark:border-slate-800/80">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Harga Per Pasar</p>
                    <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">Perbandingan Harga Antar Pasar</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        type="date"
                        v-model="pasarSelectedDate"
                        :max="latestDateLimit"
                        @change="handlePasarDateChange"
                        class="h-9 rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:focus:ring-cyan-950/50"
                    />
                    <div class="relative">
                        <select
                            v-model="pasarSelectedCommodity"
                            @change="handlePasarCommodityChange"
                            class="h-9 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:focus:border-cyan-500 dark:focus:ring-cyan-950/50"
                        >
                            <option v-for="c in pasarCommodities" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Visual Bar Chart for Pasar -->
            <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                <div v-if="pasarChartData.length === 0" class="flex min-h-[180px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500">
                    Belum ada data harga per pasar untuk ditampilkan
                </div>
                <div v-else class="space-y-6">
                    <h4 class="text-xs font-bold uppercase tracking-[0.16em] text-slate-700 mb-3 border-b border-slate-200/60 pb-2 dark:text-slate-300 dark:border-slate-800/80">
                        KOMODITAS: {{ pasarSelectedCommodity.toUpperCase() }} TANGGAL {{ formatDateDMY(pasarSelectedDate) }}
                    </h4>
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="item in pasarChartData" :key="item.id" class="space-y-2">
                            <div class="flex justify-between text-sm font-semibold text-slate-800 dark:text-slate-200">
                                <span>{{ item.name }}</span>
                                <span class="font-bold text-cyan-600 dark:text-cyan-400">
                                    {{ item.price > 0 ? 'Rp ' + formatNumber(item.price) : 'tidak tersedia' }}
                                </span>
                            </div>
                            <div class="relative group py-1 focus:outline-none" tabindex="0" v-if="item.price > 0">
                                <div class="bg-slate-200/50 dark:bg-slate-800 h-6 rounded-md overflow-hidden cursor-help">
                                    <div 
                                        class="bg-cyan-600 dark:bg-cyan-500 h-full rounded-r transition-all duration-700 ease-out" 
                                        :style="{ width: `${getPasarBarWidth(item.price)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sidebar for Pasar (Tab 3 / Pasar Tables) -->
        <aside v-if="activeTab === 'pasar'" :key="'pasar-tables-' + pasarSelectedCommodity + '-' + pasarSelectedDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900 space-y-4 animate-fade-in-up [animation-delay:300ms]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Rincian Pasar</p>
                <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Tabel Harga Per Pasar</h3>
            </div>
            <div v-if="pasarChartData.length === 0" class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
                Belum ada data harga per pasar untuk filter saat ini.
            </div>
            <div v-else class="overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 sticky top-0 z-10 dark:bg-slate-800">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Pasar</th>
                                <th class="px-3 py-2 text-right font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            <tr v-for="item in pasarChartData" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-3 py-3 text-slate-700 dark:text-slate-300 font-semibold text-xs">{{ item.name }}</td>
                                <td class="px-3 py-3 text-right font-bold text-slate-900 dark:text-white text-xs whitespace-nowrap">
                                    {{ item.price > 0 ? 'Rp ' + formatNumber(item.price) : 'data tidak tersedia' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </aside>

    </div>
</template>
