<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    editRequests: Object,
    kecamatan: String,
    filters: Object,
})

const sortFilter = ref(props.filters?.sort || 'terbaru')

const filterData = () => {
    const params = {}
    if (sortFilter.value && sortFilter.value !== 'terbaru') params.sort = sortFilter.value

    router.get(
        route('admin.kdmp.kecamatan.verifikasi_perubahan_page'),
        params,
        { preserveState: true, preserveScroll: true, replace: true }
    )
}

const verifyEdit = (editRequestId, action) => {
    let rejectionReason = ''
    if (action === 'reject') {
        rejectionReason = prompt("Masukkan alasan penolakan:")
        if (rejectionReason === null) return // cancelled
    }
    
    router.post(route('admin.kdmp.kecamatan.verify_edit', editRequestId), { 
        action, 
        rejection_reason: rejectionReason 
    })
}
</script>

<template>
    <Head title="Admin Kecamatan KDMP - Pengajuan Perubahan" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Admin Kecamatan - {{ kecamatan }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <Link :href="route('admin.kdmp.kecamatan.dashboard')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Kelola Akun KMP
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.verifikasi_kmp_page')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Verifikasi KMP
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.verifikasi_perubahan_page')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengajuan Perubahan
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.history')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>

                <!-- Verifikasi Pengajuan Perubahan -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Verifikasi Pengajuan Perubahan Data</h2>
                        
                        <div class="flex gap-2 items-center">
                            <select v-model="sortFilter" @change="filterData" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                <option value="terbaru">Terbaru</option>
                                <option value="terlama">Terlama</option>
                            </select>
                        </div>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Koperasi</th>
                                    <th class="px-6 py-3">Data Baru</th>
                                    <th class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="req in editRequests.data" :key="req.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ req.kmp?.nama_koperasi }}</td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1 bg-gray-50 dark:bg-gray-900 p-3 rounded-md border border-gray-100 dark:border-gray-800">
                                            <div v-for="(value, key) in req.requested_data" :key="key" class="flex text-sm">
                                                <span class="w-1/3 font-semibold text-gray-700 dark:text-gray-300 capitalize text-xs">
                                                    {{ String(key).replace(/_/g, ' ') }}
                                                </span>
                                                <span class="w-2/3 text-gray-600 dark:text-gray-400 text-xs">
                                                    : {{ value || '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="verifyEdit(req.id, 'approve')" class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded hover:bg-emerald-200">Setujui</button>
                                            <button @click="verifyEdit(req.id, 'reject')" class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded hover:bg-red-200">Tolak</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="editRequests.data.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center">Tidak ada pengajuan perubahan data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="editRequests" />
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
