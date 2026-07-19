<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'

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
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-50/60 blur-2xl dark:bg-cyan-950/20" />
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400">
                            <span>{{ serviceIcon }}</span>
                            <span>Data & Informasi</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                            {{ title }}
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>

                </div>
            </div>

            <!-- Main Grid -->
            <div class="mt-6 grid gap-6" :class="isPerindagkop ? 'lg:grid-cols-[2fr_1fr]' : 'grid-cols-1'">

                <!-- Chart Section -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                    <!-- Filter Controls -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Visualisasi</p>
                            <h2 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">
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
                                        <span>Komoditas Pokok</span>
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
                                                                class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out" 
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
                                                </div>
                                                <!-- Bar Price II -->
                                                <div class="flex items-center gap-3">
                                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedEndDate }}</span>
                                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                                        <!-- Track -->
                                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                            <div 
                                                                class="bg-blue-600 dark:bg-blue-500 h-full rounded-r transition-all duration-700 ease-out" 
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
                                        <span>Barang Penting</span>
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
                                                                class="bg-slate-400 h-full rounded-r transition-all duration-700 ease-out" 
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
                                                </div>
                                                <!-- Bar Price II -->
                                                <div class="flex items-center gap-3">
                                                    <span class="w-20 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-left">{{ formattedEndDate }}</span>
                                                    <div class="flex-1 relative group py-1 focus:outline-none" tabindex="0">
                                                        <!-- Track -->
                                                        <div class="bg-slate-200/50 dark:bg-slate-800 h-5 rounded-md overflow-hidden cursor-help">
                                                            <div 
                                                                class="bg-blue-600 dark:bg-blue-500 h-full rounded-r transition-all duration-700 ease-out" 
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
                                                    class="bg-cyan-600 dark:bg-cyan-500 h-full rounded-r transition-all duration-700 ease-out" 
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
                <aside v-if="isPerindagkop" class="space-y-6">

                    <!-- 1. Perbandingan Harga Barang Kebutuhan Pokok -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tabel Data</p>
                            <h3 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">Perbandingan Harga Barang Kebutuhan Pokok</h3>
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
                            <h3 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">Perbandingan Harga Barang Penting</h3>
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
            </div>
        </div>
    </FrontLayout>
</template>