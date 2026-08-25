<script setup>
import { ref } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useSlidingPagination } from '@/Composables/useSlidingPagination'

const props = defineProps({
    logs: {
        type: Array,
        default: () => []
    },
    koperasis: {
        type: Object,
        default: () => ({ data: [], links: [], current_page: 1, last_page: 1 })
    },
    totalCount: {
        type: Number,
        default: 0
    },
    importedYears: {
        type: Array,
        default: () => []
    }
})

// Sliding Pagination Composable with window size = 5
const { visiblePageLinks, prevLink, nextLink, hasPagination } = useSlidingPagination(() => props.koperasis, { windowSize: 5 })

const currentYear = new Date().getFullYear()

// Form Import
const form = useForm({
    tahun: currentYear,
    file: null,
})

const selectedFileName = ref('')
const isDragging = ref(false)
const activeTab = ref('import') // 'import' | 'data'

// State Edit & Hapus
const isEditModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedItemForDelete = ref(null)

const editForm = useForm({
    id: null,
    nama_koperasi: '',
    tanggal_badan_hukum: '',
    kecamatan: '',
    desa_kelurahan: '',
    tahun: currentYear,
})

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.file = file
        selectedFileName.value = file.name
    }
}

const handleDrop = (event) => {
    isDragging.value = false
    const file = event.dataTransfer.files[0]
    if (file) {
        form.file = file
        selectedFileName.value = file.name
    }
}

const submitImport = () => {
    if (!form.file || !form.tahun) return

    form.post(route('admin.koperasi.import'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('file')
            selectedFileName.value = ''
        }
    })
}

// Open Edit Modal
const openEditModal = (item) => {
    editForm.id = item.id
    editForm.nama_koperasi = item.nama_koperasi
    editForm.tanggal_badan_hukum = item.tanggal_badan_hukum || ''
    editForm.kecamatan = item.kecamatan
    editForm.desa_kelurahan = item.desa_kelurahan
    editForm.tahun = item.tahun
    isEditModalOpen.value = true
}

const submitEdit = () => {
    if (!editForm.id) return

    editForm.put(route('admin.koperasi.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false
        }
    })
}

// Open Single Delete Modal
const openDeleteModal = (item) => {
    selectedItemForDelete.value = item
    isDeleteModalOpen.value = true
}

const confirmDelete = () => {
    if (!selectedItemForDelete.value) return

    router.delete(route('admin.koperasi.destroy', selectedItemForDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false
            selectedItemForDelete.value = null
        }
    })
}

