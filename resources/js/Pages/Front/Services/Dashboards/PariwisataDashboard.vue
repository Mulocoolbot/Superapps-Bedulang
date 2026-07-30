<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    dashboard: {
        type: Object,
        default: () => ({}),
    },
})

const dashboardData = computed(() => props.dashboard ?? {})

// Tab state
const activeTab = ref(dashboardData.value.active_tab ?? 'semua')
const tabs = [
    { id: 'semua', label: 'Semua', icon: '📋' },
    { id: 'wisata', label: 'Wisata', icon: '🏝️' },
    { id: 'akomodasi', label: 'Akomodasi', icon: '🏨' },
]

// Featured destinations (top 3 by rating)
const featured = computed(() => dashboardData.value.featured ?? [])

// All destinations for filtering
const allDestinations = computed(() => dashboardData.value.all_destinations ?? [])

// Filtered and grouped destinations based on active tab
const filteredGroups = computed(() => {
    let filtered = allDestinations.value
    if (activeTab.value === 'wisata') {
        filtered = filtered.filter(d => d.category === 'wisata')
    } else if (activeTab.value === 'akomodasi') {
        filtered = filtered.filter(d => d.category === 'akomodasi')
    }

    // Group by subcategory
    const groups = {}
    filtered.forEach(dest => {
        const sub = dest.subcategory
        if (!groups[sub]) {
            groups[sub] = {
                title: sub,
                icon: dest.subcategory_icon,
                items: [],
            }
        }
        groups[sub].items.push(dest)
    })
    return Object.values(groups)
})

// Summary stats
const summary = computed(() => dashboardData.value.summary ?? {})

// Search functionality
const searchQuery = ref('')
const searchResults = computed(() => {
    if (!searchQuery.value.trim()) return null
    const q = searchQuery.value.toLowerCase()
    return allDestinations.value.filter(d =>
        d.name.toLowerCase().includes(q) ||
        d.description.toLowerCase().includes(q) ||
        d.subcategory.toLowerCase().includes(q)
    )
})

// Render stars
const renderStars = (rating) => {
    const full = Math.floor(rating)
    const hasHalf = rating % 1 >= 0.3
    return { full, hasHalf, empty: 5 - full - (hasHalf ? 1 : 0) }
}

