<script setup>
import { Head } from '@inertiajs/vue3'

import FrontLayout from '@/Layouts/FrontLayout.vue'
import Hero from '@/Components/Front/Hero.vue'
import InformationServices from '@/Components/Front/InformationServices.vue'
import AppLauncher from '@/Components/Front/AppLauncher.vue'
</script>

<template>

    <Head title="Beranda" />

    <FrontLayout>

        <!-- Hero Section -->
        <Hero />

        <!-- Layanan Informasi & Data -->
        <InformationServices />

        <!-- Daftar Aplikasi -->
        <AppLauncher />

    </FrontLayout>

</template>