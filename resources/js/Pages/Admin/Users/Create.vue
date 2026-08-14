<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'admin',
    service_slug: '',
})

const submit = () => {
    form.post('/admin/users', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Tambah Admin Sistem" />

    <AdminLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="rounded-2xl bg-white p-8 shadow-sm border border-gray-100">
                <div class="mb-6 pb-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Tambah Admin Sistem Baru</h1>
                        <p class="text-sm text-gray-500 mt-1">Isi informasi akun administrator baru untuk sistem BEDULANG.</p>
                    </div>
                    <Link
                        href="/admin/users"
                        class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition"
                    >
                        &larr; Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Admin Kesehatan"
                            class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Alamat Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="admin@example.com"
                            class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                            <p v-if="form.errors.password" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Ketik ulang password"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Role Admin</label>
                            <select
                                v-model="form.role"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            >
                                <option value="admin">Admin Layanan / OPD</option>
                                <option value="admin_koperasi">Admin Koperasi</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                            <p v-if="form.errors.role" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors.role }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">
                                Layanan / Domain (Slug)
                                <span class="text-xs font-normal text-gray-400 ml-1">(Opsional)</span>
                            </label>
                            <input
                                v-model="form.service_slug"
                                type="text"
                                placeholder="Contoh: koperasi, kesehatan, perindagkop-ukm"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                            />
                            <p class="mt-1 text-[11px] text-gray-400">Kosongkan jika Super Admin atau berlaku untuk semua layanan.</p>
                            <p v-if="form.errors.service_slug" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors.service_slug }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <Link
                            href="/admin/users"
                            class="px-5 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-sm font-bold text-white shadow-sm transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Admin' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
