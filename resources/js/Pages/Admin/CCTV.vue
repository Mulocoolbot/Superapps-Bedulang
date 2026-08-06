<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { ref, nextTick, computed, onBeforeUnmount } from 'vue'
import Hls from 'hls.js'

import 'leaflet/dist/leaflet.css'
import {
    LMap,
    LTileLayer,
    LMarker,
    LPopup,
} from '@vue-leaflet/vue-leaflet'

import L from 'leaflet'

delete L.Icon.Default.prototype._getIconUrl

L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const props = defineProps({
    cctvs: Array,
})

const page = usePage()
const isAdmin = computed(() => {
    const role = page.props.auth?.user?.role
    return role === 'admin' || role === 'super_admin'
})
const layoutComponent = computed(() => isAdmin.value ? AdminLayout : FrontLayout)

const resolveStreamUrl = (streamUrl) => {
    if (!streamUrl) return ''
    try {
        const lastSlashIndex = streamUrl.lastIndexOf('/')
        const baseUrl = streamUrl.substring(0, lastSlashIndex)
        const filename = streamUrl.substring(lastSlashIndex + 1)
        
        // Base64 Url Encode the baseUrl
        const base64Url = btoa(baseUrl)
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '')
            
        return `/cctv-stream/${base64Url}/${filename}`
    } catch (e) {
        return streamUrl
    }
}

const selectedCCTV = ref(null)
const video = ref(null)
const streamError = ref(false)
const streamLoading = ref(false)
let hls = null
let loadedMetadataHandler = null

const zoom = 13
const center = [-2.45, 106.15]

const isMapInteractive = ref(false)
const isMobile = ref(typeof window !== 'undefined' && window.innerWidth < 768)

const mapOptions = computed(() => {
    if (isMobile.value && !isMapInteractive.value) {
        return { dragging: false, touchZoom: false, scrollWheelZoom: false }
    }
    return { dragging: true, touchZoom: true, scrollWheelZoom: false }
})

const openCCTV = async (cctv) => {
    selectedCCTV.value = cctv
    streamError.value = false
    streamLoading.value = true
    await nextTick()
    if (hls) {
        hls.destroy()
        hls = null
    }
    const resolvedUrl = resolveStreamUrl(cctv.stream)
    if (Hls.isSupported() && video.value) {
        hls = new Hls({
            manifestLoadingTimeOut: 10000,
            manifestLoadingMaxRetry: 3,
            manifestLoadingRetryDelay: 2000,
            levelLoadingTimeOut: 10000,
            fragLoadingTimeOut: 20000,
        })
        hls.loadSource(resolvedUrl)
        hls.attachMedia(video.value)

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            streamLoading.value = false
            streamError.value = false
            video.value.play().catch(e => console.log('Autoplay handled:', e))
        })

        hls.on(Hls.Events.ERROR, (event, data) => {
            if (data.fatal) {
                streamLoading.value = false
                streamError.value = true
                console.warn('HLS fatal error:', data.type, data.details)
                hls.destroy()
                hls = null
            }
        })
    } else if (video.value && video.value.canPlayType('application/vnd.apple.mpegurl')) {
        // Native HLS support for Safari on macOS / iOS
        if (loadedMetadataHandler) {
            video.value.removeEventListener('loadedmetadata', loadedMetadataHandler)
        }
        video.value.src = resolvedUrl
        loadedMetadataHandler = () => {
            streamLoading.value = false
            streamError.value = false
            video.value.play().catch(e => console.log('Autoplay handled:', e))
        }
        video.value.addEventListener('loadedmetadata', loadedMetadataHandler)
        video.value.addEventListener('error', () => {
            streamLoading.value = false
            streamError.value = true
        }, { once: true })
    }
}

const retryCCTV = () => {
    if (selectedCCTV.value) {
        openCCTV(selectedCCTV.value)
    }
}

