<script setup>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    kmp: Object,
    pendingEdits: Object,
})

const kmpForm = useForm({
    desa_kelurahan: props.kmp?.desa_kelurahan || '',
    jenis: props.kmp?.jenis || 'Desa',
    nama_koperasi: props.kmp?.nama_koperasi || '',
    nomor_induk: props.kmp?.nomor_induk || '',
    no_ahu: props.kmp?.no_ahu || '',
    tanggal_ahu: props.kmp?.tanggal_ahu || '',
    alamat: props.kmp?.alamat || '',
    telp: props.kmp?.telp || '',
    status_operasional: props.kmp ? (props.kmp.status_operasional ? true : false) : false,
})

const certForm = useForm({
    sertifikat: null,
})

const pelatihanForm = useForm({
    pelatihan: null,
})

const managementForm = useForm({
    jumlah_anggota: props.kmp?.jumlah_anggota || 0,
    jumlah_pengawas: props.kmp?.jumlah_pengawas || 0,
    jumlah_pengurus: props.kmp?.jumlah_pengurus || 0,
    ketua_nama: props.kmp?.ketua_nama || '',
    ketua_foto: null,
    sekretaris_nama: props.kmp?.sekretaris_nama || '',
    sekretaris_foto: null,
    bendahara_nama: props.kmp?.bendahara_nama || '',
    bendahara_foto: null,
    simpanan_pokok: props.kmp?.simpanan_pokok || 0,
    simpanan_wajib: props.kmp?.simpanan_wajib || 0,
})

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0)
}

const submitKmp = () => {
    kmpForm.post(route('admin.kdmp.kmp.request_edit'), {
        preserveScroll: true,
    })
}

const submitCert = () => {
    certForm.post(route('admin.kdmp.kmp.upload_sertifikat'), {
        preserveScroll: true,
        onSuccess: () => certForm.reset('sertifikat'),
    })
}

const submitPelatihan = () => {
    pelatihanForm.post(route('admin.kdmp.kmp.upload_pelatihan'), {
        preserveScroll: true,
        onSuccess: () => pelatihanForm.reset('pelatihan'),
    })
}

const submitManagement = () => {
    managementForm.post(route('admin.kdmp.kmp.update_management'), {
        preserveScroll: true,
        onSuccess: () => {
            managementForm.reset('ketua_foto', 'sekretaris_foto', 'bendahara_foto')
        },
    })
}
</script>

