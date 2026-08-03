<script setup>
import { ref, onMounted, watch } from 'vue'
import { CheckCircle2, TrendingUp } from '@lucide/vue'
import { formatMiliar, formatPersen, getBadgeColor } from '@/Utils/helpers'

const props = defineProps({
  breakdown: {
    type: Array,
    required: true
  }
})

// Trigger width animation on mount
const animateProgress = ref(false)

const triggerAnimation = () => {
  animateProgress.value = false
  setTimeout(() => {
    animateProgress.value = true
  }, 100)
}

onMounted(() => {
  triggerAnimation()
})

watch(() => props.breakdown, () => {
  triggerAnimation()
})
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-slate-200/60 dark:border-gray-700/60 shadow-sm h-full flex flex-col justify-between">
    <div>
      <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1">
        Detail Performa Komponen SAMSAT
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
        Realisasi Pajak Kendaraan Bermotor (PKB) & Bea Balik Nama (BBNKB)
      </p>
    </div>

    <!-- Progress Cards list -->
    <div class="space-y-6 flex-1 flex flex-col justify-center">
      <div v-for="item in breakdown" :key="item.jenis" class="space-y-2">
        <div class="flex items-center justify-between text-sm">
          <div class="flex items-center gap-2">
            <span class="font-extrabold text-slate-800 dark:text-white">
              {{ item.jenis }}
            </span>
            <span class="text-xs text-slate-500 dark:text-slate-400">
              ({{ item.jenis === 'PKB' ? 'Pajak Kendaraan Bermotor' : 'Bea Balik Nama Kendaraan Bermotor' }})
            </span>
          </div>

          <!-- Capaian Badge -->
          <span
            :class="[
              'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border',
              getBadgeColor(item.status)
            ]"
          >
            <CheckCircle2 class="w-3 h-3" v-if="item.capaian >= 100" />
            <span>{{ formatPersen(item.capaian) }}</span>
          </span>
        </div>

        <!-- Animated Progress bar container -->
        <div class="w-full bg-slate-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden border border-slate-200/20">
          <div
            :class="[
              'h-full rounded-full transition-all duration-1000 ease-out',
              item.capaian >= 100 ? 'bg-emerald-500 shadow-sm shadow-emerald-500/25' : 'bg-blue-500'
            ]"
            :style="{ width: animateProgress ? `${Math.min(item.capaian, 100)}%` : '0%' }"
          ></div>
        </div>

        <!-- Values Subtitle Row -->
        <div class="grid grid-cols-2 text-xs font-semibold text-slate-500 dark:text-slate-400 pt-1">
          <div>
            Target: <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatMiliar(item.target) }}</span>
          </div>
          <div class="text-right">
            Realisasi: <span class="font-bold text-slate-800 dark:text-white">{{ formatMiliar(item.realisasi) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer note -->
    <div class="mt-6 border-t border-slate-100 dark:border-gray-700/60 pt-4 text-[10px] text-slate-400 font-semibold flex items-center gap-1.5">
      <TrendingUp class="w-3.5 h-3.5 text-emerald-500" />
      <span>Seluruh komponen utama Pajak Kendaraan berhasil melampaui target APBD.</span>
    </div>
  </div>
</template>
