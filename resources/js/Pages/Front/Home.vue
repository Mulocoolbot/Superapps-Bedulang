<script setup>
import { Head } from '@inertiajs/vue3'

import FrontLayout from '@/Layouts/FrontLayout.vue'
import VideoHero from '@/Components/Front/VideoHero.vue'
import Hero from '@/Components/Front/Hero.vue'
import InformationServices from '@/Components/Front/InformationServices.vue'
import AppLauncher from '@/Components/Front/AppLauncher.vue'
import Footer from '@/Components/Front/Footer.vue'
</script>

<template>

    <Head title="Beranda"></Head>

    <FrontLayout>

        <VideoHero></VideoHero>

        <Hero></Hero>

        <InformationServices></InformationServices>
        
        <AppLauncher></AppLauncher>

        <Footer></Footer>

    </FrontLayout>

</template>