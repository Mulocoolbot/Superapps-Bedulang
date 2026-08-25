<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
    totalCount: {
        type: Number,
        default: 0
    },
    latestYear: {
        type: Number,
        default: new Date().getFullYear()
    },
    koperasis: {
        type: Array,
        default: () => []
    },
    kecamatanDesaMap: {
        type: Object,
        default: () => ({})
    },
    yearsList: {
        type: Array,
        default: () => []
    }
})

// ==========================================
// 1. FILTER CONTROLS FOR GRAPH LINE
// ==========================================
const selectedKecamatanLine = ref('Semua')
const selectedDesaLine = ref('Semua')

// Dynamic sub-indicator options for Line Graph
const subIndikatorOptionsLine = computed(() => {
    if (selectedKecamatanLine.value === 'Semua') {
        const allDesas = []
        Object.values(props.kecamatanDesaMap).forEach(list => {
            allDesas.push(...list)
        })
        return ['Semua', ...allDesas]
    }
    return ['Semua', ...(props.kecamatanDesaMap[selectedKecamatanLine.value] || [])]
})

// Reset sub-indicator to 'Semua' when main indicator changes
watch(selectedKecamatanLine, () => {
    selectedDesaLine.value = 'Semua'
})

// Helper to extract 4-digit year from tanggal_badan_hukum (e.g. "12 November 1996" -> 1996)
const getKoperasiBHYear = (item) => {
    if (item.tanggal_badan_hukum) {
        const match = String(item.tanggal_badan_hukum).match(/\b(19\d{2}|20\d{2})\b/)
        if (match) {
            return Number(match[1])
        }
    }
    return item.tahun ? Number(item.tahun) : null
}

// Compute 5-year step interval years list for clean, uncluttered growth chart (e.g. 1990, 1995, 2000, 2005, 2010, 2015, 2020, 2025)
const bhYearsList = computed(() => {
    const dataset = props.koperasis.filter(item => item.tahun === Number(props.latestYear))
    const rawYears = []

    dataset.forEach(item => {
        const y = getKoperasiBHYear(item)
        if (y) rawYears.push(y)
    })

    if (rawYears.length === 0) {
        return props.yearsList
    }

    const minYr = Math.min(...rawYears)
    const maxYr = Math.max(...rawYears)

    // Start from 5-year mark (e.g. 1990)
    const startYr = Math.floor(minYr / 5) * 5

    const intervalYears = []
    for (let y = startYr; y <= maxYr; y += 5) {
        intervalYears.push(y)
    }

    if (intervalYears[intervalYears.length - 1] < maxYr) {
        intervalYears.push(maxYr)
    }

    return intervalYears
})

// Calculate cumulative total count of cooperatives formed up to each 5-year interval point
const lineGraphPoints = computed(() => {
    const years = bhYearsList.value
    if (years.length === 0) return []

    const dataset = props.koperasis.filter(item => item.tahun === Number(props.latestYear))

    const filteredDataset = dataset.filter(item => {
        const matchKec = selectedKecamatanLine.value === 'Semua' || item.kecamatan === selectedKecamatanLine.value
        const matchDesa = selectedDesaLine.value === 'Semua' || item.desa_kelurahan === selectedDesaLine.value
        return matchKec && matchDesa
    })

    const points = []

    years.forEach((yr, index) => {
        const countUpToYr = filteredDataset.filter(item => {
            const bhYr = getKoperasiBHYear(item)
            return bhYr !== null && bhYr <= yr
        }).length

        const prevYr = index > 0 ? years[index - 1] : 0
        const countInInterval = filteredDataset.filter(item => {
            const bhYr = getKoperasiBHYear(item)
            return bhYr !== null && bhYr <= yr && bhYr > prevYr
        }).length

        points.push({
            tahun: yr,
            jumlah: countUpToYr,
            terbentukPeriode: countInInterval,
        })
    })

    return points
})

// Dynamic Gradient Logic: Green if last > n-1, Red if < n-1, Grey if == n-1
const lineTrendColor = computed(() => {
    const pts = lineGraphPoints.value
    if (pts.length < 2) return 'grey'

    const last = pts[pts.length - 1].jumlah
    const prev = pts[pts.length - 2].jumlah

    if (last > prev) return 'green'
    if (last < prev) return 'red'
    return 'grey'
})

// Active Box Point State (Clicking point shows floating box with data)
const activePointIndex = ref(null)
const hoveredPointIndex = ref(null)

