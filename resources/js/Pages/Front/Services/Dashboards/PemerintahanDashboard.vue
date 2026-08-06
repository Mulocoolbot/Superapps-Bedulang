<script setup>
import { computed, ref, watch } from 'vue'
import AnimatedNumber from '@/Components/Front/AnimatedNumber.vue'

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

// Current Active Tab
const activeTab = ref('penduduk')

// Tab Navigation Definition
const tabs = [
    { id: 'penduduk', name: 'Penduduk'},
    { id: 'dprd', name: 'Anggota DPRD'},
    { id: 'desa', name: 'Desa & Kecamatan'}
]

// Fetch Dashboard Data from Backend
const population = computed(() => props.dashboard?.population ?? { total_penduduk: 0, laki_laki: 0, perempuan: 0, age_groups: [] })
const dprd = computed(() => props.dashboard?.dprd ?? { periode: '2024/2029', total_kursi: 30, dapil_list: [] })
const desaKecamatan = computed(() => props.dashboard?.desa_kecamatan ?? { total_kecamatan: 6, total_desa: 56, villages: [] })

// --- Tab 1: Penduduk (Charts & Distribution) ---
const maxPopulationVal = computed(() => {
    const groups = population.value.age_groups ?? []
    if (groups.length === 0) return 1
    let max = 0
    groups.forEach(g => {
        if (g.laki_laki > max) max = g.laki_laki
        if (g.perempuan > max) max = g.perempuan
    })
    return max
})

// Rounded Y-Axis Scale
const chartYAxisScale = computed(() => {
    const maxVal = maxPopulationVal.value
    // Round to nearest 2000
    const roundedMax = Math.ceil(maxVal / 2000) * 2000
    const steps = 5
    const stepVal = roundedMax / steps
    const scale = []
    for (let i = steps; i >= 0; i--) {
        scale.push(Math.round(stepVal * i))
    }
    return scale
})

const maxScaleVal = computed(() => {
    const scale = chartYAxisScale.value
    return scale.length > 0 ? scale[0] : 10000
})

// --- Tab 2: DPRD Semicircle Seats Visualization ---
const selectedDprdSeat = ref(null)
const selectedGender = ref('all') // 'all', 'Laki-laki', 'Perempuan'

const dprdGenderCounts = computed(() => {
    const rawMembers = dprd.value.members ?? []
    const male = rawMembers.filter(m => m.gender === 'Laki-laki').length
    const female = rawMembers.filter(m => m.gender === 'Perempuan').length
    return {
        male,
        female,
        total: rawMembers.length
    }
})

const dprdSeats = computed(() => {
    const seats = []
    const rows = [
        { radius: 55, count: 8 },
        { radius: 80, count: 10 },
        { radius: 105, count: 12 }
    ]
    
    let seatIndex = 0
    rows.forEach((row, rowIndex) => {
        const radius = row.radius
        const count = row.count
        
        for (let i = 0; i < count; i++) {
            // Angle distribution from 195 to 345 degrees
            const angleDeg = 195 + (i / (count - 1)) * 150
            const angleRad = (angleDeg * Math.PI) / 180
            
            // Semicircle centered around (150, 130)
            const x = 150 + radius * Math.cos(angleRad)
            const y = 130 + radius * Math.sin(angleRad)
            
            seats.push({
                id: seatIndex + 1,
                x,
                y,
                row: rowIndex + 1
            })
            
            seatIndex++
        }
    })

    // Get members list from props
    const rawMembers = dprd.value.members ?? []
    
    // Sort / Group members by Dapil
    const dapil1Members = rawMembers.filter(m => m.dapil === 'Dapil 1')
    const dapil2Members = rawMembers.filter(m => m.dapil === 'Dapil 2')
    const dapil3Members = rawMembers.filter(m => m.dapil === 'Dapil 3')
    
    // Semicircle natural order: map first 11 seats to Dapil 1, next 10 to Dapil 2, last 9 to Dapil 3
    return seats.map((seat, idx) => {
        let member = null
        let dapilName = ''
        let wilayahDapil = ''
        let colorClass = ''
        let borderClass = ''
        let colorHex = ''
        
        if (idx < 11) {
            member = dapil1Members[idx] || { name: 'Anggota DPRD', party: '-', gender: 'Laki-laki' }
            dapilName = 'Dapil 1'
            wilayahDapil = 'Koba, Lubuk Besar'
            colorClass = 'fill-cyan-500 stroke-cyan-600 dark:stroke-cyan-400'
            borderClass = 'border-cyan-500'
            colorHex = '#06b6d4'
        } else if (idx < 21) {
            member = dapil2Members[idx - 11] || { name: 'Anggota DPRD', party: '-', gender: 'Laki-laki' }
            dapilName = 'Dapil 2'
            wilayahDapil = 'Simpang Katis, Sungai Selan'
            colorClass = 'fill-indigo-500 stroke-indigo-600 dark:stroke-indigo-400'
            borderClass = 'border-indigo-500'
            colorHex = '#6366f1'
        } else {
            member = dapil3Members[idx - 21] || { name: 'Anggota DPRD', party: '-', gender: 'Laki-laki' }
            dapilName = 'Dapil 3'
            wilayahDapil = 'Pangkalan Baru, Namang'
            colorClass = 'fill-amber-500 stroke-amber-600 dark:stroke-amber-400'
            borderClass = 'border-amber-500'
            colorHex = '#f59e0b'
        }
        
        return {
            ...seat,
            member,
            dapil: dapilName,
            wilayah: wilayahDapil,
            colorClass,
            borderClass,
            colorHex
        }
    })
})

