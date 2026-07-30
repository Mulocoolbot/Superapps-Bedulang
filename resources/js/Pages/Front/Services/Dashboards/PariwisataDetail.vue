<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import Footer from '@/Components/Front/Footer.vue'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    destination: {
        type: Object,
        required: true,
    },
    other_destinations: {
        type: Array,
        default: () => [],
    }
})

// Format number
const formatNumber = (val) => {
    if (typeof val === 'string') return val
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

// Category badge colors
const getCategoryBadge = (subcategory) => {
    const map = {
        'Wisata Danau & Alam': { bg: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' },
        'Wisata Bahari & Pulau': { bg: 'bg-sky-500/20 text-sky-300 border-sky-500/30' },
        'Wisata Edukasi & Hutan Konservasi': { bg: 'bg-lime-500/20 text-lime-300 border-lime-500/30' },
        'Akomodasi & Penginapan': { bg: 'bg-violet-500/20 text-violet-300 border-violet-500/30' },
    }
    return map[subcategory] ?? { bg: 'bg-white/10 text-white/80 border-white/20' }
}

// Map embed parsing to inject rounded corners and full width
const parsedMapEmbed = computed(() => {
    let embed = props.destination.map_embed || ''
    if (embed) {
        embed = embed.replace('<iframe ', '<iframe class="w-full h-full rounded-2xl shadow-inner border border-white/10" ')
    }
    return embed
})
</script>

<template>
    <FrontLayout>
        <Head :title="destination.name + ' - ' + service.name" />

        <div 
            class="relative bg-cover bg-center bg-no-repeat min-h-screen text-white"
            style="background-image: url('/services/bg layanan.svg');"
        >
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">
                
                <!-- Back Button -->
                <div class="mb-6">
                    <Link
                        :href="route('layanan.show', service.slug)"
                        class="group inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold text-white transition bg-white/10 hover:bg-white/20 backdrop-blur-md shadow-xs border border-white/10"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 transition-transform group-hover:-translate-x-0.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        Kembali ke Dashboard Pariwisata
                    </Link>
                </div>

                <!-- Main Content Card -->
                <div class="rounded-3xl border border-white/15 bg-white/[0.04] backdrop-blur-xl p-6 sm:p-10 shadow-2xl space-y-10">
                    
                    <!-- Photo Gallery Section -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Main Photo Placeholder -->
                        <div class="md:col-span-3 aspect-[16/9] md:aspect-auto h-64 md:h-[450px] bg-slate-900/60 rounded-3xl overflow-hidden relative group border border-white/10">
                            <div :class="`absolute inset-0 bg-gradient-to-br ${destination.image_gradient} opacity-30`"></div>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-white/60">
                                <span class="text-6xl md:text-8xl mb-3 opacity-60 drop-shadow-lg">{{ destination.subcategory_icon }}</span>
                                <span class="font-bold text-lg text-white/80">Foto Utama Destinasi</span>
                                <span class="text-xs text-white/50">(Akan diupload nanti)</span>
                            </div>
                        </div>
                        <!-- Thumbnail Photos Placeholder -->
                        <div class="hidden md:flex flex-col gap-4">
                            <div v-for="i in 3" :key="i" class="flex-1 bg-slate-900/60 rounded-2xl relative overflow-hidden group border border-white/10">
                                <div :class="`absolute inset-0 bg-gradient-to-br ${destination.image_gradient} opacity-20`"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-white/40 text-xs text-center p-2">
                                    <span class="text-2xl mb-1">📷</span>
                                    <span>Kumpulan Foto</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Container -->
                    <div class="flex flex-col lg:flex-row gap-10">
                        
                        <!-- Left Info -->
                        <div class="flex-1 space-y-8">
                            
                            <!-- Header Info -->
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span :class="[getCategoryBadge(destination.subcategory).bg]" class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1 text-xs font-bold uppercase tracking-wider backdrop-blur-md shadow-sm">
                                        <span>{{ destination.subcategory_icon }}</span>
                                        {{ destination.subcategory }}
                                    </span>
                                </div>

                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white mb-4">
                                    {{ destination.name }}
                                </h1>

                                <div class="flex flex-wrap items-center gap-4 text-sm text-white/80">
                                    <!-- Rating -->
                                    <div class="flex items-center gap-1.5 bg-white/10 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/15">
                                        <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="font-bold text-white">{{ destination.rating }}</span>
                                        <span class="text-white/60">({{ formatNumber(destination.review_count) }} ulasan Google)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="h-px w-full bg-white/10"></div>

                            <!-- About Section -->
                            <div v-if="destination.about" class="space-y-8">
                                <div v-for="(content, title) in destination.about" :key="title" class="space-y-3">
                                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                        <span v-if="title.toLowerCase().includes('gambaran')" class="text-blue-400">📝</span>
                                        <span v-else-if="title.toLowerCase().includes('daya tarik')" class="text-rose-400">✨</span>
                                        <span v-else-if="title.toLowerCase().includes('aktivitas') || title.toLowerCase().includes('fasilitas')" class="text-emerald-400">🏄‍♂️</span>
                                        <span v-else-if="title.toLowerCase().includes('tips') || title.toLowerCase().includes('cocok')" class="text-amber-400">💡</span>
                                        <span v-else class="text-indigo-400">📌</span>
                                        {{ title }}
                                    </h3>
                                    
                                    <div class="text-white/80 leading-relaxed text-sm sm:text-base space-y-2 pl-1">
                                        <template v-if="content.includes('\n')">
                                            <ul class="list-disc pl-5 space-y-2 marker:text-blue-400">
                                                <li v-for="(line, idx) in content.split('\n')" :key="idx">
                                                    {{ line }}
                                                </li>
                                            </ul>
                                        </template>
                                        <template v-else>
                                            <p>{{ content }}</p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Sidebar (Location & Maps) -->
                        <div class="w-full lg:w-80 xl:w-96 shrink-0 space-y-6">
                            <div class="bg-white/[0.06] backdrop-blur-md rounded-3xl p-6 border border-white/15 shadow-xl sticky top-28">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <span>📍</span> Lokasi & Rute
                                </h3>
                                
                                <!-- Map Embed Container -->
                                <div class="aspect-square bg-slate-900/80 rounded-2xl mb-5 overflow-hidden relative border border-white/10">
                                    <div v-if="parsedMapEmbed" v-html="parsedMapEmbed" class="w-full h-full"></div>
                                    <div v-else class="absolute inset-0 flex items-center justify-center text-sm text-white/40">Map tidak tersedia</div>
                                </div>

                                <!-- Action Button -->
                                <a
                                    :href="destination.maps_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-blue-500/20 active:scale-[0.98]"
                                >
                                    Buka di Aplikasi Maps
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Destinasi Lainnya -->
                    <div class="pt-10 border-t border-white/10" v-if="other_destinations && other_destinations.length">
                        <h3 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                            <span>🌟</span> Jelajahi Destinasi Lainnya
                        </h3>
                        
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <Link
                                v-for="dest in other_destinations"
                                :key="dest.id"
                                :href="route('layanan.pariwisata.destinasi', dest.id)"
                                class="group relative overflow-hidden rounded-2xl border border-white/15 bg-white/[0.04] backdrop-blur-md shadow-sm hover:shadow-xl hover:bg-white/[0.08] transition-all duration-300 hover:-translate-y-1"
                            >
                                <div :class="`h-32 bg-gradient-to-br ${dest.image_gradient} relative overflow-hidden`">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-4xl opacity-80 drop-shadow-lg transform group-hover:scale-110 transition-transform duration-500">
                                            {{ dest.subcategory_icon }}
                                        </span>
                                    </div>
                                    <div class="absolute top-2 left-2">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-black/40 backdrop-blur-md border border-white/20 px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">
                                            {{ dest.category === 'wisata' ? 'Wisata' : 'Hotel' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="text-sm font-bold text-white truncate group-hover:text-blue-300 transition-colors">{{ dest.name }}</h4>
                                    <p class="text-xs text-white/50 mt-1 line-clamp-2">{{ dest.description }}</p>
                                    
                                    <div class="mt-3 flex items-center gap-1 text-[11px] font-semibold text-blue-300 group-hover:text-blue-200 transition-colors">
                                        Lihat Detail
                                        <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <Footer />
    </FrontLayout>
</template>
