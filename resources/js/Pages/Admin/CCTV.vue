<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import { ref, nextTick, computed } from 'vue'
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
        const url = new URL(streamUrl)
        // Dynamically use the website's domain/host on port 8888
        return `${url.protocol}//${window.location.hostname}:8888${url.pathname}${url.search}`
    } catch (e) {
        return streamUrl
    }
}

const selectedCCTV = ref(null)
const video = ref(null)
let hls = null

const zoom = 13
const center = [-2.45, 106.15]

const openCCTV = async (cctv) => {
    selectedCCTV.value = cctv
    await nextTick()
    if (hls) {
        hls.destroy()
    }
    const resolvedUrl = resolveStreamUrl(cctv.stream)
    if (Hls.isSupported() && video.value) {
        hls = new Hls()
        hls.loadSource(resolvedUrl)
        hls.attachMedia(video.value)

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            video.value.play()
        })
    } else if (video.value && video.value.canPlayType('application/vnd.apple.mpegurl')) {
        video.value.src = resolvedUrl
        video.value.addEventListener('loadedmetadata', () => {
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
<component :is="layoutComponent">
<Head title="CCTV Monitoring"/>
<div :class="isAdmin ? 'space-y-6' : 'mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6'">

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
</component>
</template>
<style scoped>
.leaflet-container{
height:100%;
width:100%;
}

</style>