const filteredDprdMembers = computed(() => {
    const rawMembers = dprd.value.members ?? []
    let list = [...rawMembers]
    
    // Sort by Dapil size (Dapil 1: 11 members, Dapil 2: 10, Dapil 3: 9)
    list.sort((a, b) => {
        const order = { 'Dapil 1': 1, 'Dapil 2': 2, 'Dapil 3': 3 }
        return (order[a.dapil] || 99) - (order[b.dapil] || 99)
    })
    
    // Apply gender filter
    if (selectedGender.value !== 'all') {
        list = list.filter(m => m.gender === selectedGender.value)
    }
    
    return list
})

// --- Tab 3: Desa & Kecamatan Interactive Table ---
const searchQuery = ref('')
const selectedKecamatan = ref('')
const showSuggestions = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(10)

const availableKecamatan = [
    { value: '', label: 'Semua Kecamatan' },
    { value: 'Koba', label: 'Koba' },
    { value: 'Pangkalanbaru', label: 'Pangkalan Baru' },
    { value: 'Namang', label: 'Namang' },
    { value: 'Lubuk Besar', label: 'Lubuk Besar' },
    { value: 'Simpang Katis', label: 'Simpang Katis' },
    { value: 'Sungai Selan', label: 'Sungai Selan' }
]

// Normalized filter for comparison
const normalizeText = (text) => text.toLowerCase().replace(/\s+/g, '')

// Autocomplete suggestions based on query
const suggestions = computed(() => {
    if (!searchQuery.value.trim()) return []
    const q = normalizeText(searchQuery.value)
    return (desaKecamatan.value.villages ?? [])
        .filter(v => normalizeText(v.desa).includes(q) || normalizeText(v.kecamatan).includes(q))
        .slice(0, 5)
})

// Main filtered villages
const filteredVillages = computed(() => {
    let list = desaKecamatan.value.villages ?? []
    
    // Dropdown filter
    if (selectedKecamatan.value) {
        list = list.filter(v => normalizeText(v.kecamatan) === normalizeText(selectedKecamatan.value))
    }
    
    // Search query filter
    if (searchQuery.value.trim()) {
        const q = normalizeText(searchQuery.value)
        list = list.filter(v => normalizeText(v.desa).includes(q) || normalizeText(v.kecamatan).includes(q))
    }
    
    return list
})

// Pagination
const totalItems = computed(() => filteredVillages.value.length)
const totalPages = computed(() => {
    if (itemsPerPage.value === 'all') return 1
    return Math.ceil(totalItems.value / itemsPerPage.value) || 1
})

const paginatedVillages = computed(() => {
    let list = filteredVillages.value
    if (itemsPerPage.value === 'all') return list
    
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return list.slice(start, end)
})

// Watchers to reset to page 1 on filter changes
watch([searchQuery, selectedKecamatan, itemsPerPage], () => {
    currentPage.value = 1
})

const selectSuggestion = (s) => {
    searchQuery.value = s.desa
    selectedKecamatan.value = s.kecamatan
    showSuggestions.value = false
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedKecamatan.value = ''
    currentPage.value = 1
}

const handleBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false
    }, 200)
}

const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

