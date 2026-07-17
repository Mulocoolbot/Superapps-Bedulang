<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    complaints: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const statusLabel = {
    pending: 'Menunggu', processing: 'Diproses', resolved: 'Selesai', rejected: 'Ditolak',
}

const statusClass = (status) => ({
    pending: 'bg-amber-100 text-amber-800', processing: 'bg-blue-100 text-blue-800',
    resolved: 'bg-emerald-100 text-emerald-800', rejected: 'bg-rose-100 text-rose-800',
}[status] ?? 'bg-slate-100 text-slate-700')

const currentStatus = computed({
    get: () => props.filters.status ?? 'all',
    set: (status) => router.get('/admin/complaints', { ...props.filters, status }, { preserveState: true, replace: true }),
})
</script>

<template>
    <AdminLayout>
        <Head title="Pengaduan" />
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h1 class="text-2xl font-bold text-slate-900">Pengaduan</h1><p class="mt-1 text-sm text-slate-500">Daftar pengaduan dari pengguna layanan.</p></div>
                <select v-model="currentStatus" class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">Semua status</option><option value="pending">Menunggu</option><option value="processing">Diproses</option><option value="resolved">Selesai</option><option value="rejected">Ditolak</option>
                </select>
            </div>
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div v-if="!complaints.data.length" class="p-10 text-center text-sm text-slate-500">Belum ada pengaduan.</div>
                <div v-else class="divide-y divide-slate-100">
                    <Link v-for="complaint in complaints.data" :key="complaint.id" :href="`/admin/complaints/${complaint.id}`" class="block p-5 transition hover:bg-slate-50">
                        <div class="flex items-start justify-between gap-4"><div><p class="font-semibold text-slate-900">{{ complaint.title }}</p><p class="mt-1 text-sm text-slate-500">{{ complaint.service_name }} · {{ complaint.contact_person_name }} · {{ complaint.created_at }}</p></div><span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(complaint.status)">{{ statusLabel[complaint.status] }}</span></div>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
