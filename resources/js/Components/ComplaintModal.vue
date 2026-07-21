<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-2xl transform overflow-hidden rounded-lg bg-white p-6 shadow-xl transition-all"
            >
              <div class="flex items-center justify-between mb-4">
                <DialogTitle class="text-xl font-bold text-gray-900">
                  📢 Form Pengaduan
                </DialogTitle>
                <button
                  type="button"
                  @click="closeModal"
                  class="text-gray-400 hover:text-gray-500"
                >
                  <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              <p class="mb-6 text-sm text-gray-600">
                Kirimkan pengaduan Anda terkait layanan {{ serviceSlug ? serviceName : 'kami' }}. Data Anda akan
                diproses dengan baik oleh tim kami.
              </p>

              <form @submit.prevent="submitComplaint" class="space-y-4">
                <div v-if="errors.general" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                  {{ errors.general }}
                </div>
                <!-- Service Info -->
                <div class="rounded-md bg-blue-50 p-4 mb-6">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <svg
                        class="h-5 w-5 text-blue-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-blue-800">
                        Pengaduan Anda akan dicatat dan diteruskan kepada tim layanan.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Service Selection Dropdown if serviceSlug is empty -->
                <div v-if="!serviceSlug">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih Layanan Terkait *
                  </label>
                  <select
                    v-model="selectedServiceSlug"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                  >
                    <option value="">Pilih layanan...</option>
                    <option v-for="s in servicesList" :key="s.slug" :value="s.slug">
                      {{ s.name }}
                    </option>
                  </select>
                  <span v-if="errors.service_slug" class="text-sm text-red-600">
                    {{ errors.service_slug }}
                  </span>
                </div>

                <!-- Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Pengaduan *
                  </label>
                  <input
                    v-model="form.title"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Ringkas pengaduan Anda"
                    required
                  />
                  <span v-if="errors.title" class="text-sm text-red-600">
                    {{ errors.title }}
                  </span>
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi Pengaduan *
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Jelaskan pengaduan Anda secara detail"
                    required
                  ></textarea>
                  <span v-if="errors.description" class="text-sm text-red-600">
                    {{ errors.description }}
                  </span>
                </div>

                <!-- Contact Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Lengkap *
                  </label>
                  <input
                    v-model="form.contact_person_name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Nama Anda"
                    required
                  />
                  <span v-if="errors.contact_person_name" class="text-sm text-red-600">
                    {{ errors.contact_person_name }}
                  </span>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6 pt-4 border-t">
                  <button
                    type="button"
                    @click="closeModal"
                    class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium transition"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 font-medium transition flex items-center justify-center gap-2"
                  >
                    <svg
                      v-if="!isSubmitting"
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                      />
                    </svg>
                    <span v-if="isSubmitting">Mengirim...</span>
                    <span v-else>Kirim Pengaduan</span>
                  </button>
                </div>
              </form>

              <!-- Success Message -->
              <Transition
                enter-active-class="duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="duration-300 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4"
              >
                <div
                  v-if="isSuccess"
                  class="mt-4 rounded-md bg-green-50 p-4 border border-green-200"
                >
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <svg
                        class="h-5 w-5 text-green-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-green-800">
                        {{ successMessage }}
                      </p>
                    </div>
                  </div>
                </div>
              </Transition>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  serviceName: {
    type: String,
    default: 'Layanan',
  },
  serviceSlug: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close'])
const page = usePage()

const isSubmitting = ref(false)
const isSuccess = ref(false)
const successMessage = ref('')
const errors = ref({})

const form = ref({
  title: '',
  description: '',
  category: '',
  contact_person_name: '',
  contact_email: '',
  contact_phone: '',
})

const selectedServiceSlug = ref('')
const servicesList = [
  { slug: 'pemerintahan', name: 'Pemerintahan' },
  { slug: 'pendidikan', name: 'Pendidikan' },
  { slug: 'kesehatan', name: 'Kesehatan' },
  { slug: 'perindagkop-ukm', name: 'Perindagkop UKM' },
  { slug: 'hukum', name: 'Hukum' },
  { slug: 'sosial', name: 'Sosial' },
  { slug: 'kependudukan', name: 'Kependudukan' },
  { slug: 'kepegawaian', name: 'Kepegawaian' },
  { slug: 'lingkungan', name: 'Lingkungan' },
  { slug: 'geografis', name: 'Geografis' },
  { slug: 'pariwisata', name: 'Pariwisata' },
  { slug: 'data-sektoral', name: 'Data Sektoral' },
  { slug: 'makanan-bergizi-gratis', name: 'Makanan Bergizi Gratis' },
]

// Auto-fill user details if logged in when modal is opened
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    if (page.props.auth?.user) {
      form.value.contact_person_name = page.props.auth.user.name || ''
      form.value.contact_email = page.props.auth.user.email || ''
    }
  }
})

const whatsappNumber = page.props.whatsapp?.complaint_phone ?? '6283128848371'

const buildWhatsAppUrl = () => {
  const activeServiceName = props.serviceSlug
    ? props.serviceName
    : (servicesList.find(s => s.slug === selectedServiceSlug.value)?.name || 'Layanan')

  const message = [
    '*PENGADUAN BARU*',
    '',
    `*Layanan:* ${activeServiceName}`,
    `*Judul:* ${form.value.title}`,
    `*Nama:* ${form.value.contact_person_name}`,
    '*Deskripsi:*',
    form.value.description,
  ].join('\n')

  return `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`
}

const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = ({ keepSuccess = false } = {}) => {
  form.value = {
    title: '',
    description: '',
    category: '',
    contact_person_name: '',
    contact_email: '',
    contact_phone: '',
  }
  selectedServiceSlug.value = ''
  errors.value = {}
  if (!keepSuccess) isSuccess.value = false
}

const submitComplaint = async () => {
  isSubmitting.value = true
  errors.value = {}

  try {
    const response = await fetch('/complaints', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({
        service_slug: props.serviceSlug || selectedServiceSlug.value,
        ...form.value,
      }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      if (data.errors) {
        errors.value = Object.fromEntries(
          Object.entries(data.errors).map(([field, messages]) => [field, Array.isArray(messages) ? messages[0] : messages])
        )
      } else {
        throw new Error(data.message || 'Terjadi kesalahan')
      }
    } else {
      const whatsappUrl = typeof data.whatsapp_url === 'string'
        ? data.whatsapp_url
        : buildWhatsAppUrl()

      if (typeof whatsappUrl !== 'string' || !whatsappUrl.startsWith('https://wa.me/')) {
        throw new Error('Pengaduan tersimpan, tetapi tautan WhatsApp tidak tersedia. Silakan hubungi admin.')
      }

      isSuccess.value = true
      successMessage.value = data.message || 'Pengaduan berhasil dikirim'

      // Reset form
      resetForm({ keepSuccess: true })

      // Pengaduan sudah tersimpan; buka WhatsApp dengan pesan yang telah disiapkan.
      setTimeout(() => {
        window.location.assign(whatsappUrl)
      }, 500)
    }
  } catch (error) {
    errors.value.general = error.message
  } finally {
    isSubmitting.value = false
  }
}
</script>
