<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'

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

defineProps({
    cctvs: Array,
})

const selectedCCTV = ref(null)
const zoom = 13
const center = [-2.45, 106.15]

const openCCTV = (cctv) => {
    selectedCCTV.value = cctv
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        @click="goBack"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100"
                    >
                        ← Kembali
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">CCTV Monitoring</h1>
                        <p class="text-sm text-slate-500 mt-1">Pantauan kamera CCTV administrator</p>
                    </div>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-cyan-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah CCTV
                </button>
            </div>

            <!-- GRID FOR MAP & LIST -->
            <div class="grid lg:grid-cols-[2fr_1fr] gap-6">
                <!-- MAP CARD -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <LMap :zoom="zoom" :center="center" class="h-[500px] w-full">
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                    <h2 class="font-bold text-lg text-slate-900 mb-4">Daftar CCTV</h2>
                    <div class="space-y-3 max-h-[440px] overflow-y-auto pr-1">
                        <div
                            v-for="cctv in cctvs"
                            :key="cctv.id"
                            @click="openCCTV(cctv)"
                            class="cursor-pointer border border-slate-200 rounded-xl p-4 transition hover:bg-slate-50 hover:border-cyan-200 hover:shadow-sm"
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

            <!-- CCTV VIEWER CARD -->
            <div v-if="selectedCCTV" class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ selectedCCTV.name }}</h2>
                        <p class="text-sm text-slate-500">IP: {{ selectedCCTV.ip }}</p>
                    </div>
                    <button @click="selectedCCTV = null" class="text-red-500 font-semibold hover:text-red-700 transition">
                        Tutup
                    </button>
                </div>
                <div class="aspect-video bg-black rounded-xl overflow-hidden shadow-inner">
                    <iframe
                        :title="selectedCCTV.name"
                        :src="`http://${selectedCCTV.ip}`"
                        class="w-full h-full"
                        frameborder="0"
                    ></iframe>
                </div>
                <p class="text-xs text-slate-400">
                    Stream: <a :href="`http://${selectedCCTV.ip}`" target="_blank" class="text-blue-500 hover:underline">http://{{ selectedCCTV.ip }}</a>
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.leaflet-container {
    height: 100%;
    width: 100%;
}
</style>
