<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ complaint: { type: Object, required: true } })

const isFinalStatus = computed(() => 
[
'rejected',
'resolved',
'cancelled'
].includes(props.complaint.status)
)

const initialStatus = () => {
    if (props.complaint.status === 'pending' || props.complaint.status === 'terkirim') return 'processing'
    if (props.complaint.status === 'processing') return 'resolved'
    return props.complaint.status
}

const status = ref(initialStatus())
const notes = ref(props.complaint.notes ?? '')
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const followupImage = ref(null)
const imagePreview = ref(null)

const statusLabel = {
    pending: 'Menunggu',
    terkirim: 'Terkirim',
    processing: 'Diproses',
    resolved: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Dibatalkan',
}

const statusClass = (st) => ({
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    terkirim: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    processing: 'bg-blue-100 text-blue-800 border-blue-200',
    resolved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-rose-100 text-rose-800 border-rose-200',
    cancelled: 'bg-slate-100 text-slate-800 border-slate-300',
}[st] ?? 'bg-slate-100 text-slate-700 border-slate-200')

const handleImage = (event) => {
    const file = event.target.files[0]
    if (file) {
        followupImage.value = file
        imagePreview.value = URL.createObjectURL(file)
        errorMessage.value = ''
    }
}

const save = () => {
    errorMessage.value = ''

    if (
        status.value === 'resolved' &&
        !followupImage.value &&
        !props.complaint.followup_image
    ) {
        errorMessage.value = 'Wajib mengunggah foto bukti tindak lanjut sebelum mengubah status menjadi Selesai.'
        return
    }

    saving.value = true

    const form = new FormData()
    form.append('status', status.value)
    form.append('notes', notes.value)

    if (followupImage.value) {
        form.append('followup_image', followupImage.value)
    }

    form.append('_method', 'PUT')

    router.post(
        `/admin/complaints/${props.complaint.id}`,
        form,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                errorMessage.value = ''
                successMessage.value = 'Status dan tindak lanjut berhasil diperbarui!'
                setTimeout(() => {
                    successMessage.value = ''
                }, 4000)
            },
            onError: (errs) => {
                if (errs.followup_image) {
                    errorMessage.value = errs.followup_image
                } else if (errs.status) {
                    errorMessage.value = errs.status
                } else {
                    errorMessage.value = Object.values(errs)[0] || 'Gagal menyimpan perubahan status.'
                }
            },
            onFinish: () => {
                saving.value = false
            }
        }
    )
}

const OPD_WHATSAPP_NUMBERS = {
    'pemerintahan': '6281234567801',
    'pendidikan': '6281234567802',
    'kesehatan': '6281234567803',
    'perindagkop-ukm': '6281234567804',
    'hukum': '6281234567805',
    'sosial': '6281234567806',
    'kependudukan': '6281234567807',
    'kepegawaian': '6281234567808',
    'pertanian': '6281234567809',
    'informasi-publik': '6281234567810',
    'pariwisata': '6281234567811',
    'data-sektoral': '6281234567812',
    'makanan-bergizi-gratis': '6281234567813',
    'pengaduan-bedulang': '6283128848371'
}

