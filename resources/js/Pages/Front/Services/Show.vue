<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ComplaintModal from '@/Components/ComplaintModal.vue'

const isComplaintModalOpen = ref(false)

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

const metadata = computed(() => {
    const dashboard = dashboardData.value ?? {}
    return {
        total: dashboard.metadata?.total ?? null,
        updated_at: dashboard.metadata?.updated_at ?? null,
        source: dashboard.metadata?.source ?? null,
    }
})

const dashboardInfo = computed(() => dashboardData.value?.info ?? {})

const serviceTypes = computed(() =>
    props.service?.filters?.length > 0
        ? props.service.filters
        : [{ label: 'Semua Jenis', value: 'all' }]
)

const selectedType = ref(serviceTypes.value[0]?.value ?? 'all')
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

const title = computed(() => `Dashboard ${props.service.name}`)
const subtitle = computed(() => props.service.description)
const serviceIcon = computed(() => props.service.icon ?? '📊')

const chartData = computed(() => {
    if (isPerindagkop.value && (props.dashboard?.commodity_chart?.labels?.length || 0)) {
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

const hasChart = computed(() => (chartData.value.labels ?? []).length > 0)
const hasBasicChart = computed(() => basicChartData.value.labels.length > 0)
const hasImportantChart = computed(() => importantChartData.value.labels.length > 0)

const visibleChart = computed(() => {
    const labels = chartData.value.labels ?? []
    const series = chartData.value.series ?? []

    if (!labels.length || !series.length) {
        return { labels: [], series: [] }
    }

    if (selectedStartDate.value && selectedEndDate.value) {
        const start = new Date(selectedStartDate.value)
        const end = new Date(selectedEndDate.value)

        if (start <= end) {
            const range = Math.max(1, Math.round((end - start) / 86400000) + 1)
            return {
                labels: labels.slice(-range),
                series: series.slice(-range),
            }
        }
    }

    return { labels, series }
})

const maxChartValue = computed(() => Math.max(...(visibleChart.value.series ?? [0]), 1))
const isPerindagkop = computed(() => props.service?.slug === 'perindagkop-ukm')
const priceTrends = computed(() => props.dashboard?.price_trends ?? [])
const hasPriceTrends = computed(() => priceTrends.value.length > 0)

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

const todayStr = '2026-07-20' // Set based on the user's current date metadata to ensure consistency

// Helper to check if a date is Monday
const isMonday = (dateStr) => {
    if (!dateStr) return false
    const date = new Date(dateStr)
    return date.getDay() === 1
}

// Helper to get nearest Monday (backward or forward)
const getNearestMonday = (dateStr) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    const day = date.getDay()
    if (day === 1) return dateStr
    
    const diff = 1 - day
    // Adjust to nearest Monday
    let adjustDays = diff
    if (day === 0) adjustDays = -6 // Sunday to previous Monday
    else if (day > 1) adjustDays = 1 - day // Tue-Sat to previous Monday
    
    date.setDate(date.getDate() + adjustDays)
    
    // Check if the result is in the future
    if (date > new Date(todayStr)) {
        date.setDate(date.getDate() - 7)
    }
    
    return date.toISOString().split('T')[0]
}

// Tab 2 (Stok Bapok) Refs & Logic
const stokDate = ref(getNearestMonday(todayStr))
const stokCompDate = ref('')

// Set default comparison date to the Monday before stokDate
const initStokCompDate = () => {
    if (stokDate.value) {
        const date = new Date(stokDate.value)
        date.setDate(date.getDate() - 7)
        stokCompDate.value = date.toISOString().split('T')[0]
    }
}
initStokCompDate()

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
}

// Tab 3 (Harga Per Pasar) Refs & Logic
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

// Deterministic seed helper
const seedRandom = (str) => {
    let hash = 0
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash)
    }
    return Math.abs(hash)
}

// Mock Bapok Stock Generator
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

// Mock Harga Per Pasar Generator
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
    const mVar = ((marketSeed % 20) - 10) / 100 // market difference
    const dVar = ((dateSeed % 10) - 5) / 100  // daily price fluctuation
    
    // Simulate ~15% chance of missing data for realistic look
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

