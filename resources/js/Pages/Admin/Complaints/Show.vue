<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ complaint: { type: Object, required: true } })
const status = ref(props.complaint.status)
const notes = ref(props.complaint.notes ?? '')
const saving = ref(false)

const save = () => {
    saving.value = true
    router.put(`/admin/complaints/${props.complaint.id}`, { status: status.value, notes: notes.value }, { preserveScroll: true, onFinish: () => saving.value = false })
}
</script>

<template>
    <AdminLayout>
        <Head :title="complaint.title" />
        <div class="mx-auto max-w-4xl space-y-6">
            <Link href="/admin/complaints" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Kembali ke pengaduan</Link>
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h1 class="text-2xl font-bold text-slate-900">{{ complaint.title }}</h1><p class="mt-2 text-sm text-slate-500">{{ complaint.service_name }} · {{ complaint.category }} · {{ complaint.created_at }}</p><div class="mt-6 whitespace-pre-line text-slate-700">{{ complaint.description }}</div></div>
            <div class="grid gap-6 md:grid-cols-2"><div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h2 class="font-semibold text-slate-900">Pelapor</h2><dl class="mt-4 space-y-2 text-sm"><div><dt class="text-slate-500">Nama</dt><dd>{{ complaint.contact_person_name }}</dd></div><div><dt class="text-slate-500">Email</dt><dd>{{ complaint.contact_email }}</dd></div><div><dt class="text-slate-500">Telepon</dt><dd>{{ complaint.contact_phone }}</dd></div></dl></div><form @submit.prevent="save" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200"><h2 class="font-semibold text-slate-900">Tindak lanjut</h2><label class="mt-4 block text-sm font-medium">Status<select v-model="status" class="mt-1 block w-full rounded-lg border-slate-300"><option value="pending">Menunggu</option><option value="processing">Diproses</option><option value="resolved">Selesai</option><option value="rejected">Ditolak</option></select></label><label class="mt-4 block text-sm font-medium">Catatan<textarea v-model="notes" rows="4" class="mt-1 block w-full rounded-lg border-slate-300" /></label><button :disabled="saving" class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:bg-slate-400">{{ saving ? 'Menyimpan…' : 'Simpan perubahan' }}</button></form></div>
        </div>
    </AdminLayout>
</template>