const togglePointBox = (index) => {
    if (activePointIndex.value === index) {
        activePointIndex.value = null
    } else {
        activePointIndex.value = index
    }
}

// SVG Graph Line Path Calculation
const svgWidth = 800
const svgHeight = 320
const padding = 50

const svgPathData = computed(() => {
    const pts = lineGraphPoints.value
    if (pts.length === 0) return { path: '', area: '', coordinates: [], minY: 0, maxY: 0 }

    const yValues = pts.map(p => p.jumlah)
    let minY = Math.min(...yValues)
    let maxY = Math.max(...yValues)

    if (pts.length === 1 || minY === maxY) {
        minY = Math.max(0, minY - 10)
        maxY = maxY + 10
    }

    const availableW = svgWidth - padding * 2
    const availableH = svgHeight - padding * 2

    const coordinates = pts.map((pt, i) => {
        const x = pts.length > 1 ? padding + (i / (pts.length - 1)) * availableW : svgWidth / 2
        const normY = (pt.jumlah - minY) / (maxY - minY || 1)
        const y = (svgHeight - padding) - normY * availableH
        return { x, y, tahun: pt.tahun, jumlah: pt.jumlah }
    })

    if (coordinates.length === 1) {
        const p = coordinates[0]
        return {
            path: `M ${padding} ${p.y} L ${svgWidth - padding} ${p.y}`,
            area: `M ${padding} ${p.y} L ${svgWidth - padding} ${p.y} L ${svgWidth - padding} ${svgHeight - padding} L ${padding} ${svgHeight - padding} Z`,
            coordinates,
            minY,
            maxY,
        }
    }

    let d = `M ${coordinates[0].x} ${coordinates[0].y}`
    for (let i = 1; i < coordinates.length; i++) {
        d += ` L ${coordinates[i].x} ${coordinates[i].y}`
    }

    const first = coordinates[0]
    const last = coordinates[coordinates.length - 1]
    const areaD = `${d} L ${last.x} ${svgHeight - padding} L ${first.x} ${svgHeight - padding} Z`

    return { path: d, area: areaD, coordinates, minY, maxY }
})


// ==========================================
// 2. PIE CHART PERSEBARAN KOPERASI
// ==========================================
const selectedPieYear = ref(props.latestYear)

// Sync default year when props update
watch(() => props.latestYear, (newYear) => {
    if (newYear) {
        selectedPieYear.value = newYear
    }
}, { immediate: true })

// Filtered koperasis in selected snapshot year
const filteredKoperasisPie = computed(() => {
    return props.koperasis.filter(item => item.tahun === Number(selectedPieYear.value))
})

// Pie Chart per Kecamatan
const pieChartData = computed(() => {
    const counts = {}
    Object.keys(props.kecamatanDesaMap).forEach(kec => { counts[kec] = 0 })

    filteredKoperasisPie.value.forEach(item => {
        if (counts[item.kecamatan] !== undefined) {
            counts[item.kecamatan]++
        } else {
            counts[item.kecamatan] = 1
        }
    })

    const labels = Object.keys(counts)
    const series = labels.map(k => counts[k])

    return { labels, series }
})

// Selected Kecamatan for Desa/Kelurahan Breakdown Detail Box
const selectedPieKecamatan = ref(null)

const handlePieSliceClick = (event, chartContext, config) => {
    const index = config.dataPointIndex
    if (index !== undefined && index >= 0) {
        const kecName = pieChartData.value.labels[index]
        selectedPieKecamatan.value = kecName
    }
}

// Compute Desa/Kelurahan breakdown for clicked Kecamatan
const selectedKecamatanDesaBreakdown = computed(() => {
    if (!selectedPieKecamatan.value) return null

    const kecName = selectedPieKecamatan.value
    const inKec = filteredKoperasisPie.value.filter(item => item.kecamatan === kecName)

    const desaCounts = {}
    const allowedDesas = props.kecamatanDesaMap[kecName] || []
    allowedDesas.forEach(d => { desaCounts[d] = 0 })

    inKec.forEach(item => {
        desaCounts[item.desa_kelurahan] = (desaCounts[item.desa_kelurahan] || 0) + 1
    })

    const details = Object.keys(desaCounts).map(d => ({
        desa: d,
        jumlah: desaCounts[d],
    })).sort((a, b) => b.jumlah - a.jumlah)

    return {
        kecamatan: kecName,
        total: inKec.length,
        details,
    }
})

