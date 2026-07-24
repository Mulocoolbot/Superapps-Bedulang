<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import Hls from 'hls.js'

const props = defineProps({
    cctvs: {
        type: Array,
        default: () => []
    },
    complaintStats: {
        type: Object,
        default: () => ({ pending: 0, processing: 0, total: 0 })
    },
    userDomain: {
        type: String,
        default: ''
    }
})

const videoRef = ref(null)
const selectedCCTV = ref(props.cctvs && props.cctvs.length > 0 ? props.cctvs[0] : null)

let hls = null

const playCCTV = (cctv) => {
    selectedCCTV.value = cctv
    nextTick(() => {
        if (!cctv || !videoRef.value) return
        const streamUrl = cctv.stream

        if (hls) {
            hls.destroy()
        }

        if (Hls.isSupported()) {
            hls = new Hls()
            hls.loadSource(streamUrl)
            hls.attachMedia(videoRef.value)
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                videoRef.value.play().catch(() => {})
            })
        } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
            videoRef.value.src = streamUrl
            videoRef.value.addEventListener('loadedmetadata', () => {
                videoRef.value.play().catch(() => {})
            })
        }
    })
}

onMounted(() => {
    if (selectedCCTV.value) {
        playCCTV(selectedCCTV.value)
    }
})

onBeforeUnmount(() => {
    if (hls) {
        hls.destroy()
        hls = null
    }
})
</script>

<template>
    <Head title="Dashboard Admin"></Head>

    <AdminLayout>
        <!-- Top header row with Landing Page button on top right -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Dashboard Admin
                </h1>
                <p class="text-gray-500 mt-1">
                    Selamat datang di Panel Administrator BEDULANG.
                    <span v-if="userDomain" class="font-semibold text-blue-600">
                        (Layanan: {{ userDomain.toUpperCase() }})
                    </span>
                </p>
            </div>

            <!-- Top right landing page back button -->
            <Link
                href="/"
                class="inline-flex justify-center items-center p-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-blue-600 shadow-sm transition shrink-0"
                title="Kembali ke Landing Page"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </Link>
        </div>

        <!-- Direct Feature & Quick Access Display -->
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-gray-800">
                Fitur & Akses Cepat
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- CCTV Live Stream Feature Screen -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Fitur CCTV - Live Stream</h3>
                                <p class="text-xs text-gray-500" v-if="selectedCCTV">
                                    Kamera: {{ selectedCCTV.name }}
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Live Stream
                        </span>
                    </div>

                    <!-- Direct Video Stream Screen -->
                    <div class="aspect-video bg-black rounded-xl overflow-hidden shadow-inner relative group">
                        <video
                            ref="videoRef"
                            controls
                            autoplay
                            muted
                            class="w-full h-full object-contain"
                        ></video>
                    </div>

                    <!-- Footer options inside CCTV Card -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div class="flex items-center gap-2" v-if="cctvs.length > 1">
                            <button
                                v-for="cctv in cctvs"
                                :key="cctv.id"
                                @click="playCCTV(cctv)"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                :class="selectedCCTV?.id === cctv.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                {{ cctv.name }}
                            </button>
                        </div>
                        <p v-else class="text-xs text-gray-400">
                            Tampilan default CCTV pertama yang ditambahkan.
                        </p>

                        <Link
                            href="/admin/cctv"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition ml-auto"
                        >
                            <span>Monitoring CCTV Lengkap</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Single Unified Complaint Data Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-lg">Monitoring Pengaduan</h3>
                                    <p class="text-xs text-gray-500">
                                        {{ userDomain ? `Layanan: ${userDomain.toUpperCase()}` : 'Semua Bidang Layanan' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Grid 3 Indicators inside Single Card -->
                        <div class="grid grid-cols-3 gap-3 pt-1">
                            <!-- Pending Stat Box -->
                            <Link
                                href="/admin/complaints?status=pending"
                                class="p-4 rounded-xl bg-amber-50/80 hover:bg-amber-100/80 border border-amber-200/70 transition space-y-2 text-center group"
                            >
                                <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider block">Menunggu</span>
                                <span class="text-3xl font-extrabold text-amber-600 block group-hover:scale-105 transition">
                                    {{ complaintStats?.pending ?? 0 }}
                                </span>
                                <span class="text-[11px] text-amber-700 font-medium block">Perlu Tindakan</span>
                            </Link>

                            <!-- Processing Stat Box -->
                            <Link
                                href="/admin/complaints?status=processing"
                                class="p-4 rounded-xl bg-blue-50/80 hover:bg-blue-100/80 border border-blue-200/70 transition space-y-2 text-center group"
                            >
                                <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider block">Diproses</span>
                                <span class="text-3xl font-extrabold text-blue-600 block group-hover:scale-105 transition">
                                    {{ complaintStats?.processing ?? 0 }}
                                </span>
                                <span class="text-[11px] text-blue-700 font-medium block">Sedang Ditangani</span>
                            </Link>

                            <!-- Resolved Stat Box -->
                            <Link
                                href="/admin/complaints?status=resolved"
                                class="p-4 rounded-xl bg-green-50/80 hover:bg-green-100/80 border border-green-200/70 transition space-y-2 text-center group"
                            >
                                <span class="text-xs font-semibold text-green-800 uppercase tracking-wider block">Selesai</span>
                                <span class="text-3xl font-extrabold text-green-600 block group-hover:scale-105 transition">
                                    {{ complaintStats?.resolved ?? 0 }}
                                </span>
                                <span class="text-[11px] text-green-700 font-medium block">Telah Diselesaikan</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Bottom Action Link -->
                    <div class="pt-2 border-t">
                        <Link
                            href="/admin/complaints"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition text-sm shadow-sm"
                        >
                            <span>Kelola Semua Pengaduan</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>