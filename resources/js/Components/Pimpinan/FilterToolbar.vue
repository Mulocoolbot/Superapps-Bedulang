<script setup>
import { FileText, Calendar, Filter, Sparkles } from '@lucide/vue'
import { exportDashboard } from '@/Utils/helpers'

const props = defineProps({
  selectedYear: {
    type: [Number, String],
    required: true
  },
  selectedSector: {
    type: String,
    required: true
  },
  selectedStatus: {
    type: String,
    required: true
  },
  periodeText: {
    type: String,
    default: 'Januari 2026'
  }
})

const emit = defineEmits(['update:selectedYear', 'update:selectedSector', 'update:selectedStatus'])

const handleYearChange = (event) => {
  emit('update:selectedYear', Number(event.target.value))
}

const handleSectorChange = (event) => {
  emit('update:selectedSector', event.target.value)
}

const handleStatusChange = (event) => {
  emit('update:selectedStatus', event.target.value)
}
</script>

<template>
  <div class="sticky top-0 z-40 bg-slate-50/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-slate-200 dark:border-gray-800 -mx-8 px-8 py-3 mb-6 flex flex-wrap items-center justify-between gap-4">
    <!-- Filters group -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Calendar Icon / Filter Title -->
      <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider pr-2">
        <Filter class="w-4 h-4 text-blue-500" />
        <span>Filter Panel</span>
      </div>

      <!-- Tahun Anggaran Select -->
      <div class="flex flex-col">
        <label for="filter-year" class="sr-only">Tahun Anggaran</label>
        <div class="relative">
          <select
            id="filter-year"
            :value="selectedYear"
            @change="handleYearChange"
            class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-200 rounded-xl py-1.5 pl-3 pr-8 text-sm font-semibold shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
          >
            <option :value="2025">TA 2025</option>
            <option :value="2024">TA 2024</option>
          </select>
        </div>
      </div>

      <!-- Periode Realisasi (Disabled, determined by Year) -->
      <div class="flex items-center gap-2 bg-slate-100 dark:bg-gray-800 border border-slate-200/50 dark:border-gray-700/50 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 shadow-sm">
        <Calendar class="w-4 h-4 text-slate-400" />
        <span>Realisasi s.d: <span class="text-blue-600 dark:text-blue-400">{{ periodeText }}</span></span>
      </div>

      <!-- Sektor PAD Select -->
      <div class="flex flex-col">
        <label for="filter-sector" class="sr-only">Sektor PAD</label>
        <select
          id="filter-sector"
          :value="selectedSector"
          @change="handleSectorChange"
          class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-200 rounded-xl py-1.5 pl-3 pr-8 text-sm font-semibold shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        >
          <option value="semua">Semua Sektor PAD</option>
          <option value="pajak">Pajak Daerah</option>
          <option value="retribusi">Retribusi Daerah</option>
          <option value="lain_lain">Lain-Lain PAD yang Sah</option>
          <option value="kekayaan">Kekayaan Daerah Dipisahkan</option>
        </select>
      </div>

      <!-- Status Capaian Select -->
      <div class="flex flex-col">
        <label for="filter-status" class="sr-only">Status Capaian</label>
        <select
          id="filter-status"
          :value="selectedStatus"
          @change="handleStatusChange"
          class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-200 rounded-xl py-1.5 pl-3 pr-8 text-sm font-semibold shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
        >
          <option value="semua">Semua Capaian</option>
          <option value="terlampaui">Terlampaui (>100%)</option>
          <option value="on_track">On Track (<100%)</option>
        </select>
      </div>
    </div>

    <!-- Actions group -->
    <div class="flex items-center">
      <!-- Export PDF button -->
      <button
        @click="exportDashboard"
        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700 font-semibold shadow-sm transition text-sm cursor-pointer"
        title="Cetak/Simpan Laporan PDF"
      >
        <FileText class="w-4 h-4 text-emerald-500" />
        <span>Ekspor PDF</span>
      </button>
    </div>
  </div>
</template>
