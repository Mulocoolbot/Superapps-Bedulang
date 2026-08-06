<script setup>
import { ref, computed, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

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

// Filter state
const selectedPeriodKey = ref('2026-1')
const selectedKecamatan = ref('Semua')
const selectedDesa = ref('Semua')

// Period options (Tahun & Semester)
const periodOptions = computed(() => {
    if (props.dashboard?.period_options && props.dashboard.period_options.length > 0) {
        return props.dashboard.period_options
    }
    return [
        { key: '2026-1', label: 'Tahun 2026 - Semester 1 (Terbaru)' },
        { key: '2026-2', label: 'Tahun 2026 - Semester 2' },
        { key: '2025-2', label: 'Tahun 2025 - Semester 2' },
        { key: '2025-1', label: 'Tahun 2025 - Semester 1' },
    ]
})

// Active district dataset according to selected Semester Period
const districts = computed(() => {
    if (props.dashboard?.periods && props.dashboard.periods[selectedPeriodKey.value]) {
        return props.dashboard.periods[selectedPeriodKey.value]
    }
    return props.dashboard?.districts || []
})

// Kecamatan options for Filter
const kecamatanOptions = computed(() => {
    return ['Semua', ...districts.value.map(d => d.name)]
})

// Desa / Kelurahan options for Filter
const desaOptions = computed(() => {
    if (selectedKecamatan.value === 'Semua') {
        return ['Semua']
    }
    const targetDistrict = districts.value.find(d => d.name === selectedKecamatan.value)
    if (!targetDistrict || !targetDistrict.villages) {
        return ['Semua']
    }
    return ['Semua', ...targetDistrict.villages.map(v => v.name)]
})

// Reset sub indicator whenever main indicator (Kecamatan) changes
watch(selectedKecamatan, () => {
    selectedDesa.value = 'Semua'
})

// Filtered list of village demographic records based on active selection
const activeVillages = computed(() => {
    let result = []
    
    districts.value.forEach(district => {
        if (selectedKecamatan.value !== 'Semua' && district.name !== selectedKecamatan.value) {
            return
        }
        
        district.villages.forEach(village => {
            if (selectedDesa.value !== 'Semua' && village.name !== selectedDesa.value) {
                return
            }
            result.push(village)
        })
    })

    return result
})

// Active scope badge text
const activeScopeLabel = computed(() => {
    const activePeriodLabel = periodOptions.value.find(p => p.key === selectedPeriodKey.value)?.label || selectedPeriodKey.value
    if (selectedKecamatan.value === 'Semua') {
        return `${activePeriodLabel} • Seluruh Bangka Tengah`
    }
    if (selectedDesa.value === 'Semua') {
        return `${activePeriodLabel} • Kec. ${selectedKecamatan.value} (Semua Desa)`
    }
    return `${activePeriodLabel} • Kec. ${selectedKecamatan.value} • Desa ${selectedDesa.value}`
})

// Helper number formatter
const formatNumber = (val) => {
    if (val === undefined || val === null) return '0'
    return Number(val).toLocaleString('id-ID')
}

// Base ApexChart options generator (Percentage labels hidden on chart slices)
const createPieChartOptions = (labels, colors, unit = 'jiwa') => ({
    chart: {
        type: 'donut',
        fontFamily: 'Inter, system-ui, sans-serif',
        background: 'transparent',
        toolbar: { show: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 600,
        }
    },
    labels: labels,
    colors: colors,
    stroke: {
        show: true,
        colors: ['#ffffff'],
        width: 2
    },
    dataLabels: {
        enabled: false, // Hapus persentase di atas slice pie chart
    },
    legend: {
        show: false
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '13px',
                        fontWeight: 600,
                        color: '#64748b'
                    },
                    value: {
                        show: true,
                        fontSize: '18px',
                        fontWeight: 800,
                        color: '#0f172a',
                        formatter: function (val) {
                            return formatNumber(val) + ' ' + unit
                        }
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '12px',
                        fontWeight: 600,
                        color: '#64748b',
                        formatter: function (w) {
                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            return formatNumber(total) + ' ' + unit
                        }
                    }
                }
            }
        }
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (val) {
                return formatNumber(val) + ' ' + unit
            }
        }
    },
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: {
                    width: '100%'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    ]
})

