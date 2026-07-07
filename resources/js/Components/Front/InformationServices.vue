<script setup>
const apps = [
    {
        name: 'PEMERINTAHAN',
        logo: '/services/govern.svg',
        url: '/login'
    },
    {
        name: 'PENDIDIKAN',
        logo: '/services/education.svg',
        url: '/login'
    },
    {
        name:'KESEHATAN',
        logo: '/services/health.svg',
        url: '/login'
    },
    {
        name:'BUDAYA',
        logo: '/services/culture.svg',
        url: '/login'
    },
    {
        name:'HUKUM',
        logo: '/services/law.svg',
        url: '/login'
    },
    {
        name: 'SOSIAL',
        logo: '/services/social.svg',
        url: '/login'
    },
    {
        name: 'KEPENDUDUKAN',
        logo: '/services/society.svg',
        url: '/login'
    },
    {
        name: 'KEPEGAWAIAN',
        logo: '/services/work.svg',
        url: '/login'
    },
    {
        name:'LINGKUNGAN',
        logo: '/services/environment.svg',
        url: '/login'
    },
    {
        name:'GEOGRAFIS',
        logo: '/services/geo.svg',
        url: '/login'
    },
    {
        name: 'PARIWISATA',
        logo: '/services/vacation.svg',
        url: '/login'
    },
    {
        name: 'DATA SEKTORAL',
        logo: '/services/voting.svg',
        url: '/login'
    },
]
</script>

<template>
<section
    data-aos="fade-up"
    class="py-14 bg-gradient-to-b from-white via-blue-50/40 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">

    <!-- Container lebih lebar -->
    <div class="w-full max-w-[1600px] mx-auto px-6 lg:px-8">

        <!-- Header -->
        <div
            data-aos="fade-up"
            class="max-w-2xl mx-auto text-center mb-12">

            <h2
                class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">

                Dashboard
                <span class="text-blue-600 dark:text-blue-400">
                    Informasi & Data
                </span>

            </h2>

            <p
                class="mt-3 text-gray-600 dark:text-gray-400">

                Pilih kategori untuk melihat dashboard informasi dan data Kabupaten Bangka Tengah.

            </p>

        </div>

        <!-- Grid -->
        <div
            class="flex flex-wrap justify-center gap-5">

            <a
                v-for="(app, index) in apps"
                :key="app.name"
                :href="app.url"
                data-aos="fade-up"
                :data-aos-delay="index * 40"
                class="group w-[180px] flex-shrink-0">

                <div
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

            </a>

        </div>

    </div>

</section>
</template>