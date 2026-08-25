<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CCTVGridCard from '@/Components/CCTVGridCard.vue'

defineProps({
    cctvs: {
        type: Array,
        default: () => []
    },
    complaintStats: {
        type: Object,
        default: () => ({ pending: 0, processing: 0, total: 0 })
    },
    koperasiStats: {
        type: Object,
        default: () => ({ total: 0, tahun: new Date().getFullYear(), logs_count: 0 })
    },
    userDomain: {
        type: String,
        default: ''
    }
})
</script>

<template>
    <Head title="Dashboard Admin"></Head>

    <AdminLayout>
        <!-- Top header row with Landing Page button on top right -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Dashboard Admin
                </h1>
                <p class="text-gray-500 mt-1">
                    Selamat datang di Panel Administrator BEDULANG.
                    <span v-if="userDomain" class="font-semibold text-blue-600">
                        (Layanan: {{ userDomain.toUpperCase() }})
                    </span>
                </p>
            </div>

            <!-- Top right landing page back button -->
            <Link
                href="/"
                class="inline-flex justify-center items-center p-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-blue-600 shadow-sm transition shrink-0"
                title="Kembali ke Landing Page"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </Link>
        </div>

        <!-- Direct Feature & Quick Access Display -->
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-gray-800">
                Fitur & Akses Cepat
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- CCTV Live Stream Feature Screen - All CCTVs Grid -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b pb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Monitoring CCTV</h3>
                                <p class="text-xs text-gray-500">Live monitoring kamera CCTV Kabupaten Bangka Tengah</p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                            {{ cctvs.length }} Kamera
                        </span>
                    </div>

                    <!-- CCTV Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <CCTVGridCard
                            v-for="cctv in cctvs"
                            :key="cctv.id"
                            :cctv="cctv"
                        />
                    </div>

                    <!-- Bottom Link -->
                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <Link
                            href="/admin/cctv"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition"
                        >
                            <span>Monitoring Peta & Detail CCTV</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Right Column: Complaints & Koperasi Cards -->
                <div class="space-y-6 flex flex-col justify-between">
                    <!-- Single Unified Complaint Data Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-lg">Monitoring Pengaduan</h3>
                                        <p class="text-xs text-gray-500">
                                            {{ userDomain ? `Layanan: ${userDomain.toUpperCase()}` : 'Semua Bidang Layanan' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Grid 3 Indicators inside Single Card -->
                            <div class="grid grid-cols-3 gap-3 pt-1">
                                <!-- Pending Stat Box -->
                                <Link
                                    href="/admin/complaints?status=pending"
                                    class="p-3 rounded-xl bg-amber-50/80 hover:bg-amber-100/80 border border-amber-200/70 transition space-y-1 text-center group"
                                >
                                    <span class="text-[10px] font-semibold text-amber-800 uppercase tracking-wider block">Menunggu</span>
                                    <span class="text-2xl font-extrabold text-amber-600 block group-hover:scale-105 transition">
                                        {{ complaintStats?.pending ?? 0 }}
                                    </span>
                                    <span class="text-[10px] text-amber-700 font-medium block">Perlu Tindakan</span>
                                </Link>

                                <!-- Processing Stat Box -->
                                <Link
                                    href="/admin/complaints?status=processing"
                                    class="p-3 rounded-xl bg-blue-50/80 hover:bg-blue-100/80 border border-blue-200/70 transition space-y-1 text-center group"
                                >
                                    <span class="text-[10px] font-semibold text-blue-800 uppercase tracking-wider block">Diproses</span>
                                    <span class="text-2xl font-extrabold text-blue-600 block group-hover:scale-105 transition">
                                        {{ complaintStats?.processing ?? 0 }}
                                    </span>
                                    <span class="text-[10px] text-blue-700 font-medium block">Ditangani</span>
                                </Link>

                                <!-- Resolved Stat Box -->
                                <Link
                                    href="/admin/complaints?status=resolved"
                                    class="p-3 rounded-xl bg-green-50/80 hover:bg-green-100/80 border border-green-200/70 transition space-y-1 text-center group"
                                >
                                    <span class="text-[10px] font-semibold text-green-800 uppercase tracking-wider block">Selesai</span>
                                    <span class="text-2xl font-extrabold text-green-600 block group-hover:scale-105 transition">
                                        {{ complaintStats?.resolved ?? 0 }}
                                    </span>
                                    <span class="text-[10px] text-green-700 font-medium block">Diselesaikan</span>
                                </Link>
                            </div>
                        </div>

                        <!-- Bottom Action Link -->
                        <div class="pt-2 border-t">
                            <Link
                                href="/admin/complaints"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition text-sm shadow-sm"
                            >
                                <span>Kelola Semua Pengaduan</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Data Koperasi Desa Card (Upload & Management) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-emerald-200/80 p-6 space-y-5 flex flex-col justify-between relative overflow-hidden group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.999 2.999 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.999 2.999 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="font-bold text-gray-800 text-lg">Koperasi Desa</h3>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                Kab. Bangka Tengah
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">Kelola & Upload Data Excel Koperasi</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100 space-y-2">
                                <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wider block">Total Koperasi (Tahun {{ koperasiStats?.tahun }})</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-extrabold text-emerald-700 block">
                                        {{ new Intl.NumberFormat('id-ID').format(koperasiStats?.total || 0) }}
                                    </span>
                                    <span class="text-xs font-medium text-emerald-600">Unit Koperasi</span>
                                </div>
                                <span class="text-[11px] text-emerald-600 font-medium block">
                                    {{ koperasiStats?.logs_count || 0 }} Snapshot log import tersimpan
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <Link
                                href="/admin/koperasi"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition text-sm shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span>Upload Excel & Kelola Data</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>