// Format number
const formatNumber = (val) => {
    if (typeof val === 'string') return val
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

// Get gradient classes for featured cards
const featuredGradients = [
    'from-sky-500/90 via-blue-600/90 to-indigo-700/90',
    'from-emerald-500/90 via-teal-600/90 to-cyan-700/90',
    'from-amber-500/90 via-orange-600/90 to-rose-700/90',
]

// Category badge colors
const getCategoryBadge = (subcategory) => {
    const map = {
        'Wisata Danau & Alam': { bg: 'bg-emerald-100 dark:bg-emerald-900/40', text: 'text-emerald-700 dark:text-emerald-400', border: 'border-emerald-200 dark:border-emerald-800' },
        'Wisata Bahari & Pulau': { bg: 'bg-sky-100 dark:bg-sky-900/40', text: 'text-sky-700 dark:text-sky-400', border: 'border-sky-200 dark:border-sky-800' },
        'Wisata Edukasi & Hutan Konservasi': { bg: 'bg-lime-100 dark:bg-lime-900/40', text: 'text-lime-700 dark:text-lime-400', border: 'border-lime-200 dark:border-lime-800' },
        'Akomodasi & Penginapan': { bg: 'bg-violet-100 dark:bg-violet-900/40', text: 'text-violet-700 dark:text-violet-400', border: 'border-violet-200 dark:border-violet-800' },
    }
    return map[subcategory] ?? { bg: 'bg-slate-100 dark:bg-slate-800', text: 'text-slate-700 dark:text-slate-400', border: 'border-slate-200 dark:border-slate-700' }
}

// Subcategory header gradients
const getSubcategoryGradient = (subcategory) => {
    const map = {
        'Wisata Danau & Alam': 'from-emerald-600 to-teal-600',
        'Wisata Bahari & Pulau': 'from-sky-600 to-blue-600',
        'Wisata Edukasi & Hutan Konservasi': 'from-lime-600 to-green-600',
        'Akomodasi & Penginapan': 'from-violet-600 to-purple-600',
    }
    return map[subcategory] ?? 'from-slate-600 to-slate-700'
}
</script>

<template>
    <div class="mt-6 space-y-8">

        <!-- ═══════════════════════════════════════════════ -->
        <!-- Summary Stats Bar                              -->
        <!-- ═══════════════════════════════════════════════ -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 animate-fade-in-up">
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 backdrop-blur-md p-5 shadow-sm transition-all duration-300 hover:bg-white/15 group">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-blue-400/10 blur-xl group-hover:bg-blue-400/20 transition-all duration-500"></div>
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200/80">Destinasi Wisata</span>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-white">{{ summary.total_wisata }}</p>
                <p class="mt-0.5 text-xs text-blue-200/60">Lokasi wisata terdaftar</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 backdrop-blur-md p-5 shadow-sm transition-all duration-300 hover:bg-white/15 group">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-purple-400/10 blur-xl group-hover:bg-purple-400/20 transition-all duration-500"></div>
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200/80">Akomodasi</span>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-white">{{ summary.total_akomodasi }}</p>
                <p class="mt-0.5 text-xs text-blue-200/60">Hotel & penginapan</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 backdrop-blur-md p-5 shadow-sm transition-all duration-300 hover:bg-white/15 group">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-amber-400/10 blur-xl group-hover:bg-amber-400/20 transition-all duration-500"></div>
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200/80">Rating Rata-rata</span>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-white flex items-center gap-1.5">
                    {{ summary.avg_rating }}
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </p>
                <p class="mt-0.5 text-xs text-blue-200/60">Google Maps rating</p>
            </div>
            <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-white/10 backdrop-blur-md p-5 shadow-sm transition-all duration-300 hover:bg-white/15 group">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-emerald-400/10 blur-xl group-hover:bg-emerald-400/20 transition-all duration-500"></div>
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200/80">Total Ulasan</span>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-white">{{ summary.total_reviews }}</p>
                <p class="mt-0.5 text-xs text-blue-200/60">Ulasan Google Maps</p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- 🏆 3 Destinasi Unggulan (Featured)             -->
        <!-- ═══════════════════════════════════════════════ -->
        <section class="animate-fade-in-up [animation-delay:150ms]">
            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-400/20 backdrop-blur-md border border-amber-400/30">
                    <span class="text-lg">🏆</span>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-white tracking-tight">Destinasi Unggulan</h2>
                    <p class="text-xs text-blue-200/60 font-medium">3 destinasi terbaik berdasarkan rating Google Maps</p>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="(dest, index) in featured"
                    :key="dest.id"
                    :href="route('layanan.pariwisata.destinasi', dest.id)"
                    class="group relative overflow-hidden rounded-2xl border border-white/15 shadow-lg transition-all duration-500 hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-1 hover:border-white/30 cursor-pointer"
                    :style="{ animationDelay: `${200 + index * 100}ms` }"
                >
                    <!-- Gradient Background with Pattern -->
                    <div :class="`absolute inset-0 bg-gradient-to-br ${featuredGradients[index]}`"></div>
                    <div class="absolute inset-0 opacity-[0.07]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
                    
                    <!-- Rank Badge -->
                    <div class="absolute top-4 left-4 z-20 flex items-center justify-center w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 shadow-lg">
                        <span class="text-sm font-black text-white">#{{ index + 1 }}</span>
                    </div>

                    <!-- Content -->
                    <div class="relative z-10 p-6 pt-16 sm:p-7 sm:pt-16">
                        <!-- Category Icon -->
                        <span class="text-4xl mb-3 block drop-shadow-lg group-hover:scale-110 transition-transform duration-300">
                            {{ dest.subcategory_icon }}
                        </span>

                        <h3 class="text-xl font-extrabold text-white tracking-tight leading-tight group-hover:underline decoration-2 underline-offset-4">
                            {{ dest.name }}
                        </h3>
                        <p class="mt-2 text-sm text-white/70 leading-relaxed line-clamp-2">
                            {{ dest.description }}
                        </p>

                        <!-- Rating -->
                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex items-center gap-1 bg-white/15 backdrop-blur-md rounded-full px-3 py-1.5 border border-white/20">
                                <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-sm font-bold text-white">{{ dest.rating }}</span>
                            </div>
                            <span class="text-xs text-white/50 font-medium">{{ formatNumber(dest.review_count) }} ulasan</span>
                        </div>

                        <!-- Subcategory Badge -->
                        <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/15 px-3 py-1 text-[11px] font-bold text-white/80 uppercase tracking-wider">
                            <span>{{ dest.subcategory_icon }}</span>
                            <span>{{ dest.subcategory }}</span>
                        </div>

                        <!-- Arrow indicator -->
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-white/60 group-hover:text-white/90 transition-colors">
                            <span>Lihat Detail Destinasi</span>
                            <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </div>
                    </div>

                    <!-- Shine effect on hover -->
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 bg-gradient-to-tr from-transparent via-white/5 to-transparent"></div>
                </Link>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- 📋 Kumpulan Destinasi (Tabbed Directory)       -->
        <!-- ═══════════════════════════════════════════════ -->
        <section class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-lg p-6 sm:p-8 shadow-lg animate-fade-in-up [animation-delay:300ms]">
            <!-- Section Header + Search -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-extrabold text-white tracking-tight flex items-center gap-2">
                        <span>📍</span> Kumpulan Destinasi
                    </h2>
                    <p class="text-xs text-blue-200/60 font-medium mt-1">Jelajahi seluruh destinasi wisata dan akomodasi</p>
                </div>
                <!-- Search -->
                <div class="relative w-full sm:w-72">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari destinasi..."
                        class="w-full h-10 rounded-xl border border-white/15 bg-white/10 backdrop-blur-md pl-10 pr-4 text-sm text-white placeholder-white/30 outline-none transition focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/20"
                    />
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 p-1 rounded-xl bg-white/[0.06] border border-white/10 mb-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg text-sm font-bold transition-all duration-300 cursor-pointer',
                        activeTab === tab.id
                            ? 'bg-white/15 text-white shadow-md border border-white/20 backdrop-blur-md'
                            : 'text-white/50 hover:text-white/80 hover:bg-white/[0.04]'
                    ]"
                >
                    <span>{{ tab.icon }}</span>
                    <span>{{ tab.label }}</span>
                    <span 
                        v-if="activeTab === tab.id"
                        class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-white/15 text-white/70 font-bold"
                    >
                        {{ tab.id === 'semua' ? allDestinations.length : allDestinations.filter(d => tab.id === 'wisata' ? d.category === 'wisata' : d.category === 'akomodasi').length }}
                    </span>
                </button>
            </div>

            <!-- Search Results View -->
            <div v-if="searchResults !== null">
                <div v-if="searchResults.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <span class="text-4xl mb-3">🔍</span>
                    <p class="text-sm text-white/40 font-medium">Tidak ditemukan destinasi untuk "<span class="text-white/70">{{ searchQuery }}</span>"</p>
                </div>
                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="dest in searchResults"
                        :key="dest.id"
                        :href="route('layanan.pariwisata.destinasi', dest.id)"
                        class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/[0.04] backdrop-blur-sm p-5 transition-all duration-300 hover:bg-white/[0.08] hover:border-white/20 hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
                    >
                        <div class="flex items-start gap-4">
                            <div :class="`flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br ${dest.image_gradient} shadow-md shrink-0`">
                                <span class="text-xl">{{ dest.subcategory_icon }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-bold text-white truncate group-hover:text-blue-300 transition-colors">{{ dest.name }}</h4>
                                <p class="text-xs text-white/40 mt-0.5 line-clamp-1">{{ dest.description }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="text-xs font-bold text-white/80">{{ dest.rating }}</span>
                                    </div>
                                    <span class="text-[10px] text-white/30">({{ formatNumber(dest.review_count) }})</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Grouped Destinations View -->
            <div v-else class="space-y-8">
                <div
                    v-for="(group, gIndex) in filteredGroups"
                    :key="group.title"
                    class="animate-fade-in-up"
                    :style="{ animationDelay: `${gIndex * 100}ms` }"
                >
                    <!-- Subcategory Header -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br" :class="getSubcategoryGradient(group.title)">
                            <span class="text-sm">{{ group.icon }}</span>
                        </div>
                        <h3 class="text-base font-bold text-white/90">{{ group.title }}</h3>
                        <div class="flex-1 h-px bg-gradient-to-r from-white/10 to-transparent"></div>
                        <span class="text-[11px] font-semibold text-white/30 px-2 py-0.5 rounded-full bg-white/[0.05] border border-white/10">
                            {{ group.items.length }} tempat
                        </span>
                    </div>

                    <!-- Destination Cards Grid -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="(dest, dIndex) in group.items"
                            :key="dest.id"
                            :href="route('layanan.pariwisata.destinasi', dest.id)"
                            class="group relative overflow-hidden rounded-xl border border-white/[0.08] bg-white/[0.03] backdrop-blur-sm transition-all duration-300 hover:bg-white/[0.07] hover:border-white/20 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/5 cursor-pointer"
                            :style="{ animationDelay: `${dIndex * 50}ms` }"
                        >
                            <!-- Card Top: Gradient Visual -->
                            <div :class="`relative h-28 bg-gradient-to-br ${dest.image_gradient} overflow-hidden`">
                                <!-- SVG Pattern overlay -->
                                <div class="absolute inset-0 opacity-10" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
                                
                                <!-- Center Icon -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-5xl opacity-80 drop-shadow-lg transform group-hover:scale-110 transition-transform duration-500">
                                        {{ dest.subcategory_icon }}
                                    </span>
                                </div>

                                <!-- Rating Pill (top right) -->
                                <div class="absolute top-3 right-3 flex items-center gap-1 bg-black/30 backdrop-blur-md rounded-full px-2.5 py-1 border border-white/10">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs font-bold text-white">{{ dest.rating }}</span>
                                </div>

                                <!-- Category Badge (top left) -->
                                <div class="absolute top-3 left-3">
                                    <span :class="[getCategoryBadge(dest.subcategory).bg, getCategoryBadge(dest.subcategory).text, getCategoryBadge(dest.subcategory).border]" class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                                        {{ dest.category === 'wisata' ? 'Wisata' : 'Hotel' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4">
                                <h4 class="text-sm font-bold text-white leading-tight group-hover:text-blue-300 transition-colors duration-200 line-clamp-1">
                                    {{ dest.name }}
                                </h4>
                                <p class="mt-1.5 text-xs text-white/40 leading-relaxed line-clamp-2">
                                    {{ dest.description }}
                                </p>

                                <div class="mt-3 pt-3 border-t border-white/[0.06] flex items-center justify-between">
                                    <span class="text-[11px] text-white/30 font-medium">
                                        {{ formatNumber(dest.review_count) }} ulasan
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-400/70 group-hover:text-blue-400 transition-colors">
                                        Lihat Detail
                                        <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════ -->
        <!-- 🗺️ Peta Lokasi (Leaflet Map)                   -->
        <!-- ═══════════════════════════════════════════════ -->
        <section class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-lg p-6 sm:p-8 shadow-lg animate-fade-in-up [animation-delay:400ms]">
            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-400/20 backdrop-blur-md border border-emerald-400/30">
                    <span class="text-lg">🗺️</span>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-white tracking-tight">Peta Lokasi</h2>
                    <p class="text-xs text-blue-200/60 font-medium">Sebaran destinasi wisata dan akomodasi di Bangka Tengah</p>
                </div>
            </div>

            <!-- Map Legend -->
            <div class="flex flex-wrap gap-4 mb-4">
                <div class="flex items-center gap-2 text-xs text-white/60">
                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-sm shadow-blue-500/30"></span>
                    <span>Wisata</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-white/60">
                    <span class="w-3 h-3 rounded-full bg-purple-500 shadow-sm shadow-purple-500/30"></span>
                    <span>Akomodasi</span>
                </div>
            </div>

            <!-- Map Container -->
            <div class="rounded-xl overflow-hidden border border-white/10 shadow-inner h-[400px] bg-slate-800/50">
                <div class="flex items-center justify-center h-full text-white/30">
                    <div class="text-center">
                        <span class="text-4xl block mb-3">🗺️</span>
                        <p class="text-sm font-medium">Peta interaktif destinasi pariwisata</p>
                        <p class="text-xs mt-1 text-white/20">Bangka Tengah, Kepulauan Bangka Belitung</p>
                        <!-- Quick links to Maps -->
                        <div class="mt-4 flex flex-wrap gap-2 justify-center">
                            <a
                                v-for="dest in allDestinations.slice(0, 5)"
                                :key="'map-' + dest.id"
                                :href="dest.maps_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-white/[0.06] border border-white/10 text-[11px] font-semibold text-white/50 hover:bg-white/10 hover:text-white/80 transition-all"
                            >
                                <span>{{ dest.subcategory_icon }}</span>
                                {{ dest.name.length > 20 ? dest.name.substring(0, 20) + '…' : dest.name }}
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
