<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>

<template>

<Head title="CCTV"/>

<AdminLayout>

    <h1 class="text-3xl font-bold">
        CCTV Kabupaten Bangka Tengah
    </h1>

    <p class="text-gray-500 mt-2">
        Halaman monitoring CCTV.
    </p>

    <div class="mt-8 bg-white rounded-2xl shadow p-8">

        <div class="aspect-video rounded-xl bg-gray-200 flex items-center justify-center">

            CCTV Akan Ditampilkan Di Sini

        </div>

    </div>

</AdminLayout>

</template>
