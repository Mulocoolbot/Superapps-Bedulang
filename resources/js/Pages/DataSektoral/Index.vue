<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import Footer from '@/Components/Front/Footer.vue'

// ==========================================
// API & State
// ==========================================
const currentStep = ref(1) // 1: Urusan, 2: SubUrusan, 3: Indikator, 4: Data
const isAnimating = ref(false)

const urusanList = ref([])
const subUrusanList = ref([])
const indikatorList = ref([])
const sektoralData = ref(null)

const selectedUrusan = ref(null)
const selectedSubUrusan = ref(null)
const selectedIndikator = ref(null)

// Default year range: current year to 4 years prior (e.g. 2026 to 2022)
const currentYear = new Date().getFullYear()
const tahunAkhir = ref(currentYear)
const tahunAwal = ref(currentYear - 4)

// Pagination states
const currentPage = ref(1)
const itemsPerPage = 10

const isLoading = ref(false)
const errorMessage = ref('')

// ==========================================
// Animation & Transition States
// ==========================================
const expandedCard = ref({
    active: false,
    top: 0,
    left: 0,
    width: 0,
    height: 0,
    title: '',
    colorClass: '',
    scaling: false,
    fading: false,
})
const showBackAnimation = ref(false)
const slideDirection = ref('next') // 'next' or 'prev'
const slideAnimKey = ref(0) // Triggers slide animation
const isCarouselSliding = ref(false)

// Colors for cards
const cardColors = [
    'from-blue-600 to-indigo-700',
    'from-emerald-500 to-teal-700',
    'from-amber-500 to-orange-700',
    'from-purple-600 to-violet-800',
    'from-pink-500 to-rose-700',
    'from-cyan-500 to-blue-700',
    'from-indigo-500 to-purple-700',
    'from-rose-500 to-red-700'
]

// ==========================================
// Carousel Logic (For items > 4)
// ==========================================
const virtualOffset = ref(1000)

const getVisibleCards = (list) => {
    if (!list || list.length === 0) return []
    if (list.length <= 4) return list

    const result = []
    const len = list.length
    for (let i = 0; i < 4; i++) {
        const idx = (virtualOffset.value + i) % len
        result.push(list[idx])
    }
    return result
}

const nextSlide = () => {
    isCarouselSliding.value = true
    slideDirection.value = 'next'
    slideAnimKey.value++
    virtualOffset.value++
}

const prevSlide = () => {
    isCarouselSliding.value = true
    slideDirection.value = 'prev'
    slideAnimKey.value++
    virtualOffset.value--
}

// ==========================================
// API Fetchers
// ==========================================
async function fetchUrusan() {
    isLoading.value = true
    try {
        const res = await fetch('/api/data-sektoral/urusan')
        const json = await res.json()
        if (json.success) {
            urusanList.value = json.data.map((item, index) => ({
                ...item,
                label: item.urusan,
                colorClass: cardColors[index % cardColors.length]
            }))
        }
    } catch (e) {
        errorMessage.value = 'Gagal memuat urusan'
    } finally {
        isLoading.value = false
    }
}

async function fetchSubUrusan(urusan) {
    isLoading.value = true
    try {
        const res = await fetch(`/api/data-sektoral/sub-urusan/${encodeURIComponent(urusan)}`)
        const json = await res.json()
        if (json.success) {
            virtualOffset.value = 1000
            subUrusanList.value = json.data.map((item, index) => ({
                ...item,
                label: item.sub_urusan,
                colorClass: cardColors[index % cardColors.length]
            }))
        }
    } catch (e) {
        errorMessage.value = 'Gagal memuat sub urusan'
    } finally {
        isLoading.value = false
    }
}

async function fetchIndikator(urusan, subUrusan) {
    isLoading.value = true
    try {
        const res = await fetch(`/api/data-sektoral/indikator/${encodeURIComponent(urusan)}/${encodeURIComponent(subUrusan)}`)
        const json = await res.json()
        if (json.success) {
            virtualOffset.value = 1000
            indikatorList.value = json.data.map((item, index) => ({
                ...item,
                label: item.indikator,
                colorClass: cardColors[index % cardColors.length]
            }))
        }
    } catch (e) {
        errorMessage.value = 'Gagal memuat indikator'
    } finally {
        isLoading.value = false
    }
}

