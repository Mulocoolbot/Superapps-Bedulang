<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
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
const selectedStartDate = ref('')
const selectedEndDate = ref('')

const title = computed(() => `Dashboard ${props.service.name}`)
const subtitle = computed(() => props.service.description)
const serviceIcon = computed(() => props.service.icon ?? '📊')

const chartData = computed(() => {
    return dashboardData.value.chart ?? {
        labels: [],
        series: [],
    }
})

const hasChart = computed(() => (chartData.value.series ?? []).length > 0)

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

                    <!-- Total Data Card -->
                    <div class="flex min-w-[180px] items-center gap-4 rounded-xl border border-slate-200/80 bg-slate-50/80 px-5 py-4 backdrop-blur-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.12em] text-slate-400">Total Data</p>
                            <p class="text-2xl font-bold text-slate-900">{{ metadata.total ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="mt-6 grid gap-6 lg:grid-cols-[2fr_1fr]">

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
                            <div class="relative">
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
                                    class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100"
                                    placeholder="Mulai"
                                />
                                <span class="text-xs text-slate-400">→</span>
                                <input
                                    type="date"
                                    v-model="selectedEndDate"
                                    class="h-7 w-32 rounded-md border-0 bg-transparent px-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-cyan-100"
                                    placeholder="Selesai"
                                />
                            </div>

                            <!-- Reset Button -->
                            <button
                                v-if="selectedStartDate || selectedEndDate"
                                @click="selectedStartDate = ''; selectedEndDate = ''"
                                class="inline-flex h-9 items-center gap-1 rounded-lg px-3 text-sm text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="mt-5 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition">
                        <div v-if="!hasChart" class="flex min-h-[220px] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-white text-center text-sm text-slate-400">
                            Belum ada data untuk ditampilkan
                        </div>
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
                    </div>

                    <!-- Chart Info Footer -->
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-[0.12em] text-slate-400">Jenis Terpilih</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ serviceTypes.find((f) => f.value === selectedType)?.label ?? 'Semua Jenis' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-[0.12em] text-slate-400">Periode</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                {{ selectedStartDate && selectedEndDate ? `${selectedStartDate} – ${selectedEndDate}` : 'Semua waktu' }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Sidebar -->
                <aside class="space-y-5">
                    <!-- Highlight Card -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Sorotan</p>
                        <div class="mt-4 space-y-4">
                            <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4">
                                <p class="text-xs font-medium text-slate-400">Info Ringkas</p>
                                <p class="mt-1.5 text-base font-semibold text-slate-900">
                                    {{ dashboardInfo.title ?? 'Belum ada informasi' }}
                                </p>
                                <p class="mt-1.5 text-sm leading-6 text-slate-500">
                                    {{ dashboardInfo.message ?? 'Informasi akan muncul ketika data tersedia.' }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4">
                                <p class="text-xs font-medium text-slate-400">Sumber & Pembaruan</p>
                                <div class="mt-2 space-y-0.5 text-sm">
                                    <p class="text-slate-700">
                                        <span class="font-medium text-slate-500">Sumber</span>
                                        <span class="ml-2 text-slate-800">{{ metadata.source ?? 'Belum tersedia' }}</span>
                                    </p>
                                    <p class="text-slate-700">
                                        <span class="font-medium text-slate-500">Terakhir</span>
                                        <span class="ml-2 text-slate-800">{{ metadata.updated_at ?? 'Belum tersedia' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Aksi Cepat</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Ekspor
                            </button>
                            <button class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:ring-2 focus:ring-cyan-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Segarkan
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </FrontLayout>
</template>