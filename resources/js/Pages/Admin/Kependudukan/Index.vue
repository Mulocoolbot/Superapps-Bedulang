<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

import * as XLSX from 'xlsx'

const props = defineProps({
    logs: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Object,
        default: () => ({})
    },
    totalRecords: {
        type: Number,
        default: 0
    }
})

const currentYear = new Date().getFullYear()

// Form Import per Semester & Category
const form = useForm({
    category_key: 'status_perkawinan',
    tahun: currentYear,
    semester: 1,
    file: null,
})

const selectedFileName = ref('')
const isDragging = ref(false)

// File Preview state
const showPreviewModal = ref(false)
const previewHeaders = ref([])
const previewRows = ref([])
const totalParsedRows = ref(0)
const fileSizeFormatted = ref('')
const hasKecamatanCol = ref(false)
const hasDesaCol = ref(false)
const detectedVariables = ref([])

const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const parseCSVLine = (text) => {
    let delimiter = ','
    if (text.includes(';') && (text.split(';').length > text.split(',').length)) {
        delimiter = ';'
    } else if (text.includes('\t') && (text.split('\t').length > text.split(',').length)) {
        delimiter = '\t'
    }
    
    const result = []
    let entry = ''
    let inQuotes = false

    for (let i = 0; i < text.length; i++) {
        const char = text[i]
        if (char === '"') {
            inQuotes = !inQuotes
        } else if (char === delimiter && !inQuotes) {
            result.push(entry.trim())
            entry = ''
        } else {
            entry += char
        }
    }
    result.push(entry.trim())
    return result
}

const processFilePreview = (file) => {
    if (!file) return

    fileSizeFormatted.value = formatFileSize(file.size)
    const reader = new FileReader()
    const ext = file.name.split('.').pop().toLowerCase()
    const isExcel = ext === 'xlsx' || ext === 'xls'

    reader.onload = (e) => {
        let matrix = []
        if (isExcel) {
            try {
                const data = new Uint8Array(e.target.result)
                const workbook = XLSX.read(data, { type: 'array' })
                const firstSheet = workbook.SheetNames[0]
                const worksheet = workbook.Sheets[firstSheet]
                matrix = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' })
            } catch (err) {
                alert('Gagal membaca file Excel: ' + err.message)
                return
            }
        } else {
            const text = e.target.result
            const rawLines = text.split(/\r\n|\n/).filter(line => line.trim().length > 0)
            matrix = rawLines.map(line => parseCSVLine(line).map(v => v.replace(/^"|"$/g, '')))
        }

        if (!matrix || matrix.length === 0) {
            alert('File kosong atau format tidak dapat dibaca.')
            return
        }

        // Find header row (either "WILAYAH" line or row 0)
        let headerRowIndex = 0
        let isMultiHeader = false
        for (let i = 0; i < Math.min(10, matrix.length); i++) {
            const rowStr = matrix[i].map(v => String(v)).join(' ').toUpperCase()
            if (rowStr.includes('WILAYAH')) {
                headerRowIndex = i
                isMultiHeader = true
                break
            }
        }

        let headers = []
        if (isMultiHeader) {
            const topHeader = matrix[headerRowIndex] || []
            const subHeader = matrix[headerRowIndex + 1] || []
            let currentCat = ''
            for (let c = 0; c < Math.max(topHeader.length, subHeader.length); c++) {
                const top = String(topHeader[c] || '').trim()
                if (top !== '') currentCat = top
                const sub = String(subHeader[c] || '').trim()
                if (c === 0) {
                    headers.push('KECAMATAN / WILAYAH')
                } else if (c === 1) {
                    headers.push('DESA / KELURAHAN')
                } else {
                    headers.push((currentCat ? currentCat + ' ' : '') + sub)
                }
            }
        } else {
            headers = (matrix[0] || []).map(h => String(h).trim())
        }

        previewHeaders.value = headers
        hasKecamatanCol.value = isMultiHeader || headers.some(h => h.toLowerCase().includes('kecamatan') || h.toLowerCase().includes('wilayah'))
        hasDesaCol.value = isMultiHeader || headers.some(h => h.toLowerCase().includes('desa') || h.toLowerCase().includes('kelurahan') || h.toLowerCase().includes('wilayah'))

        const startIdx = isMultiHeader ? headerRowIndex + 2 : 1
        const dataRows = []
        for (let i = startIdx; i < matrix.length; i++) {
            const row = matrix[i]
            if (row && row.some(v => String(v).trim() !== '')) {
                dataRows.push(row.map(v => String(v).trim()))
            }
        }

        totalParsedRows.value = dataRows.length
        previewRows.value = dataRows.slice(0, 10)
        showPreviewModal.value = true
    }

    if (isExcel) {
        reader.readAsArrayBuffer(file)
    } else {
        reader.readAsText(file)
    }
}

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.file = file
        selectedFileName.value = file.name
        processFilePreview(file)
    }
}

