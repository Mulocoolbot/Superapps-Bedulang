<script setup>
import { ref } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import Modal from '@/Components/Modal.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    logs: {
        type: Array,
        default: () => []
    },
    kmps: {
        type: Object,
        default: () => ({ data: [], links: [], current_page: 1, last_page: 1 })
    },
    totalCount: {
        type: Number,
        default: 0
    },
    kecamatans: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

// Form Import
const form = useForm({
    file: null,
})

const selectedFileName = ref('')
const isDragging = ref(false)
const activeTab = ref('import') // 'import' | 'data'

const filterKecamatan = ref(props.filters?.kecamatan || '')

const filterData = () => {
    const params = {}
    if (filterKecamatan.value) params.kecamatan = filterKecamatan.value

    router.get(
        route('admin.kdmp.import_kmp'),
        params,
        { preserveState: true, preserveScroll: true, replace: true }
    )
}

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
    form.post(route('admin.kdmp.import_kmp.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            selectedFileName.value = ''
        }
    })
}

// Edit KMP Modal
const isEditModalOpen = ref(false)
const editForm = useForm({
    id: null,
    nama_koperasi: '',
    kecamatan: '',
    desa_kelurahan: '',
    jenis: 'Desa',
    alamat: '',
    nomor_induk: '',
    no_ahu: '',
})

const openEditModal = (item) => {
    editForm.id = item.id
    editForm.nama_koperasi = item.nama_koperasi
    editForm.kecamatan = item.kecamatan
    editForm.desa_kelurahan = item.desa_kelurahan
    editForm.jenis = item.jenis || 'Desa'
    editForm.alamat = item.alamat || ''
    editForm.nomor_induk = item.nomor_induk || ''
    editForm.no_ahu = item.no_ahu || ''
    isEditModalOpen.value = true
}

const closeEditModal = () => {
    isEditModalOpen.value = false
    editForm.reset()
}

const submitEdit = () => {
    editForm.put(route('admin.kdmp.import_kmp.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => closeEditModal()
    })
}

// Delete KMP Modal
const isDeleteModalOpen = ref(false)
const selectedItemForDelete = ref(null)

const confirmDeleteKmp = (item) => {
    selectedItemForDelete.value = item
    isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false
    selectedItemForDelete.value = null
}

const submitDeleteKmp = () => {
    if (!selectedItemForDelete.value) return
    router.delete(route('admin.kdmp.import_kmp.destroy', selectedItemForDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal()
    })
}

// Delete Import Log Modal
const isDeleteLogModalOpen = ref(false)
const selectedLogForDelete = ref(null)

const confirmDeleteLog = (log) => {
    selectedLogForDelete.value = log
    isDeleteLogModalOpen.value = true
}

const closeDeleteLogModal = () => {
    isDeleteLogModalOpen.value = false
    selectedLogForDelete.value = null
}

const submitDeleteLog = () => {
    if (!selectedLogForDelete.value) return
    router.delete(route('admin.kdmp.import_kmp.destroy_log', selectedLogForDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteLogModal()
    })
}
</script>

