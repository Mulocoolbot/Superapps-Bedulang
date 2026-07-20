<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    totalAdmin: Number,
    totalApps: Number,
    totalCCTV: Number,
    apiStatus: String,
})
</script>

<template>

<Head title="Dashboard Admin"/>

<AdminLayout>

<div class="mb-8">

    <h1 class="text-3xl font-bold text-gray-800">
        Dashboard Admin
    </h1>

    <p class="text-gray-500 mt-2">
        Selamat datang di Panel Administrator BEDULANG.
    </p>

</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <div class="bg-white rounded-2xl shadow p-6">

        <div class="text-4xl">👥</div>

        <h2 class="text-3xl font-bold mt-4">
            {{ totalAdmin }}
        </h2>

        <p class="text-gray-500">
            Total Admin
        </p>

    </div>

    <div class="bg-white rounded-2xl shadow p-6">

        <div class="text-4xl">📹</div>

        <h2 class="text-3xl font-bold mt-4">
            {{ totalCCTV }}
        </h2>

        <p class="text-gray-500">
            CCTV
        </p>

    </div>

    <div class="bg-white rounded-2xl shadow p-6">

        <div class="text-4xl">🌐</div>

        <h2 class="text-xl font-bold mt-4">
            {{ apiStatus }}
        </h2>

        <p class="text-gray-500">
            Status API
        </p>

    </div>

    <div class="bg-white rounded-2xl shadow p-6">

        <div class="text-4xl">📱</div>

        <h2 class="text-3xl font-bold mt-4">
            {{ totalApps }}
        </h2>

        <p class="text-gray-500">
            Aplikasi Terintegrasi
        </p>

    </div>

</div>

<div class="mt-10">

    <h2 class="text-xl font-bold mb-4">
        Akses Cepat
    </h2>

    <div class="flex flex-wrap gap-4">

        <Link
            href="/admin/users"
            class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700">

            Kelola Admin

        </Link>

        <Link
            href="admin/cctv"
            class="px-6 py-3 rounded-xl bg-gray-800 text-white hover:bg-black">

            CCTV

        </Link>

        <Link
            href="/"
            class="px-6 py-3 rounded-xl border">

            Landing Page

        </Link>

    </div>

</div>

</AdminLayout>

</template>