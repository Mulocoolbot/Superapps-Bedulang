<script setup>
const apps = [
    {
        name: 'PEMERINTAHAN',
        desc: 'Manajemen surat masuk & keluar',
        icon: '📩'
    },
    {
        name: 'SOSIAL ',
        desc: 'Peta & data spasial',
        icon: '🗺️'
    },
    {
        name: 'KEPENDUDUKAN',
        desc: 'Informasi & publikasi',
        icon: '📰'
    },
    {
        name: 'KEPEGAWAIAN',
        desc: 'Data & dokumen digital',
        icon: '📁'
    },
    {
        name: 'PARIWISATA',
        desc: 'Layanan masyarakat',
        icon: '📢'
    },
    {
        name: 'DATA SEKTORAL',
        desc: 'Data & analitik',
        icon: '📊'
    },
]
</script>

<template>
    <section class="py-16 bg-gray-50 dark:bg-gray-900">

        <div class="container mx-auto px-6">

            <!-- TITLE -->
            <h2 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-10">
                Aplikasi Layanan
            </h2>

            <!-- GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                <div v-for="app in apps" :key="app.name"
                     class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition">

                    <div class="text-4xl mb-3">
                        {{ app.icon }}
                    </div>

                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
                        {{ app.name }}
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-300 mt-2">
                        {{ app.desc }}
                    </p>

                </div>

            </div>

        </div>

    </section>
</template>