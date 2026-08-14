<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
})

const opdPresets = [
    {
        id: 'super_admin',
        category: 'role',
        role: 'super_admin',
        slug: '',
        name: 'Super Admin',
        serviceMenu: 'Semua Layanan (Akses Penuh)',
        opd: 'Pemerintah Kabupaten Bangka Tengah',
        description: 'Role Super Admin dengan hak akses penuh ke seluruh sistem, kelola admin sistem, kelola seluruh data OPD/layanan, CCTV, dan pengaduan global.',
        badgeClass: 'bg-purple-100 text-purple-800 border-purple-200',
        features: [
            'Kelola Admin Sistem (Tambah, Edit, Hapus)',
            'Kelola & Input Data Kependudukan (8 Kategori Dukcapil)',
            'Kelola & Input Data Koperasi (CRUD & Import)',
            'Kelola & Verifikasi Admin KDMP',
            'Update Stok Darah UTD & RSUD',
            'Monitoring & Tindak Lanjut Semua Pengaduan Warga',
            'Monitoring Seluruh CCTV Daerah'
        ]
    },
    {
        id: 'kependudukan',
        category: 'layanan',
        role: 'admin_kependudukan',
        slug: 'kependudukan',
        name: 'Admin Layanan Kependudukan',
        serviceMenu: 'Menu Layanan Kependudukan',
        opd: 'Dinas Kependudukan dan Pencatatan Sipil',
        description: 'Admin pengelola menu layanan kependudukan, import data agregat 8 kategori Dukcapil, dan respon pengaduan kependudukan.',
        badgeClass: 'bg-blue-100 text-blue-800 border-blue-200',
        features: [
            'Kelola Data Kependudukan (Import CSV & XLSX)',
            '8 Kategori: Kepala Keluarga, Agama, Golongan Darah, Jenis Kelamin, Umur, Pekerjaan, Pendidikan, Status Perkawinan',
            'Unduh Template CSV Format Dukcapil',
            'Hapus & Kelola Log Riwayat Import',
            'Monitoring & Respon Pengaduan Bidang Kependudukan',
            'Akses Dashboard Kependudukan'
        ]
    },
    {
        id: 'pemerintahan',
        category: 'layanan',
        role: 'admin',
        slug: 'pemerintahan',
        name: 'Admin Layanan Pemerintahan',
        serviceMenu: 'Menu Layanan Pemerintahan',
        opd: 'Sekretariat Daerah / Bagian Tata Pemerintahan',
        description: 'Admin pengelola menu layanan administrasi pemerintahan, DPRD, dan pengaduan pemerintahan.',
        badgeClass: 'bg-indigo-100 text-indigo-800 border-indigo-200',
        features: [
            'Monitoring Pengaduan Pemerintahan',
            'Akses Dashboard Pemerintahan Daerah'
        ]
    },
    {
        id: 'informasi-publik',
        category: 'layanan',
        role: 'admin',
        slug: 'informasi-publik',
        name: 'Admin Layanan Informasi Publik',
        serviceMenu: 'Menu Layanan Informasi Publik',
        opd: 'RSUD Abu Hanifah & Dinas Kominfo',
        description: 'Admin pengelola menu layanan informasi publik, update stok darah RSUD, harga bahan pokok, dan pengaduan informasi publik.',
        badgeClass: 'bg-cyan-100 text-cyan-800 border-cyan-200',
        features: [
            'Update Stok Darah RSUD Abu Hanifah',
            'Monitoring Data Harga Bahan Pokok & Sawit',
            'Monitoring Pengaduan Informasi Publik'
        ]
    },
    {
        id: 'pendidikan',
        category: 'layanan',
        role: 'admin',
        slug: 'pendidikan',
        name: 'Admin Layanan Pendidikan',
        serviceMenu: 'Menu Layanan Pendidikan',
        opd: 'Dinas Pendidikan',
        description: 'Admin pengelola menu layanan pendidikan daerah dan monitoring pengaduan bidang pendidikan.',
        badgeClass: 'bg-amber-100 text-amber-800 border-amber-200',
        features: [
            'Monitoring Pengaduan Bidang Pendidikan',
            'Akses Dashboard Pendidikan Daerah'
        ]
    },
    {
        id: 'kepegawaian',
        category: 'layanan',
        role: 'admin',
        slug: 'kepegawaian',
        name: 'Admin Layanan Kepegawaian',
        serviceMenu: 'Menu Layanan Kepegawaian',
        opd: 'Badan Kepegawaian & Pengembangan SDM Daerah (BKPSDMD)',
        description: 'Admin pengelola menu layanan statistik ASN, PPPK, formasi kepegawaian, dan pengaduan kepegawaian.',
        badgeClass: 'bg-teal-100 text-teal-800 border-teal-200',
        features: [
            'Monitoring Pengaduan Kepegawaian',
            'Akses Dashboard Kepegawaian BKPSDMD'
        ]
    },
    {
        id: 'kependudukan',
        category: 'layanan',
        role: 'admin_kependudukan',
        slug: 'kependudukan',
        name: 'Admin Layanan Kependudukan',
        serviceMenu: 'Menu Layanan Kependudukan',
        opd: 'Dinas Kependudukan dan Pencatatan Sipil',
        description: 'Admin pengelola menu layanan kependudukan, import data agregat KK, dan pengaduan adminduk.',
        badgeClass: 'bg-blue-100 text-blue-800 border-blue-200',
        features: [
            'Import Data CSV/Excel Agregat Kependudukan & KK',
            'Kelola & Hapus Riwayat Log Import Kependudukan',
            'Monitoring Pengaduan Administrasi Kependudukan',
            'Akses Dashboard Kependudukan'
        ]
    },
    {
        id: 'perindagkop-ukm',
        category: 'layanan',
        role: 'admin',
        slug: 'perindagkop-ukm',
        name: 'Admin Layanan Perindagkop-UKM',
        serviceMenu: 'Menu Layanan Perindagkop-UKM',
        opd: 'Dinas Perindustrian dan Perdagangan',
        description: 'Admin pengelola menu layanan komoditas, harga pasar, profil UKM, dan pengaduan perindagkop.',
        badgeClass: 'bg-orange-100 text-orange-800 border-orange-200',
        features: [
            'Monitoring Data Pasar & Komoditas',
            'Monitoring Pengaduan Perindagkop',
            'Akses Dashboard Perindagkop'
        ]
    },
    {
        id: 'desa',
        category: 'layanan',
        role: 'admin',
        slug: 'desa',
        name: 'Admin Layanan Pertanian & Desa (Desa.id)',
        serviceMenu: 'Menu Layanan Desa.Id / Pertanian & Desa',
        opd: 'Dinas Pertanian dan Ketahanan Pangan',
        description: 'Admin pengelola menu layanan pertanian, peternakan, pangan, dan profil desa di Bangka Tengah.',
        badgeClass: 'bg-lime-100 text-lime-800 border-lime-200',
        features: [
            'Monitoring Pengaduan Pertanian & Pangan',
            'Akses Dashboard Pertanian & Desa'
        ]
    },
    {
        id: 'makanan-bergizi-gratis',
        category: 'layanan',
        role: 'admin',
        slug: 'makanan-bergizi-gratis',
        name: 'Admin Layanan Makanan Bergizi Gratis',
        serviceMenu: 'Menu Layanan Makanan Bergizi Gratis',
        opd: 'Satgas Program Makan Bergizi Gratis (MBG)',
        description: 'Admin pengelola menu layanan dashboard dan monitoring program makan bergizi gratis bagi siswa.',
        badgeClass: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        features: [
            'Monitoring Pengaduan Program MBG',
            'Akses Dashboard Makanan Bergizi Gratis'
        ]
    },
    {
        id: 'koperasi',
        category: 'layanan',
        role: 'admin_koperasi',
        slug: 'koperasi',
        name: 'Admin Layanan Koperasi',
        serviceMenu: 'Menu Layanan Koperasi',
        opd: 'Dinas Perindustrian, Perdagangan, Koperasi dan UKM',
        description: 'Admin pengelola menu layanan koperasi, import & CRUD data koperasi, dan monitoring pengaduan koperasi/UMKM.',
        badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        features: [
            'Kelola Data Koperasi (Import CSV/Excel & CRUD)',
            'Unduh Template CSV Data Koperasi',
            'Hapus & Kelola Log Riwayat Import Koperasi',
            'Monitoring Pengaduan Bidang Koperasi & UMKM',
            'Akses Dashboard Koperasi'
        ]
    },
    {
        id: 'kesehatan',
        category: 'layanan',
        role: 'admin',
        slug: 'kesehatan',
        name: 'Admin Layanan Kesehatan',
        serviceMenu: 'Menu Layanan Kesehatan',
        opd: 'Dinas Kesehatan & Unit Transfusi Darah (UTD)',
        description: 'Admin pengelola menu layanan informasi kesehatan daerah, update stok darah UTD real-time, dan pengaduan kesehatan.',
        badgeClass: 'bg-rose-100 text-rose-800 border-rose-200',
        features: [
            'Update Stok Darah Real-time (WB, PRC)',
            'Riwayat Perubahan Stok Darah (Log UTD)',
            'Monitoring Pengaduan Layanan Kesehatan',
            'Akses Dashboard Kesehatan'
        ]
    },
    {
        id: 'sosial',
        category: 'layanan',
        role: 'admin',
        slug: 'sosial',
        name: 'Admin Layanan Sosial',
        serviceMenu: 'Menu Layanan Sosial',
        opd: 'Dinas Sosial & Pemberdayaan Masyarakat Desa',
        description: 'Admin pengelola menu layanan informasi bantuan sosial dan monitoring pengaduan sosial.',
        badgeClass: 'bg-pink-100 text-pink-800 border-pink-200',
        features: [
            'Monitoring Pengaduan Bantuan Sosial',
            'Akses Dashboard Sosial'
        ]
    },
    {
        id: 'pariwisata',
        category: 'layanan',
        role: 'admin',
        slug: 'pariwisata',
        name: 'Admin Layanan Pariwisata',
        serviceMenu: 'Menu Layanan Pariwisata',
        opd: 'Dinas Kebudayaan, Pariwisata, Pemuda dan Olahraga',
        description: 'Admin pengelola menu layanan destinasi wisata, event daerah, dan monitoring pengaduan pariwisata.',
        badgeClass: 'bg-sky-100 text-sky-800 border-sky-200',
        features: [
            'Monitoring Destinasi Wisata & Event',
            'Monitoring Pengaduan Pariwisata',
            'Akses Dashboard Pariwisata'
        ]
    },
    {
        id: 'data-sektoral',
        category: 'layanan',
        role: 'admin',
        slug: 'data-sektoral',
        name: 'Admin Layanan Data Sektoral',
        serviceMenu: 'Menu Layanan Data Sektoral',
        opd: 'Dinas Komunikasi, Informatika dan Statistik',
        description: 'Admin pengelola menu layanan publikasi dan statistik data sektoral daerah.',
        badgeClass: 'bg-blue-100 text-blue-800 border-blue-200',
        features: [
            'Monitoring Publikasi Data Sektoral',
            'Akses Dashboard Data Sektoral'
        ]
    },
    {
        id: 'kdmp',
        category: 'layanan',
        role: 'admin',
        slug: 'kdmp',
        name: 'Admin Layanan KDMP',
        serviceMenu: 'Menu Layanan KDMP',
        opd: 'Sekretariat Program KDMP',
        description: 'Admin pengelola menu layanan dan verifikator Koperasi Merdeka Pangan tingkat kecamatan dan desa.',
        badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        features: [
            'Kelola & Verifikasi Admin KMP / Kecamatan',
            'Verifikasi Pembentukan, Sertifikat & Pelatihan',
            'Monitoring Pengaduan KDMP'
        ]
    },
    {
        id: 'hukum',
        category: 'layanan',
        role: 'admin',
        slug: 'hukum',
        name: 'Admin Layanan Hukum',
        serviceMenu: 'Menu Layanan Hukum',
        opd: 'Bagian Hukum Sekretariat Daerah',
        description: 'Admin pengelola menu layanan produk hukum daerah (JDIH) dan pengaduan regulasi.',
        badgeClass: 'bg-slate-100 text-slate-800 border-slate-200',
        features: [
            'Monitoring Produk Hukum & Regulasi',
            'Monitoring Pengaduan Hukum'
        ]
    },
    {
        id: 'cctv',
        category: 'layanan',
        role: 'admin',
        slug: 'cctv',
        name: 'Admin Layanan Monitoring CCTV',
        serviceMenu: 'Menu Layanan Monitoring CCTV',
        opd: 'Dinas Komunikasi dan Informatika',
        description: 'Admin pengelola siaran dan pemantauan CCTV di seluruh titik strategis Bangka Tengah.',
        badgeClass: 'bg-cyan-100 text-cyan-800 border-cyan-200',
        features: [
            'Monitoring Seluruh Siaran CCTV Daerah',
            'Akses Panel CCTV'
        ]
    },
    {
        id: 'agenda',
        category: 'layanan',
        role: 'admin',
        slug: 'agenda',
        name: 'Admin Layanan Agenda',
        serviceMenu: 'Menu Layanan Agenda',
        opd: 'Bagian Protokol & Komunikasi Pimpinan',
        description: 'Admin pengelola menu layanan agenda kegiatan daerah dan pimpinan.',
        badgeClass: 'bg-indigo-100 text-indigo-800 border-indigo-200',
        features: [
            'Monitoring Agenda Kegiatan Daerah',
            'Monitoring Pengaduan Agenda'
        ]
    },
    {
        id: 'event',
        category: 'layanan',
        role: 'admin',
        slug: 'event',
        name: 'Admin Layanan Event',
        serviceMenu: 'Menu Layanan Event',
        opd: 'Dinas Kebudayaan, Pariwisata, Pemuda & Olahraga / Bagian Prokompim',
        description: 'Admin pengelola menu layanan publikasi event daerah, kalender kegiatan, dan monitoring informasi.',
        badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        features: [
            'Monitoring Kalender & Publikasi Event Daerah',
            'Monitoring Pengaduan Terkait Event'
        ]
    },
    {
        id: 'inovasi-daerah',
        category: 'layanan',
        role: 'admin',
        slug: 'inovasi-daerah',
        name: 'Admin Layanan Inovasi Daerah',
        serviceMenu: 'Menu Layanan Inovasi Daerah',
        opd: 'Bappelitbangda Kabupaten Bangka Tengah',
        description: 'Admin pengelola menu layanan inovasi daerah, riset, dan publikasi inovasi pelayanan publik.',
        badgeClass: 'bg-purple-100 text-purple-800 border-purple-200',
        features: [
            'Monitoring Publikasi Inovasi Daerah (Bappelitbangda)',
            'Monitoring Pengaduan Inovasi Pelayanan Publik'
        ]
    },
    {
        id: 'perizinan-terpadu',
        category: 'layanan',
        role: 'admin',
        slug: 'perizinan-terpadu',
        name: 'Admin Layanan Perizinan Terpadu',
        serviceMenu: 'Menu Layanan Perizinan Terpadu',
        opd: 'Dinas Penanaman Modal & PTSP (DPMPTSP)',
        description: 'Admin pengelola menu layanan portal perizinan terpadu.',
        badgeClass: 'bg-violet-100 text-violet-800 border-violet-200',
        features: [
            'Monitoring Layanan Perizinan Terpadu',
            'Monitoring Pengaduan Perizinan'
        ]
    },
    {
        id: 'pelayanan-kesehatan',
        category: 'layanan',
        role: 'admin',
        slug: 'pelayanan-kesehatan',
        name: 'Admin Layanan Pelayanan Kesehatan',
        serviceMenu: 'Menu Layanan Pelayanan Kesehatan',
        opd: 'Dinas Kesehatan & Puskesmas',
        description: 'Admin pengelola menu layanan fasilitas pelayanan kesehatan daerah.',
        badgeClass: 'bg-rose-100 text-rose-800 border-rose-200',
        features: [
            'Monitoring Portal Pelayanan Kesehatan',
            'Monitoring Pengaduan Faskes'
        ]
    },
    {
        id: 'pengaduan-ppid',
        category: 'layanan',
        role: 'admin',
        slug: 'pengaduan-ppid',
        name: 'Admin Layanan Pengaduan PPID',
        serviceMenu: 'Menu Layanan Pengaduan PPID',
        opd: 'PPID Utama Kabupaten Bangka Tengah',
        description: 'Admin pengelola monitoring dan tindak lanjut aduan masyarakat via PPID / SP4N Lapor.',
        badgeClass: 'bg-amber-100 text-amber-800 border-amber-200',
        features: [
            'Monitoring & Tindak Lanjut Pengaduan PPID',
            'Integrasi Kanal Aduan Publik'
        ]
    },
    {
        id: 'administrasi-warga',
        category: 'layanan',
        role: 'admin',
        slug: 'administrasi-warga',
        name: 'Admin Layanan Administrasi Warga',
        serviceMenu: 'Menu Layanan Administrasi Warga',
        opd: 'Sekretariat Daerah & Bagian Tata Pemerintahan',
        description: 'Admin pengelola menu layanan administrasi persuratan dan kebutuhan warga.',
        badgeClass: 'bg-teal-100 text-teal-800 border-teal-200',
        features: [
            'Monitoring Layanan Administrasi Warga',
            'Monitoring Pengaduan Administrasi'
        ]
    },
    {
        id: 'custom',
        category: 'role',
        role: 'admin',
        slug: '',
        name: 'Admin Layanan Kustom / Lainnya',
        serviceMenu: 'Tanpa Layanan Khusus (Input Slug Manual)',
        opd: 'Layanan Mandiri',
        description: 'Role admin umum untuk menentukan slug layanan baru secara mandiri sesuai kebutuhan OPD.',
        badgeClass: 'bg-gray-100 text-gray-800 border-gray-200',
        features: [
            'Akses Dashboard Admin BEDULANG',
            'Monitoring Pengaduan sesuai Slug Layanan'
        ]
    }
]