// ----------------------------------------------------
// DATA AGGREGATION FOR 8 CHARTS
// ----------------------------------------------------

// --- CHART 1: KEPALA KELUARGA (Level 1: Per Kecamatan, Level 2: Mentok Per Desa) ---
const chart1Data = computed(() => {
    let subtitleText = ''
    let rawItems = []

    if (selectedKecamatan.value === 'Semua') {
        // Jika Semua Kecamatan dipilih, pie chart menunjukkan pembagian per Kecamatan
        subtitleText = 'Pembagian jumlah Kepala Keluarga per Kecamatan'
        districts.value.forEach(district => {
            let districtKkSum = 0
            district.villages.forEach(v => {
                districtKkSum += (v.kk_stat?.kk || 0)
            })
            rawItems.push({
                label: `Kec. ${district.name}`,
                count: districtKkSum
            })
        })
    } else {
        // Jika Kecamatan dipilih (misal Koba), pie chart mentok pada pembagian per Desa/Kelurahan di Kecamatan tersebut
        subtitleText = `Pembagian jumlah Kepala Keluarga per Desa/Kelurahan di Kecamatan ${selectedKecamatan.value}`
        const targetDistrict = districts.value.find(d => d.name === selectedKecamatan.value)
        if (targetDistrict && targetDistrict.villages) {
            targetDistrict.villages.forEach(v => {
                rawItems.push({
                    label: v.name,
                    count: v.kk_stat?.kk || 0
                })
            })
        }
    }

    // Sort items descending by count (highest count first)
    rawItems.sort((a, b) => b.count - a.count)

    const palette = ['#0284c7', '#0ea5e9', '#38bdf8', '#06b6d4', '#0d9488', '#14b8a6', '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e', '#f97316']
    
    const items = rawItems.map((item, idx) => ({
        label: item.label,
        count: item.count,
        color: palette[idx % palette.length]
    }))

    const series = items.map(i => i.count)
    const labels = items.map(i => i.label)
    const colors = items.map(i => i.color)
    const total = series.reduce((a, b) => a + b, 0)

    return {
        series,
        options: createPieChartOptions(labels, colors, 'KK'),
        items,
        total,
        subtitleText
    }
})

// Generic dynamic chart generator from activeVillages payload
const generateDynamicChartData = (categoryKey, defaultLabelPrefix = '', unit = 'jiwa') => {
    const sums = {}

    activeVillages.value.forEach(v => {
        const catMap = v[categoryKey]
        if (catMap && typeof catMap === 'object') {
            Object.keys(catMap).forEach(key => {
                const count = Number(catMap[key] || 0)
                if (!sums[key]) {
                    sums[key] = 0
                }
                sums[key] += count
            })
        }
    })

    let itemsList = Object.keys(sums).map(key => ({
        rawLabel: key,
        label: defaultLabelPrefix && !key.toLowerCase().startsWith(defaultLabelPrefix.toLowerCase()) ? `${defaultLabelPrefix} ${key}` : key,
        count: sums[key]
    }))

    // Sort items descending by count (highest data count first)
    itemsList.sort((a, b) => b.count - a.count)

    // Palette of vibrant colors for dynamic chart slices
    const colorPalette = [
        '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
        '#06b6d4', '#ec4899', '#f97316', '#14b8a6', '#6366f1',
        '#84cc16', '#a855f7', '#0284c7', '#d97706', '#dc2626',
        '#059669', '#4f46e5', '#c026d3', '#0891b2', '#ea580c'
    ]

    const items = itemsList.map((item, idx) => ({
        label: item.label,
        count: item.count,
        color: colorPalette[idx % colorPalette.length]
    }))

    const series = items.map(i => i.count)
    const colors = items.map(i => i.color)
    const chartLabels = items.map(i => i.label)
    const total = series.reduce((a, b) => a + b, 0)

    return {
        series,
        options: createPieChartOptions(chartLabels, colors, unit),
        items,
        total
    }
}

