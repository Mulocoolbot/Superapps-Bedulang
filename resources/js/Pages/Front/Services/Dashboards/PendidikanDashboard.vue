<script setup>
import { ref, computed, watch } from 'vue'

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

// Data references from the dashboard props
const schoolsList = computed(() => props.dashboard?.schools_list ?? [])
const summary = computed(() => props.dashboard?.summary ?? {
    total_paud_schools: 0,
    total_sd_schools: 0,
    total_smp_schools: 0,
    total_paud_students: 0,
    total_sd_students: 0,
    total_smp_students: 0,
    total_paud_teachers: 0,
    total_sd_teachers: 0,
    total_smp_teachers: 0,
})

const availableKecamatans = computed(() => props.dashboard?.available_kecamatans ?? [])
const availableJenjangs = computed(() => props.dashboard?.available_jenjangs ?? ['PAUD', 'SD', 'SMP'])

// Active main tab ('sekolah', 'peserta-didik', 'pendidik')
const activeMainTab = ref('sekolah')

// Filter states for Tab 1 (Sekolah)
const filterSekolahKecamatan = ref('all')
const filterSekolahJenjang = ref('all')
const searchSekolahQuery = ref('')

// Filter states for Tab 2 (Peserta Didik)
const filterSiswaJenjang = ref('all')
const filterSiswaKecamatan = ref('all')
const searchSiswaQuery = ref('')

// Filter states for Tab 3 (Pendidik)
const filterGuruJenjang = ref('all')
const filterGuruKecamatan = ref('all')
const searchGuruQuery = ref('')

// Pagination states
const currentPageSekolah = ref(1)
const currentPageSiswa = ref(1)
const currentPageGuru = ref(1)
const perPage = ref(5)

// Formatting helpers
const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

// Reset pagination when active tab or filters change
watch([activeMainTab, filterSekolahKecamatan, filterSekolahJenjang, searchSekolahQuery], () => {
    currentPageSekolah.value = 1
})
watch([activeMainTab, filterSiswaJenjang, filterSiswaKecamatan, searchSiswaQuery], () => {
    currentPageSiswa.value = 1
})
watch([activeMainTab, filterGuruJenjang, filterGuruKecamatan, searchGuruQuery], () => {
    currentPageGuru.value = 1
})

// Filtered Lists
const filteredSekolah = computed(() => {
    return schoolsList.value.filter(school => {
        if (filterSekolahKecamatan.value !== 'all' && school.kecamatan !== filterSekolahKecamatan.value) return false
        if (filterSekolahJenjang.value !== 'all' && school.jenjang !== filterSekolahJenjang.value) return false
        if (searchSekolahQuery.value.trim() !== '') {
            const q = searchSekolahQuery.value.toLowerCase()
            return school.nama.toLowerCase().includes(q) || school.kecamatan.toLowerCase().includes(q)
        }
        return true
    })
})

const filteredSiswa = computed(() => {
    return schoolsList.value.filter(school => {
        if (filterSiswaJenjang.value !== 'all' && school.jenjang !== filterSiswaJenjang.value) return false
        if (filterSiswaKecamatan.value !== 'all' && school.kecamatan !== filterSiswaKecamatan.value) return false
        if (searchSiswaQuery.value.trim() !== '') {
            const q = searchSiswaQuery.value.toLowerCase()
            return school.nama.toLowerCase().includes(q) || school.kecamatan.toLowerCase().includes(q)
        }
        return true
    })
})

const filteredGuru = computed(() => {
    return schoolsList.value.filter(school => {
        if (filterGuruJenjang.value !== 'all' && school.jenjang !== filterGuruJenjang.value) return false
        if (filterGuruKecamatan.value !== 'all' && school.kecamatan !== filterGuruKecamatan.value) return false
        if (searchGuruQuery.value.trim() !== '') {
            const q = searchGuruQuery.value.toLowerCase()
            return school.nama.toLowerCase().includes(q) || school.kecamatan.toLowerCase().includes(q)
        }
        return true
    })
})

// Pagination Helper Methods
const getPaginatedList = (list, page) => {
    const start = (page - 1) * perPage.value
    return list.slice(start, start + perPage.value)
}

const getTotalPages = (list) => {
    return Math.ceil(list.length / perPage.value) || 1
}

