<script setup>
import { computed, ref } from 'vue'
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

const selectedType = ref(props.dashboard?.metadata?.type ?? serviceTypes.value[0]?.value ?? 'all')
const selectedStartDate = ref(props.dashboard?.metadata?.start_date ?? '')
const selectedEndDate = ref(props.dashboard?.metadata?.end_date ?? '')

const chartData = computed(() => {
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
        type: selectedType.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="mt-6 grid gap-6 grid-cols-1">
        <!-- Chart Section -->
        <section :key="selectedStartDate + '-' + selectedEndDate + '-' + selectedType" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
            <!-- Filter Controls -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Visualisasi</p>
                    <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white font-sans">
                        Tren Data
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Type Filter -->
                    <div class="relative" v-if="serviceTypes.length > 1">
                        <select
                            v-model="selectedType"
                            @change="applyFilters"
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
                </div>
            </div>

            <!-- Charts Wrapper -->
            <div class="mt-5 space-y-6">
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
            </div>
        </section>
    </div>
</template>


