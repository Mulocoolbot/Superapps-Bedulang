<script setup>
import { Link } from '@inertiajs/vue3'

const apps = [
    {
        name: 'PEMERINTAHAN',
        slug: 'pemerintahan',
        logo: '/services/govern.svg',
    },
    {
        name: 'PENDIDIKAN',
        slug: 'pendidikan',
        logo: '/services/education.svg',
    },
    {
        name:'KESEHATAN',
        slug: 'kesehatan',
        logo: '/services/health.svg',
    },
    {
        name:'PERINDAGKOP-UKM',
        slug: 'perindagkop-ukm',
        logo: '/services/culture.svg',
    },
    {
        name:'HUKUM',
        slug: 'hukum',
        logo: '/services/law.svg',
    },
    {
        name: 'SOSIAL',
        slug: 'sosial',
        logo: '/services/social.svg',
    },
    {
        name: 'KEPENDUDUKAN',
        slug: 'kependudukan',
        logo: '/services/society.svg',
    },
    {
        name: 'KEPEGAWAIAN',
        slug: 'kepegawaian',
        logo: '/services/work.svg',
    },
    {
        name:'LINGKUNGAN',
        slug: 'lingkungan',
        logo: '/services/environment.svg',
    },
    {
        name:'GEOGRAFIS',
        slug: 'geografis',
        logo: '/services/geo.svg',
    },
    {
        name: 'PARIWISATA',
        slug: 'pariwisata',
        logo: '/services/vacation.svg',
    },
    {
        name: 'DATA SEKTORAL',
        slug: 'data-sektoral',
        logo: '/services/voting.svg',
    },
]
</script>

<template>
<section
    id="layanan"
    data-aos="fade-up"
    class="py-14 bg-gradient-to-b from-white via-blue-50/40 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">

    <div class="w-full max-w-[1600px] mx-auto px-6 lg:px-8">

        <!-- Header -->
        <div
            data-aos="fade-up"
            class="max-w-2xl mx-auto text-center mb-12">

            <h2
                class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">

                Layanan
                <span class="text-blue-600 dark:text-blue-400">
                    Informasi & Data
                </span>

            </h2>

            <p
                class="mt-3 text-gray-600 dark:text-gray-400">

                Pilih kategori untuk melihat informasi dan data Kabupaten Bangka Tengah.

            </p>

        </div>

        <!-- Grid -->
        <div
            class="flex flex-wrap justify-center gap-5">

            <Link
                v-for="(app, index) in apps"
                :key="app.slug"
                :href="route('services.show', { slug: app.slug })"
                data-aos="fade-up"
                class="group w-[180px] flex-shrink-0"
                preserve-scroll
            >

                <div
                    :id="'layanan-' + app.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                    class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-500">

                    <!-- Thumbnail -->
                    <div
                        class="h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-900/70">

                        <img
                            :src="app.logo"
                            :alt="app.name"
                            class="w-[70%] h-[70%] object-contain transition-transform duration-500 ease-out group-hover:scale-105"
                        >

                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-between px-3.5 py-3">

                        <h3
                            class="text-[13px] font-semibold uppercase tracking-wide text-gray-900 dark:text-white transition-colors duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">

                            {{ app.name }}

                        </h3>

                        <svg
                            class="w-4 h-4 text-gray-400 transition-all duration-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5l7 7-7 7"/>

                        </svg>

                    </div>

                </div>

            </Link>

        </div>

    </div>

</section>
</template>