// --- CHARTS 2 to 8: DYNAMICALLY GENERATED FROM PAYLOAD ---
const chart2Data = computed(() => generateDynamicChartData('agama', '', 'jiwa'))
const chart3Data = computed(() => generateDynamicChartData('golongan_darah', 'Gol', 'jiwa'))
const chart4Data = computed(() => generateDynamicChartData('jenis_kelamin', '', 'jiwa'))
const chart5Data = computed(() => generateDynamicChartData('kelompok_umur', '', 'jiwa'))
const chart6Data = computed(() => generateDynamicChartData('pekerjaan', '', 'jiwa'))
const chart7Data = computed(() => generateDynamicChartData('pendidikan', '', 'jiwa'))
const chart8Data = computed(() => generateDynamicChartData('status_perkawinan', '', 'jiwa'))

// Total summary statistics
const totalPenduduk = computed(() => chart4Data.value.total)
const totalKk = computed(() => {
    let sum = 0
    activeVillages.value.forEach(v => {
        sum += (v.kk_stat?.kk || 0)
    })
    return sum
})
const totalDesaCount = computed(() => activeVillages.value.length)

// Reset all filters to default ("Semua", "Semua")
const resetFilters = () => {
    selectedKecamatan.value = 'Semua'
    selectedDesa.value = 'Semua'
}
</script>

