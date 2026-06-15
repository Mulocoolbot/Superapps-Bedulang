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
    <section class="py-16 bg-gray-50 dark:bg-gray-900">

        <div class="container mx-auto px-6">

            <!-- HEADER -->
            <div class="text-center mb-12">

                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white">
                    Daftar Aplikasi
                </h2>

                <p class="mt-3 text-gray-600 dark:text-gray-300">
                    Akses berbagai aplikasi layanan digital Pemerintah Kabupaten Bangka Tengah
                </p>

            </div>

            <!-- GRID -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">

                <a
                    v-for="service in services"
                    :key="service.name"
                    :href="service.url"
                    class="group bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-2 transition-all duration-300 p-5"
                >

                    <!-- BADGE -->
                    <div class="flex justify-center mb-3">
                        <span
                            class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                        >
                            Aplikasi
                        </span>
                    </div>

                    <!-- LOGO -->
                    <div class="flex justify-center">
                        <img
                            :src="service.logo"
                            :alt="service.name"
                            class="w-28 h-28 object-contain group-hover:scale-110 transition duration-300"
                        >
                    </div>

                    <!-- NAME -->
                    <div class="mt-4 text-center">

                        <h3 class="font-bold text-gray-800 dark:text-white">
                            {{ service.name }}
                        </h3>

                        <div
                            class="mt-2 text-blue-600 dark:text-blue-400 text-sm opacity-0 group-hover:opacity-100 transition"
                        >
                            Buka Aplikasi →
                        </div>

                    </div>

                </a>

            </div>

        </div>

    </section>
</template>