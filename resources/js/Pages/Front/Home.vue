<script setup>
import { Head } from '@inertiajs/vue3'

import FrontLayout from '@/Layouts/FrontLayout.vue'
import Hero from '@/Components/Front/Hero.vue'
import InformationServices from '@/Components/Front/InformationServices.vue'
import AppLauncher from '@/Components/Front/AppLauncher.vue'
import Footer from '@/Components/Front/Footer.vue'
import NewsSection from '@/Components/Front/NewsSection.vue'
</script>

<template>

    <Head title="Beranda" />

    <FrontLayout>

        <Hero />

        <InformationServices />

        <NewsSection />
        
        <AppLauncher />

        <Footer />

    </FrontLayout>

</template>