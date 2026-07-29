<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { CheckCircle2, ShieldAlert } from '@lucide/vue'
import { formatMiliar, formatPersen } from '@/Utils/helpers'

const props = defineProps({
  samsat: {
    type: Object,
    required: true
  },
  selectedSector: {
    type: String,
    default: 'semua'
  }
})

// Highlight states based on selected sector
const isFocused = computed(() => props.selectedSector === 'pajak')

// Chart parameters
const series = computed(() => [Math.min(props.samsat.capaian_persen, 150)]) // Cap at 150% visually for radial wrap comfort

const chartOptions = computed(() => {
  const isOverTarget = props.samsat.capaian_persen >= 100
  const mainColor = isOverTarget ? '#10b981' : '#3b82f6'

  return {
    chart: {
      type: 'radialBar',
      fontFamily: 'Figtree, ui-sans-serif, system-ui',
      sparkline: {
        enabled: true
      }
    },
    colors: [mainColor],
    plotOptions: {
      radialBar: {
        startAngle: -110,
        endAngle: 110,
        hollow: {
          margin: 0,
          size: '72%',
          background: 'transparent'
        },
        track: {
          background: '#f1f5f9',
          strokeWidth: '97%',
          margin: 5
        },
        dataLabels: {
          show: true,
          name: {
            show: false
          },
          value: {
            offsetY: 8,
            color: '#1e293b',
            fontSize: '22px',
            fontWeight: 800,
            formatter: () => formatPersen(props.samsat.capaian_persen)
          }
        }
      }
    },
    fill: {
      type: 'solid'
    },
    stroke: {
      lineCap: 'round'
    },
    shadow: {
      enabled: isOverTarget,
      type: 'radial',
      color: '#10b981',
      blur: 8,
      opacity: 0.35
    }
  }
})
</script>

<template>
  <div
    :class="[
      'bg-white dark:bg-gray-800 rounded-2xl p-6 border transition-all duration-300 relative overflow-hidden flex flex-col justify-between h-full',
      isFocused
        ? 'border-blue-400 dark:border-blue-700 shadow-lg scale-[1.03] ring-4 ring-blue-500/10 dark:ring-blue-500/5'
        : 'border-slate-200/60 dark:border-gray-700/60 shadow-sm'
    ]"
  >
    <!-- Focus indicator badge -->
    <div
      v-if="isFocused"
      class="absolute top-0 right-0 bg-blue-500 text-white text-[9px] font-bold uppercase px-3 py-1 rounded-bl-xl tracking-wider animate-pulse"
    >
      Sektor Fokus Utama
    </div>

    <div>
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-base font-bold text-slate-800 dark:text-white">
            Kinerja SAMSAT
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ samsat.sumber_data }}
          </p>
        </div>
        <span
          :class="[
            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border',
            samsat.capaian_persen >= 100
              ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40 shadow-sm shadow-emerald-100/50'
              : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/40'
          ]"
        >
          <CheckCircle2 class="w-3.5 h-3.5" v-if="samsat.capaian_persen >= 100" />
          <span>{{ formatPersen(samsat.capaian_persen) }}</span>
        </span>
      </div>

      <!-- Main Section: Radial chart and values -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center my-4">
        <!-- Radial chart representation -->
        <div class="flex justify-center items-center h-36">
          <VueApexCharts
            type="radialBar"
            height="180"
            width="100%"
            :options="chartOptions"
            :series="series"
          />
        </div>

        <!-- Metric details -->
        <div class="space-y-4">
          <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">
              Target Anggaran
            </span>
            <span class="text-lg font-extrabold text-slate-700 dark:text-slate-300">
              {{ formatMiliar(samsat.total_target) }}
            </span>
          </div>

          <div>
            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">
              Realisasi Terbaru
            </span>
            <span class="text-xl font-extrabold text-slate-800 dark:text-white">
              {{ formatMiliar(samsat.total_realisasi) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Glow/Indicator styling -->
    <div
      v-if="samsat.capaian_persen >= 100"
      class="border-t border-emerald-100 dark:border-emerald-950/50 pt-4 mt-2 flex items-center gap-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
    >
      <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
      <span>Target Pendapatan SAMSAT Berhasil Dilampaui</span>
    </div>
  </div>
</template>
