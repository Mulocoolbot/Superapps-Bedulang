<script setup>
import { Link } from '@inertiajs/vue3'
import { PlusCircle, ArrowLeft } from '@lucide/vue'

defineProps({
  title: {
    type: String,
    default: 'EXECUTIVE DASHBOARD PAD'
  },
  subtitle: {
    type: String,
    default: 'Monitoring Realisasi Pendapatan Asli Daerah'
  },
  metaText: {
    type: String,
    default: 'Tahun Anggaran 2025'
  }
})
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-gray-700/60 pb-6 mb-6">
    <div>
      <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight leading-none uppercase">
        {{ title }}
      </h1>
      <p class="text-lg font-semibold text-blue-600 dark:text-blue-400 mt-2">
        Kabupaten Bangka Tengah
      </p>
      <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
        {{ subtitle }} • <span class="font-bold text-slate-700 dark:text-slate-300">{{ metaText }}</span>
      </p>
    </div>

    <!-- Right-aligned action buttons -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Back button to main Dashboard Pimpinan -->
      <Link
        href="/pimpinan"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700/60 font-semibold shadow-sm transition text-sm"
        title="Kembali ke Dashboard Utama"
      >
        <ArrowLeft class="w-4 h-4 text-slate-500 dark:text-slate-400" />
        <span>Kembali</span>
      </Link>

      <!-- Ajukan Pengaduan button -->
      <Link
        href="/layanan/pengaduan-bedulang"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold shadow-sm transition text-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        title="Ajukan Pengaduan Baru"
      >
        <PlusCircle class="w-4 h-4 text-white" />
        <span>Ajukan Pengaduan</span>
      </Link>
    </div>
  </div>
</template>