async function fetchData() {
    if (!selectedUrusan.value || !selectedSubUrusan.value || !selectedIndikator.value) return
    isLoading.value = true
    sektoralData.value = null
    currentPage.value = 1

    try {
        const url = `/api/data-sektoral/data/${encodeURIComponent(selectedUrusan.value)}/${encodeURIComponent(selectedSubUrusan.value)}/${encodeURIComponent(selectedIndikator.value)}/${tahunAwal.value}/${tahunAkhir.value}`
        const res = await fetch(url)
        const json = await res.json()
        if (json.success) {
            sektoralData.value = json.data
        }
    } catch (e) {
        errorMessage.value = 'Gagal memuat data sektoral'
    } finally {
        isLoading.value = false
    }
}

// Watch automatic year changes
watch([tahunAwal, tahunAkhir], () => {
    if (currentStep.value === 4 && tahunAwal.value <= tahunAkhir.value) {
        fetchData()
    }
})

// ==========================================
// Card Selection & Smooth Expansion Animation
// ==========================================

const handleCardClick = async (event, item, step) => {
    if (isAnimating.value) return
    isAnimating.value = true

    // 1. Capture exact position & size of clicked card
    const rect = event.currentTarget.getBoundingClientRect()
    expandedCard.value = {
        active: true,
        top: rect.top,
        left: rect.left,
        width: rect.width,
        height: rect.height,
        title: item.label,
        colorClass: item.colorClass,
        scaling: false,
        fading: false
    }

    // 2. Pre-fetch next level data immediately in background
    let fetchPromise = null
    if (step === 1) {
        selectedUrusan.value = item.label
        fetchPromise = fetchSubUrusan(item.label)
    } else if (step === 2) {
        selectedSubUrusan.value = item.label
        fetchPromise = fetchIndikator(selectedUrusan.value, item.label)
    } else if (step === 3) {
        selectedIndikator.value = item.label
        fetchPromise = fetchData()
    }

    await nextTick()

    // 3. Expand overlay card to fullscreen while opaque
    setTimeout(() => {
        expandedCard.value.top = 0
        expandedCard.value.left = 0
        expandedCard.value.width = window.innerWidth
        expandedCard.value.height = window.innerHeight
        expandedCard.value.scaling = true
    }, 20)

    // 4. While overlay covers screen (at 450ms), render next step cards UNDERNEATH!
    setTimeout(async () => {
        if (fetchPromise) await fetchPromise
        isCarouselSliding.value = false
        if (step === 1) currentStep.value = 2
        else if (step === 2) currentStep.value = 3
        else if (step === 3) currentStep.value = 4

        // 5. Now fade out opacity of overlay card AFTER next step cards are rendered underneath!
        expandedCard.value.fading = true
    }, 450)

    // 6. Complete transition and clean up overlay
    setTimeout(() => {
        expandedCard.value.active = false
        expandedCard.value.scaling = false
        expandedCard.value.fading = false
        isAnimating.value = false
    }, 1350)
}

const handleBack = () => {
    if (isAnimating.value || currentStep.value === 1) return
    isAnimating.value = true
    showBackAnimation.value = true

    setTimeout(() => {
        isCarouselSliding.value = false
        if (currentStep.value === 4) {
            currentStep.value = 3
            sektoralData.value = null
        } else if (currentStep.value === 3) {
            currentStep.value = 2
            indikatorList.value = []
        } else if (currentStep.value === 2) {
            currentStep.value = 1
            subUrusanList.value = []
        }
        showBackAnimation.value = false
        isAnimating.value = false
    }, 500)
}

const filteredTableData = computed(() => {
    if (!sektoralData.value?.data) return []
    return sektoralData.value.data
})

