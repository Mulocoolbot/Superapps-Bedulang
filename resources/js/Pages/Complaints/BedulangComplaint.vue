<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import axios from 'axios'

const props = defineProps({
    user: Object
})

const form = ref({
    judul: '',
    kategori: '',
    keterangan_kategori: '',
    deskripsi: ''
})

const showConfirm = ref(false)
const complaintId = ref(null)
const loading = ref(false)
const errors = ref({})

const kategoriList = [
    'PEMERINTAHAN',
    'PENDIDIKAN',
    'KESEHATAN',
    'PERINDAGKOP-UKM',
    'HUKUM',
    'SOSIAL',
    'KEPENDUDUKAN',
    'KEPEGAWAIAN',
    'LINGKUNGAN',
    'PARIWISATA',
    'DATA SEKTORAL',
    'MAKANAN BERGIZI GRATIS',
    'LAIN-LAIN'
]

const submitComplaint = async () => {
    loading.value = true
    errors.value = {}

    try {
        const response = await axios.post(
            '/complaints/bedulang',
            form.value
        )

        complaintId.value = response.data.id

        window.open(
            response.data.whatsapp_url,
            '_blank'
        )

        showConfirm.value = true
    } catch (error) {
        console.error(error)
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors
        } else {
            alert('Terjadi kesalahan saat mengirim pengaduan')
        }
    } finally {
        loading.value = false
    }
}

const confirmSent = async () => {
    try {
        await axios.patch(
            `/complaints/${complaintId.value}/mark-sent`
        )
        router.visit('/my-complaints')
    } catch (error) {
        console.error(error)
        alert('Gagal memperbarui status pengaduan.')
    }
}
</script>

<template>
    <FrontLayout>
        <Head title="Layanan Pengaduan Bedulang" />

        <div class="min-h-screen bg-gradient-to-b from-[#0F3D77] via-[#0F3D77]/95 to-[#0b2b52] py-12 transition-colors duration-300">
            <section class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Back Link -->
                <div class="mb-8">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-blue-100 hover:text-white transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        <span>Kembali</span>
                    </Link>
                </div>

                <!-- Header Card -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-700 p-8 sm:p-10 shadow-xl text-white mb-8">
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    
                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                            <span>📢</span>
                            <span>Layanan Internal Pimpinan</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight">
                            Layanan Pengaduan Bedulang
                        </h1>
                        <p class="text-blue-100 max-w-2xl leading-relaxed text-sm sm:text-base">
                            Platform pengaduan resmi untuk melaporkan kendala teknis, masalah operasional, atau saran perbaikan terkait portal Super App Bedulang Kabupaten Bangka Tengah.
                        </p>
                    </div>
                </div>

                <!-- Info Alert Box -->
                <div class="mb-8 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 p-5 flex gap-4 text-amber-800 dark:text-amber-300 shadow-sm">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-xl shrink-0 h-10 w-10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="text-xs sm:text-sm">
                        <p class="font-bold">Ketentuan Penggunaan:</p>
                        <p class="mt-1 leading-relaxed">
                            Layanan ini ditujukan khusus untuk pimpinan OPD. Setelah mengisi formulir ini, Anda akan diarahkan ke WhatsApp untuk mengirim pesan pengaduan ke admin. Status pengaduan akan resmi tercatat sebagai <strong>"Terkirim"</strong> setelah Anda melakukan konfirmasi pada dialog pop-up.
                        </p>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="rounded-3xl border border-slate-200/80 dark:border-slate-800/80 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-6 sm:p-10 shadow-lg space-y-8">
                    <form @submit.prevent="submitComplaint" class="space-y-6">
                        <!-- Name & Email (Read-only) -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Nama Pengaju (Pimpinan)</label>
                                <input
                                    :value="user.name"
                                    disabled
                                    type="text"
                                    class="w-full rounded-xl border-slate-200 dark:border-gray-700 bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-slate-400 px-4 py-3 text-sm font-semibold cursor-not-allowed"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Email Pengaju</label>
                                <input
                                    :value="user.email"
                                    disabled
                                    type="email"
                                    class="w-full rounded-xl border-slate-200 dark:border-gray-700 bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-slate-400 px-4 py-3 text-sm font-semibold cursor-not-allowed"
                                />
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Judul Keluhan <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.judul"
                                type="text"
                                required
                                placeholder="Masukkan ringkasan keluhan Anda..."
                                class="w-full rounded-xl border-slate-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <p v-if="errors.judul" class="text-xs text-rose-500 mt-1">{{ errors.judul[0] }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kategori Bidang Layanan <span class="text-rose-500">*</span></label>
                            <select
                                v-model="form.kategori"
                                required
                                class="w-full rounded-xl border-slate-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">-- Pilih Kategori --</option>
                                <option v-for="item in kategoriList" :key="item" :value="item">
                                    {{ item }}
                                </option>
                            </select>
                            <p v-if="errors.kategori" class="text-xs text-rose-500 mt-1">{{ errors.kategori[0] }}</p>
                        </div>

                        <!-- Keterangan Kategori (Lain-lain) -->
                        <div v-if="form.kategori === 'LAIN-LAIN'" class="space-y-2 animate-fade-in">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Keterangan Kategori <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.keterangan_kategori"
                                type="text"
                                required
                                placeholder="Jelaskan kategori pengaduan lainnya..."
                                class="w-full rounded-xl border-slate-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <p v-if="errors.keterangan_kategori" class="text-xs text-rose-500 mt-1">{{ errors.keterangan_kategori[0] }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Deskripsi Pengaduan <span class="text-rose-500">*</span></label>
                            <textarea
                                v-model="form.deskripsi"
                                required
                                rows="6"
                                placeholder="Jelaskan secara lengkap kendala, kronologi, atau pesan error yang terjadi..."
                                class="w-full rounded-xl border-slate-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                            <p v-if="errors.deskripsi" class="text-xs text-rose-500 mt-1">{{ errors.deskripsi[0] }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 border-t border-slate-100 dark:border-gray-800 flex items-center justify-end">
                            <button
                                type="submit"
                                :disabled="loading"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-3.5 shadow-md shadow-blue-500/20 transition disabled:bg-slate-300 disabled:cursor-not-allowed"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l.999 1.587-1.036 3.784 3.78-1.002 1.503.958z"/>
                                </svg>
                                <span>{{ loading ? 'Mengirim Pengaduan...' : 'Kirim via WhatsApp' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <!-- WhatsApp Sent Confirmation Modal -->
        <div
            v-if="showConfirm"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        >
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5 border border-slate-200 dark:border-gray-700 animate-scale-up">
                <div class="flex items-center gap-3 text-emerald-600 dark:text-emerald-400">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l.999 1.587-1.036 3.784 3.78-1.002 1.503.958z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Konfirmasi WhatsApp</h3>
                </div>

                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Kami telah mengalihkan Anda ke WhatsApp di tab baru dengan pesan yang sudah terisi otomatis. Apakah Anda telah berhasil mengirimkan pesan tersebut di WhatsApp?
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        @click="showConfirm = false; router.visit('/my-complaints')"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition"
                    >
                        Belum / Batal
                    </button>
                    <button
                        @click="confirmSent"
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition"
                    >
                        Ya, Sudah Terkirim
                    </button>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>