<template>
    <Head title="Import Data KMP - KDMP" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Import Data KMP Bangka Tengah
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Navigation Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <Link :href="route('admin.kdmp.dashboard')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengelolaan Data Admin
                    </Link>
                    <Link :href="route('admin.kdmp.import_kmp')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Import Data KMP
                    </Link>
                    <Link :href="route('admin.kdmp.history')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>

                <!-- Section Tabs inside Import KMP page -->
                <div class="flex flex-wrap justify-between items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center space-x-2">
                        <button
                            @click="activeTab = 'import'"
                            :class="[
                                'px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 flex items-center gap-2',
                                activeTab === 'import'
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none'
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Upload File & Logs
                        </button>

                        <button
                            @click="activeTab = 'data'"
                            :class="[
                                'px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 flex items-center gap-2',
                                activeTab === 'data'
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none'
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Lihat Data KMP ({{ totalCount }})
                        </button>
                    </div>

                    <a
                        :href="route('admin.kdmp.import_kmp.template')"
                        class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-3.5 py-2 rounded-lg transition-colors border border-indigo-100 dark:border-indigo-800/50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Format Template (CSV)
                    </a>
                </div>

                <!-- Tab 1: Upload File & Logs -->
                <div v-if="activeTab === 'import'" class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <header class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Import File KMP Baru</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Unggah dokumen berupa file CSV, XLS, atau XLSX berisi daftar Koperasi Merah Putih di Kabupaten Bangka Tengah.
                            </p>
                        </header>

                        <form @submit.prevent="submitImport" class="space-y-6 max-w-2xl">
                            <!-- Drag & Drop Zone -->
                            <div>
                                <InputLabel value="File Excel / CSV" class="mb-2 font-medium" />
                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragenter.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="[
                                        'border-2 border-dashed rounded-xl p-8 text-center transition-all cursor-pointer relative',
                                        isDragging
                                            ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400 bg-gray-50/50 dark:bg-gray-900/50'
                                    ]"
                                >
                                    <input
                                        type="file"
                                        accept=".csv, .xlsx, .xls"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        @change="handleFileSelect"
                                    />
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <span class="text-indigo-600 dark:text-indigo-400 hover:underline">Klik untuk mengunggah</span> atau drag & drop file ke sini
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Format yang didukung: XLSX, XLS, CSV (Maks. 10MB)
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="selectedFileName" class="mt-3 flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-700 dark:text-emerald-300 text-sm">
                                    <span class="truncate font-medium flex items-center gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ selectedFileName }}
                                    </span>
                                    <button type="button" @click="selectedFileName = ''; form.file = null" class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 font-bold ml-2">
                                        ✕
                                    </button>
                                </div>

                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing || !form.file" class="px-6 py-2.5">
                                    Import Data KMP
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    <!-- Import Logs History Table -->
                    <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <header class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Batch Import</h3>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-6 py-3">Nama File</th>
                                        <th class="px-6 py-3">Diimport Oleh</th>
                                        <th class="px-6 py-3">Jumlah Baris</th>
                                        <th class="px-6 py-3">Tanggal Import</th>
                                        <th class="px-6 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="log in logs" :key="log.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ log.file_name }}
                                        </td>
                                        <td class="px-6 py-4">{{ log.user?.name || '-' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 rounded-full text-xs font-semibold">
                                                {{ log.total_rows }} KMP
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="confirmDeleteLog(log)" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                                Hapus Batch
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="logs.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada riwayat import KMP.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Lihat Data KMP -->
                <div v-if="activeTab === 'data'" class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <header class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar KMP Terdaftar</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Menampilkan seluruh data KMP di Bangka Tengah yang telah diimport.</p>
                        </div>

                        <div class="flex items-center gap-2">
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
                                    <th class="px-6 py-3">Nama Koperasi</th>
                                    <th class="px-6 py-3">Kecamatan</th>
                                    <th class="px-6 py-3">Desa / Kelurahan</th>
                                    <th class="px-6 py-3">Jenis</th>
                                    <th class="px-6 py-3">Alamat</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in kmps.data" :key="item.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ item.nama_koperasi }}</td>
                                    <td class="px-6 py-4">{{ item.kecamatan }}</td>
                                    <td class="px-6 py-4">{{ item.desa_kelurahan }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[
                                            'px-2 py-1 rounded-md text-xs font-semibold',
                                            item.jenis === 'Kelurahan' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                                        ]">
                                            {{ item.jenis }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate">{{ item.alamat || '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditModal(item)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                                            <button @click="confirmDeleteKmp(item)" class="font-medium text-red-600 dark:text-red-500 hover:underline ml-2">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="kmps.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada data KMP. Silakan lakukan import data terlebih dahulu.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="kmps" />
                    </div>
                </div>

                <!-- Modal Edit KMP -->
                <Modal :show="isEditModalOpen" @close="closeEditModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Edit Data KMP
                        </h2>

                        <form @submit.prevent="submitEdit" class="mt-6 space-y-4">
                            <div>
                                <InputLabel for="edit_nama" value="Nama Koperasi" />
                                <TextInput id="edit_nama" v-model="editForm.nama_koperasi" type="text" class="mt-1 block w-full" required />
                                <InputError :message="editForm.errors.nama_koperasi" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="edit_kecamatan" value="Kecamatan" />
                                    <select id="edit_kecamatan" v-model="editForm.kecamatan" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option v-for="kec in kecamatans" :key="kec" :value="kec">{{ kec }}</option>
                                    </select>
                                    <InputError :message="editForm.errors.kecamatan" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="edit_desa" value="Desa / Kelurahan" />
                                    <TextInput id="edit_desa" v-model="editForm.desa_kelurahan" type="text" class="mt-1 block w-full" required />
                                    <InputError :message="editForm.errors.desa_kelurahan" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="edit_jenis" value="Jenis Koperasi" />
                                <select id="edit_jenis" v-model="editForm.jenis" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="Desa">Desa</option>
                                    <option value="Kelurahan">Kelurahan</option>
                                </select>
                            </div>

                            <div>
                                <InputLabel for="edit_alamat" value="Alamat Lengkap" />
                                <textarea id="edit_alamat" v-model="editForm.alamat" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="closeEditModal"> Batal </SecondaryButton>
                                <PrimaryButton :disabled="editForm.processing"> Simpan Perubahan </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

                <!-- Modal Delete Single KMP -->
                <Modal :show="isDeleteModalOpen" @close="closeDeleteModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Konfirmasi Hapus Data KMP
                        </h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Apakah Anda yakin ingin menghapus <strong>{{ selectedItemForDelete?.nama_koperasi }}</strong>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                            <DangerButton @click="submitDeleteKmp"> Hapus </DangerButton>
                        </div>
                    </div>
                </Modal>

                <!-- Modal Delete Batch Log -->
                <Modal :show="isDeleteLogModalOpen" @close="closeDeleteLogModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Konfirmasi Hapus Batch Import
                        </h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Apakah Anda yakin ingin menghapus seluruh data KMP yang diimport dari batch file <strong>{{ selectedLogForDelete?.file_name }}</strong>? Seluruh record KMP pada batch ini akan dihapus dari sistem.
                        </p>
                        <div class="mt-6 flex justify-end gap-3">
                            <SecondaryButton @click="closeDeleteLogModal"> Batal </SecondaryButton>
                            <DangerButton @click="submitDeleteLog"> Hapus Batch Data </DangerButton>
                        </div>
                    </div>
                </Modal>

            </div>
        </div>
    </AdminLayout>
</template>
