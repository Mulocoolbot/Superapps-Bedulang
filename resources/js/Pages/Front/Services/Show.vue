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
        required: false,
        default: () => ({}),
    },
})

const dashboardData = computed(() => ({
    summary: props.dashboard?.summary ?? [],
    chart: props.dashboard?.chart ?? { labels: [], series: [] },
    topData: props.dashboard?.topData ?? [],
    info: props.dashboard?.info ?? { title: '', message: '' },
    rows: props.dashboard?.rows ?? [],
}))

const serviceFilters = computed(() => props.service?.filters ?? [])
const selectedFilter = ref(serviceFilters.value[0]?.value ?? '')
const selectedFilterLabel = computed(() => serviceFilters.value.find((filter) => filter.value === selectedFilter.value)?.label ?? 'Pilih filter')

// TODO:
// Fetch dashboard data dari API OPD berdasarkan slug ketika API sudah tersedia.

const title = computed(() => `Dashboard ${props.service?.name ?? ''}`)
const subtitle = computed(() => props.service?.description ?? '')
const serviceIcon = computed(() => props.service?.icon ?? '📊')
const maxChartValue = computed(() => Math.max(...(dashboardData.value.chart?.series ?? [0]), 1))
</script>

<template>
    <FrontLayout>
        <Head :title="title" />

        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
                <div class="mb-5 flex items-center gap-3 text-sm text-slate-500">
                    <Link href="/" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-slate-700 transition hover:border-cyan-400 hover:text-cyan-600">
                        <span>←</span>
                        <span>Kembali</span>
                    </Link>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-cyan-50 p-6 shadow-sm lg:p-8">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-2xl">
                            <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-sm font-medium text-cyan-700">
                                <span>{{ serviceIcon }}</span>
                                <span>Layanan Informasi & Data</span>
                            </div>
                            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">{{ title }}</h1>
                            <p class="mt-3 text-base leading-7 text-slate-600">{{ subtitle }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 text-sm text-slate-600 shadow-sm">
                            <p class="font-medium text-slate-900">Status</p>
                            <p class="mt-1">Data dummy • siap terhubung</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
            <section class="mb-6 rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Filter Dummy</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900">Jelajahi data contoh untuk layanan ini</h2>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <span class="text-sm font-medium text-slate-500">Filter</span>
                        <select
                            id="service-filter"
                            v-model="selectedFilter"
                            class="bg-transparent text-sm font-medium text-slate-800 outline-none"
                        >
                            <option v-for="filter in serviceFilters" :key="filter.value" :value="filter.value">
                                {{ filter.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-4">
                <template v-for="card in dashboardData.summary" :key="card.title">
                    <div class="rounded-[1.25rem] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-slate-500">{{ card.title }}</p>
                        <p class="mt-4 text-2xl font-semibold text-slate-900">{{ card.value }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ card.meta }}</p>
                    </div>
                </template>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
                <section class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Grafik Simulasi</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-900">Kinerja Data Minggu Ini</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">Dummy Chart</span>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-end gap-3">
                                <template v-for="(value, index) in dashboardData.chart.series" :key="index">
                                    <div class="flex-1">
                                        <div class="mb-3 h-40 rounded-2xl bg-cyan-100/80">
                                            <div
                                                class="h-full rounded-2xl bg-cyan-600 transition-all duration-500"
                                                :style="{ height: `${Math.ceil((value / maxChartValue) * 100)}%` }"
                                            />
                                        </div>
                                        <p class="text-center text-sm text-slate-500">{{ dashboardData.chart.labels[index] }}</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm font-medium text-slate-500">Filter aktif</p>
                                <p class="mt-3 text-xl font-semibold text-slate-900">{{ selectedFilterLabel }}</p>
                            </div>
                            <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm font-medium text-slate-500">Volume data</p>
                                <p class="mt-3 text-xl font-semibold text-slate-900">6.359</p>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Top Data</p>
                        <div class="mt-5 space-y-3">
                            <div v-for="item in dashboardData.topData" :key="item.title" class="rounded-[1.1rem] border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-500">{{ item.title }}</p>
                                <p class="mt-2 text-xl font-semibold text-slate-900">{{ item.value }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ item.note }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Informasi Singkat</p>
                        <div class="mt-5 rounded-[1.1rem] border border-dashed border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-900">{{ dashboardData.info.title }}</p>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ dashboardData.info.message }}</p>
                        </div>
                    </div>
                </aside>
            </div>

        </main>
    </FrontLayout>
</template>