const emit = defineEmits(['tab-change'])
watch(activeTab, (newTab) => {
    emit('tab-change', newTab)
}, { immediate: true })

const formatDapilName = (name) => {
    if (!name) return ''
    return name
        .replace(/Dapil 1/i, 'Dapil 1')
        .replace(/Dapil 2/i, 'Dapil 2')
        .replace(/Dapil 3/i, 'Dapil 3')
}

const hoveredDprdBar = ref(null)

const dprdBarChartData = computed(() => {
    const rawMembers = dprd.value.members ?? []
    
    const getCountForDapil = (dapilName) => {
        return rawMembers.filter(m => {
            const matchesDapil = m.dapil === dapilName
            const matchesGender = selectedGender.value === 'all' || m.gender === selectedGender.value
            return matchesDapil && matchesGender
        }).length
    }
    
    const getMembersForDapil = (dapilName) => {
        return rawMembers.filter(m => {
            const matchesDapil = m.dapil === dapilName
            const matchesGender = selectedGender.value === 'all' || m.gender === selectedGender.value
            return matchesDapil && matchesGender
        })
    }
    
    return [
        { 
            label: 'Dapil 1', 
            count: getCountForDapil('Dapil 1'), 
            max: 11, 
            wilayah: 'Koba, Lubuk Besar',
            colorClass: 'bg-cyan-500 hover:bg-cyan-600 dark:bg-cyan-500/80 dark:hover:bg-cyan-500',
            members: getMembersForDapil('Dapil 1')
        },
        { 
            label: 'Dapil 2', 
            count: getCountForDapil('Dapil 2'), 
            max: 10, 
            wilayah: 'Simpang Katis, Sungai Selan',
            colorClass: 'bg-indigo-500 hover:bg-indigo-600 dark:bg-indigo-500/80 dark:hover:bg-indigo-500',
            members: getMembersForDapil('Dapil 2')
        },
        { 
            label: 'Dapil 3', 
            count: getCountForDapil('Dapil 3'), 
            max: 9, 
            wilayah: 'Pangkalan Baru, Namang',
            colorClass: 'bg-amber-500 hover:bg-amber-600 dark:bg-amber-500/80 dark:hover:bg-amber-500',
            members: getMembersForDapil('Dapil 3')
        }
    ]
})
</script>

