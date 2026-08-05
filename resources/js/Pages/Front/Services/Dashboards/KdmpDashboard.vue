<script setup>
import { computed, ref } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const isKdmpAdmin = computed(() => user.value && user.value.kdmp_role === 'admin_kdmp')

const adminDashboardRoute = computed(() => {
    if (!user.value) return '#'
    if (user.value.kdmp_role === 'admin_kdmp') return route('admin.kdmp.dashboard')
    if (user.value.kdmp_role === 'admin_kecamatan') return route('admin.kdmp.kecamatan.dashboard')
    if (user.value.kdmp_role === 'admin_kmp') return route('admin.kdmp.kmp.dashboard')
    return '#'
})

const adminHistoryRoute = computed(() => {
    if (!user.value) return '#'
    if (user.value.kdmp_role === 'admin_kdmp') return route('admin.kdmp.history')
    if (user.value.kdmp_role === 'admin_kecamatan') return route('admin.kdmp.kecamatan.history')
    if (user.value.kdmp_role === 'admin_kmp') return route('admin.kdmp.kmp.history')
    return '#'
})

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

const summary = computed(() => props.dashboard?.summary || {})
const tableData = computed(() => props.dashboard?.table_data || [])

// Pagination for table
const currentPage = ref(1)
const itemsPerPage = 10

const totalPages = computed(() => Math.ceil(tableData.value.length / itemsPerPage))

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return tableData.value.slice(start, end)
})

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++
}

const goToKecamatanDetail = (kecamatan) => {
    router.visit(`/layanan/kdmp/kecamatan/${encodeURIComponent(kecamatan)}`)
}
</script>

<template>
    <div class="mt-6 grid gap-6 grid-cols-1">
        
        <!-- Panel Admin KDMP -->
        <div v-if="isKdmpAdmin" class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm dark:border-indigo-800 dark:bg-indigo-900/20 animate-fade-in-up">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-indigo-900 dark:text-indigo-100">Panel Akses Admin KDMP</h2>
                    <p class="text-sm text-indigo-700 dark:text-indigo-300">
                        Anda login sebagai 
                        <span v-if="user.kdmp_role === 'admin_kdmp'" class="font-bold">Admin Keseluruhan</span>
                        <span v-else-if="user.kdmp_role === 'admin_kecamatan'" class="font-bold">Admin Kecamatan {{ user.kdmp_kecamatan }}</span>
                        <span v-else-if="user.kdmp_role === 'admin_kmp'" class="font-bold">Admin KMP</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link v-if="user.kdmp_role === 'admin_kdmp' || user.kdmp_role === 'admin_kecamatan'" :href="adminDashboardRoute" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm ring-1 ring-inset ring-indigo-200 hover:bg-indigo-50 dark:bg-indigo-800 dark:text-white dark:ring-indigo-700 dark:hover:bg-indigo-700 transition">
                        Kelola Admin
                    </Link>
                    <Link :href="adminHistoryRoute" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition">
                        Lihat History Log
                    </Link>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Card: Jumlah Unit -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up">
                <div class="flex items-center gap-4 border-b border-slate-200/50 pb-4 dark:border-slate-700/50">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-xl dark:bg-blue-900/30 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.999 2.999 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.999 2.999 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Koperasi</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white">
                            {{ summary.total_unit || 0 }} <span class="text-sm font-normal text-slate-500">Unit</span>
                        </h3>
                    </div>
                </div>
                <div class="pt-4 grid grid-cols-2 gap-y-3 gap-x-2">
                    <div v-for="item in tableData" :key="item.kecamatan" class="flex flex-col">
                        <span class="text-xs text-slate-400">{{ item.kecamatan }}</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ item.total_koperasi }} Unit</span>
                    </div>
                </div>
            </div>

            <!-- Card: Jumlah SDM -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:100ms]">
                <div class="flex items-center gap-4 border-b border-slate-200/50 pb-4 dark:border-slate-700/50">
                    <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl dark:bg-emerald-900/30 dark:text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total SDM</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white">
                            {{ summary.total_sdm || 0 }} <span class="text-sm font-normal text-slate-500">Orang</span>
                        </h3>
                    </div>
                </div>
                <div class="pt-4 grid grid-cols-3 gap-4">
                    <div class="flex flex-col bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg text-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Pengawas</span>
                        <span class="font-bold text-lg text-slate-800 dark:text-white">{{ summary.sdm_details?.pengawas || 0 }}</span>
                    </div>
                    <div class="flex flex-col bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg text-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Pengurus</span>
                        <span class="font-bold text-lg text-slate-800 dark:text-white">{{ summary.sdm_details?.pengurus || 0 }}</span>
                    </div>
                    <div class="flex flex-col bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg text-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Anggota</span>
                        <span class="font-bold text-lg text-slate-800 dark:text-white">{{ summary.sdm_details?.anggota || 0 }}</span>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Table Data Koperasi -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white mb-4">Sebaran Unit Koperasi per Kecamatan</h2>
            
            <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Kecamatan</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Koperasi Desa</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Koperasi Lurah</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center text-cyan-600 dark:text-cyan-400">Total Koperasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="item in paginatedData" :key="item.kecamatan" @click="goToKecamatanDetail(item.kecamatan)" class="cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm group">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    {{ item.kecamatan }}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">{{ item.koperasi_desa }}</td>
                            <td class="px-6 py-4 text-center">{{ item.koperasi_lurah }}</td>
                            <td class="px-6 py-4 text-center font-bold text-cyan-600 dark:text-cyan-400">{{ item.total_koperasi }}</td>
                        </tr>
                        <tr v-if="paginatedData.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Tidak ada data untuk ditampilkan</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between px-2" v-if="totalPages > 1">
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-medium text-slate-900 dark:text-white">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> 
                    sampai <span class="font-medium text-slate-900 dark:text-white">{{ Math.min(currentPage * itemsPerPage, tableData.length) }}</span> 
                    dari <span class="font-medium text-slate-900 dark:text-white">{{ tableData.length }}</span> data
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="prevPage" 
                        :disabled="currentPage === 1"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 dark:text-slate-200 transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                    >
                        Sebelumnya
                    </button>
                    <button 
                        @click="nextPage" 
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 dark:text-slate-200 transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                    >
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>
