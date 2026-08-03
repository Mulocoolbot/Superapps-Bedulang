<script setup>
import { ShieldCheck, Info } from '@lucide/vue'

defineProps({
  samsatDetail: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="mt-8 border-t border-slate-200 dark:border-gray-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400 dark:text-slate-500 font-medium">
    <!-- Authority/Responsibility info -->
    <div class="flex items-center gap-2">
      <ShieldCheck class="w-4 h-4 text-emerald-500" />
      <span>Penanggung Jawab Samsat: <span class="font-bold text-slate-600 dark:text-slate-300">{{ samsatDetail.penanggung_jawab }}</span> (Kepala {{ samsatDetail.sumber_data }})</span>
    </div>

    <!-- Data source info -->
    <div class="flex items-center gap-2">
      <Info class="w-4 h-4 text-blue-500" />
      <span>Sumber Data: BPKPD Kab. Bangka Tengah & {{ samsatDetail.sumber_data }}</span>
    </div>
  </div>
</template>
