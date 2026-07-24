<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ complaint: { type: Object, required: true } })

const isFinalStatus = computed(() => ['rejected', 'resolved', 'cancelled'].includes(props.complaint.status))

const initialStatus = () => {
    if (props.complaint.status === 'pending') return 'processing'
    if (props.complaint.status === 'processing') return 'resolved'
    return props.complaint.status
}

const status = ref(initialStatus())
const notes = ref(props.complaint.notes ?? '')
const saving = ref(false)
const successMessage = ref('')

const statusLabel = {
    pending: 'Menunggu',
    processing: 'Diproses',
    resolved: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Dibatalkan',
}

const statusClass = (st) => ({
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    processing: 'bg-blue-100 text-blue-800 border-blue-200',
    resolved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-rose-100 text-rose-800 border-rose-200',
    cancelled: 'bg-slate-100 text-slate-800 border-slate-300',
}[st] ?? 'bg-slate-100 text-slate-700 border-slate-200')

const save = () => {
    saving.value = true
    successMessage.value = ''
    router.put(
        `/admin/complaints/${props.complaint.id}`,
        { status: status.value, notes: notes.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Status pengaduan dan catatan tindak lanjut berhasil diperbarui!'
                setTimeout(() => { successMessage.value = '' }, 4000)
            },
            onFinish: () => { saving.value = false }
        }
    )
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
                            <option v-if="complaint.status === 'pending'" value="processing">Diproses (Processing)</option>
                            <option v-if="complaint.status === 'pending'" value="rejected">Ditolak (Rejected)</option>

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
