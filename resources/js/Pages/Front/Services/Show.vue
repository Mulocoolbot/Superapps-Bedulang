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

const hasChart = computed(() => (chartData.value.labels ?? []).length > 0)

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

const latestDateLimit = computed(() => props.dashboard?.metadata?.latest_date ?? '')

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
    if (selectedStartDate.value && selectedEndDate.value) {
        if (selectedStartDate.value > selectedEndDate.value) {
            alert('Tanggal Pembanding (Awal) tidak boleh lebih dari Tanggal Acuan (Akhir)!')
            return
        }
    }
    if (selectedEndDate.value && latestDateLimit.value) {
        if (selectedEndDate.value > latestDateLimit.value) {
            alert(`Tanggal Acuan (Akhir) tidak boleh melebihi Tanggal Terkini (${latestDateLimit.value})!`)
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
                class="group mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
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
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-50/60 blur-2xl" />
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">
                            <span>{{ serviceIcon }}</span>
                            <span>Data & Informasi</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            {{ title }}
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500">
                            {{ subtitle }}
                        </p>
                    </div>

                </div>
            </div>

            <!-- Main Grid -->
            <div class="mt-6 grid gap-6" :class="isPerindagkop ? 'lg:grid-cols-[2fr_1fr]' : 'grid-cols-1'">

                <!-- Chart Section -->
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <!-- Filter Controls -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Visualisasi</p>
                            <h2 class="mt-0.5 text-lg font-semibold text-slate-900">Tren Data</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Type Filter -->
                            <div class="relative" v-if="!isPerindagkop">
                                <select
                                    v-model="selectedType"
                                    class="h-9 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100"
                                >
                                    <option v-for="filter in serviceTypes" :key="filter.value" :value="filter.value">
                                        {{ filter.label }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>

                            <!-- Date Range -->
                            <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 p-1">
                                <input
                                    type="date"
                                    v-model="selectedStartDate"
                                    :max="selectedEndDate || latestDateLimit"
                                    @change="applyFilters"
                                    class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100"
                                    placeholder="Mulai"
                                />
                                <span class="text-xs text-slate-400">→</span>
                                <input
                                    type="date"
                                    v-model="selectedEndDate"
                                    :min="selectedStartDate"
                                    :max="latestDateLimit"
                                    @change="applyFilters"
                                    class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100"
                                    placeholder="Selesai"
                                />
                            </div>

                            <div v-if="isPerindagkop" class="relative">
                                <select
                                    v-model="selectedPasar"
                                    @change="applyFilters"
                                    class="h-9 appearance-none rounded-lg border border-slate-200 bg-slate-50 px-3 pr-8 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100"
                                >
                                    <option value="">Semua Pasar</option>
                                    <option v-for="(name, id) in pasarMap" :key="id" :value="id">{{ name }}</option>
                                </select>
                                <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="mt-5 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition">
                        <div v-if="!hasChart" class="flex min-h-[220px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400">
                            Belum ada data untuk ditampilkan
                        </div>
                        <template v-else>
                            <!-- Horizontal Dual Bar Chart for Perindagkop -->
                            <div v-if="isPerindagkop" class="space-y-6 max-h-[750px] overflow-y-auto pr-2 custom-scrollbar">
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-200/60 pb-2">
                                    <span>Komoditas</span>
                                    <div class="flex gap-4">
                                        <span class="flex items-center gap-1.5">
                                            <span class="h-2.5 w-2.5 rounded bg-slate-300 dark:bg-slate-600"></span>
                                            Awal (Price I)
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span class="h-2.5 w-2.5 rounded bg-blue-600 dark:bg-blue-500"></span>
                                            Akhir (Price II)
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="(label, idx) in chartData.labels" :key="idx" class="space-y-1.5">
                                        <div class="flex justify-between text-sm font-medium text-slate-700">
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ label }}</span>
                                            <div class="flex gap-3 text-xs text-slate-500">
                                                <span>Rp {{ formatNumber(chartData.series_i[idx]) }}</span>
                                                <span>→</span>
                                                <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ formatNumber(chartData.series_ii[idx]) }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <!-- Bar Price I -->
                                            <div class="w-full bg-slate-200/40 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                                <div 
                                                    class="bg-slate-300 dark:bg-slate-600 h-full rounded-full transition-all duration-700 ease-out" 
                                                    :style="{ width: `${getBarWidth(chartData.series_i[idx])}%` }"
                                                ></div>
                                            </div>
                                            <!-- Bar Price II -->
                                            <div class="w-full bg-slate-200/40 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                                <div 
                                                    class="bg-blue-600 dark:bg-blue-500 h-full rounded-full transition-all duration-700 ease-out" 
                                                    :style="{ width: `${getBarWidth(chartData.series_ii[idx])}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Standard Vertical Chart for non-Perindagkop -->
                            <div v-else class="overflow-x-auto pb-2">
                                <div class="flex items-end gap-3 min-h-[220px]">
                                    <template v-for="(value, index) in visibleChart.series" :key="index">
                                        <div class="flex-1 min-w-[32px]">
                                            <div class="relative h-48 overflow-hidden rounded-xl bg-cyan-100/60">
                                                <div
                                                    class="absolute bottom-0 left-0 right-0 rounded-t-xl bg-gradient-to-t from-cyan-600 to-cyan-500 transition-all duration-700 ease-out"
                                                    :style="{ height: `${Math.ceil((value / maxChartValue) * 100)}%` }"
                                                />
                                            </div>
                                            <p class="mt-2 text-center text-xs font-medium text-slate-400 truncate">
                                                {{ visibleChart.labels[index] ?? '—' }}
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                </section>

                <!-- Sidebar -->
                <aside v-if="isPerindagkop" class="space-y-5">

                    <!-- Perubahan Harga Card in Sidebar -->
                    <div v-if="isPerindagkop" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Perubahan Harga</p>
                            <h3 class="mt-0.5 text-base font-semibold text-slate-900">Data harga komoditas terbaru</h3>
                        </div>
                        
                        <div v-if="!hasPriceTrends" class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center text-sm text-slate-500">
                            Belum ada data harga yang tersedia untuk filter saat ini.
                        </div>
                        <div v-else class="overflow-hidden rounded-xl border border-slate-200/80 bg-white">
                            <div class="overflow-x-auto max-h-[500px] overflow-y-auto custom-scrollbar">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50">Komoditas</th>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50">Awal</th>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50">Akhir</th>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50">Selisih</th>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600 bg-slate-50">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        <tr v-for="item in priceTrends" :key="item.commodity" class="hover:bg-slate-50">
                                            <td class="px-3 py-2 font-medium text-slate-800">{{ item.commodity }}</td>
                                            <td class="px-3 py-2 text-slate-700">{{ item.price_i }}</td>
                                            <td class="px-3 py-2 text-slate-700">{{ item.price_ii }}</td>
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