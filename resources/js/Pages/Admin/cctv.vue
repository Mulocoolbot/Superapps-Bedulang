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