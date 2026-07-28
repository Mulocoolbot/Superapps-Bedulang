<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    kmps: Object,
    kecamatan: String,
    filters: Object,
})

const statusFilter = ref(props.filters?.status || 'semua')
const sortFilter = ref(props.filters?.sort || 'terbaru')

const filterData = () => {
    const params = {}
    if (statusFilter.value && statusFilter.value !== 'semua') params.status = statusFilter.value
    if (sortFilter.value && sortFilter.value !== 'terbaru') params.sort = sortFilter.value

    router.get(
        route('admin.kdmp.kecamatan.verifikasi_kmp_page'),
        params,
        { preserveState: true, preserveScroll: true, replace: true }
    )
}

const verifyPembentukan = (kmpId, action) => {
    if(confirm(`Apakah Anda yakin ingin ${action === 'verify' ? 'menyetujui' : 'menolak'} KMP ini?`)) {
        router.post(route('admin.kdmp.kecamatan.verify_pembentukan', kmpId), { action })
    }
}

const verifySertifikat = (kmpId, action) => {
    if(confirm(`Apakah Anda yakin ingin ${action === 'verify' ? 'menyetujui' : 'menolak'} sertifikat KMP ini?`)) {
        router.post(route('admin.kdmp.kecamatan.verify_sertifikat', kmpId), { action })
    }
}

// Preview Modal Logic
const previewModal = ref(false)
const previewUrl = ref('')
const previewType = ref('')

const openPreview = (fileUrl) => {
    previewUrl.value = fileUrl
    previewType.value = fileUrl.toLowerCase().endsWith('.pdf') ? 'pdf' : 'image'
    previewModal.value = true
}

const closePreview = () => {
    previewModal.value = false
    previewUrl.value = ''
    previewType.value = ''
}
</script>

<template>
    <Head title="Admin Kecamatan KDMP - Verifikasi KMP" />

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
                    <Link :href="route('admin.kdmp.kecamatan.verifikasi_kmp_page')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Verifikasi KMP
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.verifikasi_perubahan_page')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengajuan Perubahan
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.history')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>

                <!-- Verifikasi Pembentukan & Sertifikat -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Verifikasi Pembentukan & Sertifikat KMP</h2>
                        
                        <div class="flex gap-2 items-center">
                            <select v-model="statusFilter" @change="filterData" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                <option value="semua">Semua Status</option>
                                <option value="pending_verification">Menunggu</option>
                                <option value="verified">Diterima</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                            
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
                                    <th class="px-6 py-3">Admin</th>
                                    <th class="px-6 py-3">Status Pembentukan</th>
                                    <th class="px-6 py-3">Sertifikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="kmp in kmps.data" :key="kmp.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ kmp.nama_koperasi }}</div>
                                        <div class="text-xs">{{ kmp.jenis }} - {{ kmp.desa_kelurahan }}</div>
                                    </td>
                                    <td class="px-6 py-4">{{ kmp.user?.name }}</td>
                                    <td class="px-6 py-4">
                                        <span v-if="kmp.status_pembentukan === 'pending_verification'" class="text-amber-600">Menunggu</span>
                                        <span v-else-if="kmp.status_pembentukan === 'verified'" class="text-emerald-600">Diterima</span>
                                        <span v-else class="text-red-600">Ditolak</span>
                                        
                                        <div class="mt-2 flex gap-2" v-if="kmp.status_pembentukan === 'pending_verification'">
                                            <button @click="verifyPembentukan(kmp.id, 'verify')" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded hover:bg-emerald-200">Terima</button>
                                            <button @click="verifyPembentukan(kmp.id, 'reject')" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">Tolak</button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="kmp.file_sertifikat">
                                            <button type="button" @click="openPreview(`/storage/${kmp.file_sertifikat}`)" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1 rounded-md mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat File
                                            </button>
                                            <div class="block mt-2">
                                                <span v-if="kmp.status_sertifikat === 'pending'" class="text-amber-600 text-sm">Status Sertifikat: Menunggu</span>
                                                <span v-else-if="kmp.status_sertifikat === 'verified'" class="text-emerald-600 text-sm">Status Sertifikat: Diterima</span>
                                                <span v-else class="text-red-600 text-sm">Status Sertifikat: Ditolak</span>
                                            </div>
                                            
                                            <div class="mt-2 flex gap-2" v-if="kmp.status_sertifikat === 'pending'">
                                                <button @click="verifySertifikat(kmp.id, 'verify')" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded hover:bg-emerald-200">Terima</button>
                                                <button @click="verifySertifikat(kmp.id, 'reject')" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">Tolak</button>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400">Belum Upload</span>
                                    </td>
                                </tr>
                                <tr v-if="kmps.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center">Belum ada KMP.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="kmps" />
                    </div>
                </div>

            </div>
        </div>

        <!-- Preview Modal -->
        <Modal :show="previewModal" @close="closePreview" maxWidth="4xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pratinjau Dokumen</h3>
                    <button @click="closePreview" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="bg-gray-100 dark:bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden min-h-[50vh]">
                    <template v-if="previewType === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-[70vh] border-0" title="PDF Preview"></iframe>
                    </template>
                    <template v-else-if="previewType === 'image'">
                        <img :src="previewUrl" alt="Document Preview" class="max-w-full max-h-[70vh] object-contain" />
                    </template>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a :href="previewUrl" target="_blank" download class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 mr-2">
                        Unduh
                    </a>
                    <button @click="closePreview" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        Tutup
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
