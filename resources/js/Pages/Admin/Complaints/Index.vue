<script setup>
import { ref, watch, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useSlidingPagination } from '@/Composables/useSlidingPagination'

const props = defineProps({
    complaints: { type: Object, required: true },
    stats: {
        type: Object,
        default: () => ({ total: 0, pending: 0, processing: 0, resolved: 0, rejected: 0, cancelled: 0, terkirim: 0 })
    },
    services: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ status: 'all', service: '', search: '', type: 'all' }) },
    userDomain: { type: String, default: null },
    isSuperAdmin: { type: Boolean, default: true },
})

const { visiblePageLinks: visiblePaginationLinks, prevLink, nextLink, hasPagination } = useSlidingPagination(() => props.complaints)

const statusLabel = {
    pending: 'Menunggu',
    terkirim: 'Terkirim',
    processing: 'Diproses',
    resolved: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Dibatalkan',
}

const statusClass = (status) => ({
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    terkirim: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    processing: 'bg-blue-100 text-blue-800 border-blue-200',
    resolved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-rose-100 text-rose-800 border-rose-200',
    cancelled: 'bg-slate-100 text-slate-800 border-slate-300',
}[status] ?? 'bg-slate-100 text-slate-700 border-slate-200')

const searchInput = ref(props.filters.search ?? '')
const selectedStatus = ref(props.filters.status ?? 'all')
const selectedService = ref(props.filters.service ?? '')
const selectedType = ref(props.filters.type ?? 'all')

const applyFilters = () => {
    router.get(
        '/admin/complaints',
        {
            status: selectedStatus.value,
            service: selectedService.value,
            search: searchInput.value,
            type: selectedType.value,
        },
        { preserveState: true, replace: true }
    )
}

const filterByStatus = (st) => {
    selectedStatus.value = st
    applyFilters()
}

const resetFilters = () => {
    searchInput.value = ''
    selectedStatus.value = 'all'
    selectedService.value = ''
    selectedType.value = 'all'
    applyFilters()
}
</script>

