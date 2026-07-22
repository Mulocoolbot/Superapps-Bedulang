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
    cctvs:Array,
})


const selectedCCTV = ref(null)
const video = ref(null)
let hls = null

const zoom = 13
const center = [-2.45,106.15]
const openCCTV = async(cctv)=>{
    selectedCCTV.value = cctv
    await nextTick()
    if(hls){
        hls.destroy()
    }
    if(Hls.isSupported()){
        hls = new Hls()
        hls.loadSource(
            cctv.stream
        )
        hls.attachMedia(
            video.value
        )

        hls.on(
            Hls.Events.MANIFEST_PARSED,
            ()=>{
                video.value.play()
            }
        )
    }

}

const goBack = ()=>{
    if(window.history.length > 1){
        window.history.back()
    }
    else{
        window.location.href='/'
    }
}
</script>
<template>
<AdminLayout>
<Head title="CCTV Monitoring"/>
<div class="space-y-6">

<!-- HEADER -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
<div class="flex items-center gap-4">
<button
@click="goBack"
class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
>
← Kembali
</button>
<div>
<h1 class="text-2xl font-bold text-slate-900">
CCTV Monitoring
</h1>

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
            <!-- GRID FOR MAP & LIST -->
            <div class="grid lg:grid-cols-[3fr_1fr] gap-6">
                <!-- MAP CARD -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden h-[550px] min-h-[550px]">
                    <LMap :zoom="zoom" :center="center" class="h-full w-full z-0">
                        <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                        <template v-for="cctv in cctvs" :key="cctv.id">
                            <LMarker :lat-lng="[cctv.lat, cctv.lng]" @click="openCCTV(cctv)">
                                <LPopup>
                                    <div class="font-bold text-slate-800">{{ cctv.name }}</div>
                                    <div class="text-sm text-slate-500">IP: {{ cctv.ip }}</div>
                                    <button @click="openCCTV(cctv)" class="mt-2 bg-cyan-600 hover:bg-cyan-700 text-white px-3 py-1 rounded text-sm transition">
                                        Lihat CCTV
                                    </button>
                                </LPopup>
                            </LMarker>
                        </template>
                    </LMap>
                </div>

                <!-- LIST CARD -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 flex flex-col justify-between h-[550px]">
                    <div>
                        <h2 class="font-bold text-lg text-slate-900 mb-4 flex items-center justify-between">
                            <span>Daftar CCTV</span>
                            <span class="text-xs font-normal text-slate-500">({{ cctvs?.length || 0 }} Lokasi)</span>
                        </h2>
                        <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                            <div
                                v-for="cctv in cctvs"
                                :key="cctv.id"
                                @click="openCCTV(cctv)"
                                class="cursor-pointer border border-slate-200 rounded-xl p-4 transition hover:bg-slate-50 hover:border-cyan-200 hover:shadow-sm"
                                :class="selectedCCTV?.id === cctv.id ? 'border-cyan-500 bg-cyan-50/40 ring-1 ring-cyan-500' : ''"
                            >
                                <div class="font-semibold text-slate-800">{{ cctv.name }}</div>
                                <div class="text-sm text-slate-500">{{ cctv.ip }}</div>
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 mt-2">
                                    Online
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
>>>>>>> 7d4312814a88068581d58eab0b3b8c5c7a88a617

<!-- MAP + LIST -->
<div class="grid xl:grid-cols-[2fr_1fr] gap-6">
<!-- MAP -->
<div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
<div class="px-5 py-4 border-b">
<h2 class="font-bold text-slate-900">
Peta Lokasi CCTV
</h2>

<p class="text-xs text-slate-500">
Klik titik kamera untuk melihat live stream
</p>
</div>

<LMap
:zoom="zoom"
:center="center"
class="h-[520px] w-full"
>
<LTileLayer
url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
/>

<LMarker
v-for="cctv in cctvs"
:key="cctv.id"
:lat-lng="[cctv.lat,cctv.lng]"
@click="openCCTV(cctv)"
>

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
<p class="text-xs text-slate-500">
{{cctv.ip}}
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
</template>
<style scoped>
<<<<<<< HEAD
.leaflet-container{
height:100%;
width:100%;
=======
:deep(.leaflet-container) {
    height: 550px !important;
    min-height: 550px !important;
    width: 100% !important;
    z-index: 0;
>>>>>>> 7d4312814a88068581d58eab0b3b8c5c7a88a617
}

</style>