// Badge coloring helpers
const getJenjangBadgeClass = (jenjang) => {
    switch (jenjang) {
        case 'PAUD':
            return 'bg-pink-50 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300 border-pink-100 dark:border-pink-900/40'
        case 'SD':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-100 dark:border-blue-900/40'
        case 'SMP':
            return 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-100 dark:border-indigo-900/40'
        default:
            return 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
}
</script>

<template>
    <div class="space-y-8">
        <!-- 1. MAIN TABS SWITCHER -->
        <div class="animate-fade-in-up [animation-delay:100ms] flex justify-center">
            <div class="inline-flex p-1 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/80 shadow-sm max-w-lg w-full">
                <button
                    @click="activeMainTab = 'sekolah'"
                    class="flex-1 py-3 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer text-center flex items-center justify-center gap-2 select-none"
                    :class="activeMainTab === 'sekolah'
                        ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-md ring-1 ring-indigo-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    Sekolah
                </button>
                <button
                    @click="activeMainTab = 'peserta-didik'"
                    class="flex-1 py-3 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer text-center flex items-center justify-center gap-2 select-none"
                    :class="activeMainTab === 'peserta-didik'
                        ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-md ring-1 ring-indigo-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    Peserta Didik
                </button>
                <button
                    @click="activeMainTab = 'pendidik'"
                    class="flex-1 py-3 px-3 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer text-center flex items-center justify-center gap-2 select-none"
                    :class="activeMainTab === 'pendidik'
                        ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-md ring-1 ring-indigo-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    Pendidik
                </button>
            </div>
        </div>

        <!-- 2. TAB CONTENT -->
        <Transition name="page-fade" mode="out-in">
            <!-- TAB: SEKOLAH -->
            <div v-if="activeMainTab === 'sekolah'" key="tab-sekolah" class="space-y-8">
                <!-- Statistics cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 animate-fade-in-up [animation-delay:150ms]">
                    <!-- PAUD Schools -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-pink-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Sekolah PAUD</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ summary.total_paud_schools }} <span class="text-xs font-medium text-slate-400">Unit</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-pink-600 dark:text-pink-400">Pendidikan Anak Usia Dini</p>
                            </div>
                            
                        </div>
                    </div>

                    <!-- SD Schools -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Sekolah SD</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ summary.total_sd_schools }} <span class="text-xs font-medium text-slate-400">Sekolah</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400">Sekolah Dasar</p>
                            </div>
                            
                        </div>
                    </div>

                    <!-- SMP Schools -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Sekolah SMP</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ summary.total_smp_schools }} <span class="text-xs font-medium text-slate-400">Sekolah</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">Sekolah Menengah Pertama</p>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-md animate-fade-in-up [animation-delay:200ms]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <!-- Search Query -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Cari Sekolah</label>
                            <div class="relative">
                                <input
                                    v-model="searchSekolahQuery"
                                    type="text"
                                    placeholder="Cari nama sekolah..."
                                    class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40 focus:outline-none transition"
                                />
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Kecamatan -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kecamatan</label>
                            <div class="relative">
                                <select
                                    v-model="filterSekolahKecamatan"
                                    class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40"
                                >
                                    <option value="all">Semua Kecamatan</option>
                                    <option v-for="kec in availableKecamatans" :key="kec" :value="kec">
                                        Kec. {{ kec }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Jenjang -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jenjang Sekolah</label>
                            <div class="relative">
                                <select
                                    v-model="filterSekolahJenjang"
                                    class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40"
                                >
                                    <option value="all">Semua Jenjang</option>
                                    <option v-for="jenjang in availableJenjangs" :key="jenjang" :value="jenjang">
                                        {{ jenjang }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-md transition-all duration-300">
                    <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800/60">
                        <h3 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans flex items-center gap-2">
                            <span>📋</span> Tabel Daftar Lembaga Sekolah
                        </h3>
                    </div>

                    <div class="w-full px-3 sm:px-6 py-4">
                        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                            <table class="w-full text-left text-xs sm:text-sm border-collapse min-w-[650px]">
                                <thead class="bg-gradient-to-r from-indigo-50/90 via-purple-50/70 to-indigo-50/50 dark:from-indigo-950/80 dark:via-purple-950/60 dark:to-indigo-950/40 text-indigo-950 dark:text-indigo-200 font-extrabold uppercase text-[11px] tracking-wider border-b border-indigo-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-4 px-3 w-[8%] text-center">No</th>
                                        <th class="py-4 px-4 w-[50%] text-left">Nama Sekolah</th>
                                        <th class="py-4 px-3 w-[21%] text-center">Jenjang</th>
                                        <th class="py-4 px-4 w-[21%] text-center">Kecamatan</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                    <tr
                                        v-for="(school, idx) in getPaginatedList(filteredSekolah, currentPageSekolah)"
                                        :key="school.id"
                                        class="hover:bg-indigo-50/20 dark:hover:bg-indigo-950/20 transition-colors duration-200"
                                    >
                                        <td class="py-3.5 px-3 text-center text-slate-500 dark:text-slate-400 font-bold font-sans">
                                            {{ (currentPageSekolah - 1) * perPage + idx + 1 }}
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2.5">
                                                <div>
                                                    {{ school.nama }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="inline-flex items-center justify-center px-3 py-0.5 rounded-full border text-[10px] font-extrabold tracking-wide uppercase" :class="getJenjangBadgeClass(school.jenjang)">
                                                {{ school.jenjang }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                            {{ school.kecamatan }}
                                        </td>
                                    </tr>

                                    <tr v-if="filteredSekolah.length === 0">
                                        <td colspan="4" class="py-12 text-center text-slate-400 text-sm font-medium">
                                            Tidak ada data sekolah yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 sm:px-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                            Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ filteredSekolah.length > 0 ? (currentPageSekolah - 1) * perPage + 1 : 0 }}</span> - 
                            <span class="font-bold text-slate-800 dark:text-white">{{ Math.min(currentPageSekolah * perPage, filteredSekolah.length) }}</span> dari 
                            <span class="font-bold text-slate-800 dark:text-white">{{ filteredSekolah.length }}</span> data
                        </div>
                        
                        <div class="flex items-center gap-1.5" v-if="getTotalPages(filteredSekolah) > 1">
                            <button
                                v-if="currentPageSekolah > 1"
                                @click="currentPageSekolah--"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            
                            <button
                                v-for="page in getTotalPages(filteredSekolah)"
                                :key="page"
                                @click="currentPageSekolah = page"
                                class="h-8 w-8 rounded-lg text-xs font-bold transition duration-200 flex items-center justify-center"
                                :class="currentPageSekolah === page 
                                    ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white shadow-md' 
                                    : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            >
                                {{ page }}
                            </button>
                            
                            <button
                                v-if="currentPageSekolah < getTotalPages(filteredSekolah)"
                                @click="currentPageSekolah++"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: PESERTA DIDIK -->
            <div v-else-if="activeMainTab === 'peserta-didik'" key="tab-siswa" class="space-y-8">
                <!-- Statistics cards for total students -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 animate-fade-in-up [animation-delay:150ms]">
                    <!-- PAUD Students -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-pink-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Siswa PAUD</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ formatNumber(summary.total_paud_students) }} <span class="text-xs font-medium text-slate-400">Anak</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-pink-600 dark:text-pink-400">Total Siswa PAUD</p>
                            </div>
                            
                        </div>
                    </div>

                    <!-- SD Students -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Siswa SD</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ formatNumber(summary.total_sd_students) }} <span class="text-xs font-medium text-slate-400">Siswa</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400">Total Siswa SD</p>
                            </div>
                        
                        </div>
                    </div>

                    <!-- SMP Students -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Siswa SMP</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ formatNumber(summary.total_smp_students) }} <span class="text-xs font-medium text-slate-400">Siswa</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">Total Siswa SMP</p>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-md animate-fade-in-up [animation-delay:200ms]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <!-- Search Query -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Cari Sekolah</label>
                            <div class="relative">
                                <input
                                    v-model="searchSiswaQuery"
                                    type="text"
                                    placeholder="Cari nama sekolah..."
                                    class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40 focus:outline-none transition"
                                />
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Jenjang -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jenjang Sekolah</label>
                            <div class="relative">
                                <select
                                    v-model="filterSiswaJenjang"
                                    class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40"
                                >
                                    <option value="all">Semua Jenjang</option>
                                    <option v-for="jenjang in availableJenjangs" :key="jenjang" :value="jenjang">
                                        {{ jenjang }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Kecamatan -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kecamatan</label>
                            <div class="relative">
                                <select
                                    v-model="filterSiswaKecamatan"
                                    class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40"
                                >
                                    <option value="all">Semua Kecamatan</option>
                                    <option v-for="kec in availableKecamatans" :key="kec" :value="kec">
                                        Kec. {{ kec }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-md transition-all duration-300">
                    <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800/60">
                        <h3 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans flex items-center gap-2">
                            Tabel Distribusi Peserta Didik per Sekolah
                        </h3>
                    </div>

                    <div class="w-full px-3 sm:px-6 py-4">
                        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                            <table class="w-full text-left text-xs sm:text-sm border-collapse min-w-[650px]">
                                <thead class="bg-gradient-to-r from-indigo-50/90 via-purple-50/70 to-indigo-50/50 dark:from-indigo-950/80 dark:via-purple-950/60 dark:to-indigo-950/40 text-indigo-950 dark:text-indigo-200 font-extrabold uppercase text-[11px] tracking-wider border-b border-indigo-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-4 px-3 w-[8%] text-center">No</th>
                                        <th class="py-4 px-4 w-[40%] text-left">Nama Sekolah</th>
                                        <th class="py-4 px-3 w-[15%] text-center">Jenjang</th>
                                        <th class="py-4 px-3 w-[18%] text-center">Kecamatan</th>
                                        <th class="py-4 px-4 w-[19%] text-center rounded-r-xl">Jumlah Siswa</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                    <tr
                                        v-for="(school, idx) in getPaginatedList(filteredSiswa, currentPageSiswa)"
                                        :key="school.id"
                                        class="hover:bg-indigo-50/20 dark:hover:bg-indigo-950/20 transition-colors duration-200"
                                    >
                                        <td class="py-3.5 px-3 text-center text-slate-500 dark:text-slate-400 font-bold font-sans">
                                            {{ (currentPageSiswa - 1) * perPage + idx + 1 }}
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2.5">
                                        
                                                <div>
                                                    {{ school.nama }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="inline-flex items-center justify-center px-3 py-0.5 rounded-full border text-[10px] font-extrabold tracking-wide uppercase" :class="getJenjangBadgeClass(school.jenjang)">
                                                {{ school.jenjang }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                            {{ school.kecamatan }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-indigo-600 text-white text-xs font-black min-w-[50px] shadow-xs">
                                                {{ formatNumber(school.jumlah_siswa) }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredSiswa.length === 0">
                                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm font-medium">
                                            Tidak ada data sekolah yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 sm:px-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                            Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ filteredSiswa.length > 0 ? (currentPageSiswa - 1) * perPage + 1 : 0 }}</span> - 
                            <span class="font-bold text-slate-800 dark:text-white">{{ Math.min(currentPageSiswa * perPage, filteredSiswa.length) }}</span> dari 
                            <span class="font-bold text-slate-800 dark:text-white">{{ filteredSiswa.length }}</span> data
                        </div>
                        
                        <div class="flex items-center gap-1.5" v-if="getTotalPages(filteredSiswa) > 1">
                            <button
                                v-if="currentPageSiswa > 1"
                                @click="currentPageSiswa--"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            
                            <button
                                v-for="page in getTotalPages(filteredSiswa)"
                                :key="page"
                                @click="currentPageSiswa = page"
                                class="h-8 w-8 rounded-lg text-xs font-bold transition duration-200 flex items-center justify-center"
                                :class="currentPageSiswa === page 
                                    ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white shadow-md' 
                                    : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            >
                                {{ page }}
                            </button>
                            
                            <button
                                v-if="currentPageSiswa < getTotalPages(filteredSiswa)"
                                @click="currentPageSiswa++"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: PENDIDIK -->
            <div v-else key="tab-guru" class="space-y-8">
                <!-- Statistics cards for total teachers -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 animate-fade-in-up [animation-delay:150ms]">
                    <!-- PAUD Teachers -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-pink-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendidik PAUD</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ formatNumber(summary.total_paud_teachers) }} <span class="text-xs font-medium text-slate-400">Guru</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-pink-600 dark:text-pink-400">Total Guru PAUD</p>
                            </div>
                            
                        </div>
                    </div>

                    <!-- SD Teachers -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendidik SD</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ formatNumber(summary.total_sd_teachers) }} <span class="text-xs font-medium text-slate-400">Guru</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400">Total Guru SD</p>
                            </div>
                            
                        </div>
                    </div>

                    <!-- SMP Teachers -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-md dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 group">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-500" />
                        <div class="pl-2 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Pendidik SMP</span>
                                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                                    {{ formatNumber(summary.total_smp_teachers) }} <span class="text-xs font-medium text-slate-400">Guru</span>
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">Total Guru SMP</p>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-md animate-fade-in-up [animation-delay:200ms]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <!-- Search Query -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Cari Sekolah</label>
                            <div class="relative">
                                <input
                                    v-model="searchGuruQuery"
                                    type="text"
                                    placeholder="Cari nama sekolah..."
                                    class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40 focus:outline-none transition"
                                />
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Jenjang -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jenjang Sekolah</label>
                            <div class="relative">
                                <select
                                    v-model="filterGuruJenjang"
                                    class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40"
                                >
                                    <option value="all">Semua Jenjang</option>
                                    <option v-for="jenjang in availableJenjangs" :key="jenjang" :value="jenjang">
                                        {{ jenjang }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Kecamatan -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kecamatan</label>
                            <div class="relative">
                                <select
                                    v-model="filterGuruKecamatan"
                                    class="w-full h-10 appearance-none rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800 px-4 pr-10 text-xs sm:text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/40"
                                >
                                    <option value="all">Semua Kecamatan</option>
                                    <option v-for="kec in availableKecamatans" :key="kec" :value="kec">
                                        Kec. {{ kec }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-md transition-all duration-300">
                    <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800/60">
                        <h3 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white font-sans flex items-center gap-2">
                         Tabel Distribusi Tenaga Pendidik per Sekolah
                        </h3>
                    </div>

                    <div class="w-full px-3 sm:px-6 py-4">
                        <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                            <table class="w-full text-left text-xs sm:text-sm border-collapse min-w-[650px]">
                                <thead class="bg-gradient-to-r from-indigo-50/90 via-purple-50/70 to-indigo-50/50 dark:from-indigo-950/80 dark:via-purple-950/60 dark:to-indigo-950/40 text-indigo-950 dark:text-indigo-200 font-extrabold uppercase text-[11px] tracking-wider border-b border-indigo-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-4 px-3 w-[8%] text-center">No</th>
                                        <th class="py-4 px-4 w-[40%] text-left">Nama Sekolah</th>
                                        <th class="py-4 px-3 w-[15%] text-center">Jenjang</th>
                                        <th class="py-4 px-3 w-[18%] text-center">Kecamatan</th>
                                        <th class="py-4 px-4 w-[19%] text-center">Jumlah Guru</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                                    <tr
                                        v-for="(school, idx) in getPaginatedList(filteredGuru, currentPageGuru)"
                                        :key="school.id"
                                        class="hover:bg-indigo-50/20 dark:hover:bg-indigo-950/20 transition-colors duration-200"
                                    >
                                        <td class="py-3.5 px-3 text-center text-slate-500 dark:text-slate-400 font-bold font-sans">
                                            {{ (currentPageGuru - 1) * perPage + idx + 1 }}
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2.5">
                                                
                                                <div>
                                                    {{ school.nama }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="inline-flex items-center justify-center px-3 py-0.5 rounded-full border text-[10px] font-extrabold tracking-wide uppercase" :class="getJenjangBadgeClass(school.jenjang)">
                                                {{ school.jenjang }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                            {{ school.kecamatan }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-purple-600 text-white text-xs font-black min-w-[50px] shadow-xs">
                                                {{ formatNumber(school.jumlah_guru) }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredGuru.length === 0">
                                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm font-medium">
                                            Tidak ada data sekolah yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 sm:px-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                            Menampilkan <span class="font-bold text-slate-800 dark:text-white">{{ filteredGuru.length > 0 ? (currentPageGuru - 1) * perPage + 1 : 0 }}</span> - 
                            <span class="font-bold text-slate-800 dark:text-white">{{ Math.min(currentPageGuru * perPage, filteredGuru.length) }}</span> dari 
                            <span class="font-bold text-slate-800 dark:text-white">{{ filteredGuru.length }}</span> data
                        </div>
                        
                        <div class="flex items-center gap-1.5" v-if="getTotalPages(filteredGuru) > 1">
                            <button
                                v-if="currentPageGuru > 1"
                                @click="currentPageGuru--"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            
                            <button
                                v-for="page in getTotalPages(filteredGuru)"
                                :key="page"
                                @click="currentPageGuru = page"
                                class="h-8 w-8 rounded-lg text-xs font-bold transition duration-200 flex items-center justify-center"
                                :class="currentPageGuru === page 
                                    ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white shadow-md' 
                                    : 'border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            >
                                {{ page }}
                            </button>
                            
                            <button
                                v-if="currentPageGuru < getTotalPages(filteredGuru)"
                                @click="currentPageGuru++"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.page-fade-enter-active,
.page-fade-leave-active {
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.page-fade-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.page-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
