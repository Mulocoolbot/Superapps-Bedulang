<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { useSlidingPagination } from '@/Composables/useSlidingPagination'

const reviewRating = ref({})
const reviewText = ref({})


const submitReview = (id)=>{

    router.post(
        `/my-complaints/${id}/review`,
        {
            rating: reviewRating.value[id],
            review: reviewText.value[id]
        },
        {
            preserveScroll:true
        }
    )

}

const props = defineProps({
    complaints: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            type: 'all'
        })
    }
})

const page = usePage()
const confirmingCancelId = ref(null)
const cancelling = ref(false)

// Track expanded card details toggles
const expandedCards = ref({})

const toggleDetail = (id) => {
    expandedCards.value[id] = !expandedCards.value[id]
}

const statusLabel = {
    pending: 'Menunggu',
    terkirim: 'Terkirim',
    processing: 'Diproses',
    resolved: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Dibatalkan',
}

const statusClass = (status) => ({
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    terkirim: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    processing: 'bg-blue-100 text-blue-800 border-blue-200',
    resolved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-rose-100 text-rose-800 border-rose-200',
    cancelled: 'bg-slate-100 text-slate-800 border-slate-300',
}[status] ?? 'bg-slate-100 text-slate-700 border-slate-200')

const openCancelModal = (id) => {
    confirmingCancelId.value = id
}

const closeCancelModal = () => {
    confirmingCancelId.value = null
}

const confirmCancel = (id) => {
    cancelling.value = true
    router.patch(
        `/my-complaints/${id}/cancel`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                cancelling.value = false
                confirmingCancelId.value = null
            },
        }
    )
}

// Sliding window pagination (max 5 bubble, geser sesuai halaman aktif)
const { visiblePageLinks: pageLinks, hasPagination: showPagination } = useSlidingPagination(() => props.complaints)
</script>