const rolePresets = computed(() => opdPresets.filter(p => p.category === 'role'))
const servicePresets = computed(() => opdPresets.filter(p => p.category === 'layanan'))

// Determine initial preset
const detectPresetId = () => {
    if (props.user.role === 'super_admin') return 'super_admin'
    if (props.user.role === 'admin_kependudukan' || props.user.service_slug === 'kependudukan') return 'kependudukan'
    if (props.user.role === 'admin_koperasi' || props.user.service_slug === 'koperasi') return 'koperasi'
    if (props.user.service_slug) {
        const found = opdPresets.find(p => p.slug === props.user.service_slug)
        if (found) return found.id
    }
    return 'custom'
}

const selectedPresetId = ref(detectPresetId())

const form = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    password: '',
    password_confirmation: '',
    role: props.user.role ?? 'admin',
    service_slug: props.user.service_slug ?? '',
})

const selectedPreset = computed(() => {
    return opdPresets.find(p => p.id === selectedPresetId.value) || opdPresets[0]
})

const onPresetChange = () => {
    const preset = opdPresets.find(p => p.id === selectedPresetId.value)
    if (preset) {
        form.role = preset.role
        if (preset.id === 'custom') {
            // Keep existing or let user edit
        } else {
            form.service_slug = preset.slug
        }
    }
}