const pieApexOptions = computed(() => ({
    chart: {
        type: 'pie',
        events: {
            dataPointSelection: handlePieSliceClick,
        },
        dropShadow: {
            enabled: true,
            blur: 8,
            left: 2,
            top: 4,
            opacity: 0.1,
        }
    },
    labels: pieChartData.value.labels,
    colors: ['#059669', '#2563eb', '#d97706', '#7c3aed', '#ec4899', '#0891b2'],
    legend: {
        position: 'bottom',
        fontSize: '12px',
        fontWeight: 600,
    },
    tooltip: {
        y: {
            formatter: (val) => `${val} Koperasi`
        }
    },
    dataLabels: {
        enabled: true,
        formatter: (val) => `${val.toFixed(1)}%`,
    },
    plotOptions: {
        pie: {
            expandOnClick: true,
            donut: {
                size: '0%'
            }
        }
    }
}))


// ==========================================
// 3. TABLE FILTERING & PAGINATION CONTROLS
// ==========================================
const selectedKecamatanTable = ref('Semua')
const selectedDesaTable = ref('Semua')
const selectedYearTable = ref(props.latestYear)

const currentTablePage = ref(1)
const itemsPerPage = 10

// Sync default table year when latestYear changes
watch(() => props.latestYear, (newYear) => {
    if (newYear) {
        selectedYearTable.value = newYear
    }
}, { immediate: true })

// Dynamic sub-indicator options for Table
const subIndikatorOptionsTable = computed(() => {
    if (selectedKecamatanTable.value === 'Semua') {
        const allDesas = []
        Object.values(props.kecamatanDesaMap).forEach(list => {
            allDesas.push(...list)
        })
        return ['Semua', ...allDesas]
    }
    return ['Semua', ...(props.kecamatanDesaMap[selectedKecamatanTable.value] || [])]
})

// Filtered cooperatives for Table
const filteredKoperasisTable = computed(() => {
    return props.koperasis.filter(item => {
        const matchYear = item.tahun === Number(selectedYearTable.value)
        const matchKec = selectedKecamatanTable.value === 'Semua' || item.kecamatan === selectedKecamatanTable.value
        const matchDesa = selectedDesaTable.value === 'Semua' || item.desa_kelurahan === selectedDesaTable.value
        return matchYear && matchKec && matchDesa
    })
})

// Total Pages for Table
const totalTablePages = computed(() => {
    return Math.max(1, Math.ceil(filteredKoperasisTable.value.length / itemsPerPage))
})

// Sliding Window of Max 5 Page Numbers
const visiblePageNumbersTable = computed(() => {
    const total = totalTablePages.value
    const current = currentTablePage.value
    const windowSize = 5

    if (total <= 1) return []

    let start = current
    let end = start + windowSize - 1

    if (end > total) {
        end = total
        start = Math.max(1, end - windowSize + 1)
    }

    if (start < 1) {
        start = 1
        end = Math.min(total, start + windowSize - 1)
    }

    const pages = []
    for (let i = start; i <= end; i++) {
        pages.push(i)
    }
    return pages
})

// Paginated Slice for Table
const paginatedKoperasisTable = computed(() => {
    const start = (currentTablePage.value - 1) * itemsPerPage
    return filteredKoperasisTable.value.slice(start, start + itemsPerPage)
})

// Reset table pagination on filter change
watch([selectedKecamatanTable, selectedDesaTable, selectedYearTable], () => {
    currentTablePage.value = 1
    selectedDesaTable.value = selectedKecamatanTable.value === 'Semua' ? selectedDesaTable.value : (subIndikatorOptionsTable.value.includes(selectedDesaTable.value) ? selectedDesaTable.value : 'Semua')
})
</script>