<template>
    <Head title="Riwayat Pengaduan Saya"></Head>

    <FrontLayout>
        <div class="pt-24 sm:pt-28 pb-12 bg-slate-50/60 dark:bg-gray-900/60 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Navigation & Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                            <Link href="/" class="hover:text-blue-600 font-medium transition">Beranda</Link>
                            <span>/</span>
                            <span class="text-slate-800 dark:text-slate-200 font-semibold">Riwayat Pengaduan</span>
                        </div>
                        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Riwayat Pengaduan Saya</h1>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Pantau status dan perkembangan pengaduan yang telah Anda ajukan.
                        </p>
                        <div class="mt-4">

                    <select
                        class="rounded-xl border px-4 py-2 text-sm"
                        :value="filters.type"
                        @change="router.get('/my-complaints',{
                            type:$event.target.value
                        },{
                            preserveState:true,
                            preserveScroll:true
                        })"
                    >

                    <option value="all">
                    Semua Pengaduan
                    </option>


                    <option value="bedulang">
                    Layanan Pengaduan Bedulang
                    </option>


                    <option value="layanan_info">
                    Layanan Informasi & Data
                    </option>


                    </select>

                    </div>
                    </div>

                    <Link
                        href="/#layanan"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition self-start sm:self-auto"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Ajukan Pengaduan Baru</span>
                    </Link>
                </div>

                <!-- Session Flash Alerts -->
                <div v-if="page.props.flash?.success" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ page.props.flash.success }}</span>
                </div>

                <div v-if="page.props.errors?.status" class="p-4 mb-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-rose-600 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ page.props.errors.status }}</span>
                </div>

                <!-- Content Card Container -->
                <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 shadow-sm border border-slate-200/80 dark:border-gray-700/80">
                    <!-- Empty State -->
                    <div v-if="!complaints.data || complaints.data.length === 0" class="py-12 text-center">
                        <div class="mx-auto w-16 h-16 rounded-full bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Belum Ada Pengaduan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">
                            Anda belum pernah mengirimkan pengaduan. Pilih bidang layanan di beranda untuk mulai menyampaikan laporan.
                        </p>
                        <Link
                            href="/#layanan"
                            class="inline-block px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition"
                        >
                            Lihat Bidang Layanan
                        </Link>
                    </div>

                    <!-- Complaints List (Simple by default) -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="item in complaints.data"
                            :key="item.id"
                            class="rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-sm border border-slate-200/80 dark:border-gray-700/80 transition-all duration-300 hover:shadow-md space-y-4"
                        >
                            <!-- Simple Default Card Header Row (Judul, Status, Tanggal Update Status & Detail Toggle) -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="space-y-1.5 min-w-0 flex-1">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h2 class="text-lg font-bold text-slate-900 dark:text-white truncate">
                                            {{ item.title }}
                                        </h2>
                                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold border shrink-0" :class="statusClass(item.status)">
                                            {{ statusLabel[item.status] }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 shrink-0">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>Update Status: <strong>{{ new Date(item.updated_at).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }} WIB</strong></span>
                                    </div>
                                </div>

                                <!-- Eye Icon Detail Button -->
                                <button
                                    @click="toggleDetail(item.id)"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition shrink-0 cursor-pointer"
                                    :class="expandedCards[item.id] ? 'bg-slate-800 dark:bg-gray-700 text-white' : 'bg-slate-100 dark:bg-gray-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-gray-700'"
                                >
                                    <!-- Eye Open Icon (Default) -->
                                    <svg v-if="!expandedCards[item.id]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-600 dark:text-blue-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <!-- Eye Slash Icon (Expanded) -->
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-rose-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                    <span>{{ expandedCards[item.id] ? 'Sembunyikan Detail' : 'Lihat Detail' }}</span>
                                </button>
                            </div>

                            <!-- Collapsible Expanded Section with Transition -->
                            <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="transform opacity-0 -translate-y-2 scale-98"
                                enter-to-class="transform opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="transform opacity-100 translate-y-0 scale-100"
                                leave-to-class="transform opacity-0 -translate-y-2 scale-98"
                            >
                                <div v-show="expandedCards[item.id]" class="pt-4 border-t border-slate-100 dark:border-gray-700/60 space-y-4">
                                    <!-- Service & Category metadata -->
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-400 uppercase">Bidang Layanan:</span>
                                            <span class="px-2.5 py-0.5 rounded-md font-semibold bg-slate-100 dark:bg-gray-700 text-slate-800 dark:text-slate-200 border border-slate-200/80 dark:border-gray-600">
                                                {{ item.service_name }}
                                            </span>
                                        </div>

                                        <div class="text-slate-400">
                                            Diajukan pada: {{ new Date(item.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                        </div>
                                    </div>

                                    <!-- Full Description -->
                                    <div class="space-y-1">
                                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi Pengaduan:</h4>
                                        <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line bg-slate-50 dark:bg-gray-900/60 p-4 rounded-xl border border-slate-200/60 dark:border-gray-700/60 leading-relaxed">
                                            {{ item.description }}
                                        </div>
                                    </div>

                                    <div
                                        v-if="item.followup_image"
                                        class="space-y-2"
                                        >

                                        <h4 class="text-xs font-semibold text-slate-400 uppercase">
                                        Bukti Tindak Lanjut Admin
                                        </h4>


                                        <img

                                        :src="`/${item.followup_image}`"

                                        class="rounded-xl max-w-md border"

                                        />


                                        </div>

                                    <!-- Cancel Option (Only available when status is still pending) -->
                                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                        <template v-if="item.status === 'pending'">
                                            <button
                                                @click="openCancelModal(item.id)"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                <span>Batalkan Pengaduan Saya</span>
                                            </button>
                                        </template>

                                        <template v-else-if="item.status === 'cancelled'">
                                            <span class="text-xs italic text-slate-400">
                                                Pengaduan ini telah Anda batalkan.
                                            </span>
                                        </template>

                                        <template v-else>
                                            <span class="text-xs italic text-slate-400">
                                                Status pengaduan {{ statusLabel[item.status] }} (Tidak dapat dibatalkan).
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Review Section -->

                                    <div
                                    v-if="item.status === 'resolved'"
                                    class="rounded-xl bg-emerald-50 border border-emerald-200 p-4"
                                    >


                                    <h4 class="font-semibold text-sm">
                                    Ulasan Pengaduan
                                    </h4>


                                    <div v-if="!item.rating">


                                    <div class="flex gap-2 mt-3">

                                    <button
                                    v-for="star in 5"
                                    :key="star"
                                    @click="reviewRating[item.id]=star"
                                    type="button"
                                    class="transition-transform duration-150 hover:scale-110 focus:outline-none"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            class="w-8 h-8 transition-colors duration-150"
                                            :class="star <= (reviewRating[item.id] || 0)
                                                ? 'fill-amber-400 stroke-amber-500'
                                                : 'fill-transparent stroke-slate-400 dark:stroke-gray-500'"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.178-.367.68-.367.858 0l2.64 5.34 5.882.855c.404.059.565.556.273.84l-4.256 4.148 1.005 5.858c.069.4-.35.704-.712.51l-5.263-2.768-5.264 2.768c-.363.194-.782-.11-.712-.51l1.005-5.858L3.21 10.534c-.292-.284-.131-.78.273-.841l5.882-.855 2.64-5.34Z" />
                                        </svg>
                                    </button>

                                    </div>


                                    <textarea

                                    v-model="reviewText[item.id]"

                                    placeholder="Tulis ulasan..."

                                    class="mt-3 w-full rounded-xl border p-3"

                                    ></textarea>


                                    <button

                                    @click="submitReview(item.id)"

                                    class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold"

                                    >
                                    Kirim Ulasan
                                    </button>


                                    </div>


                                    <div v-else class="mt-2 space-y-2">

                                    <div class="flex gap-1">
                                        <svg
                                            v-for="star in 5"
                                            :key="star"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            class="w-5 h-5"
                                            :class="star <= item.rating
                                                ? 'fill-amber-400 stroke-amber-500'
                                                : 'fill-transparent stroke-slate-300 dark:stroke-gray-600'"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.178-.367.68-.367.858 0l2.64 5.34 5.882.855c.404.059.565.556.273.84l-4.256 4.148 1.005 5.858c.069.4-.35.704-.712.51l-5.263-2.768-5.264 2.768c-.363.194-.782-.11-.712-.51l1.005-5.858L3.21 10.534c-.292-.284-.131-.78.273-.841l5.882-.855 2.64-5.34Z" />
                                        </svg>
                                    </div>

                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 italic">
                                    "{{item.review}}"
                                    </p>


                                    </div>


                                    </div>
                                    <!-- Progress Logs Timeline -->
                                    <div v-if="item.progress_logs && item.progress_logs.length > 0" class="space-y-2 pt-2">
                                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Catatan Progres:</h4>
                                        <div class="space-y-2">
                                            <div
                                                v-for="log in item.progress_logs"
                                                :key="log.id"
                                                class="p-3 rounded-xl bg-slate-50 dark:bg-gray-900/40 border border-slate-200/60 dark:border-gray-700/40 text-xs space-y-1"
                                            >
                                                <div class="flex items-center justify-between text-slate-500">
                                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                                        Status diubah ke {{ statusLabel[log.status] || log.status }}
                                                    </span>
                                                    <span>
                                                        {{ new Date(log.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                                                    </span>
                                                </div>
                                                <p v-if="log.notes" class="text-slate-600 dark:text-slate-400">
                                                    {{ log.notes }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Custom Pagination with Circular Bubbles & Hiding Arrow Controls -->
                    <div v-if="showPagination" class="flex items-center justify-center gap-2 pt-6">
                        <!-- Left Arrow (Hidden on Page 1) -->
                        <Link
                            v-if="complaints.prev_page_url"
                            :href="complaints.prev_page_url"
                            class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 transition shadow-sm"
                            aria-label="Previous Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </Link>

                        <!-- Circular Page Bubbles (Page 1, 2, 3...) -->
                        <template v-for="(link, index) in pageLinks" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="w-10 h-10 rounded-full font-bold text-sm flex items-center justify-center transition shadow-sm"
                                :class="link.active ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/50' : 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-gray-700'"
                            >
                                {{ link.label }}
                            </Link>
                            <span v-else class="w-10 h-10 rounded-full flex items-center justify-center text-xs text-slate-400 font-bold">
                                {{ link.label }}
                            </span>
                        </template>

                        <!-- Right Arrow (Hidden on Last Page) -->
                        <Link
                            v-if="complaints.next_page_url"
                            :href="complaints.next_page_url"
                            class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 transition shadow-sm"
                            aria-label="Next Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Cancel Confirmation Modal -->
                <div v-if="confirmingCancelId !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-4 border border-slate-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 text-rose-600">
                            <div class="p-3 bg-rose-50 dark:bg-rose-950/40 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Batalkan Pengaduan?</h3>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Apakah Anda yakin ingin membatalkan pengaduan ini? Setelah dibatalkan, pengaduan tidak dapat diproses lagi oleh admin.
                        </p>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                @click="closeCancelModal"
                                :disabled="cancelling"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition"
                            >
                                Batal
                            </button>
                            <button
                                @click="confirmCancel(confirmingCancelId)"
                                :disabled="cancelling"
                                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-sm transition disabled:bg-slate-400"
                            >
                                {{ cancelling ? 'Membatalkan…' : 'Ya, Batalkan Pengaduan' }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </FrontLayout>
</template>