// Delete Log Batch
const deleteLogBatch = (log) => {
    if (confirm(`Apakah Anda yakin ingin menghapus seluruh data snapshot import Tahun ${log.tahun}? Seluruh data koperasi pada tahun tersebut akan terhapus.`)) {
        router.delete(route('admin.koperasi.destroy_log', log.id), {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <Head title="Panel Admin Koperasi" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-3">
                        <span>Panel Admin Koperasi</span>
                        <span class="px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">
                            Kab. Bangka Tengah
                        </span>
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        :href="route('admin.koperasi.template')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold shadow-sm transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Unduh Template Excel</span>
                    </a>
                </div>
            </div>

            <!-- Global Flash Message -->
            <div v-if="$page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200 shadow-sm flex items-center justify-between">
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="p-3.5 bg-emerald-100 text-emerald-600 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-6 0h6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Koperasi Terdata</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ totalCount }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="p-3.5 bg-blue-100 text-blue-600 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun Ter-import</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ importedYears.length > 0 ? importedYears.join(', ') : '-' }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="p-3.5 bg-indigo-100 text-indigo-600 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Petugas Admin</p>
                        <h3 class="text-base font-bold text-gray-900 truncate max-w-[180px]">{{ $page.props.auth?.user?.name }}</h3>
                    </div>
                </div>
            </div>

            <!-- Tab Switching Buttons -->
            <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                <button
                    @click="activeTab = 'import'"
                    class="px-5 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'import' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span>Form Import & History Log</span>
                </button>

                <button
                    @click="activeTab = 'data'"
                    class="px-5 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'data' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <span>Daftar Data Koperasi ({{ totalCount }})</span>
                </button>
            </div>

            <!-- TAB 1: FORM IMPORT & HISTORY LOG -->
            <div v-show="activeTab === 'import'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Form Import Excel -->
                <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
                    <div class="border-b pb-4">
                        <h2 class="text-lg font-bold text-gray-900">Upload File Excel / CSV</h2>
                    </div>

                    <form @submit.prevent="submitImport" class="space-y-4">
                        <div v-if="form.errors.file" class="p-4 rounded-xl bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                            {{ form.errors.file }}
                        </div>

                        <div v-if="form.errors.tahun" class="p-4 rounded-xl bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                            {{ form.errors.tahun }}
                        </div>

                        <!-- Mandatory Year Input Field -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Tahun Data Snapshot (Wajib) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                v-model="form.tahun"
                                placeholder="Contoh: 2025"
                                min="2000"
                                max="2099"
                                required
                                class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-50"
                            />
                            <p class="text-[10px] text-gray-500 mt-1">
                                Catatan: Tahun mewakili waktu rekap snapshot data di-import. Pengunggahan tahun yang sama akan ditolak.
                            </p>
                        </div>

                        <!-- Drag and Drop Box -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                File Excel (.xlsx / .csv) <span class="text-rose-500">*</span>
                            </label>
                            <div
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                class="relative border-2 border-dashed rounded-2xl p-6 text-center transition cursor-pointer"
                                :class="isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-gray-300 hover:border-blue-400 bg-gray-50/50'"
                            >
                                <input
                                    type="file"
                                    accept=".csv,.xlsx,.xls"
                                    @change="handleFileSelect"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                />
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700">
                                        {{ selectedFileName || 'Klik atau Drag & Drop file Excel ke sini' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Format didukung: .xlsx, .xls, .csv (Maks 10MB)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Template Specs Box -->
                        <div class="bg-slate-50 p-4 rounded-xl text-xs space-y-2 border border-slate-200/80">
                            <p class="font-bold text-gray-700">Format Kolom Excel yang Dibaca:</p>
                            <ul class="list-disc list-inside text-gray-600 space-y-1 font-mono">
                                <li>KOPERASI</li>
                                <li>TANGGAL BADAN HUKUM</li>
                                <li>DESA/KELURAHAN</li>
                                <li>KECAMATAN</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing || !form.file || !form.tahun"
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-sm shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Memproses Import...' : `Import Rekap Data Tahun ${form.tahun || ''}` }}</span>
                        </button>
                    </form>
                </div>

                <!-- Right: History Log Panel -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">History Log Importan Data</h2>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 rounded-lg">
                            {{ logs.length }} Entri Log
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b bg-gray-50 text-gray-500 font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4">Waktu Upload</th>
                                    <th class="py-3 px-4">Admin User</th>
                                    <th class="py-3 px-4">Nama File</th>
                                    <th class="py-3 px-4 text-center">Tahun Snapshot</th>
                                    <th class="py-3 px-4 text-center">Baris Data</th>
                                    <th class="py-3 px-4 text-center">Aksi / Hapus Log</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="logs.length === 0">
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        Belum ada riwayat log import data.
                                    </td>
                                </tr>

                                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50/80 transition">
                                    <td class="py-3.5 px-4 font-medium text-gray-700 whitespace-nowrap">
                                        {{ new Date(log.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-900 whitespace-nowrap">
                                        {{ log.user?.name || 'Admin System' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-blue-600 font-mono font-medium max-w-[180px] truncate" :title="log.file_name">
                                        {{ log.file_name }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-black text-emerald-600">
                                        {{ log.tahun }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-gray-800">
                                        {{ log.total_rows }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <button
                                            @click="deleteLogBatch(log)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 font-bold transition cursor-pointer"
                                            title="Hapus seluruh data import snapshot ini"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span>Hapus Batch</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: DAFTAR DATA KOPERASI (MANAGEMENT EDIT & DELETE) -->
            <div v-show="activeTab === 'data'" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Kelola Data Koperasi</h2>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-500 font-bold uppercase tracking-wider">
                                <th class="py-3 px-4 w-12">No</th>
                                <th class="py-3 px-4">Nama Koperasi</th>
                                <th class="py-3 px-4">Tanggal Badan Hukum</th>
                                <th class="py-3 px-4">Kecamatan</th>
                                <th class="py-3 px-4">Desa / Kelurahan</th>
                                <th class="py-3 px-4 text-center">Tahun Snapshot</th>
                                <th class="py-3 px-4 text-center">Aksi Kelola</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!koperasis.data || koperasis.data.length === 0">
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    Belum ada data koperasi. Silakan unggah file Excel.
                                </td>
                            </tr>

                            <tr v-for="(item, index) in koperasis.data" :key="item.id" class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-gray-400">
                                    {{ ((koperasis.current_page || 1) - 1) * (koperasis.per_page || 15) + index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-gray-900">
                                    {{ item.nama_koperasi }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 font-medium whitespace-nowrap">
                                    {{ item.tanggal_badan_hukum || '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-700">
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold">
                                        {{ item.kecamatan }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700 font-medium">
                                    {{ item.desa_kelurahan }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-emerald-600">
                                    {{ item.tahun }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit Button -->
                                        <button
                                            @click="openEditModal(item)"
                                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 border border-blue-200 transition cursor-pointer"
                                            title="Edit Data Koperasi"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            @click="openDeleteModal(item)"
                                            class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 border border-rose-200 transition cursor-pointer"
                                            title="Hapus Data Koperasi"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Sliding Pagination UI (Max 5 Page Bubbles) -->
                <div v-if="hasPagination" class="flex items-center justify-between gap-4 pt-4 border-t border-gray-100">
                    <div class="text-xs text-gray-500">
                        Halaman <span class="font-bold text-gray-900">{{ koperasis.current_page }}</span>
                        dari <span class="font-bold text-gray-900">{{ koperasis.last_page }}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Prev Arrow -->
                        <Link
                            v-if="prevLink?.url"
                            :href="prevLink.url"
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 transition shadow-sm"
                            aria-label="Previous Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </Link>
                        <span v-else class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-50 text-gray-300 opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </span>

                        <!-- Max 5 Numeric Page Bubbles -->
                        <template v-for="(link, k) in visiblePageLinks" :key="k">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center transition"
                                :class="link.active
                                    ? 'bg-blue-600 text-white shadow-sm font-black'
                                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100'"
                            >
                                {{ link.label }}
                            </Link>
                            <span v-else class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center bg-gray-50 text-gray-400">
                                {{ link.label }}
                            </span>
                        </template>

                        <!-- Next Arrow -->
                        <Link
                            v-if="nextLink?.url"
                            :href="nextLink.url"
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-600 hover:bg-gray-100 transition shadow-sm"
                            aria-label="Next Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                        <span v-else class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-50 text-gray-300 opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-fade-in-up">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-base font-extrabold text-gray-900">Edit Data Koperasi</h3>
                    <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Koperasi</label>
                        <input
                            type="text"
                            v-model="editForm.nama_koperasi"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-semibold"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tanggal Badan Hukum</label>
                        <input
                            type="text"
                            v-model="editForm.tanggal_badan_hukum"
                            placeholder="Contoh: 12 November 1996"
                            class="w-full px-3.5 py-2.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-semibold"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Kecamatan</label>
                        <input
                            type="text"
                            v-model="editForm.kecamatan"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-semibold"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Desa / Kelurahan</label>
                        <input
                            type="text"
                            v-model="editForm.desa_kelurahan"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-semibold"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tahun Snapshot</label>
                        <input
                            type="number"
                            v-model="editForm.tahun"
                            min="2000"
                            max="2099"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 font-semibold"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t">
                        <button
                            type="button"
                            @click="isEditModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-sm disabled:opacity-50"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SINGLE DELETE CONFIRMATION MODAL -->
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 animate-fade-in-up text-center">
                <div class="p-3 bg-rose-100 text-rose-600 rounded-full w-12 h-12 mx-auto flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>

                <h3 class="text-base font-extrabold text-gray-900">Konfirmasi Hapus Data</h3>
                <p class="text-xs text-gray-600">
                    Apakah Anda yakin ingin menghapus data koperasi <span class="font-bold text-gray-900">"{{ selectedItemForDelete?.nama_koperasi }}"</span>?
                </p>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        @click="isDeleteModalOpen = false"
                        class="px-4 py-2 text-xs rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-5 py-2 text-xs rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-sm"
                    >
                        Ya, Hapus Data
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
