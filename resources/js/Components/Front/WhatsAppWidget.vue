<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ComplaintModal from '@/Components/ComplaintModal.vue'

const page = usePage()
const isOpen = ref(false)
const isModalOpen = ref(false)

const service = computed(() => page.props.service)
const whatsappPhone = computed(() => page.props.whatsapp?.complaint_phone ?? '6283128848371')
const isAuthenticated = computed(() => !!page.props.auth?.user)

const whatsappUrl = computed(() => {
  let text = 'Halo Admin Layanan Pengaduan Bedulang, saya ingin menyampaikan pengaduan.'
  if (service.value?.name) {
    text = `Halo Admin Layanan Pengaduan Bedulang, saya ingin menyampaikan pengaduan terkait layanan ${service.value.name}.`
  }
  return `https://wa.me/${whatsappPhone.value}?text=${encodeURIComponent(text)}`
})

const toggleWidget = () => {
  isOpen.value = !isOpen.value
}

const openComplaintModal = () => {
  isModalOpen.value = true
  isOpen.value = false
}
</script>

<template>
  <div class="fixed bottom-6 right-6 z-50 font-sans">
    <!-- Chat Widget Window -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-4 opacity-0 scale-95"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100 scale-100"
      leave-to-class="transform translate-y-4 opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="mb-4 w-[350px] overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
      >
        <!-- Header -->
        <div class="relative bg-emerald-600 px-5 py-4 text-white dark:bg-emerald-700">
          <div class="flex items-center gap-3">
            <!-- Avatar Container with pulsing status indicator -->
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/30 text-xl">
              💬
              <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-emerald-600 bg-green-400 dark:border-emerald-700"></span>
            </div>
            <div>
              <h3 class="font-bold leading-tight">Pengaduan Bedulang</h3>
              <p class="text-xs text-emerald-100 flex items-center gap-1.5 mt-0.5">
                <span class="inline-block h-2 w-2 rounded-full bg-green-300 animate-pulse"></span>
                Biasanya membalas dengan cepat
              </p>
            </div>
          </div>
          <button
            @click="toggleWidget"
            class="absolute right-4 top-4 text-white/80 hover:text-white transition"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Chat Body -->
        <div class="bg-gray-50/50 p-5 dark:bg-gray-900/50 space-y-4">
          <!-- Welcome Message -->
          <div class="rounded-2xl rounded-tl-none bg-white p-4 text-sm text-gray-700 shadow-sm border border-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-800">
            <p class="leading-relaxed">
              Halo! Ada keluhan atau pengaduan terkait layanan kami? Silakan hubungi kami atau ajukan laporan resmi secara online.
            </p>
            <span class="block text-[10px] text-gray-400 mt-2 text-right">Customer Support</span>
          </div>

          <!-- Quick Action Buttons -->
          <div class="space-y-2.5 pt-2">
            <!-- WhatsApp Direct Button -->
            <a
              :href="whatsappUrl"
              target="_blank"
              class="flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-600 transition shadow-md shadow-emerald-500/10 hover:shadow-lg hover:shadow-emerald-500/20"
            >
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.734-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.437.002 9.861-4.416 9.863-9.864.001-2.639-1.03-5.122-2.905-6.995C16.35 1.879 13.88 1.81 12.01 1.81c-5.439 0-9.865 4.418-9.867 9.867-.001 1.84.49 3.63 1.432 5.176l-1.018 3.719 3.822-.98c1.554.848 3.1 1.3 4.67 1.3zM16.536 13.85c-.247-.125-1.464-.723-1.692-.806-.228-.083-.393-.125-.558.125-.166.247-.64.806-.784.969-.145.166-.29.185-.538.06-.247-.125-1.044-.385-1.988-1.229-.735-.656-1.232-1.465-1.378-1.712-.145-.247-.015-.38.11-.504.11-.11.247-.29.37-.434.125-.145.166-.248.248-.413.083-.165.04-.31-.02-.434-.06-.125-.558-1.344-.764-1.839-.2-.486-.403-.42-.558-.428-.145-.006-.31-.008-.475-.008-.165 0-.434.062-.66.31-.228.247-.868.847-.868 2.066 0 1.218.888 2.396.988 2.531.1.136 1.748 2.67 4.235 3.74.59.255 1.053.408 1.41.521.594.19 1.134.162 1.562.098.477-.071 1.464-.598 1.67-.176.206.422.206.784.103.908-.103.124-.558.124-.805 0z" />
              </svg>
              Chat via WhatsApp
            </a>

            <!-- Formal Online Complaint -->
            <button
              @click="openComplaintModal"
              class="w-full flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
              📢 Isi Form Pengaduan Resmi
            </button>
          </div>
        </div>

        <!-- Footer info -->
        <div class="bg-gray-100 px-5 py-2.5 text-center text-[10px] text-gray-400 dark:bg-gray-950">
          Superapp Kabupaten Bangka Tengah
        </div>
      </div>
    </Transition>

    <!-- Complaint Modal Integration -->
    <ComplaintModal
      :is-open="isModalOpen"
      :service-name="service?.name || 'Layanan'"
      :service-slug="service?.slug || ''"
      @close="isModalOpen = false"></ComplaintModal>
  </div>
</template>
