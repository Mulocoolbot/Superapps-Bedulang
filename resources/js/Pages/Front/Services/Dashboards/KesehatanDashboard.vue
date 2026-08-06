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

// Data references from props
const faskesList = computed(() => props.dashboard?.faskes_list ?? [])
const allServices = computed(() => props.dashboard?.all_services ?? [])
const summary = computed(() => props.dashboard?.summary ?? {
    total_faskes: 0,
    total_rsud: 0,
    total_puskesmas: 0,
    total_services: 0,
})

const availableJenisFaskes = computed(() => props.dashboard?.available_jenis_faskes ?? [])
const availableKecamatan = computed(() => props.dashboard?.available_kecamatan ?? [])
const availableKelompokUtama = computed(() => props.dashboard?.available_kelompok_utama ?? [])

// State filters
const selectedJenisFaskes = ref('all')
const selectedKecamatan = ref('all')
const selectedKelompokUtama = ref('all')
const searchQuery = ref('')
const activeTab = ref('daftar-faskes')

// Modal detail unit state
const selectedUnitForModal = ref(null)
const isModalOpen = ref(false)

const openUnitModal = (unit) => {
    selectedUnitForModal.value = unit
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
    selectedUnitForModal.value = null
}

// Grouped services for unit modal
const modalGroupedServices = computed(() => {
    if (!selectedUnitForModal.value || !selectedUnitForModal.value.services) return {}
    const groups = {}
    selectedUnitForModal.value.services.forEach(s => {
        const groupKey = s.kelompok_utama || 'Layanan Umum'
        if (!groups[groupKey]) {
            groups[groupKey] = []
        }
        groups[groupKey].push(s)
    })
    return groups
})

// Pagination state for Unit Tab
const currentUnitPage = ref(1)
const perUnitPage = ref(10)

// Pagination state for Services Tab
const currentServicePage = ref(1)
const perServicePage = ref(15)

// Watch filters and search to reset pagination to page 1
watch([selectedJenisFaskes, selectedKecamatan, selectedKelompokUtama, searchQuery, activeTab], () => {
    currentUnitPage.value = 1
    currentServicePage.value = 1
})

// Filter logic for Units
const filteredFaskes = computed(() => {
    return faskesList.value.filter(item => {
        // Filter: Jenis Faskes
        if (selectedJenisFaskes.value !== 'all' && item.jenis !== selectedJenisFaskes.value) {
            return false
        }
        // Filter: Kecamatan
        if (selectedKecamatan.value !== 'all' && item.kecamatan !== selectedKecamatan.value) {
            return false
        }
        // Search query (Nama, Alamat, Desa, Kecamatan)
        if (searchQuery.value.trim() !== '') {
            const query = searchQuery.value.toLowerCase()
            const matchNama = item.nama.toLowerCase().includes(query)
            const matchAlamat = (item.alamat || '').toLowerCase().includes(query)
            const matchDesa = (item.desa || '').toLowerCase().includes(query)
            const matchKec = (item.kecamatan || '').toLowerCase().includes(query)
            if (!matchNama && !matchAlamat && !matchDesa && !matchKec) {
                return false
            }
        }
        return true
    })
})

// Filter logic for All Services Catalog
const filteredServices = computed(() => {
    return allServices.value.filter(item => {
        // Filter: Jenis Faskes
        if (selectedJenisFaskes.value !== 'all' && item.unit_jenis !== selectedJenisFaskes.value) {
            return false
        }
        // Filter: Kecamatan
        if (selectedKecamatan.value !== 'all' && item.kecamatan !== selectedKecamatan.value) {
            return false
        }
        // Filter: Kelompok Utama
        if (selectedKelompokUtama.value !== 'all' && item.kelompok_utama !== selectedKelompokUtama.value) {
            return false
        }
        // Search query (Unit, Jenis Layanan, Kelompok)
        if (searchQuery.value.trim() !== '') {
            const query = searchQuery.value.toLowerCase()
            const matchUnit = (item.unit_nama || '').toLowerCase().includes(query)
            const matchJenis = (item.jenis_layanan || '').toLowerCase().includes(query)
            const matchKelompok = (item.kelompok_utama || '').toLowerCase().includes(query)
            if (!matchUnit && !matchJenis && !matchKelompok) {
                return false
            }
        }
        return true
    })
})

// Pagination calculations for Unit
const totalUnitPages = computed(() => {
    return Math.ceil(filteredFaskes.value.length / perUnitPage.value) || 1
})

const paginatedFaskes = computed(() => {
    const start = (currentUnitPage.value - 1) * perUnitPage.value
    const end = start + perUnitPage.value
    return filteredFaskes.value.slice(start, end)
})

// Pagination calculations for Services Catalog
const totalServicePages = computed(() => {
    return Math.ceil(filteredServices.value.length / perServicePage.value) || 1
})

