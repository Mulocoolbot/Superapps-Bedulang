<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    errors: Object,
})
</script>

<template>
<Head title="Tambah Admin" />

<AdminLayout>
    <div class="space-y-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h1 class="text-3xl font-bold text-gray-900">Tambah Admin</h1>
                <p class="text-gray-500 mt-2">Isi semua data admin baru dengan benar.</p>
            </div>

            <form method="post" action="/admin/users" class="space-y-6">
                <input type="hidden" name="_token" :value="props.csrf ?? ''" />

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama</label>
                    <input
                        type="text"
                        name="name"
                        class="mt-2 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    />
                    <p v-if="props.errors?.name" class="mt-2 text-sm text-red-600">{{ props.errors.name[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="mt-2 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    />
                    <p v-if="props.errors?.email" class="mt-2 text-sm text-red-600">{{ props.errors.email[0] }}</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="mt-2 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />
                        <p v-if="props.errors?.password" class="mt-2 text-sm text-red-600">{{ props.errors.password[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="mt-2 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Role</label>
                    <select
                        name="role"
                        class="mt-2 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="admin">admin</option>
                        <option value="super_admin">super_admin</option>
                    </select>
                    <p v-if="props.errors?.role" class="mt-2 text-sm text-red-600">{{ props.errors.role[0] }}</p>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </Link>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout>
</template>
