<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import Footer from '@/Components/Front/Footer.vue'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    koperasi: {
        type: Object,
        required: true,
    }
})

const title = computed(() => props.koperasi.title)
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value)
}

const getInitials = (name) => {
    if (!name) return 'U'
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}
</script>

<template>
    <FrontLayout>
        <Head :title="title"></Head>

        <div class="mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 lg:px-8 lg:pt-28 lg:pb-10">
            <!-- Back Button - subtle ghost style -->
            <Link
                :href="`/layanan/kdmp/kecamatan/${encodeURIComponent(props.koperasi.kecamatan)}`"
                class="group mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 animate-fade-in-up"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Daftar Koperasi Kecamatan {{ props.koperasi.kecamatan }}
            </Link>

            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:100ms]">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-50/60 blur-3xl dark:bg-cyan-950/20" />
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-50/60 blur-3xl dark:bg-blue-950/20" />
                
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400">
                            <span>🏢</span>
                            <span>Profil Koperasi</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white font-sans">
                            {{ title }}
                        </h1>
                        <p class="mt-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            {{ koperasi.jenis }}
                        </p>
                    </div>
                    
                    <!-- Status Badges -->
                    <div class="flex flex-wrap gap-2">
                        <div :class="koperasi.operasional === 'Sudah' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'" class="flex flex-col items-center justify-center rounded-xl px-4 py-2 shadow-sm border border-slate-200/50 dark:border-slate-700/50">
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-70">Operasional</span>
                            <span class="font-semibold text-sm">{{ koperasi.operasional }}</span>
                        </div>
                        <div :class="koperasi.sertifikat === 'Sudah' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'" class="flex flex-col items-center justify-center rounded-xl px-4 py-2 shadow-sm border border-slate-200/50 dark:border-slate-700/50">
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-70">Sertifikat</span>
                            <span class="font-semibold text-sm">{{ koperasi.sertifikat }}</span>
                        </div>
                        <div :class="koperasi.pelatihan === 'Sudah' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'" class="flex flex-col items-center justify-center rounded-xl px-4 py-2 shadow-sm border border-slate-200/50 dark:border-slate-700/50">
                            <span class="text-[10px] uppercase font-bold tracking-wider opacity-70">Pelatihan</span>
                            <span class="font-semibold text-sm">{{ koperasi.pelatihan }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Sidebar: Pengurus -->
                <div class="lg:col-span-1 space-y-6 animate-fade-in-up [animation-delay:200ms]">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Susunan Pengurus
                        </h2>
                        
                        <div class="space-y-4">
                            <!-- Kepala -->
                            <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <img v-if="koperasi.pengurus.kepala.foto" :src="`/storage/${koperasi.pengurus.kepala.foto}`" alt="Foto Kepala Koperasi" class="h-12 w-12 object-cover shrink-0 rounded-full border-2 border-cyan-400 shadow-sm" />
                                <div v-else class="h-12 w-12 shrink-0 rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white font-bold shadow-inner">
                                    {{ getInitials(koperasi.pengurus.kepala.nama) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider mb-0.5">Kepala Koperasi</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ koperasi.pengurus.kepala.nama }}</p>
                                </div>
                            </div>
                            
                            <!-- Sekretaris -->
                            <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <img v-if="koperasi.pengurus.sekretaris.foto" :src="`/storage/${koperasi.pengurus.sekretaris.foto}`" alt="Foto Sekretaris" class="h-12 w-12 object-cover shrink-0 rounded-full border-2 border-indigo-400 shadow-sm" />
                                <div v-else class="h-12 w-12 shrink-0 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold shadow-inner">
                                    {{ getInitials(koperasi.pengurus.sekretaris.nama) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-0.5">Sekretaris</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ koperasi.pengurus.sekretaris.nama }}</p>
                                </div>
                            </div>

                            <!-- Bendahara -->
                            <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <img v-if="koperasi.pengurus.bendahara.foto" :src="`/storage/${koperasi.pengurus.bendahara.foto}`" alt="Foto Bendahara" class="h-12 w-12 object-cover shrink-0 rounded-full border-2 border-emerald-400 shadow-sm" />
                                <div v-else class="h-12 w-12 shrink-0 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold shadow-inner">
                                    {{ getInitials(koperasi.pengurus.bendahara.nama) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-0.5">Bendahara</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ koperasi.pengurus.bendahara.nama }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content: Details and Finance -->
                <div class="lg:col-span-2 space-y-6 animate-fade-in-up [animation-delay:300ms]">
                    
                    <!-- Informasi Detail -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Informasi Legal & Alamat
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Nomor Induk</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ koperasi.nomor_induk }}</dd>
                            </div>
                            
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Telepon / Kontak</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ koperasi.telp }}</dd>
                            </div>
                            
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Nomor AHU</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ koperasi.no_ahu }}</dd>
                            </div>
                            
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Tanggal AHU</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ koperasi.tanggal_ahu }}</dd>
                            </div>
                            
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 md:col-span-2">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Alamat Lengkap</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white leading-relaxed">
                                    {{ koperasi.alamat }}<br/>
                                    Desa/Kelurahan: {{ koperasi.desa_kelurahan }}<br/>
                                    Kecamatan: {{ koperasi.kecamatan }}
                                </dd>
                            </div>
                        </div>
                    </div>

                    <!-- SDM Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                        <!-- Jumlah Pengurus -->
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 group hover:border-indigo-500/50 transition-colors">
                            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-indigo-50 dark:bg-indigo-900/20 opacity-50 transition-transform group-hover:scale-150"></div>
                            <div class="relative">
                                <div class="flex items-center gap-3 text-indigo-600 dark:text-indigo-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <h3 class="font-semibold text-xs xl:text-sm uppercase tracking-wider">Pengurus Koperasi</h3>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                                    {{ koperasi.jumlah?.pengurus ?? 0 }} <span class="text-sm font-medium text-slate-500">Orang</span>
                                </div>
                            </div>
                        </div>

                        <!-- Jumlah Pengawas -->
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 group hover:border-amber-500/50 transition-colors">
                            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-amber-50 dark:bg-amber-900/20 opacity-50 transition-transform group-hover:scale-150"></div>
                            <div class="relative">
                                <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <h3 class="font-semibold text-xs xl:text-sm uppercase tracking-wider">Pengawas Koperasi</h3>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                                    {{ koperasi.jumlah?.pengawas ?? 0 }} <span class="text-sm font-medium text-slate-500">Orang</span>
                                </div>
                            </div>
                        </div>

                        <!-- Jumlah Anggota -->
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 group hover:border-cyan-500/50 transition-colors">
                            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-cyan-50 dark:bg-cyan-900/20 opacity-50 transition-transform group-hover:scale-150"></div>
                            <div class="relative">
                                <div class="flex items-center gap-3 text-cyan-600 dark:text-cyan-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h3 class="font-semibold text-xs xl:text-sm uppercase tracking-wider">Anggota Koperasi</h3>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                                    {{ koperasi.jumlah?.anggota ?? 0 }} <span class="text-sm font-medium text-slate-500">Orang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keuangan Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Simpanan Pokok -->
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 group hover:border-blue-500/50 transition-colors">
                            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-50 dark:bg-blue-900/20 opacity-50 transition-transform group-hover:scale-150"></div>
                            <div class="relative">
                                <div class="flex items-center gap-3 text-blue-600 dark:text-blue-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="font-semibold text-sm uppercase tracking-wider">Simpanan Pokok</h3>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                                    {{ formatCurrency(koperasi.keuangan.simpanan_pokok) }}
                                </div>
                            </div>
                        </div>

                        <!-- Simpanan Wajib -->
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 group hover:border-emerald-500/50 transition-colors">
                            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-emerald-50 dark:bg-emerald-900/20 opacity-50 transition-transform group-hover:scale-150"></div>
                            <div class="relative">
                                <div class="flex items-center gap-3 text-emerald-600 dark:text-emerald-400 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="font-semibold text-sm uppercase tracking-wider">Simpanan Wajib</h3>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                                    {{ formatCurrency(koperasi.keuangan.simpanan_wajib) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>

        <Footer></Footer>
    </FrontLayout>
</template>

<style>
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
.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