const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Edit Admin Sistem" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="rounded-3xl bg-white p-6 sm:p-8 shadow-sm border border-gray-100">
                <!-- Header -->
                <div class="mb-6 pb-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </span>
                            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Admin Sistem</h1>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Perbarui informasi, peran, dan hak akses admin <span class="font-bold text-gray-900">{{ props.user.name }}</span>.</p>
                    </div>
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition shrink-0"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        <span>Kembali</span>
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Pilihan Role & Layanan OPD -->
                    <div class="space-y-3 bg-gradient-to-br from-blue-50/50 via-slate-50 to-indigo-50/30 p-5 rounded-2xl border border-blue-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-bold text-gray-800">
                                Pilih Akun Admin (Menu Layanan / Role)
                            </label>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                Otomatis Sinkronisasi Fitur
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Mengubah pilihan OPD/Layanan akan secara otomatis memperbarui peran (<code class="text-blue-600 font-bold">role</code>) dan slug (<code class="text-blue-600 font-bold">service_slug</code>) agar fitur terkait langsung aktif untuk admin ini.
                        </p>

                        <div class="mt-2">
                            <select
                                v-model="selectedPresetId"
                                @change="onPresetChange"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 shadow-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            >
                                <optgroup label="── Role Sistem (Tanpa Layanan Khusus) ──">
                                    <option
                                        v-for="preset in rolePresets"
                                        :key="preset.id"
                                        :value="preset.id"
                                    >
                                        {{ preset.name }} — {{ preset.serviceMenu }}
                                    </option>
                                </optgroup>
                                <optgroup label="── Admin Berdasarkan Menu Layanan ──">
                                    <option
                                        v-for="preset in servicePresets"
                                        :key="preset.id"
                                        :value="preset.id"
                                    >
                                        {{ preset.name }} — {{ preset.opd }}
                                    </option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Live Capability Preview Card -->
                        <div class="mt-4 p-4 rounded-xl bg-white border border-gray-200 shadow-xs space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">{{ selectedPreset.name }}</h3>
                                    <p class="text-xs text-blue-600 font-medium mt-0.5">{{ selectedPreset.serviceMenu }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ selectedPreset.description }}</p>
                                </div>
                                <span :class="['px-3 py-1 rounded-lg text-[11px] font-bold border shrink-0', selectedPreset.badgeClass]">
                                    {{ selectedPreset.role }} {{ selectedPreset.slug ? `(${selectedPreset.slug})` : '' }}
                                </span>
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Fitur & Hak Akses yang Langsung Aktif:</p>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <li
                                        v-for="(feature, idx) in selectedPreset.features"
                                        :key="idx"
                                        class="flex items-center gap-2 text-xs text-gray-700 font-medium"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-emerald-600 shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        <span>{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Slug Override / Manual Field (Muncul saat custom dipilih) -->
                    <div v-if="selectedPresetId === 'custom'" class="grid gap-6 md:grid-cols-2 p-4 rounded-2xl bg-amber-50/60 border border-amber-200">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Role Sistem</label>
                            <select
                                v-model="form.role"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            >
                                <option value="admin">Admin Layanan / OPD (admin)</option>
                                <option value="admin_kependudukan">Admin Kependudukan (admin_kependudukan)</option>
                                <option value="admin_koperasi">Admin Koperasi (admin_koperasi)</option>
                                <option value="super_admin">Super Admin (super_admin)</option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-xs font-semibold text-red-600">{{ form.errors.role }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                Layanan / Domain Slug
                            </label>
                            <input
                                v-model="form.service_slug"
                                type="text"
                                placeholder="Contoh: dinas-sosial, bapenda, dll."
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                            <p v-if="form.errors.service_slug" class="mt-1 text-xs font-semibold text-red-600">{{ form.errors.service_slug }}</p>
                        </div>
                    </div>

                    <!-- Detail Akun Admin -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Nama Lengkap Admin</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs font-semibold text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Alamat Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs font-semibold text-red-600">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Password Baru</label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                            <p class="mt-1 text-[11px] text-gray-400">Kosongkan jika tidak ingin mengubah password.</p>
                            <p v-if="form.errors.password" class="mt-1 text-xs font-semibold text-red-600">{{ form.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Ketik ulang password baru"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                        </div>
                    </div>

                    <!-- Submit & Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                        <Link
                            href="/admin/users"
                            class="px-5 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-sm font-bold text-white shadow-sm transition disabled:opacity-50 flex items-center gap-2 cursor-pointer shadow-blue-500/10 hover:shadow-md"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
