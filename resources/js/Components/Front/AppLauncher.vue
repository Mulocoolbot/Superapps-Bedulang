<script setup>
const services = [
    {
        name: 'SIPELAWAN',
        logo: '/services/sipelawan.png',
        url: '/login'
    },
    {
        name: 'SIDOLPIN',
        logo: '/services/sidolpin.png',
        url: '/login'
    },
    {
        name: 'SILAKSO',
        logo: '/services/silakso.png',
        url: '/login'
    },
    {
        name: 'SIKOK DATA',
        logo: '/services/sikok-data.png',
        url: '/login'
    },
    {
        name: 'LAPOR PPID',
        logo: '/services/lapor.png',
        url: '/login'
    },
    {
        name: 'SIMENTARI',
        logo: '/services/seperadik.png',
        url: '/login'
    },
]
</script>

<template>
<section
    id="aplikasi"
    data-aos="fade-up"
    class="py-16 bg-white dark:bg-gray-950">

    <div class="max-w-7xl mx-auto px-6">

        <!-- Header -->

        <div class="text-center mb-12">

            <h2
                class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">

                Aplikasi Daerah

            </h2>

            <p
                class="mt-3 text-gray-600 dark:text-gray-400">

                Akses berbagai aplikasi digital Pemerintah Kabupaten Bangka Tengah.

            </p>

        </div>

        <!-- Grid -->

        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <a
                v-for="(service,index) in services"
                :key="service.name"
                :href="service.url"
                target="_blank"
                data-aos="fade-up"
                :data-aos-delay="index*50"
                class="group">

                <div
                    :id="'aplikasi-' + service.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                    class="flex items-center gap-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 transition-all duration-300 hover:-translate-y-1 hover:border-blue-400 hover:shadow-lg">

                    <!-- Logo -->

                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-900">

                        <img
                            :src="service.logo"
                            :alt="service.name"
                            class="h-12 w-12 object-contain transition duration-300 group-hover:scale-105">

                    </div>

                    <!-- Text -->

                    <div class="flex-1">

                        <h3
                            class="font-semibold text-gray-900 dark:text-white">

                            {{ service.name }}

                        </h3>

                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400">

                            Buka aplikasi

                        </p>

                    </div>

                    <!-- Arrow -->

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 17L17 7M17 7H9M17 7V15"/>

                        </svg>

                    </div>

                </div>

            </a>

        </div>

    </div>

</section>
</template>