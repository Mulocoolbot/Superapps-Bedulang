<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    kmpAdmins: Object,
    kecamatan: String,
})

const form = useForm({
    name: '',
    email: '',
    password: '',
})

const submitAdmin = () => {
    form.post(route('admin.kdmp.kecamatan.store_admin'), {
        onSuccess: () => form.reset(),
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
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Buat akun untuk pengurus KMP di kecamatan Anda.</p>
                    </header>

                    <form @submit.prevent="submitAdmin" class="mt-6 space-y-6 max-w-xl">
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
                        
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Simpan Akun</PrimaryButton>
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
                                    <th class="px-6 py-3">Nama</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Terdaftar Pada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="admin in kmpAdmins.data" :key="admin.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ admin.name }}</td>
                                    <td class="px-6 py-4">{{ admin.email }}</td>
                                    <td class="px-6 py-4">{{ new Date(admin.created_at).toLocaleDateString('id-ID') }}</td>
                                </tr>
                                <tr v-if="kmpAdmins.data.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center">Belum ada Admin KMP.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div class="mt-4 flex justify-end">
                        <Pagination :pagination="kmpAdmins" />
                    </div>
                </div>



            </div>
        </div>
    </AdminLayout>
</template>
