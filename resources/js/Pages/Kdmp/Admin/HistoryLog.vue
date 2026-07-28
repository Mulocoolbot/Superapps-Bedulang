<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    logs: Object,
    filters: Object,
})

const kecamatans = ['Koba', 'Pangkalan Baru', 'Namang', 'Lubuk Besar', 'Simpang Katis', 'Sungai Selan']

// Filtering
const filterKecamatan = ref(props.filters?.kecamatan || '')

const filterData = () => {
    const params = {}
    if (filterKecamatan.value) params.kecamatan = filterKecamatan.value

    router.get(
        route('admin.kdmp.history'),
        params,
        { preserveState: true, preserveScroll: true, replace: true }
    )
}
</script>

<template>
    <Head title="Admin Keseluruhan KDMP - History Log" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin Keseluruhan KDMP</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <Link :href="route('admin.kdmp.dashboard')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengelolaan Data
                    </Link>
                    <Link :href="route('admin.kdmp.history')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>
                
                <!-- History Logs -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Log Riwayat KMP</h2>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <select v-model="filterKecamatan" @change="filterData" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Kecamatan</option>
                                <option v-for="kec in kecamatans" :key="kec" :value="kec">{{ kec }}</option>
                            </select>
                        </div>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Waktu</th>
                                    <th class="px-6 py-3">Pelaku</th>
                                    <th class="px-6 py-3">KMP</th>
                                    <th class="px-6 py-3">Aksi</th>
                                    <th class="px-6 py-3">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs.data" :key="log.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                                    <td class="px-6 py-4">{{ log.user?.name }}</td>
                                    <td class="px-6 py-4">{{ log.kmp?.nama_koperasi || '-' }}</td>
                                    <td class="px-6 py-4 font-mono text-xs">{{ log.action }}</td>
                                    <td class="px-6 py-4">{{ log.description }}</td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center">Belum ada riwayat aktivitas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="logs" />
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