// ==========================================
// Pagination Logic
// ==========================================
const groupedData = computed(() => {
    if (!filteredTableData.value) return []
    const sorted = [...filteredTableData.value].sort((a, b) => {
        const levelsA = [a.sub_indikator_1, a.sub_indikator_2, a.sub_indikator_3, a.sub_indikator_4, a.sub_indikator_5, a.kecamatan].filter(v => v !== null && v !== undefined && v !== '')
        const levelsB = [b.sub_indikator_1, b.sub_indikator_2, b.sub_indikator_3, b.sub_indikator_4, b.sub_indikator_5, b.kecamatan].filter(v => v !== null && v !== undefined && v !== '')
        
        const minLength = Math.min(levelsA.length, levelsB.length)
        for (let i = 0; i < minLength; i++) {
            if (levelsA[i] !== levelsB[i]) {
                return levelsA[i].localeCompare(levelsB[i])
            }
        }
        
        if (levelsA.length !== levelsB.length) {
            return levelsB.length - levelsA.length // Deeper hierarchy first
        }
        
        return (a.tahun || 0) - (b.tahun || 0)
    })

    const groupsMap = new Map()

    sorted.forEach(row => {
        const levels = [
            row.sub_indikator_1, 
            row.sub_indikator_2, 
            row.sub_indikator_3, 
            row.sub_indikator_4, 
            row.sub_indikator_5, 
            row.kecamatan
        ].filter(v => v !== null && v !== undefined && v !== '')
        
        let parentHierarchy = []
        let leaf = '-'
        
        if (levels.length > 0) {
            parentHierarchy = levels.slice(0, -1)
            leaf = levels[levels.length - 1]
        }

        const parentKey = parentHierarchy.join('|')

        if (!groupsMap.has(parentKey)) {
            groupsMap.set(parentKey, {
                key: parentKey,
                hierarchy: parentHierarchy,
                rows: []
            })
        }
        
        const group = groupsMap.get(parentKey)
        group.rows.push({
            ...row,
            leafName: leaf
        })
    })

    return Array.from(groupsMap.values())
})

const selectedLeaves = ref({})

watch(groupedData, (newGroups) => {
    newGroups.forEach(group => {
        if (!selectedLeaves.value[group.key]) {
            const leaves = [...new Set(group.rows.map(r => r.leafName))]
            if (leaves.length > 0) {
                selectedLeaves.value[group.key] = leaves[0]
            }
        }
    })
}, { immediate: true })

const getGroupUniqueLeaves = (group) => {
    return [...new Set(group.rows.map(r => r.leafName))]
}

const getGroupRows = (group) => {
    const selected = selectedLeaves.value[group.key]
    return group.rows.filter(r => r.leafName === selected)
}

const totalPages = computed(() => Math.ceil(groupedData.value.length / itemsPerPage))

const paginatedGroups = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return groupedData.value.slice(start, start + itemsPerPage)
})

const shouldShowLevel = (index, lvlIdx) => {
    if (index === 0) return true
    const current = paginatedGroups.value[index].hierarchy
    const prev = paginatedGroups.value[index - 1].hierarchy
    
    if (prev.length <= lvlIdx) return true
    
    // If the current branch is a strict prefix of the previous branch 
    // (meaning we jumped UP the tree), we reprint the level to establish context.
    let isStrictPrefix = true
    for (let i = 0; i < current.length; i++) {
        if (current[i] !== prev[i]) {
            isStrictPrefix = false
            break
        }
    }
    
    if (isStrictPrefix) return true
    
    for (let i = 0; i <= lvlIdx; i++) {
        if (current[i] !== prev[i]) return true
    }
    
    return false
}

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++
}

const visiblePageNumbers = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value
    
    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i)
    } else {
        if (current <= 4) {
            pages.push(1, 2, 3, 4, 5, '...', total)
        } else if (current >= total - 3) {
            pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
        } else {
            pages.push(1, '...', current - 1, current, current + 1, '...', total)
        }
    }
    return pages
})

// Init
fetchUrusan()
</script>