<template>
    <AdminLayout>
        <Head title="Monitoring Pengaduan"></Head>

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-slate-900">Monitoring Pengaduan</h1>
                        <span v-if="isSuperAdmin" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                            SuperAdmin (Semua Bidang)
                        </span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                            Admin Bidang: {{ userDomain }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        Pantau dan kelola pengaduan masyarakat secara realtime berdasarkan status dan bidang layanan.
                    </p>
                </div>
            </div>

            <!-- Summary Statistics Cards (7 Cards) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
                <!-- Total Pengaduan -->
                <button
                    @click="filterByStatus('all')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'all' ? 'border-slate-800 ring-2 ring-slate-800' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-slate-100 text-slate-700 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-slate-900">{{ stats.total }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</p>
                </button>

                <!-- Menunggu (Pending) -->
                <button
                    @click="filterByStatus('pending')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'pending' ? 'border-amber-500 ring-2 ring-amber-500' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-amber-600">{{ stats.pending }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Menunggu</p>
                </button>

                <!-- Terkirim (Terkirim) -->
                <button
                    @click="filterByStatus('terkirim')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'terkirim' ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-indigo-600">{{ stats.terkirim || 0 }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Terkirim</p>
                </button>

                <!-- Sedang Diproses (Processing) -->
                <button
                    @click="filterByStatus('processing')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'processing' ? 'border-blue-500 ring-2 ring-blue-500' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-blue-600">{{ stats.processing }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Diproses</p>
                </button>

                <!-- Telah Diproses / Selesai (Resolved) -->
                <button
                    @click="filterByStatus('resolved')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'resolved' ? 'border-emerald-500 ring-2 ring-emerald-500' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-emerald-600">{{ stats.resolved }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Selesai</p>
                </button>

                <!-- Ditolak (Rejected) -->
                <button
                    @click="filterByStatus('rejected')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'rejected' ? 'border-rose-500 ring-2 ring-rose-500' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-rose-600">{{ stats.rejected }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Ditolak</p>
                </button>

                <!-- Dibatalkan (Cancelled) -->
                <button
                    @click="filterByStatus('cancelled')"
                    class="bg-white rounded-2xl p-4 shadow-sm border text-left transition hover:shadow-md cursor-pointer space-y-2"
                    :class="selectedStatus === 'cancelled' ? 'border-slate-600 ring-2 ring-slate-600' : 'border-slate-200/80'"
                >
                    <div class="flex items-center justify-between">
                        <div class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-slate-700">{{ stats.cancelled }}</h2>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Dibatalkan</p>
                </button>
            </div>

            <!-- Filter Controls Bar -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Search Input -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Cari Pengaduan</label>
                        <div class="relative">
                            <input
                                v-model="searchInput"
                                type="text"
                                placeholder="Cari judul, pelapor, atau email..."
                                class="w-full rounded-xl border-slate-200 text-sm pl-10 focus:border-blue-500 focus:ring-blue-500"
                                @keyup.enter="applyFilters"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Status Pengaduan</label>
                        <select
                            v-model="selectedStatus"
                            @change="applyFilters"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="all">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="terkirim">Terkirim</option>
                            <option value="processing">Diproses</option>
                            <option value="resolved">Selesai</option>
                            <option value="rejected">Ditolak</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Tipe Pengaduan Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Tipe Pengaduan</label>
                        <select
                            v-model="selectedType"
                            @change="applyFilters"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="all">Semua Tipe</option>
                            <option value="bedulang">Pengaduan Bedulang</option>
                            <option value="layanan_info">Informasi & Data</option>
                        </select>
                    </div>

                    <!-- Bidang / Service Filter -->
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Bidang Layanan</label>
                        <select
                            v-model="selectedService"
                            @change="applyFilters"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Semua Bidang</option>
                            <option v-for="service in services" :key="service.slug" :value="service.slug">
                                {{ service.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <p class="text-xs text-slate-500">
                        Menampilkan {{ complaints.data.length }} dari {{ complaints.total }} pengaduan.
                    </p>
                    <button
                        @click="resetFilters"
                        class="text-xs font-medium text-slate-500 hover:text-blue-600 transition"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Complaints Table / List Card -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200/80">
                <div v-if="!complaints.data.length" class="p-12 text-center text-sm text-slate-500 space-y-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="font-semibold text-slate-700">Tidak ada pengaduan ditemukan</p>
                    <p class="text-xs text-slate-400">Coba ubah kriteria pencarian atau filter status & bidang Anda.</p>
                </div>

                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="complaint in complaints.data"
                        :key="complaint.id"
                        class="p-5 transition hover:bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border" :class="statusClass(complaint.status)">
                                    {{ statusLabel[complaint.status] }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ complaint.service_name }}
                                </span>
                                <span class="text-xs text-slate-400">• {{ complaint.category }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base leading-snug">{{ complaint.title }}</h3>
                            <p class="text-xs text-slate-500">
                                Pelapor: <span class="font-medium text-slate-700">{{ complaint.contact_person_name }}</span> ({{ complaint.contact_email }})
                                • Tanggal: {{ new Date(complaint.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </p>
                        </div>

                        <Link
                            :href="`/admin/complaints/${complaint.id}`"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-xs font-semibold transition shrink-0 self-start md:self-center"
                        >
                            <span>{{ $page.props.auth.user.role === 'pimpinan' ? 'Detail Pengaduan' : 'Tindak Lanjut & Detail' }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Pagination Controls (Sliding Window 5 Bubbles) -->
                <div v-if="hasPagination" class="p-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Halaman <span class="font-bold text-slate-900 font-mono">{{ complaints.current_page }}</span>
                        dari <span class="font-bold text-slate-900 font-mono">{{ complaints.last_page }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <!-- Prev Arrow -->
                        <Link
                            v-if="prevLink?.url"
                            :href="prevLink.url"
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all duration-200 transform hover:scale-105 shadow-xs"
                            aria-label="Previous Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </Link>
                        <span v-else class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-300 opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </span>

                        <!-- Numeric Page Bubbles -->
                        <template v-for="(link, k) in visiblePaginationLinks" :key="k">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center transition-all duration-200 transform hover:scale-105"
                                :class="link.active
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                    : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'"
                            >
                                {{ link.label }}
                            </Link>
                            <span v-else class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center bg-slate-50 text-slate-400">
                                {{ link.label }}
                            </span>
                        </template>

                        <!-- Next Arrow -->
                        <Link
                            v-if="nextLink?.url"
                            :href="nextLink.url"
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all duration-200 transform hover:scale-105 shadow-xs"
                            aria-label="Next Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                        <span v-else class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-300 opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
