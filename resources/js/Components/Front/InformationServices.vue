<script setup>
const apps = [
    {
        name: 'PEMERINTAHAN',
        desc: 'Informasi pemerintahan daerah dan pelayanan publik',
        logo: '/services/thumbnail1.png',
        url: '/login'
    },
    {
        name: 'SOSIAL',
        desc: 'Data sosial, bantuan dan kesejahteraan masyarakat',
        logo: '/services/thumbnail2.png',
        url: '/login'
    },
    {
        name: 'KEPENDUDUKAN',
        desc: 'Data dan informasi kependudukan daerah',
        logo: '/services/thumbnail3.png',
        url: '/login'
    },
    {
        name: 'KEPEGAWAIAN',
        desc: 'Informasi ASN dan manajemen kepegawaian',
        logo: '/services/thumbnail4.png',
        url: '/login'
    },
    {
        name: 'PARIWISATA',
        desc: 'Destinasi wisata dan potensi daerah',
        logo: '/services/thumbnail5.png',
        url: '/login'
    },
    {
        name: 'DATA SEKTORAL',
        desc: 'Bank data sektoral Kabupaten Bangka Tengah',
        logo: '/services/thumbnail6.png',
        url: '/login'
    },
]
</script>

<template>
    <section
        class="py-14 bg-gradient-to-b from-white via-blue-50/40 to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">

        <div class="container mx-auto px-6">

            <!-- HEADER -->
            <div class="max-w-3xl mx-auto text-center mb-10">

                <h2
                    class="mt-4 text-3xl md:text-4xl font-bold tracking-tight text-gray-900 dark:text-white">

                    Jelajahi Layanan &
                    <span class="text-blue-600">
                        Informasi Bangka Tengah
                    </span>

                </h2>

                <p
                    class="mt-4 text-base text-gray-600 dark:text-gray-300 leading-7">

                    Temukan berbagai layanan publik, data sektoral, serta informasi
                    Kabupaten Bangka Tengah dalam satu portal digital yang terintegrasi.

                </p>

            </div>

            <!-- GRID -->

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                <a
    v-for="app in apps"
    :key="app.name"
    :href="app.url"
    class="group rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">

    <!-- Logo -->
    <img
        :src="app.logo"
        :alt="app.name"
        class="w-16 h-16 rounded-2xl object-cover shadow-sm"
    >

    <!-- Content -->
    <h3 class="mt-5 text-xl font-bold text-gray-900 dark:text-white">
        {{ app.name }}
    </h3>

    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300 line-clamp-2">
        {{ app.desc }}
    </p>

    <div class="mt-6 flex items-center justify-between">

        <span class="text-sm font-semibold text-blue-600">
            Buka Layanan
        </span>

        <div
            class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-600 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white">

            →

        </div>

    </div>

</a>

            </div>

        </div>

    </section>
</template>