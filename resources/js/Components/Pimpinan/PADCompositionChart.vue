<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { formatMiliar } from '@/Utils/helpers'

const props = defineProps({
  komposisi: {
    type: Array,
    required: true
  },
  selectedSector: {
    type: String,
    default: 'semua'
  }
})

const emit = defineEmits(['select-sector'])

// Base colors for the 4 categories
const baseColors = {
  pajak: '#3b82f6',     // Blue-500
  retribusi: '#10b981', // Emerald-500
  lain_lain: '#f59e0b', // Amber-500
  kekayaan: '#8b5cf6'   // Violet-500
}

// Map index to sector ID
const indexToIdMap = ['pajak', 'retribusi', 'lain_lain', 'kekayaan']

const chartSeries = computed(() => {
  return props.komposisi.map(item => item.target)
})

const chartColors = computed(() => {
  // If no sector is selected, use full bright colors
  if (props.selectedSector === 'semua') {
    return [baseColors.pajak, baseColors.retribusi, baseColors.lain_lain, baseColors.kekayaan]
  }

  // If a sector is selected, highlight it and dim others (40% opacity)
  return indexToIdMap.map(id => {
    if (id === props.selectedSector) {
      return baseColors[id] // Bright
    } else {
      return baseColors[id] + '66' // Dimmed (approx 40% opacity in hex '66')
    }
  })
})

const chartOptions = computed(() => {
  return {
    chart: {
      type: 'donut',
      events: {
        dataPointSelection: (event, chartContext, config) => {
          const selectedIndex = config.dataPointIndex
          if (selectedIndex !== undefined && selectedIndex >= 0) {
            const sectorId = indexToIdMap[selectedIndex]
            // If clicking the already selected sector, toggle back to 'semua'
            if (props.selectedSector === sectorId) {
              emit('select-sector', 'semua')
            } else {
              emit('select-sector', sectorId)
            }
          }
        }
      },
      fontFamily: 'Figtree, ui-sans-serif, system-ui',
      toolbar: {
        show: false
      }
    },
    labels: props.komposisi.map(item => item.nama),
    colors: chartColors.value,
    stroke: {
      width: 2,
      colors: ['#ffffff']
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: true,
      position: 'bottom',
      horizontalAlign: 'center',
      fontSize: '13px',
      fontWeight: 600,
      labels: {
        colors: '#64748b' // Slate-500
      },
      itemMargin: {
        horizontal: 8,
        vertical: 4
      }
    },
    tooltip: {
      enabled: true,
      y: {
        formatter: (value) => formatMiliar(value)
      }
    },
    plotOptions: {
      pie: {
        donut: {
          size: '72%',
          labels: {
            show: true,
            name: {
              show: true,
              fontSize: '14px',
              fontWeight: 600,
              color: '#64748b',
              offsetY: -8
            },
            value: {
              show: true,
              fontSize: '20px',
              fontWeight: 800,
              color: '#1e293b', // Slate-800
              offsetY: 8,
              formatter: (value) => {
                const num = Number(value)
                return `Rp${(num / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 })} M`
              }
            },
            total: {
              show: true,
              label: 'Total Target',
              color: '#64748b',
              fontSize: '13px',
              fontWeight: 600,
              formatter: (w) => {
                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                return `Rp${(total / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 })} M`
              }
            }
          }
        }
      }
    }
  }
})
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/60 dark:border-gray-700/60 shadow-sm h-full flex flex-col justify-between">
    <div>
      <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1">
        Komposisi Target PAD
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
        Klik sektor pada chart untuk menyaring detail data realisasi
      </p>
    </div>

    <!-- Chart container -->
    <div class="flex-1 flex items-center justify-center py-2">
      <VueApexCharts
        type="donut"
        width="100%"
        max-width="380"
        :options="chartOptions"
        :series="chartSeries"
      />
    </div>

    <!-- Sub sector list/legends with customized selection indicators -->
    <div class="mt-6 border-t border-slate-100 dark:border-gray-700/60 pt-4 space-y-2.5">
      <div
        v-for="(item, idx) in komposisi"
        :key="item.id"
        @click="emit('select-sector', selectedSector === item.id ? 'semua' : item.id)"
        :class="[
          'flex items-center justify-between p-2 rounded-xl border text-xs cursor-pointer transition-all duration-200',
          selectedSector === item.id
            ? 'bg-blue-50/70 border-blue-200 text-blue-800 font-bold dark:bg-blue-950/20 dark:border-blue-900/50 dark:text-blue-300'
            : selectedSector === 'semua'
              ? 'border-transparent hover:bg-slate-50 dark:hover:bg-gray-700/40 text-slate-600 dark:text-slate-300'
              : 'border-transparent opacity-40 hover:opacity-70 text-slate-400'
        ]"
      >
        <div class="flex items-center gap-2 min-w-0">
          <span
            class="w-3 h-3 rounded-full shrink-0"
            :style="{ backgroundColor: baseColors[item.id] }"
          ></span>
          <span class="truncate font-semibold">{{ item.nama }}</span>
        </div>
        <div class="flex items-center gap-2 font-bold shrink-0">
          <span class="text-slate-500 dark:text-slate-400">({{ item.persen_kontribusi }}%)</span>
          <span>{{ formatMiliar(item.target) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