<template>
    <Head title="Admin KMP" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin KMP</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div v-if="pendingEdits" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700">
                                Anda memiliki pengajuan perubahan data yang sedang <strong>menunggu verifikasi</strong> Admin Kecamatan.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="kmp && kmp.status_pembentukan === 'pending_verification'" class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Status pembentukan KMP Anda sedang <strong>menunggu verifikasi</strong> Admin Kecamatan. Data Anda belum akan tampil di halaman publik.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="kmp && kmp.status_pembentukan === 'rejected'" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                Pembentukan KMP Anda <strong>ditolak</strong> oleh Admin Kecamatan. Silakan hubungi kecamatan untuk detail lebih lanjut.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <Link :href="route('admin.kdmp.kmp.dashboard')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengelolaan Data
                    </Link>
                    <Link :href="route('admin.kdmp.kmp.history')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>
                
                <!-- Info No KMP Assigned -->
                <div v-if="!kmp" class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-300 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Belum Ada KMP Terhubung</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Akun Anda belum terhubung dengan Koperasi Merah Putih (KMP). Silakan hubungi Admin Kecamatan untuk menghubungkan akun Anda ke KMP yang telah terdaftar.
                    </p>
                </div>

                <!-- Form Data KMP -->
                <div v-if="kmp" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detail Koperasi Merah Putih</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Anda dapat mengajukan perubahan data KMP di bawah ini.
                        </p>
                    </header>

                    <form @submit.prevent="submitKmp" class="mt-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="nama_koperasi" value="Nama Koperasi" />
                                <TextInput id="nama_koperasi" v-model="kmpForm.nama_koperasi" type="text" class="mt-1 block w-full" required />
                                <InputError :message="kmpForm.errors.nama_koperasi" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="jenis" value="Jenis Koperasi (Desa / Kelurahan)" />
                                <select id="jenis" v-model="kmpForm.jenis" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="Desa">Desa</option>
                                    <option value="Kelurahan">Kelurahan</option>
                                </select>
                                <InputError :message="kmpForm.errors.jenis" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="desa_kelurahan" value="Nama Desa/Kelurahan" />
                                <TextInput id="desa_kelurahan" v-model="kmpForm.desa_kelurahan" type="text" class="mt-1 block w-full" required />
                                <InputError :message="kmpForm.errors.desa_kelurahan" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="nomor_induk" value="Nomor Induk" />
                                <TextInput id="nomor_induk" v-model="kmpForm.nomor_induk" type="text" class="mt-1 block w-full" />
                                <InputError :message="kmpForm.errors.nomor_induk" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="no_ahu" value="No AHU" />
                                <TextInput id="no_ahu" v-model="kmpForm.no_ahu" type="text" class="mt-1 block w-full" />
                                <InputError :message="kmpForm.errors.no_ahu" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="tanggal_ahu" value="Tanggal AHU" />
                                <TextInput id="tanggal_ahu" v-model="kmpForm.tanggal_ahu" type="date" class="mt-1 block w-full" />
                                <InputError :message="kmpForm.errors.tanggal_ahu" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="telp" value="Nomor Telepon" />
                                <TextInput id="telp" v-model="kmpForm.telp" type="text" class="mt-1 block w-full" />
                                <InputError :message="kmpForm.errors.telp" class="mt-2" />
                            </div>
                            
                            <div class="col-span-1 md:col-span-2">
                                <InputLabel for="alamat" value="Alamat Lengkap" />
                                <textarea id="alamat" v-model="kmpForm.alamat" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                                <InputError :message="kmpForm.errors.alamat" class="mt-2" />
                            </div>

                            <div class="col-span-1 md:col-span-2 flex items-center">
                                <input id="status_operasional" type="checkbox" v-model="kmpForm.status_operasional" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="status_operasional" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Koperasi Sudah Operasional</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="kmpForm.processing || pendingEdits">Ajukan Perubahan</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="kmpForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Berhasil dikirim.</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <!-- Pemberkasan Sertifikat -->
                <div v-if="kmp" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pemberkasan Sertifikat KMP</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Unggah dokumen sertifikat KMP (PDF/Gambar) untuk diverifikasi oleh Admin Kecamatan.
                        </p>
                    </header>

                    <div v-if="kmp.status_sertifikat === 'pending' && kmp.file_sertifikat" class="mt-4 bg-amber-50 text-amber-700 p-3 rounded text-sm">
                        Sertifikat sedang dalam proses verifikasi.
                    </div>
                    <div v-else-if="kmp.status_sertifikat === 'verified' && kmp.file_sertifikat" class="mt-4 bg-emerald-50 text-emerald-700 p-3 rounded text-sm">
                        Sertifikat telah <strong>disetujui</strong>.
                    </div>
                    <div v-else-if="kmp.status_sertifikat === 'rejected'" class="mt-4 bg-red-50 text-red-700 p-3 rounded text-sm">
                        Sertifikat sebelumnya ditolak. Silakan unggah dokumen yang benar.
                    </div>

                    <form @submit.prevent="submitCert" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="sertifikat" value="File Sertifikat" />
                            <input id="sertifikat" type="file" @input="certForm.sertifikat = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept=".pdf,.jpg,.jpeg,.png">
                            <InputError :message="certForm.errors.sertifikat" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="certForm.processing || !certForm.sertifikat">Unggah Sertifikat</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="certForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Berhasil diunggah.</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <!-- Pemberkasan Bukti Pelatihan -->
                <div v-if="kmp" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pemberkasan Bukti Pelaksanaan Pelatihan</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Unggah bukti pelaksanaan pelatihan KMP (PDF/Gambar) untuk diverifikasi oleh Admin Kecamatan.
                        </p>
                    </header>

                    <div v-if="kmp.status_pelatihan === 'pending' && kmp.file_pelatihan" class="mt-4 bg-amber-50 text-amber-700 p-3 rounded text-sm">
                        Bukti pelatihan sedang dalam proses verifikasi.
                    </div>
                    <div v-else-if="kmp.status_pelatihan === 'verified' && kmp.file_pelatihan" class="mt-4 bg-emerald-50 text-emerald-700 p-3 rounded text-sm">
                        Bukti pelatihan telah <strong>disetujui</strong>.
                    </div>
                    <div v-else-if="kmp.status_pelatihan === 'rejected'" class="mt-4 bg-red-50 text-red-700 p-3 rounded text-sm">
                        Bukti pelatihan sebelumnya ditolak. Silakan unggah dokumen yang benar.
                    </div>

                    <form @submit.prevent="submitPelatihan" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="pelatihan" value="File Bukti Pelatihan" />
                            <input id="pelatihan" type="file" @input="pelatihanForm.pelatihan = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept=".pdf,.jpg,.jpeg,.png">
                            <InputError :message="pelatihanForm.errors.pelatihan" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="pelatihanForm.processing || !pelatihanForm.pelatihan">Unggah Bukti Pelatihan</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="pelatihanForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Berhasil diunggah.</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <!-- Manajemen Struktur Koperasi -->
                <div v-if="kmp" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Manajemen Struktur & Keuangan Koperasi</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Kelola jumlah keanggotaan, struktur kepengurusan, serta data simpanan KMP Anda.
                        </p>
                    </header>

                    <form @submit.prevent="submitManagement" class="mt-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="jumlah_anggota" value="Jumlah Anggota Koperasi" />
                                <TextInput id="jumlah_anggota" v-model="managementForm.jumlah_anggota" type="number" min="0" class="mt-1 block w-full" />
                                <InputError :message="managementForm.errors.jumlah_anggota" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="jumlah_pengawas" value="Jumlah Pengawas" />
                                <TextInput id="jumlah_pengawas" v-model="managementForm.jumlah_pengawas" type="number" min="0" class="mt-1 block w-full" />
                                <InputError :message="managementForm.errors.jumlah_pengawas" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="jumlah_pengurus" value="Jumlah Pengurus" />
                                <TextInput id="jumlah_pengurus" v-model="managementForm.jumlah_pengurus" type="number" min="0" class="mt-1 block w-full" />
                                <InputError :message="managementForm.errors.jumlah_pengurus" class="mt-2" />
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700" />

                        <div class="space-y-6">
                            <!-- Ketua -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <div>
                                    <InputLabel for="ketua_nama" value="Nama Ketua Koperasi" />
                                    <TextInput id="ketua_nama" v-model="managementForm.ketua_nama" type="text" class="mt-1 block w-full" />
                                    <InputError :message="managementForm.errors.ketua_nama" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="ketua_foto" value="Foto Ketua (Opsional)" />
                                    <input id="ketua_foto" type="file" @input="managementForm.ketua_foto = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
                                    <InputError :message="managementForm.errors.ketua_foto" class="mt-2" />
                                    <div v-if="kmp.ketua_foto" class="mt-2">
                                        <img :src="`/storage/${kmp.ketua_foto}`" alt="Foto Ketua" class="h-16 w-16 object-cover rounded shadow">
                                    </div>
                                </div>
                            </div>

                            <!-- Sekretaris -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <div>
                                    <InputLabel for="sekretaris_nama" value="Nama Sekretaris" />
                                    <TextInput id="sekretaris_nama" v-model="managementForm.sekretaris_nama" type="text" class="mt-1 block w-full" />
                                    <InputError :message="managementForm.errors.sekretaris_nama" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="sekretaris_foto" value="Foto Sekretaris (Opsional)" />
                                    <input id="sekretaris_foto" type="file" @input="managementForm.sekretaris_foto = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
                                    <InputError :message="managementForm.errors.sekretaris_foto" class="mt-2" />
                                    <div v-if="kmp.sekretaris_foto" class="mt-2">
                                        <img :src="`/storage/${kmp.sekretaris_foto}`" alt="Foto Sekretaris" class="h-16 w-16 object-cover rounded shadow">
                                    </div>
                                </div>
                            </div>

                            <!-- Bendahara -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <div>
                                    <InputLabel for="bendahara_nama" value="Nama Bendahara" />
                                    <TextInput id="bendahara_nama" v-model="managementForm.bendahara_nama" type="text" class="mt-1 block w-full" />
                                    <InputError :message="managementForm.errors.bendahara_nama" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="bendahara_foto" value="Foto Bendahara (Opsional)" />
                                    <input id="bendahara_foto" type="file" @input="managementForm.bendahara_foto = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/*">
                                    <InputError :message="managementForm.errors.bendahara_foto" class="mt-2" />
                                    <div v-if="kmp.bendahara_foto" class="mt-2">
                                        <img :src="`/storage/${kmp.bendahara_foto}`" alt="Foto Bendahara" class="h-16 w-16 object-cover rounded shadow">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700" />

                        <!-- Data Keuangan -->
                        <div class="space-y-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100">Data Keuangan</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <div>
                                    <InputLabel for="simpanan_pokok" value="Total Simpanan Pokok (Rp)" />
                                    <TextInput id="simpanan_pokok" v-model="managementForm.simpanan_pokok" type="number" min="0" class="mt-1 block w-full" />
                                    <p class="mt-1 text-sm text-gray-500">{{ formatRupiah(managementForm.simpanan_pokok) }}</p>
                                    <InputError :message="managementForm.errors.simpanan_pokok" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="simpanan_wajib" value="Total Simpanan Wajib (Rp)" />
                                    <TextInput id="simpanan_wajib" v-model="managementForm.simpanan_wajib" type="number" min="0" class="mt-1 block w-full" />
                                    <p class="mt-1 text-sm text-gray-500">{{ formatRupiah(managementForm.simpanan_wajib) }}</p>
                                    <InputError :message="managementForm.errors.simpanan_wajib" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="managementForm.processing">Simpan Struktur</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="managementForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Tersimpan.</p>
                            </Transition>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
