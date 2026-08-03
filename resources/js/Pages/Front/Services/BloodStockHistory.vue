<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useSlidingPagination } from '@/Composables/useSlidingPagination'

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
})

const logsList = computed(() => props.logs?.data || [])
const pagination = computed(() => props.logs || {})

// Sliding window pagination (max 5 bubble, geser sesuai halaman aktif)
const { visiblePageLinks, prevLink, nextLink, hasPagination } = useSlidingPagination(() => props.logs)

const getDeltaBadgeClass = (delta) => {
    if (delta > 0) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
    if (delta < 0) return 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-200 dark:border-rose-800'
    return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}
</script>

<template>
    <Head title="Riwayat Perubahan Stok Darah UTD" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8 px-4 sm:px-6 lg:px-8 text-slate-900 dark:text-slate-100 transition-colors duration-300">
        <div class="max-w-6xl mx-auto space-y-6 animate-fade-in-up">

            <!-- HEADER NAVIGATION & TOP BAR -->
            <div class="flex items-center justify-between gap-4">
                <Link
                    href="/layanan/informasi-publik"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 transform hover:-translate-x-1 shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Kembali ke Informasi UTD</span>
                </Link>

                <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-900 shadow-xs transition-transform hover:scale-105">
                    🩸 Unit Transfusi Darah (UTD)
                </span>
            </div>

            <!-- HERO HEADER CARD -->
            <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-gradient-to-r from-rose-500 via-rose-600 to-red-700 p-8 shadow-xl text-white transition-all duration-300 hover:shadow-2xl">
                <div class="relative z-10 space-y-3 max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider text-rose-100 shadow-xs">
                        <span>📜 Logs Audit & History</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                        Riwayat Perubahan Stok Transfusi Darah
                    </h1>
                    <p class="text-xs sm:text-sm text-rose-100 leading-relaxed opacity-95">
                        Catatan lengkap histori pembaruan stok darah per komponen dan golongan darah di Unit Transfusi Darah (UTD) Kabupaten Bangka Tengah.
                    </p>
                </div>
                <div class="absolute -right-10 -bottom-10 opacity-10 text-9xl pointer-events-none select-none transition-transform duration-700 hover:scale-110">
                    🩸
                </div>
            </div>

            <!-- HISTORY LIST TABLE CARD -->
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-md transition-all duration-300">
                
                <!-- Table Header Bar -->
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Daftar Perubahan Stok Darah
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Menampilkan 10 data per halaman secara berurutan berdasarkan waktu terbaru.
                        </p>
                    </div>

                    <div v-if="pagination.total" class="text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-200/60 dark:bg-slate-800 px-3.5 py-1.5 rounded-xl self-start sm:self-auto shadow-xs">
                        Total Records: <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">{{ pagination.total }}</span> Log
                    </div>
                </div>

                <!-- Table Content with Animated Page Transitions -->
                <div class="overflow-x-auto">
                    <Transition name="table-page-fade" mode="out-in">
                        <table :key="pagination.current_page" class="w-full text-left text-xs sm:text-sm transition-all duration-300">
                            <thead class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-4 px-6 text-center w-12">#</th>
                                    <th class="py-4 px-6">Waktu Perubahan</th>
                                    <th class="py-4 px-6">Komponen & Gol. Darah</th>
                                    <th class="py-4 px-6 text-center">Perubahan Stok</th>
                                    <th class="py-4 px-6 text-center">Selisih</th>
                                    <th class="py-4 px-6">Deskripsi Aktivitas</th>
                                    <th class="py-4 px-6 text-right">Petugas / Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200 font-medium">
                                <tr v-if="logsList.length === 0">
                                    <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                        Belum ada catatan riwayat perubahan stok darah.
                                    </td>
                                </tr>

                                <tr
                                    v-for="(log, idx) in logsList"
                                    :key="log.id"
                                    class="hover:bg-rose-50/40 dark:hover:bg-slate-800/50 transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-xs"
                                >
                                    <!-- Index Number -->
                                    <td class="py-4 px-6 text-center text-xs text-slate-400 font-mono">
                                        {{ ((pagination.current_page - 1) * pagination.per_page) + idx + 1 }}
                                    </td>

                                    <!-- Timestamp -->
                                    <td class="py-4 px-6 font-mono text-xs text-slate-600 dark:text-slate-300">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400">⏱️</span>
                                            <span>{{ log.updated_at || '-' }}</span>
                                        </div>
                                    </td>

                                    <!-- Component & Blood Type -->
                                    <td class="py-4 px-6 font-bold">
                                        <div class="flex items-center gap-2">
                                            <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 font-black text-xs flex items-center justify-center border border-rose-200 dark:border-rose-900 shrink-0 transition-transform duration-200 hover:scale-110">
                                                {{ log.blood_type }}
                                            </span>
                                            <div>
                                                <span class="text-slate-900 dark:text-white font-extrabold block text-xs sm:text-sm">
                                                    {{ log.component }}
                                                </span>
                                                <span class="text-[11px] text-slate-400 font-medium">
                                                    Golongan Darah {{ log.blood_type }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Stock Change (Old -> New) -->
                                    <td class="py-4 px-6 text-center font-mono text-xs">
                                        <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 rounded-xl transition-all duration-200 hover:bg-slate-200 dark:hover:bg-slate-700">
                                            <span class="text-slate-400">{{ log.old_amount }}</span>
                                            <span class="text-rose-500 font-bold">➔</span>
                                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ log.new_amount }}</span>
                                            <span class="text-[10px] text-slate-400">Ktg</span>
                                        </div>
                                    </td>

                                    <!-- Delta Badge -->
                                    <td class="py-4 px-6 text-center font-mono font-extrabold text-xs">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-black shadow-xs transition-transform duration-200 hover:scale-105" :class="getDeltaBadgeClass(log.delta)">
                                            {{ log.delta > 0 ? '+' + log.delta : log.delta }} Ktg
                                        </span>
                                    </td>

                                    <!-- Description -->
                                    <td class="py-4 px-6 text-xs text-slate-600 dark:text-slate-300">
                                        {{ log.description || `Perubahan stok ${log.component} Gol. ${log.blood_type}` }}
                                    </td>

                                    <!-- Updater Name -->
                                    <td class="py-4 px-6 text-right text-xs font-bold text-slate-700 dark:text-slate-300">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] flex items-center justify-center font-bold">
                                                👤
                                            </span>
                                            <span>{{ log.updater_name }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </Transition>
                </div>

                <!-- KETERANGAN KOMPONEN DARAH -->
                <div class="px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-6">
                    <span class="font-bold text-slate-700 dark:text-slate-300">ℹ️ Keterangan:</span>
                    <div class="flex flex-wrap items-center gap-4">
                        <div><strong class="text-slate-800 dark:text-slate-200">Whole Blood (WB)</strong> = Darah Lengkap</div>
                        <div><strong class="text-slate-800 dark:text-slate-200">Packed Red Cell (PRC)</strong> = Darah Merah Pekat</div>
                    </div>
                </div>

                <!-- PAGINATION CONTROLS (Sliding Window 5 Bubbles) -->
                <div v-if="hasPagination" class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ pagination.from || 0 }}</span>
                        sampai
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ pagination.to || 0 }}</span>
                        dari
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ pagination.total || 0 }}</span>
                        perubahan data
                    </div>

                    <!-- Pagination Bubbles: ◀ [n] [n+1] [n+2] [n+3] [n+4] ▶ -->
                    <div class="flex items-center gap-1.5">
                        <!-- Prev Arrow Button -->
                        <Link
                            v-if="prevLink?.url"
                            :href="prevLink.url"
                            class="w-9 h-9 rounded-xl flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 transform hover:scale-105 shadow-xs"
                            aria-label="Previous Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </Link>
                        <span v-else class="w-9 h-9 rounded-xl flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600 opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </span>

                        <!-- Numeric Page Bubbles (Sliding Window) -->
                        <template v-for="(link, key) in visiblePageLinks" :key="key">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="w-9 h-9 rounded-xl text-xs font-bold flex items-center justify-center transition-all duration-200 transform hover:scale-105 cursor-pointer"
                                :class="link.active
                                    ? 'bg-rose-600 text-white shadow-md shadow-rose-500/20 ring-2 ring-rose-200 dark:ring-rose-900/50'
                                    : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            >
                                {{ link.label }}
                            </Link>
                            <span
                                v-else
                                class="w-9 h-9 rounded-xl text-xs font-bold flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500"
                            >
                                {{ link.label }}
                            </span>
                        </template>

                        <!-- Next Arrow Button -->
                        <Link
                            v-if="nextLink?.url"
                            :href="nextLink.url"
                            class="w-9 h-9 rounded-xl flex items-center justify-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 transform hover:scale-105 shadow-xs"
                            aria-label="Next Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                        <span v-else class="w-9 h-9 rounded-xl flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600 opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>

</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.table-page-fade-enter-active,
.table-page-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.table-page-fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.table-page-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
