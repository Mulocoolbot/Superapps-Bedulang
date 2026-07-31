<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ExecutiveSummaryCard from '@/Components/Pimpinan/ExecutiveSummaryCard.vue'
import CCTVGridCard from '@/Components/CCTVGridCard.vue'

defineProps({
    cctvs: {
        type: Array,
        default: () => []
    },
    complaintStats: {
        type: Object,
        default: () => ({ pending: 0, processing: 0, total: 0, resolved: 0 })
    },
    recap: {
        type: Object,
        default: () => ({})
    },
    userDomain: {
        type: String,
        default: ''
    }
})
</script>

<template>
    <Head title="Dashboard Pimpinan"></Head>

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b pb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        Dashboard Pimpinan
                    </h1>
                    <p class="mt-1 text-gray-500">
                        Pemantauan Layanan dan Pengaduan Seluruh Bidang
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
 
            <!-- Grand Recap -->
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">Ringkasan Eksekutif</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Harga Pasar -->
                    <Link href="/layanan/informasi-publik?tab=bapok" class="block transition hover:scale-[1.02]">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Rata-Rata Harga Pasar</p>
                                    <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                                        {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(recap.pasar?.avg_today || 0) }}
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span :class="(recap.pasar?.change_percentage || 0) > 0 ? 'text-rose-600 bg-rose-50' : 'text-green-600 bg-green-50'" class="px-2 py-0.5 rounded-md text-xs font-semibold">
                                            {{ (recap.pasar?.change_percentage || 0) > 0 ? '+' : '' }}{{ Number(recap.pasar?.change_percentage || 0).toFixed(2) }}%
                                        </span>
                                        <span class="text-xs text-slate-500">vs Kemarin</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl dark:bg-indigo-900/30 dark:text-indigo-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.999 2.999 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.999 2.999 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Harga Sawit -->
                    <Link href="/layanan/informasi-publik?tab=sawit" class="block transition hover:scale-[1.02]">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Rata-Rata Harga Sawit</p>
                                    <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                                        {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(recap.sawit?.avg_today || 0) }}
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span :class="(recap.sawit?.change_percentage || 0) > 0 ? 'text-green-600 bg-green-50' : 'text-rose-600 bg-rose-50'" class="px-2 py-0.5 rounded-md text-xs font-semibold">
                                            {{ (recap.sawit?.change_percentage || 0) > 0 ? '+' : '' }}{{ Number(recap.sawit?.change_percentage || 0).toFixed(2) }}%
                                        </span>
                                        <span class="text-xs text-slate-500">vs Kemarin</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-amber-100 text-amber-600 rounded-xl dark:bg-amber-900/30 dark:text-amber-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Kepegawaian -->
                    <Link href="/layanan/kepegawaian" class="block transition hover:scale-[1.02]">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Kepegawaian</p>
                                    <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                                        {{ new Intl.NumberFormat('id-ID').format(recap.kepegawaian?.total_pegawai || 0) }} <span class="text-base font-medium text-slate-500">Pegawai</span>
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600">
                                            {{ recap.kepegawaian?.total_upd || 0 }} OPD
                                        </span>
                                    </div>
                                </div>
                                <div class="p-3 bg-sky-100 text-sky-600 rounded-xl dark:bg-sky-900/30 dark:text-sky-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- MBG -->
                    <Link href="/layanan/makanan-bergizi-gratis" class="block transition hover:scale-[1.02]">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Penerima MBG</p>
                                    <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                                        {{ new Intl.NumberFormat('id-ID').format(recap.mbg?.total_penerima || 0) }} <span class="text-base font-medium text-slate-500">Anak</span>
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-slate-500">Tersebar di seluruh jenjang</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-pink-100 text-pink-600 rounded-xl dark:bg-pink-900/30 dark:text-pink-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <Link href="/dashboard/pad" class="block transition hover:scale-[1.02]">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Realisasi Anggaran Daerah Terbaru</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                            Rp0 Miliar
                        </h3>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                0%
                            </span>
                            <span class="text-xs text-slate-500">Target APBD 2025 (Rp0 Miliar)</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Update Januari 2026</p>
                    </div>
                    <!-- Icon Landmark -->
                    <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl dark:bg-indigo-900/30 dark:text-indigo-400 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5M3 21h18M3 9h18" />
                        </svg>
                    </div>
                </div>
            </div>
        </Link>

                    <!-- UTD Stok Darah -->
                    <Link href="/layanan/informasi-publik?tab=utd" class="block transition hover:scale-[1.02]">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Stok Darah UTD</p>
                                    <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                                        {{ new Intl.NumberFormat('id-ID').format(recap.utd?.total_bags || 0) }} <span class="text-base font-medium text-slate-500">Kantong</span>
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-slate-500">
                                            Update: {{ recap.utd?.last_update ? new Date(recap.utd.last_update).toLocaleDateString('id-ID') : '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-3 bg-red-100 text-red-600 rounded-xl dark:bg-red-900/30 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- KDMP -->
                    <Link href="/layanan/hukum" class="block transition hover:scale-[1.02]">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700 h-full">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Jumlah KDMP</p>
                                    <h3 class="text-3xl font-bold text-slate-800 dark:text-white mt-1">
                                        63 <span class="text-base font-medium text-slate-500">KDMP</span>
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-slate-500">Koperasi Merah Putih</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-teal-100 text-teal-600 rounded-xl dark:bg-teal-900/30 dark:text-teal-400 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                </div>
            </div>

            <!-- Stats Overview -->
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4 mt-4">Statistik Pengaduan</h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Total Pengaduan -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-slate-100 text-slate-600 rounded-xl dark:bg-slate-900/30 dark:text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9ZM9.75 14.25l1.039 1.039" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pengaduan</p>
                                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">{{ complaintStats.total }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Laporan Selesai -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-green-100 text-green-600 rounded-xl dark:bg-green-900/30 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-green-600 dark:text-green-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Laporan Selesai</p>
                                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">{{ complaintStats.resolved }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Menunggu Tindak Lanjut -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-amber-100 text-amber-600 rounded-xl dark:bg-amber-900/30 dark:text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum Diproses</p>
                                <h3 class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ complaintStats.pending }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Sedang Diproses -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-slate-200/60 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-xl dark:bg-blue-900/30 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Dalam Penanganan</p>
                                <h3 class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ complaintStats.processing }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <Link
                        href="/admin/complaints"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white dark:bg-blue-900/20 dark:hover:bg-blue-600 text-sm font-semibold transition"
                    >
                        <span>Lihat Semua Pengaduan</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- CCTV Monitor -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Monitoring CCTV Live</h3>
                            <p class="text-xs text-gray-500">Live monitoring kamera CCTV Kabupaten Bangka Tengah</p>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                        {{ cctvs.length }} Kamera
                    </span>
                </div>

                <!-- CCTV Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <CCTVGridCard
                        v-for="cctv in cctvs"
                        :key="cctv.id"
                        :cctv="cctv"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
