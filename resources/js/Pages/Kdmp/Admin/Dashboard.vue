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
    admins: Object,
    filters: Object,
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    kdmp_role: 'admin_kecamatan',
    kdmp_kecamatan: '',
})

const submit = () => {
    form.post(route('admin.kdmp.store_admin'), {
        onSuccess: () => form.reset(),
    })
}

const kecamatans = ['Koba', 'Pangkalan Baru', 'Namang', 'Lubuk Besar', 'Simpang Katis', 'Sungai Selan']

// Filtering
const filterRole = ref(props.filters?.role || '')
const filterKecamatan = ref(props.filters?.kecamatan || '')
const filterStatus = ref(props.filters?.status || '')

const filterData = () => {
    const params = {}
    if (filterRole.value) params.role = filterRole.value
    if (filterKecamatan.value) params.kecamatan = filterKecamatan.value
    if (filterStatus.value) params.status = filterStatus.value

    router.get(
        route('admin.kdmp.dashboard'),
        params,
        { preserveState: true, preserveScroll: true, replace: true }
    )
}

// Edit Logic
const editingAdmin = ref(null)
const editForm = useForm({
    name: '',
    email: '',
    password: '',
    kdmp_role: '',
    kdmp_kecamatan: '',
})

const openEditModal = (admin) => {
    editingAdmin.value = admin
    editForm.name = admin.name
    editForm.email = admin.email
    editForm.kdmp_role = admin.kdmp_role
    editForm.kdmp_kecamatan = admin.kdmp_kecamatan || ''
    editForm.password = ''
}

const closeEditModal = () => {
    editingAdmin.value = null
    editForm.reset()
}

const submitEdit = () => {
    editForm.put(route('admin.kdmp.update_admin', editingAdmin.value.id), {
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
    router.delete(route('admin.kdmp.destroy_admin', deletingAdmin.value.id), {
        onSuccess: () => closeDeleteModal(),
    })
}

// Verify Logic
const verifyAdmin = (adminId, action) => {
    router.post(route('admin.kdmp.verify_admin', adminId), { action: action }, { preserveScroll: true })
}
</script>

<template>
    <Head title="Admin Keseluruhan KDMP" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin Keseluruhan KDMP</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <Link :href="route('admin.kdmp.dashboard')" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pengelolaan Data
                    </Link>
                    <Link :href="route('admin.kdmp.history')" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        History Log
                    </Link>
                </div>
                
                <!-- Tab: Pengelolaan Data -->
                <!-- Create Admin -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tambah Admin KDMP Baru</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Buat akun untuk Admin Kecamatan atau Admin KMP secara manual.</p>
                    </header>

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" value="Nama" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus />
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
                        
                        <div>
                            <InputLabel for="kdmp_role" value="Role KDMP" />
                            <select id="kdmp_role" v-model="form.kdmp_role" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="admin_kecamatan">Admin Kecamatan</option>
                                <option value="admin_kmp">Admin KMP</option>
                            </select>
                            <InputError :message="form.errors.kdmp_role" class="mt-2" />
                        </div>

                        <div v-if="form.kdmp_role === 'admin_kecamatan'">
                            <InputLabel for="kdmp_kecamatan" value="Pilih Kecamatan" />
                            <select id="kdmp_kecamatan" v-model="form.kdmp_kecamatan" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option v-for="kec in kecamatans" :key="kec" :value="kec">{{ kec }}</option>
                            </select>
                            <InputError :message="form.errors.kdmp_kecamatan" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Simpan Admin</PrimaryButton>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Tersimpan.</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <!-- Admin List -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <header class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Admin KDMP</h2>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <select v-model="filterRole" @change="filterData" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Role</option>
                                <option value="admin_kecamatan">Admin Kecamatan</option>
                                <option value="admin_kmp">Admin KMP</option>
                            </select>
                            <select v-model="filterKecamatan" @change="filterData" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Kecamatan</option>
                                <option v-for="kec in kecamatans" :key="kec" :value="kec">{{ kec }}</option>
                            </select>
                            <select v-model="filterStatus" @change="filterData" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="approved">Disetujui</option>
                                <option value="pending">Menunggu</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Nama</th>
                                    <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Kecamatan</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="admin in admins.data" :key="admin.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ admin.name }}</td>
                                    <td class="px-6 py-4">{{ admin.email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-md text-xs font-semibold">
                                            {{ admin.kdmp_role === 'admin_kecamatan' ? 'Kecamatan' : 'KMP' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ admin.kdmp_kecamatan || '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-start gap-2">
                                            <span v-if="admin.kdmp_admin_status === 'pending'" class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-md text-xs font-semibold">Pending</span>
                                            <span v-else-if="admin.kdmp_admin_status === 'approved'" class="px-2 py-1 bg-green-100 text-green-800 rounded-md text-xs font-semibold">Disetujui</span>
                                            <span v-else class="px-2 py-1 bg-red-100 text-red-800 rounded-md text-xs font-semibold">Ditolak</span>
                                            
                                            <div v-if="admin.kdmp_admin_status === 'pending'" class="flex gap-2">
                                                <button @click="verifyAdmin(admin.id, 'verify')" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded hover:bg-emerald-200 font-semibold">Setujui</button>
                                                <button @click="verifyAdmin(admin.id, 'reject')" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200 font-semibold">Tolak</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditModal(admin)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                                            <button @click="confirmDelete(admin)" class="font-medium text-red-600 dark:text-red-500 hover:underline ml-2">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="admins.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center">Tidak ada data admin.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="admins" />
                    </div>
                </div>

                <!-- Edit Modal -->
                <Modal :show="editingAdmin !== null" @close="closeEditModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Edit Admin KDMP
                        </h2>
                        
                        <form @submit.prevent="submitEdit" class="mt-6 space-y-6">
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
                            
                            <div>
                                <InputLabel for="edit_kdmp_role" value="Role KDMP" />
                                <select id="edit_kdmp_role" v-model="editForm.kdmp_role" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="admin_kecamatan">Admin Kecamatan</option>
                                    <option value="admin_kmp">Admin KMP</option>
                                </select>
                                <InputError :message="editForm.errors.kdmp_role" class="mt-2" />
                            </div>

                            <div v-if="editForm.kdmp_role === 'admin_kecamatan'">
                                <InputLabel for="edit_kdmp_kecamatan" value="Pilih Kecamatan" />
                                <select id="edit_kdmp_kecamatan" v-model="editForm.kdmp_kecamatan" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option v-for="kec in kecamatans" :key="kec" :value="kec">{{ kec }}</option>
                                </select>
                                <InputError :message="editForm.errors.kdmp_kecamatan" class="mt-2" />
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
