<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'

const props = defineProps({
    kmpAdmins: Object,
    kecamatan: String,
    kmpsInKecamatan: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    kmp_id: '',
})

const submitAdmin = () => {
    form.post(route('admin.kdmp.kecamatan.store_admin'), {
        onSuccess: () => form.reset(),
    })
}

// Edit Logic
const editingAdmin = ref(null)
const editForm = useForm({
    name: '',
    email: '',
    password: '',
    kmp_id: '',
})

const openEditModal = (admin) => {
    editingAdmin.value = admin
    editForm.name = admin.name
    editForm.email = admin.email
    editForm.password = ''
    editForm.kmp_id = admin.kmp?.id || ''
}

const closeEditModal = () => {
    editingAdmin.value = null
    editForm.reset()
}

const submitEdit = () => {
    editForm.put(route('admin.kdmp.kecamatan.update_admin', editingAdmin.value.id), {
        onSuccess: () => closeEditModal(),
    })
}

// Delete Logic
const deletingAdmin = ref(null)

const confirmDelete = (admin) => {
    deletingAdmin.value = admin
}

const closeDeleteModal = () => {
    deletingAdmin.value = null
}

const submitDelete = () => {
    router.delete(route('admin.kdmp.kecamatan.destroy_admin', deletingAdmin.value.id), {
        onSuccess: () => closeDeleteModal(),
    })
}
</script>

<template>
    <Head title="Admin Kecamatan KDMP" />

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
                    <Link :href="route('admin.kdmp.kecamatan.dashboard')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Kelola Akun KMP
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.verifikasi_kmp_page')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Verifikasi KMP
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.verifikasi_perubahan_page')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengajuan Perubahan
                    </Link>
                    <Link :href="route('admin.kdmp.kecamatan.history')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>
                
                <!-- Tab: Kelola Akun -->
                <!-- Buat Akun Admin KMP -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tambah Akun Admin KMP</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pilih KMP yang ada di kecamatan {{ kecamatan }} dan buatkan akun adminnya.</p>
                    </header>

                    <form @submit.prevent="submitAdmin" class="mt-6 space-y-6 max-w-xl">
                        <div>
                            <InputLabel for="kmp_id" value="Pilih KMP" />
                            <select id="kmp_id" v-model="form.kmp_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled>-- Pilih Koperasi Merah Putih --</option>
                                <option v-for="kmpItem in kmpsInKecamatan" :key="kmpItem.id" :value="kmpItem.id">
                                    {{ kmpItem.nama_koperasi }} ({{ kmpItem.desa_kelurahan }}) {{ kmpItem.user_id ? '• [Sudah ada akun]' : '' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.kmp_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Nama Admin KMP" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Simpan Akun Admin KMP</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Berhasil ditambahkan.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
                
                <!-- Daftar Admin KMP -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header class="mb-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Akun Admin KMP Terdaftar</h2>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">KMP / Koperasi</th>
                                    <th class="px-6 py-3">Nama Admin</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Terdaftar Pada</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="admin in kmpAdmins.data" :key="admin.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        {{ admin.kmp?.nama_koperasi || '-' }}
                                        <span v-if="admin.kmp?.desa_kelurahan" class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                                            Desa/Kel: {{ admin.kmp.desa_kelurahan }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ admin.name }}</td>
                                    <td class="px-6 py-4">{{ admin.email }}</td>
                                    <td class="px-6 py-4">
                                        <span v-if="admin.kdmp_admin_status === 'pending'" class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-md text-xs font-semibold">Pending</span>
                                        <span v-else-if="admin.kdmp_admin_status === 'approved'" class="px-2 py-1 bg-green-100 text-green-800 rounded-md text-xs font-semibold">Disetujui</span>
                                        <span v-else class="px-2 py-1 bg-red-100 text-red-800 rounded-md text-xs font-semibold">Ditolak</span>
                                    </td>
                                    <td class="px-6 py-4">{{ new Date(admin.created_at).toLocaleDateString('id-ID') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditModal(admin)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                                            <button @click="confirmDelete(admin)" class="font-medium text-red-600 dark:text-red-500 hover:underline ml-2">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="kmpAdmins.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center">Belum ada Admin KMP.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="kmpAdmins" />
                    </div>
                </div>

                <!-- Edit Modal -->
                <Modal :show="editingAdmin !== null" @close="closeEditModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Edit Akun Admin KMP
                        </h2>
                        
                        <form @submit.prevent="submitEdit" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="edit_kmp_id" value="KMP Terhubung" />
                                <select id="edit_kmp_id" v-model="editForm.kmp_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Tanpa KMP / Pilih Nanti --</option>
                                    <option v-for="kmpItem in kmpsInKecamatan" :key="kmpItem.id" :value="kmpItem.id">
                                        {{ kmpItem.nama_koperasi }} ({{ kmpItem.desa_kelurahan }})
                                    </option>
                                </select>
                                <InputError :message="editForm.errors.kmp_id" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="edit_name" value="Nama" />
                                <TextInput id="edit_name" v-model="editForm.name" type="text" class="mt-1 block w-full" required />
                                <InputError :message="editForm.errors.name" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="edit_email" value="Email" />
                                <TextInput id="edit_email" v-model="editForm.email" type="email" class="mt-1 block w-full" required />
                                <InputError :message="editForm.errors.email" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="edit_password" value="Password (Kosongkan jika tidak ingin mengubah)" />
                                <TextInput id="edit_password" v-model="editForm.password" type="password" class="mt-1 block w-full" />
                                <InputError :message="editForm.errors.password" class="mt-2" />
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <SecondaryButton @click="closeEditModal"> Batal </SecondaryButton>
                                <PrimaryButton class="ml-3" :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">
                                    Simpan
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

                <!-- Delete Confirmation Modal -->
                <Modal :show="deletingAdmin !== null" @close="closeDeleteModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Konfirmasi Hapus Akun
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Apakah Anda yakin ingin menghapus akun {{ deletingAdmin?.name }}? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeDeleteModal"> Batal </SecondaryButton>
                            <DangerButton class="ml-3" @click="submitDelete">
                                Hapus
                            </DangerButton>
                        </div>
                    </div>
                </Modal>

            </div>
        </div>
    </AdminLayout>
</template>