const stokChartData = computed(() => {
    if (!stokDate.value || !stokCompDate.value) return []
    return stokCommodities.map(name => {
        const qtyStok = getMockStock(name, stokDate.value)
        const qtyComp = getMockStock(name, stokCompDate.value)
        return {
            name,
            qtyStok,
            qtyComp,
            diff: qtyStok - qtyComp
        }
    })
})

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
    <FrontLayout>
        <Head :title="title" />

        <div class="mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 lg:px-8 lg:pt-28 lg:pb-10">

            <!-- Back Button - subtle ghost style -->
            <Link
                href="/"
                class="group mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali
            </Link>

            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-50/60 blur-2xl dark:bg-cyan-950/20" />
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400">
                            <span>{{ serviceIcon }}</span>
                            <span>Data & Informasi</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            {{ title }}
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>

                    <!-- Complaint Action Button -->
                    <div class="flex shrink-0 items-center">
                        <button
                            @click="isComplaintModalOpen = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            Ajukan Pengaduan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menu Bar Section (only for Perindagkop) -->
            <div v-if="isPerindagkop" class="mt-6 flex flex-wrap gap-2 rounded-xl bg-slate-100 p-1.5 dark:bg-slate-800/60 w-full animate-fade-in-up [animation-delay:100ms]">
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
            <div class="mt-6 grid gap-6" :class="isPerindagkop ? 'lg:grid-cols-[2fr_1fr]' : 'grid-cols-1'">

                <!-- Chart Section (Tab 1 / Non-Perindagkop) -->
                <section v-if="!isPerindagkop || activeTab === 'harga'" :key="activeTab + '-' + selectedStartDate + '-' + selectedEndDate + '-' + selectedType" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
                    <!-- Filter Controls -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Visualisasi</p>
                            <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ isPerindagkop ? 'Grafik Perbandingan Harga' : 'Tren Data' }}
                            </h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Type Filter -->
                            <div class="relative" v-if="!isPerindagkop">
                                <select
                                    v-model="selectedType"
                                    class="h-9 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:focus:border-cyan-500 dark:focus:ring-cyan-950/50"
                                >
                                    <option v-for="filter in serviceTypes" :key="filter.value" :value="filter.value">
                                        {{ filter.label }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>

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

                            <div v-if="isPerindagkop" class="relative">
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
                        <!-- Perindagkop Split Charts -->
                        <template v-if="isPerindagkop">
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
                                            <!-- Box Bars Container -->
                                            <div class="grid grid-cols-1 gap-2 bg-slate-100/50 dark:bg-slate-900/40 p-2.5 rounded-lg border border-slate-200/40 dark:border-slate-800">
                                                <!-- Bar Price I -->
                                                <div class="flex items-center gap-3">
                                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedStartDate }}</span>
                                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                                        <!-- Track -->
                                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                            <div 
                                                                :key="'basic-i-' + idx + '-' + basicChartData.series_i[idx]"
                                                                class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                                :style="{ width: `${getBarWidth(basicChartData.series_i[idx])}%` }"
                                                            ></div>
                                                        </div>
                                                        <!-- Tooltip Information Box -->
                                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                            <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                                Nominal: Rp {{ formatNumber(basicChartData.series_i[idx]) }}
                                                            </div>
                                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                                        </div>
                                                    </div>
                                                    <!-- Price Label on the right -->
                                                    <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                        {{ basicChartData.series_i[idx] > 0 ? 'Rp ' + formatNumber(basicChartData.series_i[idx]) : '-' }}
                                                    </span>
                                                </div>
                                                <!-- Bar Price II -->
                                                <div class="flex items-center gap-3">
                                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedEndDate }}</span>
                                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                                        <!-- Track -->
                                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                            <div 
                                                                :key="'basic-ii-' + idx + '-' + basicChartData.series_ii[idx]"
                                                                class="bg-blue-600 dark:bg-blue-500 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                                :style="{ width: `${getBarWidth(basicChartData.series_ii[idx])}%` }"
                                                            ></div>
                                                        </div>
                                                        <!-- Tooltip Information Box -->
                                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                            <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                                Nominal: Rp {{ formatNumber(basicChartData.series_ii[idx]) }}
                                                            </div>
                                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                                        </div>
                                                    </div>
                                                    <!-- Price Label on the right -->
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
                                            <!-- Box Bars Container -->
                                            <div class="grid grid-cols-1 gap-2 bg-slate-100/50 dark:bg-slate-900/40 p-2.5 rounded-lg border border-slate-200/40 dark:border-slate-800">
                                                <!-- Bar Price I -->
                                                <div class="flex items-center gap-3">
                                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedStartDate }}</span>
                                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                                        <!-- Track -->
                                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                            <div 
                                                                :key="'important-i-' + idx + '-' + importantChartData.series_i[idx]"
                                                                class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                                :style="{ width: `${getBarWidth(importantChartData.series_i[idx])}%` }"
                                                            ></div>
                                                        </div>
                                                        <!-- Tooltip Information Box -->
                                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                            <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                                Nominal: Rp {{ formatNumber(importantChartData.series_i[idx]) }}
                                                            </div>
                                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                                        </div>
                                                    </div>
                                                    <!-- Price Label on the right -->
                                                    <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                        {{ importantChartData.series_i[idx] > 0 ? 'Rp ' + formatNumber(importantChartData.series_i[idx]) : '-' }}
                                                    </span>
                                                </div>
                                                <!-- Bar Price II -->
                                                <div class="flex items-center gap-3">
                                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedEndDate }}</span>
                                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                                        <!-- Track -->
                                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                            <div 
                                                                :key="'important-ii-' + idx + '-' + importantChartData.series_ii[idx]"
                                                                class="bg-blue-600 dark:bg-blue-500 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                                :style="{ width: `${getBarWidth(importantChartData.series_ii[idx])}%` }"
                                                            ></div>
                                                        </div>
                                                        <!-- Tooltip Information Box -->
                                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                            <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                                Nominal: Rp {{ formatNumber(importantChartData.series_ii[idx]) }}
                                                            </div>
                                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                                        </div>
                                                    </div>
                                                    <!-- Price Label on the right -->
                                                    <span class="w-24 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                        {{ importantChartData.series_ii[idx] > 0 ? 'Rp ' + formatNumber(importantChartData.series_ii[idx]) : '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Standard Vertical Chart for non-Perindagkop -->
                        <template v-else>
                            <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                                <div v-if="!hasChart" class="flex min-h-[220px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-500">
                                    Belum ada data untuk ditampilkan
                                </div>
                                <div v-else class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                    <div v-for="(value, index) in visibleChart.series" :key="index" class="space-y-1.5">
                                        <div class="flex justify-between text-sm font-semibold text-slate-700 dark:text-slate-200">
                                            <span>{{ visibleChart.labels[index] ?? '—' }}</span>
                                        </div>
                                        <div class="relative group py-1 focus:outline-none" tabindex="0">
                                            <!-- Track -->
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-6 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'generic-' + index + '-' + value"
                                                    class="bg-cyan-600 dark:bg-cyan-500 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${Math.max(0, Math.min(100, Math.ceil((value / maxChartValue) * 100)))}%` }"
                                                ></div>
                                            </div>
                                            <!-- Tooltip Information Box -->
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-lg shadow-xl border border-slate-700/30 whitespace-nowrap">
                                                    Jumlah: {{ value }}
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                </section>

                <!-- Sidebar -->
                <aside v-if="isPerindagkop && activeTab === 'harga'" :key="'sidebar-harga-' + activeTab + '-' + selectedStartDate + '-' + selectedEndDate" class="space-y-6 animate-fade-in-up [animation-delay:300ms]">

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

                <!-- Stok Bapok Chart Section (Left Column) -->
                <section v-if="isPerindagkop && activeTab === 'stok'" :key="'chart-stok-' + activeTab + '-' + stokDate + '-' + stokCompDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 space-y-6 animate-fade-in-up [animation-delay:200ms]">
                    <!-- Filters -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b border-slate-200/60 pb-5 dark:border-slate-800/80">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Data & Informasi</p>
                            <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">Stok Barang Kebutuhan Pokok</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tanggal Stok (Senin)</span>
                                <input
                                    type="date"
                                    v-model="stokDate"
                                    min="2020-01-06"
                                    step="7"
                                    :max="todayStr"
                                    @change="handleStokDateChange"
                                    class="h-9 rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tanggal Pembanding</span>
                                <input
                                    type="date"
                                    v-model="stokCompDate"
                                    min="2020-01-06"
                                    step="7"
                                    :max="todayStr"
                                    @change="handleStokCompDateChange"
                                    class="h-9 rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Double Horizontal Bar Chart -->
                    <div class="animate-fade-in space-y-4">
                        <!-- Legend -->
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-200/40 pb-3 dark:text-slate-400 dark:border-slate-800/60 mb-6">
                            <span>Komoditas Pokok</span>
                            <div class="flex gap-4">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded bg-cyan-600"></span>
                                    Stok ({{ formatDateDMY(stokDate) }})
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded bg-slate-400"></span>
                                    Pembanding ({{ formatDateDMY(stokCompDate) }})
                                </span>
                            </div>
                        </div>
                        
                        <!-- List of Commodities (Stretches Horizontal) -->
                        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="item in stokChartData" :key="item.name" class="space-y-1.5">
                                <div class="flex justify-between text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    <span>{{ item.name }}</span>
                                </div>
                                <div class="grid grid-cols-1 gap-2 bg-slate-50/50 dark:bg-slate-900/40 p-2.5 rounded-lg border border-slate-200/40 dark:border-slate-800">
                                    <!-- Stok Bar -->
                                    <div class="flex items-center gap-3">
                                        <span class="w-24 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formatDateDMY(stokDate) }}</span>
                                        <div class="flex-1 relative group py-0.5 focus:outline-none" tabindex="0">
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-4 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'stok-qty-' + item.name + '-' + item.qtyStok"
                                                    class="bg-cyan-600 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${getStokBarWidth(item.qtyStok)}%` }"
                                                ></div>
                                            </div>
                                            <!-- Tooltip Centered on horizontal bar -->
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[10px] font-semibold px-2 py-1 rounded shadow-md whitespace-nowrap">
                                                    Jumlah: {{ item.qtyStok }} Ton
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                        <span class="w-16 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">{{ item.qtyStok }} Ton</span>
                                    </div>
                                    <!-- Comp Bar -->
                                    <div class="flex items-center gap-3">
                                        <span class="w-24 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formatDateDMY(stokCompDate) }}</span>
                                        <div class="flex-1 relative group py-0.5 focus:outline-none" tabindex="0">
                                            <div class="bg-slate-200/50 dark:bg-slate-800 h-4 rounded-md overflow-hidden cursor-help">
                                                <div 
                                                    :key="'stok-comp-' + item.name + '-' + item.qtyComp"
                                                    class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out animate-grow-width" 
                                                    :style="{ width: `${getStokBarWidth(item.qtyComp)}%` }"
                                                ></div>
                                            </div>
                                            <!-- Tooltip Centered on horizontal bar -->
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[10px] font-semibold px-2 py-1 rounded shadow-md whitespace-nowrap">
                                                    Jumlah: {{ item.qtyComp }} Ton
                                                </div>
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900/95 dark:border-t-slate-800/95"></div>
                                            </div>
                                        </div>
                                        <span class="w-16 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">{{ item.qtyComp }} Ton</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stok Bapok Table Section (Right Column) -->
                <aside v-if="isPerindagkop && activeTab === 'stok'" :key="'table-stok-' + activeTab + '-' + stokDate + '-' + stokCompDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 space-y-4 animate-fade-in-up [animation-delay:300ms]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tabel Data</p>
                        <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Daftar Stok & Selisih</h3>
                    </div>
                    
                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <div class="overflow-x-auto max-h-[500px] overflow-y-auto custom-scrollbar">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 sticky top-0 z-10 dark:bg-slate-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Komoditas</th>
                                        <th class="px-3 py-2 text-right font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Selisih</th>
                                        <th class="px-3 py-2 text-center font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                    <tr v-for="item in stokChartData" :key="item.name" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-3 py-2.5 font-semibold text-slate-800 dark:text-slate-200">
                                            <div>{{ item.name }}</div>
                                            <div class="text-[10px] font-normal text-slate-400">
                                                Stok: {{ item.qtyStok }}T | Pmb: {{ item.qtyComp }}T
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-semibold whitespace-nowrap text-xs" :class="item.diff > 0 ? 'text-emerald-600' : (item.diff < 0 ? 'text-rose-600' : 'text-slate-500')">
                                            {{ item.diff > 0 ? '+' : '' }}{{ item.diff }} T
                                        </td>
                                        <td class="px-3 py-2.5 text-center">
                                            <span class="font-bold text-lg">
                                                <span v-if="item.diff > 0" class="text-emerald-600" title="Naik">▲</span>
                                                <span v-else-if="item.diff < 0" class="text-rose-600" title="Turun">▼</span>
                                                <span v-else class="text-slate-400" title="Tetap">=</span>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </aside>

                <!-- Harga Per Pasar Chart Section (Left Column) -->
                <section v-if="isPerindagkop && activeTab === 'pasar'" :key="'chart-pasar-' + activeTab + '-' + pasarSelectedCommodity + '-' + pasarSelectedDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 space-y-6 animate-fade-in-up [animation-delay:200ms]">
                    <!-- Filters -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b border-slate-200/60 pb-5 dark:border-slate-800/80">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Data & Informasi</p>
                            <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">Harga Komoditi per Pasar</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Komoditas</span>
                                <div class="relative">
                                    <select
                                        v-model="pasarSelectedCommodity"
                                        @change="handlePasarCommodityChange"
                                        class="h-9 w-48 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        <option v-for="c in pasarCommodities" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tanggal</span>
                                <input
                                    type="date"
                                    v-model="pasarSelectedDate"
                                    :max="todayStr"
                                    @change="handlePasarDateChange"
                                    class="h-9 rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Vertical Single Bar Chart (Harga Per Pasar) -->
                    <div class="relative bg-slate-50/50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/80 animate-fade-in">
                        <div class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 border-b border-slate-200/40 pb-3 dark:text-slate-400 dark:border-slate-800/60 mb-6">
                            Grafik Harga {{ pasarSelectedCommodity }} Per Pasar ({{ formatDateDMY(pasarSelectedDate) }})
                        </div>
                        
                        <!-- Chart container with Y-axis on the left -->
                        <div class="flex gap-4">
                            <!-- Y-axis scale -->
                            <div class="h-96 flex flex-col justify-between text-right text-[10px] font-semibold text-slate-400 dark:text-slate-500 pb-36 pt-4 pr-1 w-16 select-none relative">
                                <div v-for="(tick, idx) in yAxisTicks" :key="idx" class="h-0 flex items-center justify-end relative">
                                    <span class="absolute right-0">Rp {{ formatNumber(tick) }}</span>
                                </div>
                            </div>

                            <!-- Chart Box - full width, high bottom padding for vertical labels, no horizontal scroll -->
                            <div class="flex-1 h-96 flex items-end justify-between border-b border-slate-200 dark:border-slate-800 pb-36 pt-4 gap-2 relative">
                                <!-- Horizontal Gridlines -->
                                <div class="absolute inset-0 pb-36 pt-4 flex flex-col justify-between pointer-events-none">
                                    <div v-for="n in 5" :key="n" class="w-full border-t border-slate-100 dark:border-slate-800/40 h-0"></div>
                                </div>

                                <div v-for="item in pasarChartData" :key="item.id" class="flex-1 flex flex-col items-center h-full justify-end relative group z-10">
                                    <!-- Bar -->
                                    <div class="flex items-end justify-center w-full h-full relative">
                                        <div 
                                            v-if="item.price > 0"
                                            :key="'pasar-' + item.id + '-' + item.price"
                                            class="w-full max-w-[28px] bg-gradient-to-t from-cyan-600 to-cyan-500 rounded-t-sm hover:from-cyan-500 hover:to-cyan-400 transition-all duration-300 relative group cursor-help animate-grow-height"
                                            :style="{ height: `${getPasarBarPercent(item.price)}%` }"
                                        >
                                            <!-- Centered Tooltip inside the bar, preventing top boundary clip -->
                                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 ease-out z-30 scale-95 group-hover:scale-100">
                                                <div class="bg-slate-900/95 dark:bg-slate-800/95 text-white text-[10px] font-semibold px-2.5 py-1 rounded shadow-md border border-slate-700/30 whitespace-nowrap">
                                                    Rp {{ formatNumber(item.price) }}
                                                </div>
                                            </div>
                                        </div>
                                        <span v-else class="text-[10px] text-slate-400 font-semibold mb-2">T/D</span>
                                    </div>
                                    <!-- Vertical Label (no transform rotation, uses vertical-rl mode for perfect fit without scroll) -->
                                    <div 
                                        class="absolute top-[102%] left-1/2 transform -translate-x-1/2 text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap text-left pt-2"
                                        style="writing-mode: vertical-rl; text-orientation: mixed;"
                                    >
                                        {{ item.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Harga Per Pasar Table Section (Right Column) -->
                <aside v-if="isPerindagkop && activeTab === 'pasar'" :key="'table-pasar-' + activeTab + '-' + pasarSelectedCommodity + '-' + pasarSelectedDate" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 space-y-4 animate-fade-in-up [animation-delay:300ms]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tabel Data</p>
                        <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Rincian Harga</h3>
                    </div>
                    
                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <div class="overflow-x-auto max-h-[500px] overflow-y-auto custom-scrollbar">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 sticky top-0 z-10 dark:bg-slate-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300">Nama Pasar</th>
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
        </div>

        <!-- Complaint Modal -->
        <ComplaintModal
            :is-open="isComplaintModalOpen"
            :service-name="service.name"
            :service-slug="service.slug"
            @close="isComplaintModalOpen = false"
        />
    </FrontLayout>
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

@keyframes growWidth {
    from {
        width: 0%;
    }
}

@keyframes growHeight {
    from {
        height: 0%;
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.animate-grow-width {
    animation: growWidth 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.animate-grow-height {
    animation: growHeight 1.2s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
