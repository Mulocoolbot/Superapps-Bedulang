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
    <section class="py-16 bg-gray-50 dark:bg-gray-900">

        <div class="container mx-auto px-6">

            <!-- HEADER -->
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white">
                    Layanan Informasi & Data
                </h2>

                <p class="mt-3 text-gray-600 dark:text-gray-300">
                    Akses informasi publik, data sektoral dan layanan informasi Kabupaten Bangka Tengah
                </p>
            </div>

            <!-- GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <a
                    v-for="app in apps"
                    :key="app.name"
                    :href="app.url"
                    class="group bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition duration-300"
                >

                    <!-- IMAGE -->
                    <div class="overflow-hidden">
                        <img
                            :src="app.logo"
                            :alt="app.name"
                            class="w-full h-56 object-cover group-hover:scale-105 transition duration-500"
                        />
                    </div>

                    <!-- CONTENT -->
                    <div class="p-5">

                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                            {{ app.name }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ app.desc }}
                        </p>

                        <div class="mt-4 flex items-center text-blue-600 dark:text-blue-400 font-medium">
                            Lihat Informasi
                            <span class="ml-2">→</span>
                        </div>

                    </div>

                </a>

            </div>

        </div>

    </section>
</template>