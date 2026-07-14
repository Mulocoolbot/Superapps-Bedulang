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
        required: true,
    },
})

const data = ref(props.dashboard)
// TODO:
// Fetch dashboard data dari API OPD berdasarkan slug ketika API sudah tersedia.

const title = computed(() => `Dashboard ${service.name}`)
const subtitle = computed(() => service.description)
const maxChartValue = computed(() => Math.max(...data.value.chart.series))
</script>

<template>
    <FrontLayout>
        <Head :title="title" />

        <section class="relative overflow-hidden bg-slate-950 text-white">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.18),_transparent_35%),radial-gradient(circle_at_top_right,_rgba(59,130,246,0.14),_transparent_30%),linear-gradient(180deg,_rgba(15,23,42,1),_rgba(15,23,42,0.88))]" />
            <div class="absolute left-1/2 top-0 h-72 w-72 -translate-x-1/2 rounded-full bg-cyan-500/20 blur-3xl" />
            <div class="relative mx-auto max-w-7xl px-6 py-16 lg:px-8">
                <div class="mb-10 flex items-center gap-4 text-sm text-slate-200">
                    <Link href="/" class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900/80 px-4 py-2 transition hover:border-slate-500 hover:bg-slate-800">
                        <span class="text-xl">←</span>
                        <span>Kembali</span>
                    </Link>
                </div>

                <div class="grid gap-6 rounded-[2rem] border border-white/10 bg-slate-900/90 p-8 shadow-2xl backdrop-blur-xl lg:grid-cols-[1.2fr_0.8fr] lg:p-12">
                    <div class="space-y-6">
                        <p class="text-sm uppercase tracking-[0.4em] text-cyan-300/80">Layanan Informasi & Data</p>
                        <h1 class="text-4xl font-semibold tracking-tight text-white sm:text-5xl">{{ title }}</h1>
                        <p class="max-w-3xl text-base leading-8 text-slate-300">{{ subtitle }}</p>
                    </div>

                    <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/90 p-6 sm:p-8">
                        <div class="space-y-4">
                            <div class="rounded-3xl bg-slate-900/80 p-5 shadow-lg shadow-cyan-500/10">
                                <p class="text-sm uppercase tracking-[0.35em] text-cyan-300/80">Status</p>
                                <p class="mt-4 text-2xl font-semibold text-white">Belum Terhubung</p>
                                <p class="mt-2 text-sm text-slate-400">API masih menggunakan data dummy sementara backend OPD belum tersedia.</p>
                            </div>

                            <div class="rounded-3xl bg-slate-900/80 p-5 shadow-lg shadow-slate-900/20">
                                <p class="text-sm uppercase tracking-[0.35em] text-slate-400">Info Penting</p>
                                <p class="mt-4 text-sm leading-7 text-slate-300">Ketika API terhubung, halaman ini akan menampilkan informasi realtime langsung dari OPD terkait tanpa mengubah tampilan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-4">
                <template v-for="card in data.value.summary" :key="card.title">
                    <div class="rounded-3xl border border-slate-200/10 bg-white/90 p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-1 hover:border-slate-300">
                        <p class="text-sm font-medium uppercase tracking-[0.22em] text-slate-500">{{ card.title }}</p>
                        <p class="mt-4 text-3xl font-semibold text-slate-900">{{ card.value }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ card.meta }}</p>
                    </div>
                </template>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
                <section class="rounded-[2rem] border border-slate-200/10 bg-white p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-1">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.26em] text-slate-500">Grafik Simulasi</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Kinerja Data Minggu Ini</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">Dummy Chart</span>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50 p-5">
                            <div class="flex items-end gap-3"> 
                                <template v-for="(value, index) in data.value.chart.series" :key="index">
                                    <div class="flex-1">
                                        <div class="mb-3 h-48 rounded-3xl bg-cyan-500/10">
                                            <div
                                                class="h-full rounded-3xl bg-cyan-500 transition-all duration-500"
                                                :style="{ height: `${Math.ceil((value / maxChartValue) * 100)}%` }"
                                            />
                                        </div>
                                        <p class="text-center text-sm text-slate-500">{{ data.value.chart.labels[index] }}</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-5">
                                <p class="text-sm font-medium text-slate-500">Performa rata-rata</p>
                                <p class="mt-4 text-3xl font-semibold text-slate-900">86%</p>
                            </div>
                            <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-5">
                                <p class="text-sm font-medium text-slate-500">Volume data</p>
                                <p class="mt-4 text-3xl font-semibold text-slate-900">6.359</p>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200/10 bg-white p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-1">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Top Data</p>
                        <div class="mt-6 space-y-4">
                            <div v-for="item in data.value.topData" :key="item.title" class="rounded-3xl border border-slate-200/80 bg-slate-50 p-5">
                                <p class="text-sm text-slate-500">{{ item.title }}</p>
                                <p class="mt-3 text-2xl font-semibold text-slate-900">{{ item.value }}</p>
                                <p class="mt-2 text-sm text-slate-500">{{ item.note }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200/10 bg-white p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-1">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Informasi Singkat</p>
                        <div class="mt-6 space-y-3 rounded-3xl border border-dashed border-slate-200/80 bg-slate-50 p-6">
                            <p class="text-sm font-semibold text-slate-900">{{ data.value.info.title }}</p>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ data.value.info.message }}</p>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200/10 bg-white shadow-lg shadow-slate-900/5">
                <div class="border-b border-slate-200/80 bg-slate-50 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-900">Riwayat Data</h2>
                    <p class="mt-1 text-sm text-slate-500">Lima entri terbaru dengan status simulasi dummy.</p>
                </div>
                <div class="overflow-x-auto px-6 py-5">
                    <table class="min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th class="pb-3 font-semibold text-slate-500">Nama</th>
                                <th class="pb-3 font-semibold text-slate-500">Nilai</th>
                                <th class="pb-3 font-semibold text-slate-500">Tanggal</th>
                                <th class="pb-3 font-semibold text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80">
                            <tr v-for="row in data.value.rows" :key="row.name" class="hover:bg-slate-50 transition-colors">
                                <td class="py-4 pr-6 font-medium text-slate-900">{{ row.name }}</td>
                                <td class="py-4 pr-6">{{ row.value }}</td>
                                <td class="py-4 pr-6">{{ row.date }}</td>
                                <td class="py-4 pr-6">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">{{ row.status }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </FrontLayout>
</template>