onBeforeUnmount(() => {
    if (hls) {
        hls.destroy()
        hls = null
    }
    if (video.value && loadedMetadataHandler) {
        video.value.removeEventListener('loadedmetadata', loadedMetadataHandler)
        loadedMetadataHandler = null
    }
})

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back()
    } else {
        window.location.href = '/'
    }
}
</script>
<template>
    <component :is="layoutComponent">
        <Head title="CCTV Monitoring" />
        <div :class="isAdmin ? 'space-y-6' : 'mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6'">

            <!-- HEADER -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <Link
                            href="/"
                            class="inline-flex justify-center items-center p-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm cursor-pointer"
                            title="Kembali ke Landing Page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">
                                CCTV Monitoring
                            </h1>
                            <p class="text-sm text-slate-500">
                                Live monitoring kamera CCTV administrator
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold">
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
                            {{ cctvs?.length || 0 }} Kamera Aktif
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAP + LIST GRID -->
            <div class="grid xl:grid-cols-[2fr_1fr] gap-6">
                <!-- MAP -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900">
                                Peta Lokasi CCTV
                            </h2>
                            <p class="text-xs text-slate-500">
                                Klik titik kamera untuk melihat live stream
                            </p>
                        </div>
                    </div>

                    <div class="h-[520px] w-full relative">
                        <!-- Mobile Overlay hint to prevent scroll hijacking -->
                        <div
                            v-if="isMobile && !isMapInteractive"
                            @click="isMapInteractive = true"
                            class="absolute inset-0 bg-slate-900/10 backdrop-blur-[1px] z-10 flex items-center justify-center cursor-pointer p-4 transition-all"
                        >
                            <button
                                type="button"
                                class="px-4 py-2 bg-slate-900/85 text-white text-xs font-bold rounded-xl shadow-lg border border-white/20 flex items-center gap-2"
                            >
                                <span>👆 Klik untuk Interaksi Peta</span>
                            </button>
                        </div>

                        <!-- Toggle back button when map is unlocked on mobile -->
                        <button
                            v-if="isMobile && isMapInteractive"
                            @click="isMapInteractive = false"
                            type="button"
                            class="absolute top-3 right-3 z-10 px-3 py-1.5 bg-white/90 dark:bg-slate-800/90 text-slate-800 dark:text-white text-xs font-extrabold rounded-lg shadow-md border border-slate-200 dark:border-slate-700 flex items-center gap-1.5"
                        >
                            <span>🔒 Kunci Peta</span>
                        </button>

                        <LMap
                            :zoom="zoom"
                            :center="center"
                            :options="mapOptions"
                            class="h-full w-full z-0"
                        >
                            <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></LTileLayer>

                            <LMarker
                                v-for="cctv in cctvs"
                                :key="cctv.id"
                                :lat-lng="[cctv.lat, cctv.lng]"
                                @click="openCCTV(cctv)"
                            >
                                <LPopup>
                                    <div class="space-y-2 p-1">
                                        <div class="font-bold text-slate-800 text-sm">
                                            {{ cctv.name }}
                                        </div>
                                        <p class="text-xs text-slate-500">
                                            IP: {{ cctv.ip }}
                                        </p>

                                        <button
                                            @click="openCCTV(cctv)"
                                            class="w-full rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700 transition"
                                        >
                                            Lihat Kamera
                                        </button>
                                    </div>
                                </LPopup>
                            </LMarker>
                        </LMap>
                    </div>
                </div>

                <!-- LIST -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                            <h2 class="font-bold text-slate-900">
                                Daftar Kamera
                            </h2>
                            <span class="rounded-full bg-slate-100 px-3 py-0.5 text-xs font-semibold text-slate-600">
                                {{ cctvs?.length || 0 }} Lokasi
                            </span>
                        </div>
                        <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                            <div
                                v-for="cctv in cctvs"
                                :key="cctv.id"
                                @click="openCCTV(cctv)"
                                class="cursor-pointer rounded-xl border border-slate-200 p-4 transition hover:border-cyan-300 hover:bg-cyan-50/50"
                                :class="selectedCCTV?.id === cctv.id ? 'border-cyan-500 bg-cyan-50/80 ring-1 ring-cyan-500' : ''"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-slate-800 text-sm">
                                            {{ cctv.name }}
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ cctv.ip }}
                                        </p>
                                    </div>
                                    <span class="flex items-center gap-1 text-xs text-green-600 font-semibold">
                                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                        Online
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIDEO PLAYER MODAL / PANEL -->
            <div
                v-if="selectedCCTV"
                class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 animate-fade-in-up"
            >
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ selectedCCTV.name }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Stream IP: {{ selectedCCTV.ip }}
                        </p>
                    </div>
                    <button
                        @click="selectedCCTV = null"
                        class="rounded-xl bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100 transition cursor-pointer"
                    >
                        Tutup Stream
                    </button>
                </div>

                <div class="rounded-2xl overflow-hidden bg-black shadow-lg aspect-video max-w-4xl mx-auto relative">
                    <!-- Loading Overlay -->
                    <div v-if="streamLoading && !streamError" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/80 z-10">
                        <svg class="animate-spin h-10 w-10 text-cyan-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-white text-sm font-medium">Menghubungkan ke kamera...</span>
                    </div>

                    <!-- Error Overlay -->
                    <div v-if="streamError" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/90 z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-rose-400 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span class="text-white text-sm font-semibold mb-1">Stream Tidak Tersedia</span>
                        <span class="text-slate-400 text-xs mb-4 text-center px-6">Kamera sedang offline atau tidak dapat dijangkau dari jaringan ini.</span>
                        <button
                            @click="retryCCTV"
                            class="rounded-lg bg-cyan-600 px-5 py-2 text-sm font-semibold text-white hover:bg-cyan-700 transition cursor-pointer"
                        >
                            Coba Lagi
                        </button>
                    </div>

                    <video
                        ref="video"
                        controls
                        autoplay
                        muted
                        class="w-full h-full object-contain"
                    ></video>
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-slate-500 max-w-4xl mx-auto">
                    <span class="flex items-center gap-1.5 font-medium">
                        <span class="h-2 w-2 rounded-full" :class="streamError ? 'bg-rose-500' : 'bg-green-500 animate-pulse'"></span>
                        Status: {{ streamError ? 'Offline' : 'Live Online' }}
                    </span>

                    <span class="font-mono text-slate-400">
                        Hikvision RTSP HLS Stream
                    </span>
                </div>
            </div>

        </div>
    </component>
</template>
<style scoped>
:deep(.leaflet-container) {
    height: 100% !important;
    min-height: 520px !important;
    width: 100% !important;
    z-index: 0;
}
</style>
