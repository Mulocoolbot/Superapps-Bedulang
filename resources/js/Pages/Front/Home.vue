<script setup>
import { Head } from '@inertiajs/vue3'

import FrontLayout from '@/Layouts/FrontLayout.vue'
import Hero from '@/Components/Front/Hero.vue'
import InformationServices from '@/Components/Front/InformationServices.vue'
import AppLauncher from '@/Components/Front/AppLauncher.vue'
import Footer from '@/Components/Front/Footer.vue'
</script>

<template>

    <Head title="Beranda" />

    <FrontLayout>

        <Hero />

        <InformationServices />
        
        <AppLauncher />

        <Footer />

    </FrontLayout>

</template>