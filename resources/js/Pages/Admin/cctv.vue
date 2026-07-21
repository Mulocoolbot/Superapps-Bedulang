<script setup>
import { Head } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
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
    cctvs:Array,
})


const selectedCCTV = ref(null)


const zoom = 13

const center = [-2.45,106.15]


const openCCTV = (cctv)=>{

    selectedCCTV.value = cctv

}



const goBack = () => {

    if(window.history.length > 1){
        window.history.back()
    }
    else{
        window.location.href='/'
    }

}

</script>


<template>

<FrontLayout>

<Head title="CCTV Monitoring"/>


<div class="relative max-w-7xl mx-auto px-4 py-8 space-y-6">


<button
@click="goBack"
class="absolute left-4 top-4 rounded-full bg-white border px-3 py-2 shadow z-10"
>
← Kembali
</button>



<!-- HEADER -->

<div
class="pt-14 bg-white rounded-2xl shadow border p-6"
>


<h1 class="text-2xl font-bold text-slate-900">
CCTV Monitoring
</h1>


<p class="text-slate-500 text-sm">
Pantauan kamera CCTV administrator
</p>


</div>





<div class="grid lg:grid-cols-[2fr_1fr] gap-6">



<!-- MAP -->

<div
class="bg-white rounded-2xl shadow border overflow-hidden"
>


<LMap
:zoom="zoom"
:center="center"
class="h-[500px]"
>


<LTileLayer
url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
/>



<template
v-for="cctv in cctvs"
:key="cctv.id"
>


<LMarker
:lat-lng="[cctv.lat,cctv.lng]"
@click="openCCTV(cctv)"
>


<LPopup>


<div class="font-bold">

{{cctv.name}}

</div>


<div class="text-sm">

IP :
{{cctv.ip}}

</div>



<button
@click="openCCTV(cctv)"
class="mt-2 bg-cyan-600 text-white px-3 py-1 rounded text-sm"
>

Lihat CCTV

</button>


</LPopup>


</LMarker>


<<<<<<< HEAD
=======
                <!-- List Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">Daftar CCTV</h2>
                        <span class="text-sm text-slate-400 bg-slate-100 px-3 py-0.5 rounded-full">
                            {{ cctvs?.length ?? 0 }}
                        </span>
                    </div>
                    <div class="space-y-3 max-h-[440px] overflow-y-auto pr-1">
                        <template v-if="cctvs?.length">
                            <div v-for="cctv in cctvs" :key="cctv.id" class="group flex items-start gap-3 rounded-xl border border-slate-200/80 p-4 transition hover:border-cyan-200 hover:shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-800 truncate">{{ cctv.name ?? 'CCTV' }}</span>
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            (cctv.status ?? '').toLowerCase() === 'online'
                                                ? 'bg-green-100 text-green-700'
                                                : (cctv.status ?? '').toLowerCase() === 'offline'
                                                    ? 'bg-red-100 text-red-700'
                                                    : 'bg-slate-100 text-slate-500'
                                        ]">
                                            {{ cctv.status ?? 'Aktif' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 truncate">{{ cctv.location ?? 'Lokasi tidak tersedia' }}</p>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div v-else class="text-center py-10 text-slate-400">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                            </svg>
                            <p class="mt-3 text-sm">Belum ada data CCTV</p>
                            <p class="text-xs">Klik “Tambah CCTV” untuk menambahkan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
>>>>>>> 75d5a7c1104a1ba9e633d12aee6422992cb55399
</template>


</LMap>


</div>





<!-- LIST -->

<div
class="bg-white rounded-2xl shadow border p-5"
>


<h2 class="font-bold text-lg mb-4">
Daftar CCTV
</h2>



<div
v-for="cctv in cctvs"
:key="cctv.id"
@click="openCCTV(cctv)"
class="cursor-pointer border rounded-xl p-4 mb-3 hover:bg-slate-50"
>


<div class="font-semibold">

{{cctv.name}}

</div>


<div class="text-sm text-slate-500">

{{cctv.ip}}

</div>


<span
class="text-xs text-green-600"
>

Online

</span>


</div>



</div>


</div>






<!-- CCTV VIEWER -->


<div
v-if="selectedCCTV"
class="bg-white rounded-2xl shadow border p-5"
>



<div class="flex justify-between items-center mb-4">


<div>

<h2 class="text-xl font-bold">

{{selectedCCTV.name}}

</h2>


<p class="text-sm text-slate-500">

IP :
{{selectedCCTV.ip}}

</p>


</div>


<button
@click="selectedCCTV=null"
class="text-red-500"
>

Tutup

</button>


</div>





<div
class="aspect-video bg-black rounded-xl overflow-hidden"
>


<iframe

:title="selectedCCTV.name"

:src="`http://${selectedCCTV.ip}`"

class="w-full h-full"

frameborder="0"

>


</iframe>


</div>



<p class="text-xs text-slate-400 mt-3">

Stream:
http://{{selectedCCTV.ip}}

</p>


</div>



</div>


</FrontLayout>

</template>



<style scoped>

.leaflet-container{
height:100%;
width:100%;
}

</style>