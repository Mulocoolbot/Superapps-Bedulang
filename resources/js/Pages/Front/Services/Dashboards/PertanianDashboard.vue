<script setup>
import { computed } from 'vue'

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
</script>

<template>
    <div class="mt-6 space-y-8 animate-fade-in-up">
        <!-- HEADER CARD -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 p-6 shadow-sm dark:border-slate-800 dark:from-emerald-950/40 dark:via-teal-950/40 dark:to-slate-900">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-1">
                        Dinas Pertanian Kab. Bangka Tengah
                    </span>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                        Dashboard Layanan Pertanian
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                        Informasi publik mengenai harga komoditas pasar harian dan penetapan harga sawit (TBS) dapat diakses melalui portal Informasi Publik.
                    </p>
                </div>
            </div>
        </div>

        <!-- EMPTY STATE CARD TEMPLATE -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-12 text-center shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mx-auto shadow-inner">
                🌾
            </div>
            <div class="max-w-md mx-auto space-y-2">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    Data Pertanian Terintegrasi
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Data harga TBS kelapa sawit dan komoditas hasil pertanian telah digabungkan ke dalam menu <strong>Informasi Publik</strong> untuk kemudahan pemantauan masyarakat.
                </p>
            </div>
            <div class="pt-2">
                <a
                    href="/layanan/informasi-publik"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm"
                >
                    <span>Buka Portal Informasi Publik</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
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