<template>
    <Head title="Data Sektoral" />

    <FrontLayout>
        <div class="relative min-h-screen overflow-hidden bg-gray-50 dark:bg-gray-900 pb-16">
            
            <!-- Global Back Button Below Navbar -->
            <transition name="fade">
                <div v-if="currentStep > 1 && !isAnimating" class="container mx-auto px-6 pt-4">
                    <button 
                        @click="handleBack"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-md transition-all hover:-translate-x-1 hover:bg-gray-100 hover:shadow-lg dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 cursor-pointer"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </button>
                </div>
            </transition>

            <!-- Main Content Area -->
            <div class="container mx-auto px-6 py-6">
                
                <!-- STEP 1 - 3: CARDS NAVIGATION CAROUSEL -->
                <div v-if="currentStep <= 3" class="relative">
                    
                    <!-- Section Title -->
                    <div class="mb-10 text-center popup-anim">
                        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ 
                                currentStep === 1 ? 'Pilih Urusan Data Sektoral' : 
                                currentStep === 2 ? 'Pilih Sub Urusan' : 
                                'Pilih Indikator Data' 
                            }}
                        </h1>
                        <p class="mt-3 text-base text-gray-600 dark:text-gray-400">
                            {{ 
                                currentStep === 1 ? 'Silakan pilih jenis urusan untuk melihat data sektoral Kabupaten Bangka Tengah' : 
                                currentStep === 2 ? `Urusan: ${selectedUrusan}` : 
                                `${selectedUrusan} » ${selectedSubUrusan}` 
                            }}
                        </p>
                    </div>

                    <!-- Cards Container with Tall Vertical Glass Floating Arrow Controls -->
                    <div class="relative flex items-center justify-center">
                        
                        <!-- Left Arrow (Tall Vertical Bar, Glassmorphism & Floating) -->
                        <button 
                            v-if="currentStep > 1 && (currentStep === 2 ? subUrusanList.length : indikatorList.length) > 4"
                            @click="prevSlide"
                            class="absolute -left-4 sm:-left-6 z-20 h-full w-12 md:w-16 flex items-center justify-center rounded-2xl border border-white/50 bg-white/40 backdrop-blur-md shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:bg-white/60 dark:border-gray-700/50 dark:bg-gray-800/40 dark:hover:bg-gray-800/60 cursor-pointer group"
                            aria-label="Previous Slide"
                        >
                            <div class="flex items-center justify-center group-hover:scale-125 transition-transform duration-300">
                                <svg class="h-8 w-8 text-gray-800 dark:text-white drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Cards Grid with Smooth Slide Transition -->
                        <div class="overflow-hidden py-2 w-full relative">
                            <Transition :name="slideDirection === 'next' ? 'carousel-right' : 'carousel-left'">
                                <div 
                                    :key="slideAnimKey"
                                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                                >
                                    <template v-for="(item, index) in (
                                        currentStep === 1 ? urusanList : 
                                        currentStep === 2 ? getVisibleCards(subUrusanList) : 
                                        getVisibleCards(indikatorList)
                                    )" :key="item.label + index">
                                        
                                        <div 
                                            @click="handleCardClick($event, item, currentStep)"
                                            class="group cursor-pointer rounded-2xl bg-gradient-to-br p-6 shadow-lg transition-all duration-300 hover:-translate-y-3 hover:shadow-2xl flex flex-col justify-between min-h-[220px] border border-white/20"
                                            :class="[item.colorClass, isCarouselSliding ? '' : 'popup-anim']"
                                            :style="isCarouselSliding ? '' : `animation-delay: ${0.08 * (index + 1)}s`"
                                        >
                                            <div class="text-white/80">
                                                <svg class="h-10 w-10 mb-4 opacity-80 group-hover:scale-110 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-bold leading-snug text-white group-hover:text-white/90">
                                                {{ item.label }}
                                            </h3>
                                        </div>
                                    </template>
                                </div>
                            </Transition>
                        </div>

                        <!-- Right Arrow (Tall Vertical Bar, Glassmorphism & Floating) -->
                        <button 
                            v-if="currentStep > 1 && (currentStep === 2 ? subUrusanList.length : indikatorList.length) > 4"
                            @click="nextSlide"
                            class="absolute -right-4 sm:-right-6 z-20 h-full w-12 md:w-16 flex items-center justify-center rounded-2xl border border-white/50 bg-white/40 backdrop-blur-md shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:bg-white/60 dark:border-gray-700/50 dark:bg-gray-800/40 dark:hover:bg-gray-800/60 cursor-pointer group"
                            aria-label="Next Slide"
                        >
                            <div class="flex items-center justify-center group-hover:scale-125 transition-transform duration-300">
                                <svg class="h-8 w-8 text-gray-800 dark:text-white drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- STEP 4: DATASET TABLE DASHBOARD -->
                <div v-else-if="currentStep === 4" class="popup-anim mt-10">
                    <div class="rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800 lg:p-10 border border-gray-100 dark:border-gray-700">
                        
                        <!-- Header & Title -->
                        <div class="mb-6">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ selectedIndikator }}</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ selectedUrusan }} &raquo; {{ selectedSubUrusan }}
                            </p>
                        </div>
                        
                        <!-- Automatic Filter Controls Bar (Year Range & Sub-Indicator Dropdowns) -->
                        <div class="mb-8 flex flex-wrap items-center justify-between gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                            <!-- Year Inputs (Automatic fetch on change) -->
                            <div class="flex items-center gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tahun Awal</label>
                                    <input 
                                        v-model.number="tahunAwal" 
                                        @change="fetchData"
                                        type="number" 
                                        class="w-24 rounded-lg border-gray-300 py-1.5 text-sm font-semibold dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                    >
                                </div>
                                <span class="text-gray-400 font-bold mt-4">-</span>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Tahun Akhir</label>
                                    <input 
                                        v-model.number="tahunAkhir" 
                                        @change="fetchData"
                                        type="number" 
                                        class="w-24 rounded-lg border-gray-300 py-1.5 text-sm font-semibold dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                    >
                                </div>
                            </div>

                        </div>

                        <!-- Grouped Tables -->
                        <div v-if="isLoading" class="py-12 text-center w-full">
                            <svg class="mx-auto h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                        <div v-else-if="paginatedGroups?.length">
                            <div v-for="(group, gIdx) in paginatedGroups" :key="gIdx" class="mb-8 last:mb-0">
                                <!-- Hierarchy Title & Dropdown -->
                                <div class="mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="text-gray-900 dark:text-gray-100 font-bold leading-relaxed text-sm">
                                        <template v-for="(level, lvlIdx) in group.hierarchy" :key="lvlIdx">
                                            <div v-if="shouldShowLevel(gIdx, lvlIdx)" :style="{ marginLeft: (lvlIdx * 12) + 'px' }">
                                                {{ lvlIdx === 0 ? level : '- ' + level }}
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <div v-if="getGroupUniqueLeaves(group).length > 0" class="shrink-0 w-full sm:w-auto">
                                        <select 
                                            v-model="selectedLeaves[group.key]"
                                            class="w-full sm:w-auto rounded-lg border-gray-300 py-1.5 pl-3 pr-10 text-sm font-medium dark:bg-gray-800 dark:border-gray-600 dark:text-white cursor-pointer bg-white"
                                        >
                                            <option v-for="leaf in getGroupUniqueLeaves(group)" :key="leaf" :value="leaf">
                                                {{ leaf }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Group Table -->
                                <div class="overflow-x-auto rounded-lg border border-[#c2dfbc] dark:border-gray-700 shadow-sm">
                                    <table class="w-full text-left text-sm whitespace-nowrap">
                                        <thead class="bg-[#d5ebd1] text-gray-900 dark:bg-gray-700/50 dark:text-gray-200 uppercase font-bold text-[11px] tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-600 w-24">TAHUN</th>
                                                <th class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-600">DATA</th>
                                                <th class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-600 w-32">SATUAN</th>
                                                <th class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-600">SUMBER DATA</th>
                                                <th class="px-4 py-3">KETERANGAN</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#c2dfbc] dark:divide-gray-700 bg-white dark:bg-gray-800">
                                            <tr v-for="(row, rIdx) in getGroupRows(group)" :key="rIdx" class="hover:bg-[#f2f8f0] dark:hover:bg-gray-700/50 transition">
                                                <td class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-700 font-semibold">{{ row.tahun || '-' }}</td>
                                                <td class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-700">{{ row.dataset || '0' }}</td>
                                                <td class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-700">{{ row.satuan || '-' }}</td>
                                                <td class="px-4 py-3 border-r border-[#c2dfbc] dark:border-gray-700">{{ row.sumber_data || '-' }}</td>
                                                <td class="px-4 py-3">{{ row.keterangan || '--' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-16 text-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-xl">
                            Tidak ada data ditemukan untuk filter yang dipilih.
                        </div>

                        <!-- Table Pagination Controls -->
                        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-700" v-if="groupedData.length > itemsPerPage">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> 
                                sampai <span class="font-bold text-gray-900 dark:text-white">{{ Math.min(currentPage * itemsPerPage, groupedData.length) }}</span> 
                                dari <span class="font-bold text-gray-900 dark:text-white">{{ groupedData.length }}</span> tabel data
                            </div>
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="prevPage" 
                                    :disabled="currentPage === 1"
                                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 cursor-pointer"
                                >
                                    Sebelumnya
                                </button>
                                <div class="flex items-center gap-1">
                                    <template v-for="(page, pIdx) in visiblePageNumbers" :key="pIdx">
                                        <span v-if="page === '...'" class="px-2 text-gray-400 font-bold">...</span>
                                        <button
                                            v-else
                                            @click="currentPage = page"
                                            class="h-8 w-8 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer"
                                            :class="currentPage === page ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                        >
                                            {{ page }}
                                        </button>
                                    </template>
                                </div>
                                <button 
                                    @click="nextPage" 
                                    :disabled="currentPage === totalPages"
                                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 cursor-pointer"
                                >
                                    Selanjutnya
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Absolute Overlay for Fullscreen Card Expansion Animation -->
            <div 
                v-if="expandedCard.active"
                class="fixed z-50 rounded-2xl bg-gradient-to-br shadow-2xl transition-all duration-[900ms] ease-[cubic-bezier(0.25,1,0.5,1)] flex items-center justify-center overflow-hidden"
                :class="[
                    expandedCard.colorClass, 
                    expandedCard.fading ? 'opacity-0 scale-110' : 'opacity-100 scale-100'
                ]"
                :style="{
                    top: expandedCard.top + 'px',
                    left: expandedCard.left + 'px',
                    width: expandedCard.width + 'px',
                    height: expandedCard.height + 'px',
                }"
            >
                <div class="p-8 h-full w-full flex flex-col items-center justify-center text-center max-w-4xl mx-auto">
                     <h3 
                        class="font-extrabold text-white transition-all duration-700 ease-out leading-tight"
                        :class="[
                            expandedCard.scaling ? 'text-3xl md:text-5xl lg:text-6xl scale-105' : 'text-xl md:text-2xl scale-100',
                            expandedCard.fading ? 'opacity-0' : 'opacity-100'
                        ]"
                    >
                        {{ expandedCard.title }}
                    </h3>
                </div>
            </div>

        </div>
        <Footer />
    </FrontLayout>
</template>

<style scoped>
/* Keyframe for Pop Up Entrance */
@keyframes pop-up {
    0% {
        opacity: 0;
        transform: scale(0.85) translateY(24px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.popup-anim {
    animation: pop-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

/* Smooth Carousel Slide Transitions */

/* Right arrow click: cards slide to the RIGHT */
.carousel-right-enter-active,
.carousel-right-leave-active {
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.carousel-right-enter-from {
    transform: translateX(-100%);
}
.carousel-right-leave-active {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}
.carousel-right-leave-to {
    transform: translateX(100%);
}

/* Left arrow click: cards slide to the LEFT */
.carousel-left-enter-active,
.carousel-left-leave-active {
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.carousel-left-enter-from {
    transform: translateX(100%);
}
.carousel-left-leave-active {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}
.carousel-left-leave-to {
    transform: translateX(-100%);
}

/* Fade transition for back button */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
