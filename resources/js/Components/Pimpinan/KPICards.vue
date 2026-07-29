<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Landmark, ArrowUpRight, TrendingUp, HelpCircle } from '@lucide/vue'
import { formatMiliar, formatPersen } from '@/Utils/helpers'

const props = defineProps({
  target: {
    type: Number,
    required: true
  },
  realisasi: {
    type: Number,
    required: true
  },
  persen: {
    type: Number,
    required: true
  },
  yoy: {
    type: Number,
    required: true
  },
  tahun: {
    type: Number,
    required: true
  }
})

// Animated fraction from 0 to 1
const animatedFraction = ref(0)

const animate = () => {
  let startTime = null
  const duration = 800 // 800ms animation duration

  const step = (timestamp) => {
    if (!startTime) startTime = timestamp
    const progress = Math.min((timestamp - startTime) / duration, 1)
    animatedFraction.value = progress
    if (progress < 1) {
      window.requestAnimationFrame(step)
    }
  }
  window.requestAnimationFrame(step)
}

onMounted(() => {
  animate()
})

// Re-run animation if target changes (e.g., year changed)
watch(() => props.target, () => {
  animatedFraction.value = 0
  animate()
})

// Calculations for current animated states
const currentTarget = computed(() => props.target * animatedFraction.value)
const currentRealisasi = computed(() => props.realisasi * animatedFraction.value)
const currentPersen = computed(() => props.persen * animatedFraction.value)
const currentRemaining = computed(() => {
  const rem = Math.max(props.target - props.realisasi, 0)
  return rem * animatedFraction.value
})
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Card 1: Target PAD -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/60 dark:border-gray-700/60 shadow-sm relative overflow-hidden group">
      <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-24 h-24 bg-blue-50/40 dark:bg-blue-900/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
      
      <div class="flex items-center justify-between mb-4">
        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Target PAD
        </span>
        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl dark:bg-blue-950/40 dark:text-blue-400">
          <Landmark class="w-5 h-5" />
        </div>
      </div>
      
      <div>
        <h3 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
          {{ formatMiliar(currentTarget) }}
        </h3>
        <div class="mt-3 flex items-center gap-1.5 text-xs">
          <span class="inline-flex items-center gap-0.5 font-bold text-emerald-600 dark:text-emerald-400">
            <ArrowUpRight class="w-3.5 h-3.5" />
            <span>Naik {{ yoy }}%</span>
          </span>
          <span class="text-slate-500 font-medium">dibanding TA {{ tahun - 1 }}</span>
        </div>
      </div>
    </div>

    <!-- Card 2: Realisasi -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/60 dark:border-gray-700/60 shadow-sm relative overflow-hidden group">
      <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-24 h-24 bg-emerald-50/40 dark:bg-emerald-900/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
      
      <div class="flex items-center justify-between mb-4">
        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Realisasi PAD
        </span>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
          {{ formatPersen(currentPersen) }}
        </span>
      </div>
      
      <div>
        <h3 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
          {{ formatMiliar(currentRealisasi) }}
        </h3>
        <div class="mt-3 flex items-center gap-1 text-xs">
          <span class="text-slate-500 font-medium">Update per:</span>
          <span class="text-blue-600 dark:text-blue-400 font-bold">Januari {{ tahun + 1 }}</span>
        </div>
      </div>
    </div>

    <!-- Card 3: Sisa Target -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/60 dark:border-gray-700/60 shadow-sm relative overflow-hidden group">
      <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-24 h-24 bg-amber-50/40 dark:bg-amber-900/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
      
      <div class="flex items-center justify-between mb-4">
        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Sisa Target
        </span>
        <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl dark:bg-amber-950/40 dark:text-amber-400">
          <TrendingUp class="w-5 h-5" />
        </div>
      </div>
      
      <div>
        <h3 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
          {{ formatMiliar(currentRemaining) }}
        </h3>
        <div class="mt-3 flex items-center gap-1 text-xs">
          <span class="text-slate-500 font-medium">Sisa Target APBD TA {{ tahun }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
