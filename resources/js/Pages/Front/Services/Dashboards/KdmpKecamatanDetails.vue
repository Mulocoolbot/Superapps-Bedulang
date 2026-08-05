<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import Footer from '@/Components/Front/Footer.vue'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    kecamatan: {
        type: String,
        required: true,
    },
    koperasiDetails: {
        type: Array,
        default: () => [],
    },
})

const title = computed(() => `Koperasi Merah Putih Kecamatan ${props.kecamatan}`)
const subtitle = computed(() => `Detail Data Koperasi untuk wilayah Kecamatan ${props.kecamatan}`)

// Search & Pagination for table
const searchQuery = ref('')

const filteredData = computed(() => {
    if (!searchQuery.value) return props.koperasiDetails
    const q = searchQuery.value.toLowerCase()
    return props.koperasiDetails.filter(item => 
        (item.koperasi && item.koperasi.toLowerCase().includes(q)) ||
        (item.desa_kelurahan && item.desa_kelurahan.toLowerCase().includes(q)) ||
        (item.kecamatan && item.kecamatan.toLowerCase().includes(q))
    )
})

const currentPage = ref(1)
const itemsPerPage = 10

const totalPages = computed(() => Math.ceil(filteredData.value.length / itemsPerPage))

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return filteredData.value.slice(start, end)
})

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++
}

const goToKoperasiDetail = (id) => {
    router.visit(`/layanan/kdmp/koperasi/${encodeURIComponent(id)}`)
}

// Modal for AHU Certificate
const isCertModalOpen = ref(false)
const selectedAhu = ref('')

const openCertModal = (ahu, event) => {
    event.stopPropagation() // Prevent row click
    selectedAhu.value = ahu
    isCertModalOpen.value = true
}

const closeCertModal = () => {
    isCertModalOpen.value = false
    setTimeout(() => {
        selectedAhu.value = ''
    }, 200)
}
</script>

<template>
    <FrontLayout>
        <Head :title="title"></Head>

        <div class="mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 lg:px-8 lg:pt-28 lg:pb-10">
            <!-- Back Button - subtle ghost style -->
            <Link
                :href="`/layanan/${service.slug}`"
                class="group mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Dashboard KDMP
            </Link>

            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-50/60 blur-2xl dark:bg-cyan-950/20" />
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400">
                            <span>{{ service.icon || '🤝' }}</span>
                            <span>Data & Informasi</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white font-sans">
                            {{ title }}
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Table Data Koperasi Detail -->
            <div class="mt-8 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up [animation-delay:200ms]">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Daftar Koperasi</h2>
                    
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari koperasi / desa..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 pl-9 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-400"
                        />
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                </div>
                
                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Koperasi</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Desa / Kelurahan</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Kecamatan</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">Operasional</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center">No AHU</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-center text-cyan-600 dark:text-cyan-400">Jumlah Anggota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="(item, index) in paginatedData" :key="index" @click="goToKoperasiDetail(item.id)" class="cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm group">
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        {{ item.koperasi }}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ item.desa_kelurahan }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ item.kecamatan }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="item.operasional === 'Sudah' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'" class="px-2 py-1 rounded-md text-xs font-semibold">
                                        {{ item.operasional }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <button v-if="item.no_ahu && item.no_ahu !== 'Belum ada' && item.no_ahu !== '-'" @click="(e) => openCertModal(item.no_ahu, e)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 font-medium text-xs transition border border-blue-200/50 dark:border-blue-700/50 shadow-sm hover:shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        {{ item.no_ahu }}
                                    </button>
                                    <span v-else class="text-xs text-slate-400 font-medium">Belum Ada</span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-cyan-600 dark:text-cyan-400 whitespace-nowrap">{{ item.anggota }}</td>
                            </tr>
                            <tr v-if="paginatedData.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Tidak ada data untuk ditampilkan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex items-center justify-between px-2" v-if="totalPages > 1">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan <span class="font-medium text-slate-900 dark:text-white">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> 
                        sampai <span class="font-medium text-slate-900 dark:text-white">{{ Math.min(currentPage * itemsPerPage, filteredData.length) }}</span> 
                        dari <span class="font-medium text-slate-900 dark:text-white">{{ filteredData.length }}</span> data
                    </div>
                    <div class="flex gap-2">
                        <button 
                            @click="prevPage" 
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                        >
                            Sebelumnya
                        </button>
                        <button 
                            @click="nextPage" 
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                        >
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal for Sertifikat -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isCertModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-800 border border-slate-200 dark:border-slate-700" @click.stop>
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-700">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Dokumen Sertifikat AHU</h3>
                            <button @click="closeCertModal" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Menampilkan sertifikat bernomor registrasi:</p>
                                <p class="text-base font-bold text-slate-800 dark:text-slate-200">{{ selectedAhu }}</p>
                            </div>
                            
                            <div class="flex h-64 w-full items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-700">
                                <div class="text-center text-slate-400 dark:text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-12 w-12 mb-2 opacity-50">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Gambar Sertifikat<br>(Mockup)</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 text-right dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700">
                            <button @click="closeCertModal" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">Tutup</button>
                        </div>
                    </div>
                </div>
            </Transition>

        </div>

        <Footer></Footer>
    </FrontLayout>
</template>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
