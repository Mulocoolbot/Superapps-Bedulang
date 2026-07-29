<script setup>
import { computed } from 'vue'
import { Landmark, BarChart3, TrendingUp, Users, Shield, AlertTriangle, Activity, Award } from '@lucide/vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: String,
    required: true
  },
  progress: {
    type: [Number, String],
    default: null
  },
  subtitle: {
    type: String,
    default: ''
  },
  footerText: {
    type: String,
    default: ''
  },
  badgeText: {
    type: String,
    default: ''
  },
  isActive: {
    type: Boolean,
    default: false
  },
  iconName: {
    type: String,
    default: 'Landmark'
  }
})

// Map icon string to Lucide component
const iconComponent = computed(() => {
  const icons = {
    Landmark,
    BarChart3,
    TrendingUp,
    Users,
    Shield,
    AlertTriangle,
    Activity,
    Award
  }
  return icons[props.iconName] || Landmark
})

const progressValue = computed(() => {
  if (props.progress === null) return 0
  return parseFloat(props.progress)
})
</script>

<template>
  <div
    :class="[
      'bg-white dark:bg-gray-800 rounded-2xl p-6 border transition-all duration-300 flex flex-col justify-between h-full',
      isActive
        ? 'border-blue-200 dark:border-blue-900/50 shadow-sm hover:shadow-lg hover:-translate-y-1 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500'
        : 'border-slate-200/60 dark:border-gray-700 opacity-65 cursor-not-allowed select-none'
    ]"
    :tabindex="isActive ? 0 : -1"
    :aria-label="`${title}: ${value}. ${isActive ? 'Klik untuk melihat detail.' : 'Segera hadir.'}`"
  >
    <div class="space-y-4">
      <!-- Header of Card -->
      <div class="flex items-start justify-between">
        <div>
          <span
            v-if="badgeText"
            :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider',
              isActive
                ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
                : 'bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-gray-400'
            ]"
          >
            {{ badgeText }}
          </span>
          <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 mt-2">
            {{ title }}
          </h3>
        </div>
        <div
          :class="[
            'p-3 rounded-xl transition',
            isActive
              ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400'
              : 'bg-slate-100 text-slate-400 dark:bg-gray-700 dark:text-gray-500'
          ]"
        >
          <component :is="iconComponent" class="w-6 h-6" />
        </div>
      </div>

      <!-- Content Values -->
      <div>
        <h4 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
          {{ value }}
        </h4>
        <div v-if="progress !== null" class="mt-3">
          <div class="flex items-center justify-between text-xs font-bold mb-1.5">
            <span class="text-slate-500">Capaian</span>
            <span :class="isActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600'">
              {{ progress }}%
            </span>
          </div>
          <div class="w-full bg-slate-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
            <div
              :class="[
                'h-full rounded-full transition-all duration-500',
                isActive ? 'bg-emerald-500' : 'bg-slate-400'
              ]"
              :style="{ width: `${Math.min(progressValue, 100)}%` }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Subtitle and Footer -->
    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-gray-700/60 flex flex-col gap-1">
      <p class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-pre-line" v-if="subtitle">
        {{ subtitle }}
      </p>
      <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold" v-if="footerText">
        {{ footerText }}
      </p>
    </div>
  </div>
</template>
