<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>

<template>

<Head title="Dashboard Admin" />

<AdminLayout>

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard Admin
        </h1>

        <p class="text-gray-500 mt-2">
            Selamat datang di Panel Administrator BEDULANG.
        </p>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <div class="bg-white rounded-2xl shadow p-6">

            <div class="text-4xl">
                📹
            </div>

            <h3 class="mt-4 font-semibold text-lg">
                CCTV
            </h3>

            <p class="text-gray-500 mt-2">
                Monitoring CCTV Kabupaten Bangka Tengah.
            </p>

        </div>

        <div class="bg-white rounded-2xl shadow p-6">

            <div class="text-4xl">
                👤
            </div>

            <h3 class="mt-4 font-semibold text-lg">
                Profil
            </h3>

            <p class="text-gray-500 mt-2">
                Kelola akun administrator.
            </p>

        </div>

        <div class="bg-white rounded-2xl shadow p-6">

            <div class="text-4xl">
                ⚙️
            </div>

            <h3 class="mt-4 font-semibold text-lg">
                Pengaturan
            </h3>

            <p class="text-gray-500 mt-2">
                Pengaturan sistem aplikasi.
            </p>

        </div>

    </div>

</AdminLayout>

</template>