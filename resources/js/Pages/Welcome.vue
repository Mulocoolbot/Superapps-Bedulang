<script setup>
import { Head } from '@inertiajs/vue3'

import Navbar from '@/Components/Front/Navbar.vue'
import Hero from '@/Components/Front/Hero.vue'
import InformationServices from '@/Components/Front/InformationServices.vue'
import NewsSection from '@/Components/Front/NewsSection.vue'
import AppLaunch from '@/Components/Front/AppLaunch.vue'
import Footer from '@/Components/Front/Footer.vue'
</script>

<template>
    <Head title="BEDULANG - Bangka Tengah" />

    <div class="min-h-screen bg-white dark:bg-gray-900">

        <Navbar />

        <Hero />

        <InformationServices />

        <NewsSection />

        <AppLaunch />

        <Footer />
        
    </div>
</template>