<template>
    <div class="space-y-8 animate-fade-in-up">
        
        <!-- TOP STATS SUMMARY BANNER -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1: Total Penduduk -->
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-md dark:bg-slate-900/80 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Total Penduduk</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">{{ formatNumber(totalPenduduk) }}</h3>
                        <p class="mt-1 text-xs text-blue-100/80">Jiwa terdaftar</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/20 text-2xl text-blue-200 border border-blue-400/30">
                        👥
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Kepala Keluarga -->
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-md dark:bg-slate-900/80 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-200">Kepala Keluarga</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-white">{{ formatNumber(totalKk) }}</h3>
                        <p class="mt-1 text-xs text-cyan-100/80">Kartu Keluarga (KK)</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-500/20 text-2xl text-cyan-200 border border-cyan-400/30">
                        🏠
                    </div>
                </div>
            </div>

            <!-- Card 3: Scope Kecamatan -->
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-md dark:bg-slate-900/80 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Scope Kecamatan</p>
                        <h3 class="mt-2 text-xl font-extrabold text-white truncate max-w-[160px]">
                            {{ selectedKecamatan === 'Semua' ? '6 Kecamatan' : selectedKecamatan }}
                        </h3>
                        <p class="mt-1 text-xs text-emerald-100/80">Wilayah terpilih</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/20 text-2xl text-emerald-200 border border-emerald-400/30">
                        🗺️
                    </div>
                </div>
            </div>

            <!-- Card 4: Scope Desa / Kelurahan -->
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-md dark:bg-slate-900/80 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-purple-200">Scope Desa / Kel</p>
                        <h3 class="mt-2 text-xl font-extrabold text-white truncate max-w-[160px]">
                            {{ selectedDesa === 'Semua' ? `${totalDesaCount} Desa/Kel` : selectedDesa }}
                        </h3>
                        <p class="mt-1 text-xs text-purple-100/80">Cakupan data</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-500/20 text-2xl text-purple-200 border border-purple-400/30">
                        🏛️
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER CONTROL PANEL -->
        <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 dark:bg-blue-400/20 dark:text-blue-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Filter Data Kependudukan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Pilih Periode Semester, Kecamatan, dan Desa/Kelurahan untuk memperbarui diagram data demografi.
                        </p>
                    </div>
                </div>

                <!-- Reset Filter Button -->
                <button
                    v-if="selectedKecamatan !== 'Semua' || selectedDesa !== 'Semua'"
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 self-start md:self-auto cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Filter Wilayah
                </button>
            </div>

            <!-- Dropdown Select Controls (3 Columns) -->
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Periode Semester -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        📅 Periode Semester
                    </label>
                    <select
                        v-model="selectedPeriodKey"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option v-for="p in periodOptions" :key="p.key" :value="p.key">
                            {{ p.label }}
                        </option>
                    </select>
                </div>

                <!-- Kecamatan -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        📍 Kecamatan
                    </label>
                    <select
                        v-model="selectedKecamatan"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option v-for="opt in kecamatanOptions" :key="opt" :value="opt">
                            {{ opt === 'Semua' ? 'Semua Kecamatan' : `Kecamatan ${opt}` }}
                        </option>
                    </select>
                </div>

                <!-- Desa / Kelurahan -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        🏡 Desa / Kelurahan
                    </label>
                    <select
                        v-model="selectedDesa"
                        :disabled="selectedKecamatan === 'Semua'"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <option v-for="opt in desaOptions" :key="opt" :value="opt">
                            {{ opt === 'Semua' ? (selectedKecamatan === 'Semua' ? 'Semua Desa (Pilih Kecamatan)' : 'Semua Desa / Kelurahan') : opt }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Active Filter Badge -->
            <div class="mt-4 flex items-center gap-2 pt-2 border-t border-slate-200/60 dark:border-slate-800">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Filter Aktif:</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    {{ activeScopeLabel }}
                </span>
            </div>
        </div>

        <!-- 8 INTERACTIVE CHART CARDS GRID -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

            <!-- KEPALA KELUARGA -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300 text-lg">
                                🏠
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Kepala Keluarga</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ chart1Data.subtitleText }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart1Data.series.length > 0 && chart1Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart1Data.options"
                            :series="chart1Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart1Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} KK</span>
                    </div>
                </div>
            </div>

            <!-- AGAMA -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300 text-lg">
                                🕌
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Agama</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan pemeluk Agama</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart2Data.series.length > 0 && chart2Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart2Data.options"
                            :series="chart2Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart2Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

            <!-- GOLONGAN DARAH -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-300 text-lg">
                                🩸
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Golongan Darah</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan Golongan Darah</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart3Data.series.length > 0 && chart3Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart3Data.options"
                            :series="chart3Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart3Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

            <!-- JENIS KELAMIN -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300 text-lg">
                                👥
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Jenis Kelamin</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan Jenis Kelamin</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart4Data.series.length > 0 && chart4Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart4Data.options"
                            :series="chart4Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart4Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

            <!-- KELOMPOK UMUR -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300 text-lg">
                                🎂
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Kelompok Umur</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan rentang Usia</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart5Data.series.length > 0 && chart5Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart5Data.options"
                            :series="chart5Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart5Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

            <!-- PEKERJAAN -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300 text-lg">
                                💼
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Pekerjaan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan Mata Pencaharian / Pekerjaan</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart6Data.series.length > 0 && chart6Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart6Data.options"
                            :series="chart6Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart6Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

            <!-- PENDIDIKAN -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-600 dark:bg-orange-500/20 dark:text-orange-300 text-lg">
                                🎓
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Pendidikan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan Tingkat Pendidikan Terakhir</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart7Data.series.length > 0 && chart7Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart7Data.options"
                            :series="chart7Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart7Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

            <!-- STATUS PERKAWINAN -->
            <div class="rounded-3xl border border-white/20 bg-white/95 dark:bg-slate-900/95 p-6 shadow-xl backdrop-blur-xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-500/10 text-pink-600 dark:bg-pink-500/20 dark:text-pink-300 text-lg">
                                💍
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pembagian Status Perkawinan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pembagian jumlah penduduk berdasarkan Status Perkawinan</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-6 min-h-[300px] flex items-center justify-center">
                        <VueApexCharts
                            v-if="chart8Data.series.length > 0 && chart8Data.total > 0"
                            type="donut"
                            height="290"
                            width="100%"
                            :options="chart8Data.options"
                            :series="chart8Data.series"
                        />
                        <div v-else class="text-sm text-slate-400 font-medium py-12">Tidak ada data untuk filter ini.</div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div v-for="item in chart8Data.items" :key="item.label" class="flex items-center justify-between text-xs py-0.5">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.label }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatNumber(item.count) }} jiwa</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