const getFollowUpUrl = (complaint) => {
    const defaultPhone = '6283128848371'
    const opdPhone = OPD_WHATSAPP_NUMBERS[complaint.service_slug] || defaultPhone
    
    const formattedDate = new Date(complaint.created_at).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
    
    const message = `*TINDAK LANJUT PENGADUAN - BEDULANG*

Halo OPD Terkait,
Berikut adalah laporan pengaduan dari layanan *${complaint.service_name}* yang perlu segera ditindaklanjuti karena statusnya saat ini masih *Menunggu*:

*Detail Laporan Pengaduan:*
• *Judul:* ${complaint.title}
• *Kategori:* ${complaint.category || '-'}
• *Deskripsi:* ${complaint.description}
• *Pelapor:* ${complaint.contact_person_name} (${complaint.contact_phone || '-'} / ${complaint.contact_email || '-'})
• *Tanggal:* ${formattedDate}
• *Status:* Menunggu

*Keterangan:* Laporan pengaduan ini masih menunggu proses and harus segera diproses. Mohon tanggapan dan tindak lanjutnya sesegera mungkin. Terima kasih.`

    return `https://wa.me/${opdPhone}?text=${encodeURIComponent(message)}`
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Detail Pengaduan - ${complaint.title}`"></Head>

        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Back navigation link -->
            <Link href="/admin/complaints" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Monitoring Pengaduan</span>
            </Link>

            <!-- Error Alert -->
            <div v-if="errorMessage" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-rose-600 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ errorMessage }}</span>
            </div>

            <!-- Success Alert -->
            <div v-if="successMessage" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ successMessage }}</span>
            </div>

            <!-- Final Status Locked Alert -->
            <div v-if="isFinalStatus" class="p-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-sm font-medium flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-slate-500 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>Status pengaduan ini telah <strong class="uppercase text-slate-900">{{ statusLabel[complaint.status] }}</strong> dan bersifat final (tidak dapat diubah lagi).</span>
            </div>

            <!-- Complaint Detail Card -->
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border" :class="statusClass(complaint.status)">
                            {{ statusLabel[complaint.status] }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                            {{ complaint.service_name }}
                        </span>
                    </div>
                    <span class="text-xs text-slate-400">
                        Waktu Pengaduan: {{ new Date(complaint.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </span>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-slate-900 leading-tight">{{ complaint.title }}</h1>
                    <p class="mt-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Kategori: {{ complaint.category }}</p>
                </div>

                <div class="pt-2">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Deskripsi Pengaduan:</h3>
                    <div class="whitespace-pre-line text-sm text-slate-700 bg-slate-50 rounded-xl p-4 border border-slate-200/60 leading-relaxed">
                        {{ complaint.description }}
                    </div>
                <div
                    v-if="complaint.rating"
                    class="rounded-xl bg-yellow-50 border border-yellow-200 p-4"
                    >

                    <h3 class="font-semibold text-sm">
                    Review Pelapor
                    </h3>


                    <p class="mt-2">
                    Rating:
                    {{ complaint.rating }} ⭐
                    </p>


                    <p class="mt-2 text-sm">
                    {{ complaint.review }}
                    </p>


                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Reporter Information Card -->
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <h2 class="font-bold text-slate-900 text-lg">Informasi Pelapor</h2>
                    </div>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-400 uppercase font-semibold">Nama Lengkap</dt>
                            <dd class="font-semibold text-slate-800 mt-0.5">{{ complaint.contact_person_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 uppercase font-semibold">Email Contact</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ complaint.contact_email || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400 uppercase font-semibold">Nomor Telepon / WhatsApp</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ complaint.contact_phone || '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Tindak Lanjut Pimpinan Card -->
                <div v-if="$page.props.auth.user.role === 'pimpinan' && complaint.status === 'pending'" class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                        <h2 class="font-bold text-slate-900 text-lg">Tindak Lanjut Pimpinan</h2>
                    </div>
                    <p class="text-sm text-slate-600">
                        Pengaduan ini masih dalam status <strong class="text-amber-600">Menunggu</strong>. Hubungi OPD terkait via WhatsApp untuk segera menindaklanjuti dan memproses pengaduan ini.
                    </p>
                    <a
                        :href="getFollowUpUrl(complaint)"
                        target="_blank"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition text-sm shadow-sm cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.224-3.826l.409.243c1.6.95 3.791 1.453 6.012 1.454 5.717 0 10.368-4.63 10.371-10.35.002-2.772-1.07-5.378-3.02-7.332-1.951-1.953-4.546-3.029-7.322-3.03C7.03 1.159 2.38 5.789 2.378 11.51c0 2.13.552 4.208 1.6 6.023l.267.461-1.055 3.861 3.96-.104c-.035-.004-.07-.008-.104-.012z"/>
                        </svg>
                        <span>Follow Up Pengaduan ke WhatsApp OPD</span>
                    </a>
                </div>

                <!-- Update Status / Action Form Card -->
                <form v-if="$page.props.auth.user.role !== 'pimpinan'" @submit.prevent="save" class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-purple-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        <h2 class="font-bold text-slate-900 text-lg">Tindak Lanjut Admin</h2>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Status Pengaduan</label>
                        <select v-model="status" :disabled="isFinalStatus" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-500">
                            <option v-if="complaint.status === 'pending' || complaint.status === 'terkirim'" value="processing">Diproses (Processing)</option>
                            <option v-if="complaint.status === 'pending' || complaint.status === 'terkirim'" value="rejected">Ditolak (Rejected)</option>
                            <option v-if="complaint.status === 'processing'" value="resolved">Selesai (Resolved)</option>
                            <option v-if="complaint.status === 'processing'" value="rejected">Ditolak (Rejected)</option>

                            <option v-if="isFinalStatus" :value="complaint.status">
                                {{ statusLabel[complaint.status] }} (Final - Terkunci)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Catatan Admin / Hasil Tindak Lanjut</label>
                        <textarea
                            v-model="notes"
                            :disabled="isFinalStatus"
                            rows="3"
                            placeholder="Tuliskan catatan tindak lanjut atau tanggapan admin..."
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-500"
                        ></textarea>
                        <div
                            v-if="status === 'resolved'"
                            class="mt-4"
                        >
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">
                                Bukti Tindak Lanjut
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="file"
                                accept="image/*"
                                @change="handleImage"
                                class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                            />
                            <p class="text-[11px] text-slate-400 mt-1.5">
                                Keterangan: Hanya diperbolehkan file gambar format JPG, JPEG, atau PNG dengan ukuran maksimal 2MB.
                            </p>

                            <img
                                v-if="imagePreview"
                                :src="imagePreview"
                                class="mt-3 rounded-xl max-w-xs border border-slate-200 shadow-sm"
                            />

                            <div
                                v-if="complaint.followup_image"
                                class="mt-3"
                            >
                                <p class="text-xs text-slate-500 mb-2">
                                    Gambar sebelumnya:
                                </p>
                                <img
                                    :src="`/${complaint.followup_image}`"
                                    class="rounded-xl max-w-xs border border-slate-200 shadow-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="saving || isFinalStatus"
                        class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition shadow-sm disabled:bg-slate-300 disabled:cursor-not-allowed"
                    >
                        {{ saving ? 'Menyimpan Perubahan…' : (isFinalStatus ? 'Status Telah Final' : 'Simpan Pembaruan Status') }}
                    </button>
                </form>
            </div>

            <!-- Timeline / Progress Log History Card -->
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <h2 class="font-bold text-slate-900 text-lg">Riwayat Pembaruan Progres (Timeline)</h2>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                        {{ complaint.progress_logs ? complaint.progress_logs.length : 0 }} Pembaruan
                    </span>
                </div>

                <div v-if="complaint.progress_logs && complaint.progress_logs.length > 0" class="relative border-l-2 border-slate-200 ml-4 space-y-6 py-2">
                    <div v-for="log in complaint.progress_logs" :key="log.id" class="relative pl-6">
                        <!-- Dot Icon -->
                        <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full border-2 border-white shadow-sm"
                            :class="{
                                'bg-amber-500': log.status === 'pending',
                                'bg-indigo-500': log.status === 'terkirim',
                                'bg-blue-500': log.status === 'processing',
                                'bg-emerald-500': log.status === 'resolved',
                                'bg-rose-500': log.status === 'rejected',
                            }">
                        </div>

                        <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/60 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border" :class="statusClass(log.status)">
                                        {{ statusLabel[log.status] }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-700">
                                        {{ log.user ? log.user.name : 'Sistem / Pelapor' }}
                                    </span>
                                </div>
                                <span class="text-xs font-medium text-slate-500">
                                    Waktu Update: {{ new Date(log.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </span>
                            </div>

                            <p v-if="log.notes" class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                                {{ log.notes }}
                            </p>
                            <p v-else class="text-xs italic text-slate-400">
                                Tanpa catatan tambahan.
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-slate-400 text-sm">
                    Belum ada riwayat pembaruan progres.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
