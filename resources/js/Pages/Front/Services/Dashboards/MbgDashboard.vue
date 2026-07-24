<script setup>
import { computed, ref, watch } from 'vue'
import AnimatedNumber from '@/Components/Front/AnimatedNumber.vue'

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

const dashboardData = computed(() => props.dashboard ?? {})

const availableJenjangs = computed(() => {
    return dashboardData.value.available_jenjangs || ['paud', 'sd', 'smp']
})

const serviceTypes = computed(() => {
    return availableJenjangs.value.map(j => ({
        label: j.toUpperCase(),
        value: j
    }))
})

const selectedType = ref(availableJenjangs.value[0] || 'paud')

// If data changes, ensure selectedType is valid
watch(availableJenjangs, (newVal) => {
    if (!newVal.includes(selectedType.value) && newVal.length > 0) {
        selectedType.value = newVal[0]
    }
})

const maxStudentCount = computed(() => {
    const list = dashboardData.value.tables?.[selectedType.value] || []
    if (list.length === 0) return 1
    return Math.max(...list.map(r => (r.jumlah_perempuan || 0) + (r.jumlah_laki || 0)), 1)
})

const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0)
}
</script>

<template>

    <!-- Summary Statistics Grid -->
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4 animate-fade-in-up [animation-delay:150ms]">
        <div 
            v-for="(card, index) in (dashboardData.summary?.[selectedType] || [])" 
            :key="index"
            class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700"
        >
            <!-- Highlight color based on index or title -->
            <div 
                class="absolute left-0 top-0 bottom-0 w-1.5"
                :class="[
                    index === 0 ? 'bg-blue-600 dark:bg-blue-500' : '',
                    index === 1 ? 'bg-indigo-600 dark:bg-indigo-500' : '',
                    index === 2 ? 'bg-pink-600 dark:bg-pink-500' : '',
                    index === 3 ? 'bg-amber-600 dark:bg-amber-500' : ''
                ]"
            />
            <div class="pl-2">
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">
                    {{ card.title }}
                </span>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    <AnimatedNumber :value="card.value"></AnimatedNumber>
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ card.meta }}</p>
            </div>
        </div>
    </div>

    <!-- Main Grid for Makanan Bergizi Gratis (MBG) -->
    <div class="mt-6 grid gap-6 lg:grid-cols-[2fr_1fr]">
        <!-- Left Column: Table of subdistricts -->
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Data Wilayah</p>
                    <h2 class="mt-0.5 text-lg font-extrabold text-slate-900 dark:text-white">
                        Rincian Penerima & Kondisi Kesehatan per Kecamatan
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan data penerima program Makanan Bergizi Gratis di tingkat {{ selectedType.toUpperCase() }}.
                    </p>
                </div>
                <div class="shrink-0">
                    <select
                        v-model="selectedType"
                        class="rounded-xl border-slate-200 bg-slate-50 py-2 pl-4 pr-10 text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        <option v-for="type in serviceTypes" :key="type.value" :value="type.value">
                            Jenjang: {{ type.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Kecamatan</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Perempuan</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Laki-laki</th>
                                <th class="px-4 py-3 text-right font-semibold text-amber-600 dark:text-amber-400">Alergi</th>
                                <th class="px-4 py-3 text-right font-semibold text-rose-600 dark:text-rose-400">Fobia</th>
                                <th class="px-4 py-3 text-right font-semibold text-indigo-600 dark:text-indigo-400">Intoleran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            <tr 
                                v-for="row in (dashboardData.tables?.[selectedType] || [])" 
                                :key="row.kode_kecamatan" 
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="px-4 py-3.5 font-bold text-slate-800 dark:text-slate-200">{{ row.kecamatan }}</td>
                                <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-300">{{ formatNumber(row.jumlah_perempuan) }}</td>
                                <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-300">{{ formatNumber(row.jumlah_laki) }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-amber-600 dark:text-amber-400 bg-amber-50/20 dark:bg-amber-950/10">{{ formatNumber(row.jumlah_alergi) }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-rose-600 dark:text-rose-400 bg-rose-50/20 dark:bg-rose-950/10">{{ formatNumber(row.jumlah_fobia) }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50/20 dark:bg-indigo-950/10">{{ formatNumber(row.jumlah_intoleransi) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Right Column: Visualizations / Charts -->
        <aside class="space-y-6 animate-fade-in-up [animation-delay:250ms]">
            <!-- Chart Card: Total Penerima per Kecamatan -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Statistik</p>
                    <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Peta Sebaran Total Penerima</h3>
                </div>

                <!-- Mini Horizontal Bar Chart -->
                <div class="space-y-4">
                    <div 
                        v-for="(row, index) in (dashboardData.tables?.[selectedType] || [])" 
                        :key="'chart-' + selectedType + '-' + row.kode_kecamatan" 
                        class="space-y-1.5"
                    >
                        <div class="flex justify-between text-xs font-semibold text-slate-700 dark:text-slate-200">
                            <span>{{ row.kecamatan }}</span>
                            <span class="font-bold"><AnimatedNumber :value="row.jumlah_perempuan + row.jumlah_laki"></AnimatedNumber> siswa</span>
                        </div>
                        <div class="relative group py-0.5 focus:outline-none" tabindex="0">
                            <!-- Track -->
                            <div class="bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden">
                                <div 
                                    class="bg-blue-600 dark:bg-blue-500 h-full rounded-full transition-all duration-700 ease-out animate-grow-width" 
                                    :style="{ 
                                        width: `${Math.max(5, Math.min(100, Math.ceil(((row.jumlah_perempuan + row.jumlah_laki) / maxStudentCount) * 100)))}%` 
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Summary / Risk Cards -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Kesehatan</p>
                    <h3 class="mt-0.5 text-base font-extrabold text-slate-900 dark:text-white">Kebutuhan Medis Khusus</h3>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-xl bg-amber-50/50 p-3 border border-amber-100/50 dark:bg-amber-950/10 dark:border-amber-900/20">
                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Alergi</span>
                        <p class="mt-1 text-xl font-extrabold text-amber-700 dark:text-amber-300">
                            <AnimatedNumber :value="(dashboardData.tables?.[selectedType] || []).reduce((sum, r) => sum + r.jumlah_alergi, 0)" />
                        </p>
                    </div>
                    <div class="rounded-xl bg-rose-50/50 p-3 border border-rose-100/50 dark:bg-rose-950/10 dark:border-rose-900/20">
                        <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Fobia</span>
                        <p class="mt-1 text-xl font-extrabold text-rose-700 dark:text-rose-300">
                            <AnimatedNumber :value="(dashboardData.tables?.[selectedType] || []).reduce((sum, r) => sum + r.jumlah_fobia, 0)" />
                        </p>
                    </div>
                    <div class="rounded-xl bg-indigo-50/50 p-3 border border-indigo-100/50 dark:bg-indigo-950/10 dark:border-indigo-900/20">
                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Intoleran</span>
                        <p class="mt-1 text-xl font-extrabold text-indigo-700 dark:text-indigo-300">
                            <AnimatedNumber :value="(dashboardData.tables?.[selectedType] || []).reduce((sum, r) => sum + r.jumlah_intoleransi, 0)" />
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>


