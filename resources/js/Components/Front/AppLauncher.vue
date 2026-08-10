<script setup>
const services = [
    {
        name: 'SIPELAWAN',
        logo: '/services/sipelawan.png',
        url: 'https://dashboard-sipelawan.bangkatengahkab.go.id/dashboard/'
    },
    {
        name: 'SEPERADIK',
        logo: '/services/seperadik.png',
        url: 'https://seperadik.bangkatengahkab.go.id/'
    },
    {
        name: 'SILAKSO',
        logo: '/services/silakso.svg',
        url: 'https://silakso.bangkatengahkab.go.id/'
    },
    {
        name: 'SIKOK DATA',
        logo: '/services/sikok-data.svg',
        url: 'https://sikokdata.bangkatengahkab.go.id/'
    },
    {
        name: 'LAPOR PPID',
        logo: '/services/lapor.png',
        url: 'https://www.lapor.go.id/instansi/pemerintah-kabupaten-bangka-tengah'
    },
    {
        name: 'SIMENTARI',
        logo: '',
        url: 'https://inspektorat.bangkatengahkab.go.id'
    },
]
</script>

<template>
<section
    id="aplikasi"
    data-aos="fade-up"
    class="py-16 bg-gradient-to-b from-blue-50/20 via-white to-white dark:from-slate-950 dark:via-slate-900/30 dark:to-slate-950 transition-colors duration-300">

    <div class="max-w-7xl mx-auto px-6">

        <!-- Header -->
        <div class="text-center mb-12">
            <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Aplikasi Daerah
            </h2>
            <p class="mt-4 text-base md:text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto font-medium">
                Akses berbagai aplikasi digital sektoral Pemerintah Kabupaten Bangka Tengah.
            </p>
        </div>

        <!-- Grid -->

        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <a
                v-for="(service,index) in services"
                :key="service.name"
                :href="'/app/launch?name=' + encodeURIComponent(service.name) + '&url=' + encodeURIComponent(service.url)"
                target="_blank"
                data-aos="fade-up"
                :data-aos-delay="index*50"
                class="group"
            >

                <div
                    :id="'aplikasi-' + service.name.toLowerCase().replace(/[^a-z0-9]/g, '-')"
                    class="flex items-center gap-4 rounded-2xl border border-blue-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg">

                    <!-- Logo -->

                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-xl transition-colors duration-300"
                        :class="service.name === 'SIKOK DATA' 
                            ? 'bg-slate-900 dark:bg-slate-800 p-2' 
                            : 'bg-blue-50/40 dark:bg-slate-800/60'"
                    >

                        <img
                            v-if="service.logo"
                            :src="service.logo"
                            :alt="service.name"
                            class="h-12 w-12 object-contain transition duration-300 group-hover:scale-105"
                        >
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-8 w-8 text-slate-400 dark:text-slate-500"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>

                    </div>

                    <!-- Text -->

                    <div class="flex-1">

                        <h3
                            class="font-bold text-slate-900 dark:text-white">

                            {{ service.name }}

                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">

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