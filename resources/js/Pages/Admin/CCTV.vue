<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, nextTick } from 'vue'
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

const selectedCCTV = ref(null)
const video = ref(null)
let hls = null

const zoom = 13
<<<<<<< HEAD
const center = [-2.45,106.15]
const openCCTV = async(cctv)=>{
=======
const center = [-2.45, 106.15]

const openCCTV = async (cctv) => {
>>>>>>> 86d4849e5dc185762e8f80286f6645ea4318c172
    selectedCCTV.value = cctv
    await nextTick()
    if (hls) {
        hls.destroy()
    }
    if (Hls.isSupported() && video.value) {
        hls = new Hls()
        hls.loadSource(cctv.stream)
        hls.attachMedia(video.value)

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            video.value.play()
        })
    }
}

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back()
    } else {
        window.location.href = '/'
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="CCTV Monitoring" />
        <div class="space-y-6">

            <!-- HEADER -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <button
                            @click="goBack"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                        >
                            ← Kembali
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">
                                CCTV Monitoring
                            </h1>
                            <p class="text-sm text-slate-500">
                                Live monitoring kamera CCTV administrator
                            </p>
                        </div>
                    </div>

<<<<<<< HEAD
<p class="text-sm text-slate-500">
Live monitoring kamera CCTV administrator
</p>
</div>
</div>

<div class="flex items-center gap-2">
<div class="flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold">
<span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
{{ cctvs.length }} Kamera Aktif
</div>
</div>
</div>
</div>
=======
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
                        <LMap
                            :zoom="zoom"
                            :center="center"
                            class="h-full w-full z-0"
                        >
                            <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />

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
>>>>>>> 86d4849e5dc185762e8f80286f6645ea4318c172

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

                <div class="rounded-2xl overflow-hidden bg-black shadow-lg aspect-video max-w-4xl mx-auto">
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
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        Status: Live Online
                    </span>

                    <span class="font-mono text-slate-400">
                        Hikvision RTSP HLS Stream
                    </span>
                </div>
            </div>

<<<<<<< HEAD
<LPopup>
<div class="space-y-2">
<div class="font-bold text-slate-800">
{{cctv.name}}
</div>

<button
@click="openCCTV(cctv)"
class="w-full rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700"
>
Lihat Kamera
</button>
</div>
</LPopup>
</LMarker>
</LMap>
</div>

<!-- LIST -->
<div class="bg-white rounded-2xl border shadow-sm p-5">
<div class="flex justify-between items-center mb-5">
<h2 class="font-bold text-slate-900">
Daftar Kamera
</h2>
<span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">
{{cctvs.length}}
</span>
</div>
<div class="space-y-3 max-h-[470px] overflow-y-auto">
<div
v-for="cctv in cctvs"
:key="cctv.id"
@click="openCCTV(cctv)"
class="cursor-pointer rounded-xl border p-4 transition hover:border-cyan-300 hover:bg-cyan-50"
>
<div class="flex justify-between">

<div>
<h3 class="font-semibold text-slate-800">
{{cctv.name}}
</h3>
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

<!-- VIDEO -->
<div
v-if="selectedCCTV"
class="bg-white rounded-2xl border shadow-sm p-6"
>
<div class="flex justify-between items-center mb-5">
<div>
<h2 class="text-xl font-bold text-slate-900">
{{selectedCCTV.name}}
</h2>
</div>
<button
@click="selectedCCTV=null"
class="rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100"
>
Tutup
</button>
</div>

<div class="rounded-2xl overflow-hidden bg-black shadow-lg aspect-video">
<video
ref="video"
controls
autoplay
muted
class="w-full h-full"
></video>
</div>

<div class="mt-4 flex justify-between text-xs text-slate-400">
<span>
Status : Online
</span>

<span>
Hikvision RTSP Stream
</span>

</div>
</div>
</div>
</AdminLayout>
=======
        </div>
    </AdminLayout>
>>>>>>> 86d4849e5dc185762e8f80286f6645ea4318c172
</template>

<style scoped>
<<<<<<< HEAD
.leaflet-container{
height:100%;
width:100%;
=======
:deep(.leaflet-container) {
    height: 100% !important;
    min-height: 520px !important;
    width: 100% !important;
    z-index: 0;
>>>>>>> 86d4849e5dc185762e8f80286f6645ea4318c172
}
</style>