<template>
    <Head title="Detail Koperasi Bangka Tengah" />

    <AdminLayout>
        <div class="space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <Link
                            href="/pimpinan"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            <span>Kembali ke Dashboard Pimpinan</span>
                        </Link>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-3">
                        <span>Data Detail Koperasi Kabupaten Bangka Tengah</span>
                    </h1>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl flex items-center gap-4 shrink-0 shadow-sm">
                    <div class="p-3 bg-emerald-600 text-white rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-6 0h6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Total Koperasi (Tahun {{ latestYear }})</p>
                        <h2 class="text-2xl font-black text-emerald-900">{{ totalCount }} <span class="text-xs font-normal">Koperasi</span></h2>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- COMPONENT 1: GRAPH LINE PERTUMBUHAN KOPERASI -->
            <!-- ============================================================== -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-extrabold text-gray-900">Grafik Pertumbuhan Jumlah Koperasi</h2>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize"
                            :class="{
                                'bg-emerald-100 text-emerald-800': lineTrendColor === 'green',
                                'bg-rose-100 text-rose-800': lineTrendColor === 'red',
                                'bg-gray-100 text-gray-700': lineTrendColor === 'grey'
                            }"
                        >
                            Trend: {{ lineTrendColor === 'green' ? 'Meningkat ↗' : (lineTrendColor === 'red' ? 'Menurun ↘' : 'Konstan ➔') }}
                        </span>
                    </div>

                    <!-- Clean Filter Select Controls without labels -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <select
                                v-model="selectedKecamatanLine"
                                class="appearance-none pl-4 pr-10 py-2.5 text-xs font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white text-gray-700 shadow-sm min-w-[170px]"
                            >
                                <option value="Semua">Semua Kecamatan</option>
                                <option v-for="(desas, kec) in kecamatanDesaMap" :key="kec" :value="kec">
                                    {{ kec }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        <div class="relative">
                            <select
                                v-model="selectedDesaLine"
                                class="appearance-none pl-4 pr-10 py-2.5 text-xs font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white text-gray-700 shadow-sm min-w-[180px]"
                            >
                                <option v-for="desa in subIndikatorOptionsLine" :key="desa" :value="desa">
                                    {{ desa === 'Semua' ? 'Semua Desa / Kelurahan' : desa }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transparent Interactive SVG Line Chart with Black Text & Lines -->
                <div class="relative bg-white border border-gray-200 rounded-2xl p-6 overflow-hidden shadow-sm">
                    <svg class="w-full h-80" viewBox="0 0 800 320">
                        <defs>
                            <linearGradient id="gradGreen" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                            </linearGradient>
                            <linearGradient id="gradRed" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#ef4444" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#ef4444" stop-opacity="0.0"/>
                            </linearGradient>
                            <linearGradient id="gradGrey" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#9ca3af" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#9ca3af" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>

                        <!-- Black Grid Lines (Dashed) -->
                        <line x1="50" y1="50" x2="750" y2="50" stroke="#000000" stroke-opacity="0.15" stroke-dasharray="4" stroke-width="1" />
                        <line x1="50" y1="115" x2="750" y2="115" stroke="#000000" stroke-opacity="0.15" stroke-dasharray="4" stroke-width="1" />
                        <line x1="50" y1="180" x2="750" y2="180" stroke="#000000" stroke-opacity="0.15" stroke-dasharray="4" stroke-width="1" />
                        <line x1="50" y1="245" x2="750" y2="245" stroke="#000000" stroke-opacity="0.15" stroke-dasharray="4" stroke-width="1" />

                        <!-- Black Y-Axis Text Labels -->
                        <text x="35" y="55" fill="#000000" font-size="12" text-anchor="end" font-weight="800">{{ svgPathData.maxY }}</text>
                        <text x="35" y="250" fill="#000000" font-size="12" text-anchor="end" font-weight="800">{{ svgPathData.minY }}</text>

                        <!-- Gradient Area Under Line -->
                        <path
                            :d="svgPathData.area"
                            :fill="lineTrendColor === 'green' ? 'url(#gradGreen)' : (lineTrendColor === 'red' ? 'url(#gradRed)' : 'url(#gradGrey)')"
                        />

                        <!-- Main Line Graph -->
                        <path
                            :d="svgPathData.path"
                            fill="none"
                            :stroke="lineTrendColor === 'green' ? '#10b981' : (lineTrendColor === 'red' ? '#ef4444' : '#6b7280')"
                            stroke-width="4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        <!-- Interactive Points / Dots on Line -->
                        <g v-for="(pt, idx) in svgPathData.coordinates" :key="idx">
                            <circle
                                :cx="pt.x"
                                :cy="pt.y"
                                :r="hoveredPointIndex === idx || activePointIndex === idx ? 12 : 7"
                                :fill="lineTrendColor === 'green' ? '#10b981' : (lineTrendColor === 'red' ? '#ef4444' : '#6b7280')"
                                fill-opacity="0.25"
                                class="transition-all duration-200 cursor-pointer"
                            />

                            <circle
                                :cx="pt.x"
                                :cy="pt.y"
                                :r="hoveredPointIndex === idx || activePointIndex === idx ? 7 : 5"
                                fill="#ffffff"
                                :stroke="lineTrendColor === 'green' ? '#10b981' : (lineTrendColor === 'red' ? '#ef4444' : '#6b7280')"
                                stroke-width="3.5"
                                class="transition-all duration-200 cursor-pointer"
                                @mouseenter="hoveredPointIndex = idx"
                                @mouseleave="hoveredPointIndex = null"
                                @click="togglePointBox(idx)"
                            />

                            <!-- Black X-Axis Year Labels -->
                            <text :x="pt.x" y="295" fill="#000000" font-size="12" font-weight="800" text-anchor="middle">
                                {{ pt.tahun }}
                            </text>
                        </g>
                    </svg>

                    <!-- Interactive Floating Box precisely next to data point dot -->
                    <template v-for="(pt, idx) in svgPathData.coordinates" :key="'box-' + idx">
                        <div
                            v-if="activePointIndex === idx"
                            class="absolute z-30 bg-gray-900 text-white rounded-xl p-3 shadow-2xl border border-gray-700 transform -translate-x-1/2 -translate-y-full transition-all cursor-pointer animate-fade-in-up"
                            :style="{ left: `${(pt.x / 800) * 100}%`, top: `${(pt.y / 320) * 100 - 12}px` }"
                            @click="activePointIndex = null"
                            title="Klik lagi untuk menutup box"
                        >
                            <div class="flex items-center justify-between gap-3 border-b border-gray-700 pb-1 mb-1 whitespace-nowrap">
                                <span class="text-[10px] font-extrabold text-gray-300 uppercase">Tahun {{ pt.tahun }}</span>
                                <span class="text-[9px] bg-emerald-500 text-white px-1.5 py-0.2 rounded font-bold">Tutup</span>
                            </div>
                            <p class="text-xs font-bold text-gray-100 whitespace-nowrap">
                                Jumlah Koperasi: <span class="text-sm font-black text-emerald-400">{{ pt.jumlah }}</span>
                            </p>
                        </div>
                    </template>
                </div>
            </div>


            <!-- ============================================================== -->
            <!-- COMPONENT 2: PIE CHART PERSEBARAN KOPERASI -->
            <!-- ============================================================== -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-gray-900">Pie Chart Persebaran Koperasi (%)</h2>

                    <!-- Clean Year Filter Dropdown without label -->
                    <div class="relative">
                        <select
                            v-model="selectedPieYear"
                            class="appearance-none pl-4 pr-10 py-2.5 text-xs font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white text-gray-700 shadow-sm min-w-[170px]"
                        >
                            <option v-for="yr in yearsList" :key="yr" :value="yr">
                                Tahun {{ yr }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <!-- Pie Chart Renderer (Per Kecamatan) -->
                    <div class="md:col-span-2 flex justify-center py-4">
                        <VueApexCharts
                            v-if="pieChartData.series.length > 0"
                            type="pie"
                            width="480"
                            :options="pieApexOptions"
                            :series="pieChartData.series"
                        />
                        <div v-else class="py-12 text-center text-gray-400 text-sm">
                            Tidak ada data koperasi untuk tahun yang dipilih.
                        </div>
                    </div>

                    <!-- Detail Breakdown Box per Desa/Kelurahan on Click Slice -->
                    <div class="md:col-span-1 bg-slate-50 border border-slate-200 p-5 rounded-2xl space-y-4">
                        <h3 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider border-b pb-2">
                            Persebaran Per Desa / Kelurahan
                        </h3>

                        <div v-if="selectedKecamatanDesaBreakdown" class="space-y-3 animate-fade-in-up">
                            <div class="flex items-center justify-between border-b pb-2">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Kecamatan Selected</p>
                                    <p class="text-base font-black text-gray-900">{{ selectedKecamatanDesaBreakdown.kecamatan }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                    Total: {{ selectedKecamatanDesaBreakdown.total }} Koperasi
                                </span>
                            </div>

                            <div class="max-h-64 overflow-y-auto space-y-2 pr-1">
                                <div
                                    v-for="item in selectedKecamatanDesaBreakdown.details"
                                    :key="item.desa"
                                    class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm"
                                >
                                    <span class="text-xs font-bold text-gray-700">{{ item.desa }}</span>
                                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                                        {{ item.jumlah }} Unit
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-6 text-xs text-gray-400">
                            Klik salah satu bagian/slice kecamatan pada Pie Chart di samping untuk melihat rincian persebaran per desa/kelurahan.
                        </div>
                    </div>
                </div>
            </div>


            <!-- ============================================================== -->
            <!-- COMPONENT 3: TABLE DATA KOPERASI (WITH PAGINATION) -->
            <!-- ============================================================== -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                    <h2 class="text-xl font-extrabold text-gray-900">Tabel Data Koperasi</h2>

                    <!-- Clean Filter Select Controls without labels -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <select
                                v-model="selectedKecamatanTable"
                                class="appearance-none pl-4 pr-10 py-2.5 text-xs font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white text-gray-700 shadow-sm min-w-[170px]"
                            >
                                <option value="Semua">Semua Kecamatan</option>
                                <option v-for="(desas, kec) in kecamatanDesaMap" :key="kec" :value="kec">
                                    {{ kec }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        <div class="relative">
                            <select
                                v-model="selectedDesaTable"
                                class="appearance-none pl-4 pr-10 py-2.5 text-xs font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white text-gray-700 shadow-sm min-w-[180px]"
                            >
                                <option v-for="desa in subIndikatorOptionsTable" :key="desa" :value="desa">
                                    {{ desa === 'Semua' ? 'Semua Desa / Kelurahan' : desa }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        <div class="relative">
                            <select
                                v-model="selectedYearTable"
                                class="appearance-none pl-4 pr-10 py-2.5 text-xs font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white text-gray-700 shadow-sm min-w-[150px]"
                            >
                                <option v-for="yr in yearsList" :key="yr" :value="yr">
                                    Tahun {{ yr }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Content (4 Main Variables: Nama Koperasi, Tanggal Badan Hukum, Desa/Kelurahan, Kecamatan) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-500 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-16">No</th>
                                <th class="py-3.5 px-4">Nama Koperasi</th>
                                <th class="py-3.5 px-4">Tanggal Badan Hukum</th>
                                <th class="py-3.5 px-4">Desa / Kelurahan</th>
                                <th class="py-3.5 px-4">Kecamatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="filteredKoperasisTable.length === 0">
                                <td colspan="5" class="py-8 text-center text-gray-400">
                                    Tidak ada data koperasi sesuai kriteria filter.
                                </td>
                            </tr>

                            <tr v-for="(item, idx) in paginatedKoperasisTable" :key="item.id" class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-gray-400">
                                    {{ (currentTablePage - 1) * itemsPerPage + idx + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-gray-900">
                                    {{ item.nama_koperasi }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 font-medium whitespace-nowrap">
                                    {{ item.tanggal_badan_hukum || '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-700 font-medium">
                                    {{ item.desa_kelurahan }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-700">
                                    <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 font-semibold border border-blue-100">
                                        {{ item.kecamatan }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination UI Bar -->
                <div v-if="filteredKoperasisTable.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100">
                    <div class="text-xs text-gray-500">
                        Menampilkan <span class="font-bold text-gray-800">{{ (currentTablePage - 1) * itemsPerPage + 1 }}</span>
                        - <span class="font-bold text-gray-800">{{ Math.min(currentTablePage * itemsPerPage, filteredKoperasisTable.length) }}</span>
                        dari <span class="font-bold text-gray-800">{{ filteredKoperasisTable.length }}</span> total data (Tahun {{ selectedYearTable }})
                    </div>

                    <div v-if="totalTablePages > 1" class="flex items-center gap-1.5">
                        <!-- Prev Button -->
                        <button
                            @click="currentTablePage > 1 && currentTablePage--"
                            :disabled="currentTablePage === 1"
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm"
                            aria-label="Previous Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        <!-- Page Numbers (Max 5 Sliding Bubbles) -->
                        <button
                            v-for="pg in visiblePageNumbersTable"
                            :key="pg"
                            @click="currentTablePage = pg"
                            class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center transition"
                            :class="currentTablePage === pg
                                ? 'bg-blue-600 text-white shadow-sm font-black'
                                : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100'"
                        >
                            {{ pg }}
                        </button>

                        <!-- Next Button -->
                        <button
                            @click="currentTablePage < totalTablePages && currentTablePage++"
                            :disabled="currentTablePage === totalTablePages"
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm"
                            aria-label="Next Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
