<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

import {
    LMap,
    LTileLayer,
    LMarker,
    LPopup,
} from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'

defineProps({
    cctvs: Array,
})

const zoom = 11
const center = [-2.45, 106.15]
</script>

<template>
    <Head title="CCTV Admin" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow p-6">
                <h1 class="text-3xl font-bold text-gray-800">CCTV</h1>
                <p class="text-gray-500 mt-2">
                    Pantau lokasi CCTV Anda dan lihat detail kamera di peta.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div class="bg-white rounded-2xl shadow overflow-hidden">
                    <LMap :zoom="zoom" :center="center" class="h-[520px] w-full">
                        <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                        <LMarker :lat-lng="center">
                            <LPopup>Lokasi pusat tampilan CCTV.</LPopup>
                        </LMarker>
                        <template v-for="cctv in cctvs ?? []" :key="cctv.id">
                            <LMarker :lat-lng="[cctv.lat, cctv.lng]">
                                <LPopup>
                                    <div class="font-semibold">{{ cctv.name ?? 'CCTV' }}</div>
                                    <div class="text-sm">{{ cctv.location ?? 'Lokasi tidak tersedia' }}</div>
                                </LPopup>
                            </LMarker>
                        </template>
                    </LMap>
                </div>

                <div class="space-y-4">
                    <div class="bg-white rounded-2xl shadow p-6">
                        <h2 class="text-xl font-semibold mb-3">Daftar CCTV</h2>
                        <div class="space-y-3">
                            <template v-if="cctvs?.length">
                                <div
                                    v-for="cctv in cctvs"
                                    :key="cctv.id"
                                    class="rounded-2xl border border-gray-200 p-4"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-gray-800">{{ cctv.name ?? 'CCTV' }}</div>
                                            <div class="text-sm text-gray-500">{{ cctv.location ?? 'Lokasi tidak tersedia' }}</div>
                                        </div>
                                        <div class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            {{ cctv.status ?? 'Aktif' }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else class="text-gray-500">
                                Tidak ada data CCTV tersedia.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