const handleDrop = (event) => {
    isDragging.value = false
    const file = event.dataTransfer.files[0]
    if (file) {
        form.file = file
        selectedFileName.value = file.name
        processFilePreview(file)
    }
}

const cancelPreview = () => {
    form.reset('file')
    selectedFileName.value = ''
    showPreviewModal.value = false
    previewHeaders.value = []
    previewRows.value = []
}

const confirmAndSubmit = () => {
    if (!form.file || !form.tahun || !form.semester || !form.category_key) return

    form.post(route('admin.kependudukan.import'), {
        preserveScroll: true,
        onSuccess: () => {
            showPreviewModal.value = false
            form.reset('file')
            selectedFileName.value = ''
        }
    })
}

// Delete Log snapshot
const confirmDeleteLog = (log) => {
    if (confirm(`Apakah Anda yakin ingin menghapus log import ${log.category_name} (${log.tahun} Semester ${log.semester})? Semua data terkait akan terhapus.`)) {
        router.delete(route('admin.kependudukan.destroy_log', log.id), {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <Head title="Panel Kelola Kependudukan" />

    <AdminLayout>
        <div class="space-y-8 p-6 lg:p-10">

            <!-- HEADER TITLE BANNER -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-6">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                        <span>Panel Kelola Kependudukan</span>
                        <span class="px-3 py-1 text-xs font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200 rounded-full border border-blue-200 dark:border-blue-800">
                            DUKCAPIL Kab. Bangka Tengah
                        </span>
                    </h1>
                    <p class="text-sm font-bold text-gray-600 dark:text-gray-300 mt-1 max-w-3xl">
                        Unggah dan kelola file data kependudukan per semester (Semester 1 & Semester 2) untuk 8 kategori demografi.
                    </p>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 shadow-xs border border-gray-200 dark:border-gray-800 flex items-center gap-4 shrink-0">
                    <div class="p-3 bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-xl text-2xl">
                        📊
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Baris Terdaftar</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">{{ totalRecords.toLocaleString('id-ID') }}</h3>
                    </div>
                </div>
            </div>

            <!-- MAIN INPUT CONTENT GRID -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">

                <!-- LEFT PANEL: UPLOAD FILE FORM (5 COLUMNS) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-slate-900">
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-800">
                            <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="p-2 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-xl">📥</span>
                                <span>Form Input File Semester</span>
                            </h2>
                        </div>

                        <form @submit.prevent="showPreviewModal ? confirmAndSubmit() : null" class="mt-6 space-y-5">
                            
                            <!-- Select Kategori Data -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-900 dark:text-gray-100 mb-2">
                                    Kategori Data
                                </label>
                                <select
                                    v-model="form.category_key"
                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-extrabold text-gray-900 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option v-for="(name, key) in categories" :key="key" :value="key" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white font-bold">
                                        {{ name.replace(/^[0-9]+\.\s*/, '') }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_key" class="mt-1 text-xs font-black text-rose-600 dark:text-rose-400">{{ form.errors.category_key }}</p>
                            </div>

                            <!-- Select Tahun & Semester -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-900 dark:text-gray-100 mb-2">
                                        Tahun Data
                                    </label>
                                    <input
                                        v-model="form.tahun"
                                        type="number"
                                        min="2000"
                                        max="2099"
                                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-extrabold text-gray-900 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-slate-800 dark:text-white"
                                    />
                                    <p v-if="form.errors.tahun" class="mt-1 text-xs font-black text-rose-600 dark:text-rose-400">{{ form.errors.tahun }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-900 dark:text-gray-100 mb-2">
                                        Semester
                                    </label>
                                    <select
                                        v-model="form.semester"
                                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-extrabold text-gray-900 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-slate-800 dark:text-white"
                                    >
                                        <option :value="1" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white font-bold">Semester 1</option>
                                        <option :value="2" class="bg-white text-gray-900 dark:bg-slate-800 dark:text-white font-bold">Semester 2</option>
                                    </select>
                                    <p v-if="form.errors.semester" class="mt-1 text-xs font-black text-rose-600 dark:text-rose-400">{{ form.errors.semester }}</p>
                                </div>
                            </div>

                            <!-- Drag & Drop File Upload -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-900 dark:text-gray-100 mb-2">
                                    Unggah File CSV / Excel (Format Tabel Dukcapil)
                                </label>

                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="[
                                        'relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-6 text-center transition cursor-pointer',
                                        isDragging ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/60' : 'border-gray-300 hover:border-gray-400 dark:border-gray-700 dark:hover:border-gray-600 bg-gray-50 dark:bg-slate-800/60',
                                        selectedFileName ? 'bg-emerald-50 border-emerald-500 dark:bg-emerald-950/50' : ''
                                    ]"
                                    @click="$refs.fileInput.click()"
                                >
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".csv,.txt,.xlsx,.xls"
                                        class="hidden"
                                        @change="handleFileSelect"
                                    />

                                    <div v-if="!selectedFileName" class="space-y-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-2xl text-blue-800 dark:bg-blue-900 dark:text-blue-200 mx-auto shadow-xs">
                                            📁
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-gray-900 dark:text-white">Klik atau seret file CSV/Excel ke sini</p>
                                            <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mt-1">Format didukung: .csv, .xlsx (Maks 10MB)</p>
                                        </div>
                                    </div>

                                    <div v-else class="flex items-center gap-3 text-emerald-800 dark:text-emerald-200">
                                        <span class="text-2xl">🔍</span>
                                        <div class="text-left">
                                            <p class="text-sm font-black truncate max-w-[200px] text-gray-900 dark:text-white">{{ selectedFileName }}</p>
                                            <p class="text-xs font-black text-blue-600 dark:text-blue-400">Klik untuk Pratinjau (Preview)</p>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.file" class="mt-1 text-xs font-black text-rose-600 dark:text-rose-400">{{ form.errors.file }}</p>
                            </div>

                            <!-- Open Preview Button -->
                            <button
                                v-if="form.file"
                                type="button"
                                @click="processFilePreview(form.file)"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3.5 text-sm font-black text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-700 cursor-pointer"
                            >
                                <span>🔍</span>
                                <span>Lihat Pratinjau (Preview Data)</span>
                            </button>

                        </form>
                    </div>
                </div>

                <!-- RIGHT PANEL: IMPORT LOGS HISTORY TABLE (7 COLUMNS) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-slate-900">
                        <div class="pb-4 border-b border-gray-200 dark:border-gray-800">
                            <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="p-2 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-xl">📜</span>
                                <span>Riwayat Import File Kependudukan</span>
                            </h2>
                            <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mt-1">
                                Daftar file per semester yang telah diunggah ke dalam sistem.
                            </p>
                        </div>

                        <!-- Logs Table -->
                        <div class="mt-6 overflow-x-auto">
                            <table v-if="logs.length > 0" class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 text-[11px] font-black uppercase tracking-wider text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-slate-800">
                                        <th class="py-3.5 px-4 rounded-l-xl">Kategori & Periode</th>
                                        <th class="py-3.5 px-4">Nama File</th>
                                        <th class="py-3.5 px-4 text-center">Jumlah Baris</th>
                                        <th class="py-3.5 px-4 text-center">Pengunggah</th>
                                        <th class="py-3.5 px-4 text-right rounded-r-xl">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-xs">
                                    <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-black text-gray-900 dark:text-white text-sm">{{ log.category_name.replace(/^[0-9]+\.\s*/, '') }}</div>
                                            <div class="text-[11px] text-blue-700 dark:text-blue-400 font-black mt-0.5">
                                                Tahun {{ log.tahun }} • Semester {{ log.semester }}
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-gray-100 truncate max-w-[160px]">
                                            {{ log.file_name }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-950 px-2.5 py-1 text-[11px] font-black text-emerald-900 dark:text-emerald-100 border border-emerald-300 dark:border-emerald-700">
                                                {{ log.total_rows }} Desa/Kel
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-black text-gray-900 dark:text-gray-100">
                                            {{ log.user ? log.user.name : 'System Admin' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <button
                                                @click="confirmDeleteLog(log)"
                                                class="rounded-lg bg-rose-100 p-2 text-rose-700 hover:bg-rose-200 dark:bg-rose-950 dark:text-rose-200 dark:hover:bg-rose-900 transition cursor-pointer border border-rose-300 dark:border-rose-700"
                                                title="Hapus Data Import Ini"
                                            >
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-else class="py-12 text-center text-gray-800 dark:text-gray-200">
                                <span class="text-3xl">📭</span>
                                <p class="text-sm font-black mt-2 text-gray-900 dark:text-white">Belum ada riwayat file import kependudukan.</p>
                                <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mt-1">Gunakan form di samping untuk mengunggah file data per semester.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- PREVIEW FILE MODAL -->
        <Teleport to="body">
            <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4 backdrop-blur-xs animate-fade-in">
                <div class="relative w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-200 p-6 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-100 text-xl text-blue-600 dark:bg-blue-950 dark:text-blue-300">
                                🔍
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">Pratinjau Content File Upload</h3>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                    Periksa struktur kolom dan isi data sebelum melakukan proses pengimporan.
                                </p>
                            </div>
                        </div>
                        <button
                            @click="cancelPreview"
                            class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-white transition cursor-pointer"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="p-6 overflow-y-auto space-y-6">

                        <!-- File Summary Metadata -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/60">
                                <p class="text-[11px] font-black uppercase text-slate-500 dark:text-slate-400">Nama & Ukuran File</p>
                                <p class="text-sm font-black text-slate-900 dark:text-white truncate mt-0.5">{{ selectedFileName }}</p>
                                <p class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ fileSizeFormatted }}</p>
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/60">
                                <p class="text-[11px] font-black uppercase text-slate-500 dark:text-slate-400">Total Baris Terdeteksi</p>
                                <p class="text-sm font-black text-slate-900 dark:text-white mt-0.5">{{ totalParsedRows }} Baris Data</p>
                                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Siap Diproses</p>
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-800/80 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/60">
                                <p class="text-[11px] font-black uppercase text-slate-500 dark:text-slate-400">Target Kategori</p>
                                <p class="text-sm font-black text-slate-900 dark:text-white mt-0.5">
                                    {{ categories[form.category_key] ? categories[form.category_key].replace(/^[0-9]+\.\s*/, '') : form.category_key }}
                                </p>
                                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400">Tahun {{ form.tahun }} • Semester {{ form.semester }}</p>
                            </div>
                        </div>

                        <!-- Validation Status Alert -->
                        <div
                            v-if="hasKecamatanCol && hasDesaCol"
                            class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-xs font-bold"
                        >
                            <span class="text-xl">✅</span>
                            <div>
                                <p class="font-black text-emerald-950 dark:text-emerald-100">Format Tabel Terdeteksi</p>
                                <p>Struktur kolom dan baris wilayah terdeteksi dengan benar. Data demografi siap disimpan.</p>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs font-bold"
                        >
                            <span class="text-xl">⚠️</span>
                            <div>
                                <p class="font-black text-amber-950 dark:text-amber-100">Peringatan Kolom</p>
                                <p>Pastikan file memuat kolom lokasi Kecamatan dan Desa/Kelurahan.</p>
                            </div>
                        </div>

                        <!-- Detected Variable Badges -->
                        <div>
                            <p class="text-xs font-black uppercase text-slate-700 dark:text-slate-200 mb-2">Kolom Header Terdeteksi ({{ previewHeaders.length }} Kolom):</p>
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="header in previewHeaders"
                                    :key="header"
                                    class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-blue-50 dark:bg-slate-800 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-slate-700"
                                >
                                    📌 {{ header }}
                                </span>
                            </div>
                        </div>

                        <!-- Data Table Preview (First 10 rows) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-black uppercase text-slate-700 dark:text-slate-200">Pratinjau 10 Baris Pertama Data:</p>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Menampilkan 10 dari {{ totalParsedRows }} baris</span>
                            </div>
                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 max-h-60">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 font-black text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                                        <tr>
                                            <th class="py-2.5 px-3 border-b border-slate-200 dark:border-slate-700">#</th>
                                            <th v-for="h in previewHeaders" :key="h" class="py-2.5 px-3 border-b border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                {{ h }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-bold text-slate-900 dark:text-slate-100">
                                        <tr v-for="(row, idx) in previewRows" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                            <td class="py-2 px-3 text-slate-400 font-mono text-[11px]">{{ idx + 1 }}</td>
                                            <td v-for="(val, cIdx) in row" :key="cIdx" class="py-2 px-3 whitespace-nowrap font-mono">
                                                {{ val }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer / Action Buttons -->
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 p-6 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80">
                        <button
                            type="button"
                            @click="cancelPreview"
                            class="rounded-xl px-5 py-2.5 text-xs font-black text-slate-700 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800 transition cursor-pointer border border-slate-300 dark:border-slate-700"
                        >
                            ❌ Batalkan & Ganti File
                        </button>

                        <button
                            type="button"
                            @click="confirmAndSubmit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-black text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-700 focus:outline-none disabled:opacity-50 cursor-pointer"
                        >
                            <span v-if="form.processing" class="h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                            <span v-else>🚀</span>
                            <span>Konfirmasi & Import Sekarang</span>
                        </button>
                    </div>

                </div>
            </div>
        </Teleport>

    </AdminLayout>
</template>