<template>
    <div class="mt-8 space-y-6">
        
        <!-- Tab Switches - Sleek & Responsive Segmented Control -->
        <div class="flex justify-center animate-fade-in-up [animation-delay:100ms]">
            <nav class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-800/80 shadow-sm" aria-label="Tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        activeTab === tab.id
                            ? 'bg-white text-blue-600 dark:bg-slate-900 dark:text-blue-400 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-white/40 dark:hover:bg-slate-900/40',
                        'px-4 py-2 text-xs md:text-sm font-semibold rounded-lg transition-all duration-300 flex items-center gap-2 cursor-pointer'
                    ]"
                >
                    <span>{{ tab.icon }}</span>
                    <span>{{ tab.name }}</span>
                </button>
            </nav>
        </div>

        <!-- -------------------- TAB 1: PENDUDUK -------------------- -->
        <div v-if="activeTab === 'penduduk'" class="space-y-6">
            <!-- Cards Grid -->
            <div class="grid gap-5 sm:grid-cols-3 animate-fade-in-up [animation-delay:150ms]">
                
                <!-- Card 1: Total Penduduk -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600 dark:bg-blue-500" />
                    <div class="pl-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-400">Jumlah Penduduk</span>
                            <span class="text-lg"></span>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            <AnimatedNumber :value="population.total_penduduk"></AnimatedNumber>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Total kependudukan Bangka Tengah</p>
                    </div>
                </div>

                <!-- Card 2: Laki-laki -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-sky-500 dark:bg-sky-400" />
                    <div class="pl-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-400">Laki-Laki</span>
                            <span class="text-lg"></span>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            <AnimatedNumber :value="population.laki_laki"></AnimatedNumber>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ formatNumber(population.total_penduduk > 0 ? (population.laki_laki / population.total_penduduk * 100) : 0) }}% dari total penduduk
                        </p>
                    </div>
                </div>

                <!-- Card 3: Perempuan -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-rose-500 dark:bg-rose-400" />
                    <div class="pl-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-400">Perempuan</span>
                            <span class="text-lg"></span>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            <AnimatedNumber :value="population.perempuan"></AnimatedNumber>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ formatNumber(population.total_penduduk > 0 ? (population.perempuan / population.total_penduduk * 100) : 0) }}% dari total penduduk
                        </p>
                    </div>
                </div>

            </div>

            <!-- Grouped Bar Chart Card -->
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Demografi Penduduk</span>
                    <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                        Pembagian Penduduk Berdasarkan Jenis Kelamin & Pengelompokan Umur
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Visualisasi perbandingan kelompok umur antara laki-laki dan perempuan di Kabupaten Bangka Tengah.
                    </p>
                </div>

                <!-- Custom HTML/CSS Grouped Bar Chart -->
                <div class="mt-8">
                    <!-- Chart Y-axis Labels and Gridlines Container -->
                    <div class="relative h-72 w-full">
                        
                        <!-- Grid lines -->
                        <div class="absolute inset-0 flex flex-col justify-between">
                            <div 
                                v-for="gridVal in chartYAxisScale" 
                                :key="gridVal" 
                                class="w-full flex items-center border-b border-slate-100 dark:border-slate-800/60 pb-1 text-[10px] text-slate-400 font-semibold"
                                :style="{ height: `${100 / (chartYAxisScale.length - 1)}%` }"
                            >
                                <span class="w-10 text-left">{{ formatNumber(gridVal) }}</span>
                            </div>
                        </div>

                        <!-- Bar Groups Container (Scrollable on small screens) -->
                        <div class="absolute inset-y-0 left-12 right-0 overflow-x-auto custom-scrollbar flex items-end justify-between gap-1.5 md:gap-4 pl-2 pt-4">
                            <div 
                                v-for="group in population.age_groups" 
                                :key="group.range" 
                                class="flex-1 min-w-[32px] md:min-w-[40px] h-full flex flex-col justify-end items-center group relative"
                            >
                                <!-- Bars side-by-side -->
                                <div class="flex items-end justify-center gap-1 w-full h-[88%]">
                                    <!-- Male Bar -->
                                    <div 
                                        class="w-3 md:w-4 rounded-t transition-all duration-500 hover:brightness-110 shadow-sm animate-grow-height"
                                        :style="{ height: `${(group.laki_laki / maxScaleVal) * 100}%`, backgroundColor: '#030596' }"
                                    ></div>
                                    
                                    <!-- Female Bar -->
                                    <div 
                                        class="w-3 md:w-4 rounded-t transition-all duration-500 hover:brightness-110 shadow-sm animate-grow-height"
                                        :style="{ height: `${(group.perempuan / maxScaleVal) * 100}%`, backgroundColor: '#960323' }"
                                    ></div>
                                </div>

                                <!-- Age Label -->
                                <span class="mt-2 text-[10px] font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ group.range }}
                                </span>

                                <!-- Tooltip on Hover -->
                                <div class="absolute bottom-16 z-10 hidden group-hover:block bg-slate-900 text-white text-xs rounded-xl p-3 shadow-xl border border-slate-700/50 w-44 pointer-events-none transform -translate-y-1">
                                    <p class="font-bold text-center border-b border-slate-700 pb-1 mb-1 text-[11px] text-slate-300">
                                        Umur {{ group.range }}
                                    </p>
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full" style="background-color: #030596"></span> Laki-laki:
                                        </span>
                                        <span class="font-bold">{{ formatNumber(group.laki_laki) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mt-0.5">
                                        <span class="flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full" style="background-color: #960323"></span> Perempuan:
                                        </span>
                                        <span class="font-bold">{{ formatNumber(group.perempuan) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mt-1 pt-1 border-t border-slate-700/50 font-extrabold text-blue-400">
                                        <span>Total:</span>
                                        <span>{{ formatNumber(group.laki_laki + group.perempuan) }}</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-8 flex justify-center gap-6 text-xs font-semibold">
                    <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                        <span class="h-3 w-3 rounded-sm" style="background-color: #030596"></span>
                        Laki-Laki
                    </span>
                    <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                        <span class="h-3 w-3 rounded-sm" style="background-color: #960323"></span>
                        Perempuan
                    </span>
                </div>
            </section>
        </div>

        <!-- -------------------- TAB 2: DPRD MEMBERS -------------------- -->
        <div v-if="activeTab === 'dprd'" class="space-y-6">
            
            <!-- Sleek Leadership Banner Card -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:120ms]">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-400 mb-4 block">Struktur Pimpinan</span>
                <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white mb-4">Pimpinan DPRD Kabupaten Bangka Tengah (Periode {{ dprd.periode }})</h3>
                
                <div class="grid gap-4 sm:grid-cols-3">
                    <!-- Ketua -->
                    <div class="relative overflow-hidden rounded-xl border border-blue-100 bg-blue-50/20 p-4 dark:border-blue-950/20 dark:bg-blue-950/10">
                        <div class="absolute -right-3 -top-3 text-4xl opacity-10 select-none"></div>
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Ketua DPRD</span>
                        <h4 class="mt-1 font-extrabold text-sm text-slate-800 dark:text-slate-200">Batianus, S.E., M.M.</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Partai Golkar</p>
                    </div>

                    <!-- Wakil Ketua I -->
                    <div class="relative overflow-hidden rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Wakil Ketua I</span>
                        <h4 class="mt-1 font-extrabold text-sm text-slate-800 dark:text-slate-200">H. Korari Suwondo, S.H.</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">PDI Perjuangan</p>
                    </div>

                    <!-- Wakil Ketua II -->
                    <div class="relative overflow-hidden rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Wakil Ketua II</span>
                        <h4 class="mt-1 font-extrabold text-sm text-slate-800 dark:text-slate-200">Jumri</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Partai Gerindra</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                
                <!-- Left: DPRD stats cards & seat distribution list -->
                <div class="lg:col-span-1 space-y-5 animate-fade-in-up [animation-delay:150ms]">
                    
                    <!-- Card: Total DPRD Members -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600 dark:bg-blue-500" />
                        <div class="pl-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Anggota DPRD</span>
                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                <AnimatedNumber :value="dprd.total_kursi"></AnimatedNumber> <span class="text-lg font-bold text-slate-400 dark:text-slate-500">Kursi</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Periode Jabatan {{ dprd.periode }}</p>
                        </div>
                    </div>

                    <!-- Dapil Seat Allocations list -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Rincian Pembagian Kursi</span>
                        <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white mb-4">Sebaran per Dapil</h3>
                        
                        <div class="space-y-4">
                            <div 
                                v-for="dapil in dprd.dapil_list" 
                                :key="dapil.no"
                                class="p-3.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50"
                            >
                                <div class="flex items-center justify-between font-bold text-sm text-slate-800 dark:text-slate-200">
                                    <div class="flex items-center gap-2">
                                        <span 
                                            class="w-2.5 h-2.5 rounded-full"
                                            :class="[
                                                dapil.no === 1 ? 'bg-cyan-500' : '',
                                                dapil.no === 2 ? 'bg-indigo-500' : '',
                                                dapil.no === 3 ? 'bg-amber-500' : ''
                                            ]"
                                        ></span>
                                        <span>{{ formatDapilName(dapil.nama) }}</span>
                                    </div>
                                    <span class="text-blue-600 dark:text-blue-400 font-extrabold">{{ dapil.jumlah_kursi }} Kursi</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium pl-4.5">
                                    Kecamatan: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ dapil.wilayah }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right: Interactive Parliament Bar Chart -->
                <section class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 flex flex-col justify-between animate-fade-in-up [animation-delay:200ms]">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Peta Parlemen</span>
                            <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                                Jumlah Kursi DPRD per Wilayah Pemilihan
                            </h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Visualisasi sebaran kursi legislatif. Arahkan kursor pada bar untuk melihat daftar nama legislator.
                            </p>
                        </div>
                        
                        <!-- Gender Pills Filter & Stats -->
                        <div class="shrink-0 flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl border border-slate-200/50 dark:border-slate-700/50 shadow-sm text-xs font-bold">
                            <button 
                                @click="selectedGender = 'all'"
                                :class="[
                                    selectedGender === 'all' 
                                        ? 'bg-white text-slate-900 dark:bg-slate-900 dark:text-white shadow-sm' 
                                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                                    'px-3 py-1.5 rounded-lg transition cursor-pointer'
                                ]"
                            >
                                Semua
                            </button>
                            <button 
                                @click="selectedGender = 'Laki-laki'"
                                :class="[
                                    selectedGender === 'Laki-laki' 
                                        ? 'bg-sky-500 text-white shadow-sm' 
                                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                                    'px-3 py-1.5 rounded-lg transition cursor-pointer'
                                ]"
                            >
                                Laki-Laki ({{ dprdGenderCounts.male }})
                            </button>
                            <button 
                                @click="selectedGender = 'Perempuan'"
                                :class="[
                                    selectedGender === 'Perempuan' 
                                        ? 'bg-rose-500 text-white shadow-sm' 
                                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                                    'px-3 py-1.5 rounded-lg transition cursor-pointer'
                                ]"
                            >
                                Perempuan ({{ dprdGenderCounts.female }})
                            </button>
                        </div>
                    </div>

                    <!-- Parliament Bar Chart -->
                    <div class="flex flex-col justify-center py-6 h-full min-h-[280px]">
                        <div class="space-y-6 w-full max-w-lg mx-auto">
                            <div 
                                v-for="(bar, idx) in dprdBarChartData" 
                                :key="bar.label"
                                class="relative group"
                                @mouseenter="hoveredDprdBar = idx"
                                @mouseleave="hoveredDprdBar = null"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                            {{ bar.label }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold">
                                            Kecamatan: {{ bar.wilayah }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 px-2.5 py-1 rounded-lg">
                                        {{ bar.count }} / {{ bar.max }} Kursi
                                    </span>
                                </div>
                                
                                <!-- Progress/Bar Track -->
                                <div class="w-full bg-slate-100 dark:bg-slate-800 h-5 rounded-full overflow-hidden shadow-inner border border-slate-200/20">
                                    <div 
                                        class="h-full rounded-full transition-all duration-700 ease-out animate-grow-width shadow-sm"
                                        :class="bar.colorClass"
                                        :style="{ width: `${(bar.count / bar.max) * 100}%` }"
                                    ></div>
                                </div>

                                <!-- Tooltip listing members in this Dapil -->
                                <div 
                                    v-if="hoveredDprdBar === idx && bar.members.length > 0"
                                    class="absolute left-0 right-0 top-full mt-2 bg-slate-900/95 dark:bg-slate-950/95 text-white text-xs rounded-2xl p-4 border border-slate-850 dark:border-slate-700 shadow-2xl z-30 backdrop-blur-md"
                                >
                                    <p class="font-extrabold text-blue-400 border-b border-slate-800/80 pb-1.5 mb-2 flex justify-between items-center">
                                        <span>Daftar Legislator - {{ bar.label }}</span>
                                        <span class="text-[10px] bg-slate-855 px-2 py-0.5 rounded text-white/70">{{ bar.count }} Orang</span>
                                    </p>
                                    <div class="max-h-40 overflow-y-auto custom-scrollbar pr-1 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1.5 text-[11px] text-slate-300">
                                        <div 
                                            v-for="member in bar.members" 
                                            :key="member.name"
                                            class="flex justify-between items-center py-0.5 border-b border-slate-800/40 last:border-0"
                                        >
                                            <span class="font-semibold truncate max-w-[130px]">{{ member.name }}</span>
                                            <span 
                                                class="text-[9px] px-1.5 py-0.2 rounded font-bold shrink-0"
                                                :class="member.gender === 'Laki-laki' ? 'bg-sky-500/20 text-sky-300' : 'bg-rose-500/20 text-rose-300'"
                                            >
                                                {{ member.gender === 'Laki-laki' ? 'L' : 'P' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Legend -->
                    <div class="flex flex-wrap justify-center gap-4 text-xs font-semibold border-t border-slate-100 dark:border-slate-800/80 pt-4">
                        <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                            <span class="w-3 h-3 rounded-full bg-cyan-500"></span>
                            Dapil 1 (11 Kursi)
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                            <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                            Dapil 2 (10 Kursi)
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            Dapil 3 (9 Kursi)
                        </span>
                    </div>
                </section>

            </div>

            <!-- DPRD Members List Table -->
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:250ms]">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Daftar Legislator</span>
                        <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                            Daftar Anggota DPRD Kabupaten Bangka Tengah
                        </h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Menampilkan seluruh anggota DPRD periode 2024/2029, diurutkan berdasarkan jumlah kursi Dapil terbanyak.
                        </p>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-inner">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="w-12 px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">NO</th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">NAMA ANGGOTA</th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">DAERAH PEMILIHAN</th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">JENIS KELAMIN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                <tr 
                                    v-for="(member, index) in filteredDprdMembers" 
                                    :key="member.name + '-' + member.party"
                                    class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
                                >
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-400">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-800 dark:text-slate-200">
                                        {{ member.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span 
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset"
                                            :class="[
                                                member.dapil === 'Dapil 1' ? 'bg-cyan-50 text-cyan-700 ring-cyan-600/20 dark:bg-cyan-950/20 dark:text-cyan-400 dark:ring-cyan-500/30' : '',
                                                member.dapil === 'Dapil 2' ? 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-950/20 dark:text-indigo-400 dark:ring-indigo-500/30' : '',
                                                member.dapil === 'Dapil 3' ? 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/20 dark:text-amber-400 dark:ring-amber-500/30' : ''
                                            ]"
                                        >
                                            {{ formatDapilName(member.dapil) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-600 dark:text-slate-400">
                                        {{ member.gender === 'Laki-laki' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <!-- -------------------- TAB 3: DESA & KECAMATAN -------------------- -->
        <div v-if="activeTab === 'desa'" class="space-y-6">
            <!-- Cards Grid -->
            <div class="grid gap-5 sm:grid-cols-2 animate-fade-in-up [animation-delay:150ms]">
                
                <!-- Card 1: Total Kecamatan -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600 dark:bg-blue-500" />
                    <div class="pl-2">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-400">Kecamatan</span>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            <AnimatedNumber :value="desaKecamatan.total_kecamatan"></AnimatedNumber>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Kecamatan aktif di Bangka Tengah</p>
                    </div>
                </div>

                <!-- Card 2: Total Desa -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500 dark:bg-emerald-400" />
                    <div class="pl-2">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-400">Desa</span>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            <AnimatedNumber :value="desaKecamatan.total_desa"></AnimatedNumber>
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Total desa terdaftar</p>
                    </div>
                </div>

            </div>

            <!-- Table and Filters Card -->
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
                
                <!-- Filters Header Section -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Data Desa</span>
                        <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                            Informasi Wilayah Administratif Desa
                        </h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Cari dan filter data koordinat GPS (Google Maps) serta rencana nama domain web desa.
                        </p>
                    </div>
                    
                    <!-- Filters Grid -->
                    <div class="flex flex-wrap items-center gap-3">
                        
                        <!-- Search Bar with Suggestions -->
                        <div class="relative w-full sm:w-64">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari desa..."
                                    @focus="showSuggestions = true"
                                    @blur="handleBlur"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-9 pr-8 text-sm font-semibold text-slate-700 shadow-sm transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-500"
                                />
                                <span class="absolute left-3 top-2.5 text-slate-400 dark:text-slate-500">🔍</span>
                                <button
                                    v-if="searchQuery"
                                    @click="searchQuery = ''"
                                    class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 text-xs font-bold cursor-pointer"
                                >
                                    ✕
                                </button>
                            </div>

                            <!-- Suggestions List -->
                            <div 
                                v-if="showSuggestions && suggestions.length > 0" 
                                class="absolute left-0 right-0 mt-1 z-30 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-xl overflow-hidden animate-fade-in"
                            >
                                <button
                                    v-for="s in suggestions"
                                    :key="s.desa"
                                    @mousedown="selectSuggestion(s)"
                                    class="w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800/40 last:border-b-0 flex justify-between items-center cursor-pointer"
                                >
                                    <span>{{ s.desa }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                                        {{ s.kecamatan }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Filter for Kecamatan -->
                        <div class="w-full sm:w-52">
                            <select
                                v-model="selectedKecamatan"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-4 pr-10 text-xs font-bold text-slate-700 shadow-sm focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 cursor-pointer"
                            >
                                <option 
                                    v-for="kec in availableKecamatan" 
                                    :key="kec.value" 
                                    :value="kec.value"
                                >
                                    {{ kec.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Reset filters button -->
                        <button
                            v-if="searchQuery || selectedKecamatan"
                            @click="clearFilters"
                            class="px-3.5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Reset
                        </button>

                    </div>
                </div>

                <!-- Pagination Page size selector -->
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mt-4">
                    <div>
                        Menampilkan <span class="font-bold text-slate-700 dark:text-slate-300">{{ paginatedVillages.length }}</span> dari <span class="font-bold text-slate-700 dark:text-slate-300">{{ totalItems }}</span> desa
                    </div>
                    <div class="flex items-center gap-2">
                        <span>Tampilkan:</span>
                        <select 
                            v-model="itemsPerPage" 
                            class="rounded-lg border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800 text-xs py-1 px-2 font-bold cursor-pointer focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option :value="10">10 Baris</option>
                            <option :value="20">20 Baris</option>
                            <option :value="50">50 Baris</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                </div>

                <!-- Table Village list -->
                <div class="mt-4 overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-inner">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="w-12 px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">NO</th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">DESA</th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">KECAMATAN</th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-slate-400">RENCANA DOMAIN</th>
                                    <th scope="col" class="px-4 py-3 text-center font-bold uppercase tracking-wider text-slate-400">GOOGLE MAPS</th>
                                    <th scope="col" class="px-4 py-3 text-center font-bold uppercase tracking-wider text-slate-400">WEB DESA</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                <tr 
                                    v-for="(row, index) in paginatedVillages" 
                                    :key="row.kecamatan + '-' + row.desa"
                                    class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
                                >
                                    <!-- Index No -->
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-400">
                                        {{ itemsPerPage === 'all' ? (index + 1) : ((currentPage - 1) * itemsPerPage + index + 1) }}
                                    </td>
                                    
                                    <!-- Village Name -->
                                    <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-800 dark:text-slate-200">
                                        {{ row.desa }}
                                    </td>

                                    <!-- Kecamatan -->
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-bold ring-1 ring-inset"
                                            :class="[
                                                row.kecamatan === 'Koba' ? 'bg-cyan-50/50 text-cyan-700 ring-cyan-600/20 dark:bg-cyan-950/20 dark:text-cyan-400 dark:ring-cyan-500/30' : '',
                                                row.kecamatan === 'Pangkalanbaru' ? 'bg-indigo-50/50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-950/20 dark:text-indigo-400 dark:ring-indigo-500/30' : '',
                                                row.kecamatan === 'Namang' ? 'bg-amber-50/50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/20 dark:text-amber-400 dark:ring-amber-500/30' : '',
                                                row.kecamatan === 'Lubuk Besar' ? 'bg-purple-50/50 text-purple-700 ring-purple-600/20 dark:bg-purple-950/20 dark:text-purple-400 dark:ring-purple-500/30' : '',
                                                row.kecamatan === 'Simpang Katis' ? 'bg-rose-50/50 text-rose-700 ring-rose-600/20 dark:bg-rose-950/20 dark:text-rose-400 dark:ring-rose-500/30' : '',
                                                row.kecamatan === 'Sungai Selan' ? 'bg-emerald-50/50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/20 dark:text-emerald-400 dark:ring-emerald-500/30' : ''
                                            ]"
                                        >
                                            {{ row.kecamatan === 'Pangkalanbaru' ? 'Pangkalan Baru' : row.kecamatan }}
                                        </span>
                                    </td>

                                    <!-- Domain Name -->
                                    <td class="whitespace-nowrap px-4 py-3 font-mono font-medium text-slate-500 dark:text-slate-400">
                                        {{ row.domain }}
                                    </td>

                                    <!-- Google Maps Button -->
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <a 
                                            :href="row.gmaps" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-[10px] font-bold text-emerald-700 transition hover:bg-emerald-100 hover:text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 dark:hover:bg-emerald-950/50 shadow-sm"
                                        >
                                            <span>🗺️</span>
                                            <span>Peta</span>
                                        </a>
                                    </td>

                                    <!-- Web Desa Button -->
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <a 
                                            :href="'https://' + row.domain" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1.5 text-[10px] font-bold text-blue-700 transition hover:bg-blue-100 hover:text-blue-800 dark:bg-blue-950/30 dark:text-blue-400 dark:hover:bg-blue-950/50 shadow-sm"
                                        >
                                            <span>🌐</span>
                                            <span>Kunjungi</span>
                                        </a>
                                    </td>
                                </tr>

                                <tr v-if="paginatedVillages.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-bold">
                                        Tidak ada desa yang cocok dengan pencarian atau filter Anda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Table Pagination Footer Controls -->
                <div v-if="itemsPerPage !== 'all' && totalPages > 1" class="flex items-center justify-between mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button
                        @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="rounded-xl border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 transition hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Sebelumnya
                    </button>
                    
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        Halaman {{ currentPage }} dari {{ totalPages }}
                    </span>

                    <button
                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="rounded-xl border border-slate-200 dark:border-slate-800 px-3.5 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 transition hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Selanjutnya
                    </button>
                </div>

            </section>
        </div>

    </div>
</template>

<style scoped>
/* Scrollbar Customization */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animations */
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
@keyframes growHeight {
    from {
        height: 0%;
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.animate-grow-height {
    animation: growHeight 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
