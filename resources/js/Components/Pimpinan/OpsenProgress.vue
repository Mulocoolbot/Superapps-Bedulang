<script setup>
import { computed } from 'vue'
import { Landmark, LandmarkIcon } from '@lucide/vue'
import { formatMiliar } from '@/Utils/helpers'

const props = defineProps({
  opsen: {
    type: Object,
    required: true
  }
})

// Formatting numbers dynamically
const kabNominal = computed(() => formatMiliar(props.opsen.kabupaten_nominal))
const provNominal = computed(() => formatMiliar(props.opsen.provinsi_nominal))
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/60 dark:border-gray-700/60 shadow-sm h-full flex flex-col justify-between">
    <div>
      <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1">
        Distribusi Opsen Pajak Kendaraan (UU HKPD)
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
        Pembagian porsi bagi hasil pajak kendaraan bermotor daerah
      </p>
    </div>

    <!-- Stacked horizontal progress bar -->
    <div class="space-y-4 py-2">
      <div class="w-full bg-slate-100 dark:bg-gray-700 rounded-2xl h-8 flex overflow-hidden shadow-inner border border-slate-200/20">
        <!-- Kabupaten 66% (Emerald) -->
        <div
          class="bg-emerald-500 text-white text-xs font-extrabold flex items-center justify-center transition-all duration-700 hover:opacity-90"
          :style="{ width: `${opsen.kabupaten_persen}%` }"
          :aria-label="`Kas Daerah Kabupaten: ${opsen.kabupaten_persen}%`"
        >
          {{ opsen.kabupaten_persen }}%
        </div>
        <!-- Provinsi 34% (Slate) -->
        <div
          class="bg-slate-300 dark:bg-slate-500 text-slate-700 dark:text-slate-100 text-xs font-extrabold flex items-center justify-center transition-all duration-700 hover:opacity-90"
          :style="{ width: `${opsen.provinsi_persen}%` }"
          :aria-label="`Bagian Provinsi: ${opsen.provinsi_persen}%`"
        >
          {{ opsen.provinsi_persen }}%
        </div>
      </div>
    </div>

    <!-- Labels & Info Grid -->
    <div class="grid grid-cols-2 gap-4 mt-6 border-t border-slate-100 dark:border-gray-700/60 pt-4">
      <!-- Left Label: Kabupaten -->
      <div class="p-3 bg-emerald-50/50 dark:bg-emerald-950/10 rounded-xl border border-emerald-100/60 dark:border-emerald-900/30 flex items-start gap-3">
        <div class="p-2 bg-emerald-500 text-white rounded-lg mt-0.5 shrink-0">
          <Landmark class="w-4 h-4" />
        </div>
        <div>
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
            Kas Daerah ({{ opsen.kabupaten_persen }}%)
          </span>
          <span class="text-sm font-extrabold text-slate-800 dark:text-white block mt-0.5">
            {{ kabNominal }}
          </span>
          <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-semibold block mt-0.5">
            Pendapatan Bersih Kab.
          </span>
        </div>
      </div>

      <!-- Right Label: Provinsi -->
      <div class="p-3 bg-slate-50 dark:bg-gray-800/40 rounded-xl border border-slate-200/50 dark:border-gray-700/40 flex items-start gap-3">
        <div class="p-2 bg-slate-300 dark:bg-slate-500 text-slate-600 dark:text-slate-200 rounded-lg mt-0.5 shrink-0">
          <LandmarkIcon class="w-4 h-4" />
        </div>
        <div>
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
            Provinsi ({{ opsen.provinsi_persen }}%)
          </span>
          <span class="text-sm font-extrabold text-slate-800 dark:text-white block mt-0.5">
            {{ provNominal }}
          </span>
          <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold block mt-0.5">
            Porsi Pemerintah Provinsi
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
