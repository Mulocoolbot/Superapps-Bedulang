<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Header from '@/Components/Pimpinan/Header.vue'
import FilterToolbar from '@/Components/Pimpinan/FilterToolbar.vue'
import KPICards from '@/Components/Pimpinan/KPICards.vue'
import PADCompositionChart from '@/Components/Pimpinan/PADCompositionChart.vue'
import SamsatHighlight from '@/Components/Pimpinan/SamsatHighlight.vue'
import OpsenProgress from '@/Components/Pimpinan/OpsenProgress.vue'
import PerformanceProgress from '@/Components/Pimpinan/PerformanceProgress.vue'
import FooterSource from '@/Components/Pimpinan/FooterSource.vue'

import { dashboardData } from '@/Data/dashboardData'

// Reactive state filters
const selectedYear = ref(2025)
const selectedSector = ref('semua') // 'semua', 'pajak', 'retribusi', 'lain_lain', 'kekayaan'
const selectedStatus = ref('semua') // 'semua', 'terlampaui', 'on_track'

// Get current year active data
const yearData = computed(() => dashboardData[selectedYear.value])
const padMakro = computed(() => yearData.value.pad_makro)
const samsatDetail = computed(() => yearData.value.samsat_detail)

// Sektor filter synchronizer (updates from chart slice click)
const handleSelectSector = (sectorId) => {
  selectedSector.value = sectorId
}

// Conditional Visibility Logic based on Status Capaian Filter
// "Jika Terlampaui (>100%) maka hanya tampil: SAMSAT, PKB, BBNKB"
// "Jika On Track (<100%) maka tampil hanya komponen dengan capaian di bawah 100%"
const showGeneralPAD = computed(() => {
  return selectedStatus.value !== 'terlampaui'
})

const showSamsatSection = computed(() => {
  // Samsat has achievement 107.17% (exceeds 100%).
  // So it is hidden when filter is "on_track" (under 100%).
  // It is also hidden if user selects a sector other than "semua" and "pajak" (since Samsat handles only tax/pajak).
  if (selectedStatus.value === 'on_track') return false
  if (selectedSector.value !== 'semua' && selectedSector.value !== 'pajak') return false
  return true
})
</script>

<template>
  <Head title="Executive Dashboard PAD"></Head>

  <AdminLayout>
    <!-- CSS print rules to hide sidebar/nav header and format page nicely -->
    <component is="style">
      @media print {
        body {
          background-color: white !important;
          color: black !important;
        }
        aside, header, nav, button, a {
          display: none !important;
        }
        main {
          padding: 0 !important;
          margin: 0 !important;
        }
        .no-print {
          display: none !important;
        }
        .print-full {
          width: 100% !important;
          max-width: 100% !important;
          box-shadow: none !important;
          border: none !important;
        }
        .page-break {
          page-break-before: always;
        }
      }
    </component>

    <div class="space-y-6 max-w-[1600px] mx-auto pb-12 print-full">
      <!-- Title Header Section -->
      <Header
        title="EXECUTIVE DASHBOARD PAD"
        subtitle="Monitoring Realisasi Pendapatan Asli Daerah"
        :metaText="`Tahun Anggaran ${selectedYear}`"
      />

      <!-- Sticky Filter Bar -->
      <FilterToolbar
        v-model:selectedYear="selectedYear"
        v-model:selectedSector="selectedSector"
        v-model:selectedStatus="selectedStatus"
        :periodeText="yearData.periode_realisasi"
        class="no-print"
      />

      <!-- KPI Summary Section -->
      <KPICards
        v-if="showGeneralPAD"
        :target="padMakro.target_apbd_2025"
        :realisasi="padMakro.realisasi_jan_2026"
        :persen="padMakro.capaian_persen"
        :yoy="padMakro.pertumbuhan_yoy_persen"
        :tahun="selectedYear"
      />

      <!-- Dashboard Layout Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
        <!-- 1. Donut Chart (General PAD Composition) -->
        <div v-if="showGeneralPAD" class="h-full">
          <PADCompositionChart
            :komposisi="padMakro.komposisi"
            :selectedSector="selectedSector"
            @select-sector="handleSelectSector"
          />
        </div>

        <!-- 2. SAMSAT Highlight Performance -->
        <div v-if="showSamsatSection" :class="['h-full transition-all duration-300', !showGeneralPAD ? 'xl:col-span-2' : '']">
          <SamsatHighlight
            :samsat="samsatDetail"
            :selectedSector="selectedSector"
          />
        </div>
      </div>

      <!-- SAMSAT Sub-Details Section (Opsen & Horizontal Progress PKB/BBNKB) -->
      <div v-if="showSamsatSection" class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch mt-6">
        <!-- Opsen Stacked Progress -->
        <div class="h-full">
          <OpsenProgress :opsen="samsatDetail.opsen_porsi" />
        </div>

        <!-- PKB & BBNKB Horizontal Performance -->
        <div class="h-full">
          <PerformanceProgress :breakdown="samsatDetail.breakdown" />
        </div>
      </div>

      <!-- Data Source / Accountability Footer -->
      <FooterSource :samsatDetail="samsatDetail" />
    </div>
  </AdminLayout>
</template>