const paginatedServices = computed(() => {
    const start = (currentServicePage.value - 1) * perServicePage.value
    const end = start + perServicePage.value
    return filteredServices.value.slice(start, end)
})

// Helper badge styles based on facility type
const getBadgeStyles = (jenis) => {
    switch (jenis) {
        case 'Rumah Sakit Daerah':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200 border border-blue-300 dark:border-blue-800 font-bold'
        case 'Puskesmas':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-800 font-bold'
        default:
            return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 font-bold'
    }
}
</script>

<template>
    <div class="space-y-8 text-slate-900 dark:text-slate-100 font-sans">
        <!-- 1. SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Unit Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-700 bg-white p-5 shadow-sm dark:bg-slate-900 transition-all duration-300 hover:shadow-md">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-600 dark:bg-emerald-500" />
                <div class="pl-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">Total Unit Faskes</span>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                        {{ summary.total_faskes }} <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Unit</span>
                    </p>
                    <span class="mt-2 inline-block px-2 py-0.5 rounded text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                        Unit Layanan Resmi
                    </span>
                </div>
            </div>

            <!-- RSUD Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-700 bg-white p-5 shadow-sm dark:bg-slate-900 transition-all duration-300 hover:shadow-md">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-600 dark:bg-blue-500" />
                <div class="pl-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">RS Daerah (RSUD)</span>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                        {{ summary.total_rsud }} <span class="text-xs font-bold text-slate-600 dark:text-slate-400">RSUD</span>
                    </p>
                    <span class="mt-2 inline-block px-2 py-0.5 rounded text-[11px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300">
                        Rumah Sakit Rujukan
                    </span>
                </div>
            </div>

            <!-- Puskesmas Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-700 bg-white p-5 shadow-sm dark:bg-slate-900 transition-all duration-300 hover:shadow-md">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-teal-600 dark:bg-teal-500" />
                <div class="pl-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">Puskesmas</span>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                        {{ summary.total_puskesmas }} <span class="text-xs font-bold text-slate-600 dark:text-slate-400">UPTD</span>
                    </p>
                    <span class="mt-2 inline-block px-2 py-0.5 rounded text-[11px] font-extrabold bg-teal-100 text-teal-800 dark:bg-teal-950/80 dark:text-teal-300">
                        Pusat Kesehatan Masyarakat
                    </span>
                </div>
            </div>

            <!-- Total Services Card -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-700 bg-white p-5 shadow-sm dark:bg-slate-900 transition-all duration-300 hover:shadow-md">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-purple-600 dark:bg-purple-500" />
                <div class="pl-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">Total Jenis Layanan</span>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white font-sans tracking-tight">
                        {{ summary.total_services }} <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Item</span>
                    </p>
                    <span class="mt-2 inline-block px-2 py-0.5 rounded text-[11px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300">
                        Layanan Terdaftar
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. SEARCH & FILTER CONTROLS -->
        <div class="rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <!-- Search Box -->
                <div class="relative flex-1">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama faskes, kecamatan, alamat, atau jenis layanan..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all shadow-sm"
                    />
                    <svg class="w-5 h-5 absolute left-3 top-3 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Filter Faskes -->
                    <div class="relative inline-block">
                        <select
                            v-model="selectedJenisFaskes"
                            class="appearance-none py-2.5 pl-3.5 pr-10 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs font-extrabold text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm cursor-pointer"
                        >
                            <option value="all">Semua Jenis Faskes</option>
                            <option v-for="j in availableJenisFaskes" :key="j" :value="j">{{ j }}</option>
                        </select>
                        <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 dark:text-slate-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <!-- Filter Kecamatan -->
                    <div class="relative inline-block">
                        <select
                            v-model="selectedKecamatan"
                            class="appearance-none py-2.5 pl-3.5 pr-10 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs font-extrabold text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm cursor-pointer"
                        >
                            <option value="all">Semua Kecamatan</option>
                            <option v-for="k in availableKecamatan" :key="k" :value="k">{{ k }}</option>
                        </select>
                        <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 dark:text-slate-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <!-- Filter Kelompok Utama (Only active on Services tab) -->
                    <div v-if="activeTab === 'katalog-layanan'" class="relative inline-block">
                        <select
                            v-model="selectedKelompokUtama"
                            class="appearance-none py-2.5 pl-3.5 pr-10 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs font-extrabold text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-emerald-500 shadow-sm cursor-pointer max-w-[240px] truncate"
                        >
                            <option value="all">Semua Kelompok Layanan</option>
                            <option v-for="ku in availableKelompokUtama" :key="ku" :value="ku">{{ ku }}</option>
                        </select>
                        <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 dark:text-slate-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. NAVIGATION TABS (Pill Cards with Clean White Theme) -->
        <div class="p-1.5 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm inline-flex flex-wrap gap-2">
            <button
                @click="activeTab = 'daftar-faskes'"
                class="py-2.5 px-5 rounded-xl text-xs sm:text-sm font-extrabold transition-all duration-200 cursor-pointer flex items-center gap-2 border select-none"
                :class="activeTab === 'daftar-faskes'
                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-md dark:bg-emerald-600 dark:text-white dark:border-emerald-600'
                    : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750'"
            >
                <span>🏥</span> Daftar Unit Layanan ({{ filteredFaskes.length }})
            </button>

            <button
                @click="activeTab = 'katalog-layanan'"
                class="py-2.5 px-5 rounded-xl text-xs sm:text-sm font-extrabold transition-all duration-200 cursor-pointer flex items-center gap-2 border select-none"
                :class="activeTab === 'katalog-layanan'
                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-md dark:bg-emerald-600 dark:text-white dark:border-emerald-600'
                    : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750'"
            >
                <span>📋</span> Katalog Jenis Layanan ({{ filteredServices.length }})
            </button>
        </div>

        <!-- 4. TAB 1: DAFTAR UNIT LAYANAN FASKES -->
        <div v-if="activeTab === 'daftar-faskes'" class="overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        Fasilitas Kesehatan Kabupaten Bangka Tengah
                    </h3>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mt-0.5">
                        Menampilkan unit Puskesmas & RSUD beserta lokasi dan daftar layanan resminya.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-black uppercase text-[11px] border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-[50px] hidden sm:table-cell">No</th>
                            <th class="py-3.5 px-4 sm:w-[28%]">Unit Layanan</th>
                            <th class="py-3.5 px-3 text-center sm:w-[130px]">Jenis</th>
                            <th class="py-3.5 px-4 w-[15%] hidden sm:table-cell">Desa/Kelurahan</th>
                            <th class="py-3.5 px-4 w-[13%] hidden sm:table-cell">Kecamatan</th>
                            <th class="py-3.5 px-4 w-[25%] hidden sm:table-cell">Alamat Lengkap</th>
                            <th class="py-3.5 px-4 text-center sm:w-[130px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-900 dark:text-slate-100 font-medium">
                        <tr v-for="(item, idx) in paginatedFaskes" :key="item.id" class="hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                {{ (currentUnitPage - 1) * perUnitPage + idx + 1 }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                                {{ item.nama }}
                                <div class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-400 mt-0.5">
                                    {{ item.total_layanan }} Jenis Layanan Terdaftar
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[11px]" :class="getBadgeStyles(item.jenis)">
                                    {{ item.jenis }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200 hidden sm:table-cell">
                                {{ item.desa || '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-slate-100 hidden sm:table-cell">
                                {{ item.kecamatan }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium text-slate-700 dark:text-slate-300 leading-relaxed hidden sm:table-cell">
                                {{ item.alamat }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button
                                    @click="openUnitModal(item)"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition-colors shadow-sm inline-flex items-center gap-1 cursor-pointer"
                                >
                                    <span>🔍</span> Detail Layanan
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredFaskes.length === 0">
                            <td colspan="3" class="sm:hidden py-10 text-center text-slate-500 dark:text-slate-400 text-sm font-bold">
                                Tidak ada data fasilitas kesehatan yang sesuai.
                            </td>
                            <td colspan="7" class="hidden sm:table-cell py-10 text-center text-slate-500 dark:text-slate-400 text-sm font-bold">
                                Tidak ada data fasilitas kesehatan yang sesuai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Units -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">
                    Menampilkan {{ filteredFaskes.length > 0 ? (currentUnitPage - 1) * perUnitPage + 1 : 0 }} - {{ Math.min(currentUnitPage * perUnitPage, filteredFaskes.length) }} dari {{ filteredFaskes.length }} Unit Faskes
                </span>
                <div class="flex gap-2">
                    <button
                        @click="currentUnitPage--"
                        :disabled="currentUnitPage <= 1"
                        class="px-3 py-1.5 rounded-lg border text-xs font-extrabold disabled:opacity-40 disabled:cursor-not-allowed border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Sebelumnya
                    </button>
                    <button
                        @click="currentUnitPage++"
                        :disabled="currentUnitPage >= totalUnitPages"
                        class="px-3 py-1.5 rounded-lg border text-xs font-extrabold disabled:opacity-40 disabled:cursor-not-allowed border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. TAB 2: KATALOG JENIS LAYANAN (ALL SERVICES) -->
        <div v-if="activeTab === 'katalog-layanan'" class="overflow-hidden rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        Katalog Jenis Layanan Kesehatan Bangka Tengah
                    </h3>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mt-0.5">
                        Rincian seluruh {{ summary.total_services }} jenis pelayanan kesehatan terdaftar per unit faskes.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-black uppercase text-[11px] border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-[50px]">No</th>
                            <th class="py-3.5 px-4 w-[32%]">Unit Layanan</th>
                            <th class="py-3.5 px-4 w-[32%]">Kelompok Utama</th>
                            <th class="py-3.5 px-4 w-[36%]">Jenis Layanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-900 dark:text-slate-100 font-medium">
                        <tr v-for="(s, idx) in paginatedServices" :key="s.id" class="hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 transition-colors">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-500 dark:text-slate-400">
                                {{ (currentServicePage - 1) * perServicePage + idx + 1 }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                                {{ s.unit_nama }}
                                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                    {{ s.kecamatan }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                                {{ s.kelompok_utama }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                                {{ s.jenis_layanan }}
                            </td>
                        </tr>
                        <tr v-if="filteredServices.length === 0">
                            <td colspan="4" class="py-10 text-center text-slate-500 dark:text-slate-400 text-sm font-bold">
                                Tidak ada jenis layanan yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Services -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">
                    Menampilkan {{ filteredServices.length > 0 ? (currentServicePage - 1) * perServicePage + 1 : 0 }} - {{ Math.min(currentServicePage * perServicePage, filteredServices.length) }} dari {{ filteredServices.length }} Item Layanan
                </span>
                <div class="flex gap-2">
                    <button
                        @click="currentServicePage--"
                        :disabled="currentServicePage <= 1"
                        class="px-3 py-1.5 rounded-lg border text-xs font-extrabold disabled:opacity-40 disabled:cursor-not-allowed border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Sebelumnya
                    </button>
                    <button
                        @click="currentServicePage++"
                        :disabled="currentServicePage >= totalServicePages"
                        class="px-3 py-1.5 rounded-lg border text-xs font-extrabold disabled:opacity-40 disabled:cursor-not-allowed border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL UNIT LAYANAN -->
        <div v-if="isModalOpen && selectedUnitForModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm animate-fade-in">
            <div class="relative w-full max-w-3xl max-h-[90vh] flex flex-col rounded-2xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 shadow-2xl overflow-hidden">
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-gradient-to-r from-emerald-700 to-teal-800 text-white flex items-center justify-between">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded bg-white/20 text-[11px] font-black tracking-wider uppercase mb-1">
                            {{ selectedUnitForModal.jenis }}
                        </span>
                        <h3 class="text-lg font-black">
                            {{ selectedUnitForModal.nama }}
                        </h3>
                    </div>
                    <button @click="closeModal" class="p-1.5 rounded-lg hover:bg-white/20 text-white transition-colors cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-6">
                    <!-- Location Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-100 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-xs">
                        <div>
                            <span class="text-slate-600 dark:text-slate-400 font-extrabold block">Alamat Lengkap</span>
                            <span class="font-bold text-slate-900 dark:text-white leading-relaxed block mt-0.5">{{ selectedUnitForModal.alamat }}</span>
                        </div>
                        <div class="space-y-1.5">
                            <div>
                                <span class="text-slate-600 dark:text-slate-400 font-bold">Desa / Kelurahan: </span>
                                <span class="font-black text-slate-900 dark:text-white">{{ selectedUnitForModal.desa || '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-600 dark:text-slate-400 font-bold">Kecamatan: </span>
                                <span class="font-black text-slate-900 dark:text-white">{{ selectedUnitForModal.kecamatan }}</span>
                            </div>
                            <div>
                                <span class="text-slate-600 dark:text-slate-400 font-bold">Kode Pos: </span>
                                <span class="font-mono font-black text-emerald-700 dark:text-emerald-400">{{ selectedUnitForModal.kode_pos || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Services Grouped by Kelompok Utama -->
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span>Daftar Pelayanan ({{ selectedUnitForModal.total_layanan }} Item)</span>
                        </h4>

                        <div class="space-y-4">
                            <div v-for="(services, groupName) in modalGroupedServices" :key="groupName" class="rounded-xl border border-slate-300 dark:border-slate-700 p-4 bg-white dark:bg-slate-900">
                                <h5 class="text-xs font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-wide border-b border-slate-200 dark:border-slate-800 pb-2 mb-3">
                                    {{ groupName }}
                                </h5>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div v-for="s in services" :key="s.id" class="p-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs flex items-start gap-2 border border-slate-200/80 dark:border-slate-700/60">
                                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm font-sans shrink-0">
                                            •
                                        </span>
                                        <div>
                                            <span class="font-black text-slate-900 dark:text-white block">{{ s.jenis_layanan }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 flex justify-end">
                    <button @click="closeModal" class="px-5 py-2 rounded-xl bg-slate-300 hover:bg-slate-400 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white text-xs font-black transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
