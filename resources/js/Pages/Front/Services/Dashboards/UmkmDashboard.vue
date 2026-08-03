<script setup>
import { ref, computed, watch } from 'vue'

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

// Tab Navigation
const activeUmkmTab = ref('sebaran') // 'sebaran' or 'produk'

// UMKM Sebaran State & Computeds
const selectedSektor = ref('')
const selectedKecamatan = ref('')
const selectedDesa = ref('')
const selectedTahun = ref('')

const formatNumber = (val) => {
    return new Intl.NumberFormat('id-ID').format(val || 0)
}

const availableSektors = computed(() => {
    const list = new Set((props.dashboard?.umkm_list ?? []).map(item => item.sektor_usaha))
    return Array.from(list).sort()
})

const availableKecamatans = computed(() => {
    const list = new Set((props.dashboard?.umkm_list ?? []).map(item => item.kecamatan))
    return Array.from(list).sort()
})

const availableDesas = computed(() => {
    let list = props.dashboard?.umkm_list ?? []
    if (selectedKecamatan.value) {
        list = list.filter(item => item.kecamatan === selectedKecamatan.value)
    }
    const set = new Set(list.map(item => item.desa_kel))
    return Array.from(set).sort()
})

const availableYears = computed(() => {
    const list = new Set((props.dashboard?.umkm_list ?? []).map(item => item.tahun))
    return Array.from(list).sort().reverse()
})

watch(selectedKecamatan, () => {
    selectedDesa.value = ''
})

const filteredUmkm = computed(() => {
    let list = props.dashboard?.umkm_list ?? []
    
    if (selectedSektor.value) {
        list = list.filter(item => item.sektor_usaha === selectedSektor.value)
    }
    if (selectedKecamatan.value) {
        list = list.filter(item => item.kecamatan === selectedKecamatan.value)
    }
    if (selectedDesa.value) {
        list = list.filter(item => item.desa_kel === selectedDesa.value)
    }
    if (selectedTahun.value) {
        list = list.filter(item => item.tahun == selectedTahun.value)
    }
    
    return list
})

const totalUmkm = computed(() => {
    return filteredUmkm.value.reduce((acc, item) => acc + item.total, 0)
})

const currentPage = ref(1)
const itemsPerPage = ref(10)

const paginatedUmkm = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredUmkm.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(filteredUmkm.value.length / itemsPerPage.value)
})

watch(filteredUmkm, () => {
    currentPage.value = 1
})

const resetUmkmFilters = () => {
    selectedSektor.value = ''
    selectedKecamatan.value = ''
    selectedDesa.value = ''
    selectedTahun.value = ''
}

// UMKM Produk Unggulan State & Computeds
const productSearchQuery = ref('')
const selectedProductSector = ref('')

const mockProducts = [
    {
        name: 'Kemplang Panggang Khas Kurau',
        owner: 'Ibu Hajjah Maryam',
        sector: 'Kuliner (Makanan & Minuman)',
        district: 'Koba',
        village: 'Kurau',
        price: 'Rp 25.000 / bungkus',
        phone: '6281273881234',
        image: '/services/products/kemplang.jpg',
        description: 'Kerupuk kemplang ikan khas Bangka yang dipanggang secara tradisional dengan sambal terasi manis pedas.'
    },
    {
        name: 'Madu Hitam Pahit Namang',
        owner: 'Pak Suharli',
        sector: 'Pertanian & Agribisnis',
        district: 'Namang',
        village: 'Namang',
        price: 'Rp 120.000 / botol',
        phone: '6285269004321',
        image: '/services/products/madu.jpg',
        description: 'Madu hutan asli Bangka Tengah dari lebah liar pohon Pelawan yang berkhasiat tinggi untuk kesehatan.'
    },
    {
        name: 'Kopi Robusta Arang Beluluk',
        owner: 'CV Bangka Coffee',
        sector: 'Kuliner (Makanan & Minuman)',
        district: 'Pangkalan Baru',
        village: 'Beluluk',
        price: 'Rp 45.000 / 250gr',
        phone: '6281989776655',
        image: '/services/products/kopi.jpg',
        description: 'Kopi robusta pilihan dengan proses roasting menggunakan kayu arang alami untuk aroma yang mantap.'
    },
    {
        name: 'Batik Cual Bangka Tengah',
        owner: 'Sanggar Cual Lestari',
        sector: 'Fashion & Tekstil',
        district: 'Koba',
        village: 'Kelurahan Berok',
        price: 'Rp 350.000 / meter',
        phone: '6281234567890',
        image: '/services/products/batik_cual.jpg',
        description: 'Kain cual tenun tradisional khas Bangka dengan motif flora dan fauna laut khas Kepulauan Bangka Belitung.'
    }
]

const filteredProducts = computed(() => {
    let list = mockProducts
    if (productSearchQuery.value) {
        const query = productSearchQuery.value.toLowerCase()
        list = list.filter(p => p.name.toLowerCase().includes(query) || p.owner.toLowerCase().includes(query))
    }
    if (selectedProductSector.value) {
        list = list.filter(p => p.sector === selectedProductSector.value)
    }
    return list
})
</script>

<template>
    <div class="mt-4 space-y-6 animate-fade-in-up font-sans text-slate-700 dark:text-slate-350">
        
        <!-- SUB-TAB NAVIGATION -->
        <div class="flex border-b border-slate-200/80 dark:border-slate-800 gap-6">
            <button
                @click="activeUmkmTab = 'sebaran'"
                class="pb-3.5 text-sm font-bold transition-all relative cursor-pointer"
                :class="activeUmkmTab === 'sebaran'
                    ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                    : 'text-slate-400 hover:text-slate-650 dark:hover:text-slate-300'"
            >
                Sebaran Wilayah UMKM
            </button>
            <button
                @click="activeUmkmTab = 'produk'"
                class="pb-3.5 text-sm font-bold transition-all relative cursor-pointer"
                :class="activeUmkmTab === 'produk'
                    ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                    : 'text-slate-400 hover:text-slate-650 dark:hover:text-slate-300'"
            >
                Produk Unggulan UMKM
            </button>
        </div>

        <!-- 💼 VIEW 1: SEBARAN WILAYAH -->
        <div v-if="activeUmkmTab === 'sebaran'" class="space-y-6 animate-fade-in-up">
            <!-- STATS CARD ROW -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Card 1: Total UMKM -->
                <div class="relative overflow-hidden rounded-3xl border border-slate-200/70 dark:border-slate-800/80 bg-white dark:bg-slate-900 p-6 shadow-xs flex items-center gap-5 transition-all hover:-translate-y-0.5 hover:shadow-md duration-300">
                    <div class="absolute -right-10 -bottom-10 h-32 w-32 rounded-full bg-blue-500/5 blur-2xl pointer-events-none dark:bg-blue-500/10" />
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl shrink-0">
                        💼
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total UMKM Terdata</span>
                        <h3 class="text-3xl font-bold text-slate-950 dark:text-white font-sans tracking-tight mt-0.5">
                            {{ formatNumber(totalUmkm) }} <span class="text-sm font-semibold text-slate-400 dark:text-slate-500">Usaha</span>
                        </h3>
                    </div>
                </div>

                <!-- Card 2: Tahun Data -->
                <div class="relative overflow-hidden rounded-3xl border border-slate-200/70 dark:border-slate-800/80 bg-white dark:bg-slate-900 p-6 shadow-xs flex items-center gap-5 transition-all hover:-translate-y-0.5 hover:shadow-md duration-300">
                    <div class="absolute -right-10 -bottom-10 h-32 w-32 rounded-full bg-cyan-500/5 blur-2xl pointer-events-none dark:bg-cyan-500/10" />
                    <div class="w-14 h-14 rounded-2xl bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl shrink-0">
                        📅
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tahun Data</span>
                        <h3 class="text-2xl font-bold text-slate-950 dark:text-white font-sans tracking-tight mt-0.5">
                            {{ selectedTahun ? selectedTahun : 'Semua (2024 - 2026)' }}
                        </h3>
                    </div>
                </div>
            </div>

            <!-- FILTER CONTROLS -->
            <div class="bg-slate-50/50 dark:bg-slate-950/20 p-6 rounded-3xl border border-slate-200/60 dark:border-slate-800/85 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200/40 pb-3 dark:border-slate-800/60">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Filter Data UMKM</h4>
                    <button 
                        v-if="selectedSektor || selectedKecamatan || selectedDesa || selectedTahun"
                        @click="resetUmkmFilters"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 transition-colors cursor-pointer"
                    >
                        Reset Filter
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                    <!-- Filter Sektor Usaha -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Sektor Usaha</label>
                        <div class="relative">
                            <select
                                v-model="selectedSektor"
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-350 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
                            >
                                <option value="">Semua Sektor</option>
                                <option v-for="sektor in availableSektors" :key="sektor" :value="sektor">{{ sektor }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Kecamatan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Kecamatan</label>
                        <div class="relative">
                            <select
                                v-model="selectedKecamatan"
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-350 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
                            >
                                <option value="">Semua Kecamatan</option>
                                <option v-for="kec in availableKecamatans" :key="kec" :value="kec">Kec. {{ kec }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Desa / Kelurahan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Desa / Kelurahan</label>
                        <div class="relative">
                            <select
                                v-model="selectedDesa"
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-350 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
                            >
                                <option value="">Semua Desa/Kel</option>
                                <option v-for="desa in availableDesas" :key="desa" :value="desa">{{ desa }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Tahun -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">Tahun</label>
                        <div class="relative">
                            <select
                                v-model="selectedTahun"
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-350 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
                            >
                                <option value="">Semua Tahun</option>
                                <option v-for="year in availableYears" :key="year" :value="year">Tahun {{ year }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/90 shadow-xs p-6 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Daftar Sebaran UMKM
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                            Menampilkan {{ filteredUmkm.length }} data hasil agregasi sektor wilayah
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                                <th class="py-3.5 px-4 text-center w-16">No</th>
                                <th class="py-3.5 px-4">Desa / Kelurahan</th>
                                <th class="py-3.5 px-4">Kecamatan</th>
                                <th class="py-3.5 px-4">Sektor Usaha</th>
                                <th class="py-3.5 px-4 text-right">Jumlah UMKM</th>
                                <th class="py-3.5 px-4 text-center w-24">Tahun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            <tr
                                v-for="(item, idx) in paginatedUmkm"
                                :key="idx"
                                class="text-sm text-slate-600 dark:text-slate-350 hover:bg-blue-50/5 dark:hover:bg-slate-800/30 transition-colors"
                            >
                                <td class="py-4 px-4 text-center font-mono text-xs text-slate-400 dark:text-slate-500">
                                    {{ (currentPage - 1) * itemsPerPage + idx + 1 }}
                                </td>
                                <td class="py-4 px-4 text-slate-900 dark:text-100 font-semibold">
                                    {{ item.desa_kel }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center rounded-full bg-slate-50 dark:bg-slate-800/60 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300 border border-slate-200/30 dark:border-slate-700/30">
                                        {{ item.kecamatan }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-normal text-slate-600 dark:text-slate-300">
                                    {{ item.sektor_usaha }}
                                </td>
                                <td class="py-4 px-4 text-right font-mono font-semibold text-blue-600 dark:text-blue-400">
                                    {{ formatNumber(item.total) }}
                                </td>
                                <td class="py-4 px-4 text-center font-mono text-slate-400 dark:text-slate-500">
                                    {{ item.tahun }}
                                </td>
                            </tr>
                            <tr v-if="filteredUmkm.length === 0">
                                <td colspan="6" class="py-16 text-center text-sm text-slate-450 dark:text-slate-550 font-medium">
                                    Tidak ada data UMKM yang cocok dengan penyaringan Anda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION CONTROLS -->
                <div v-if="totalPages > 1" class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">
                        Menampilkan Halaman {{ currentPage }} dari {{ totalPages }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button
                            @click="currentPage > 1 && currentPage--"
                            :disabled="currentPage === 1"
                            class="px-4 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 dark:bg-slate-800/50 dark:hover:bg-slate-800 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-350 transition-all duration-200 disabled:opacity-30 disabled:hover:bg-slate-50 dark:disabled:hover:bg-slate-800/50 cursor-pointer"
                        >
                            Sebelumnya
                        </button>
                        <button
                            @click="currentPage < totalPages && currentPage++"
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/60 dark:bg-slate-800/50 dark:hover:bg-slate-800 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-355 transition-all duration-200 disabled:opacity-30 disabled:hover:bg-slate-50 dark:disabled:hover:bg-slate-800/50 cursor-pointer"
                        >
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🛍️ VIEW 2: PRODUK UNGGULAN -->
        <div v-else class="space-y-6 animate-fade-in-up">
            <!-- PRODUCT FILTERS -->
            <div class="bg-slate-50/50 dark:bg-slate-950/20 p-5 rounded-3xl border border-slate-200/60 dark:border-slate-800/85 flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="relative w-full md:w-72 font-sans">
                    <input
                        type="text"
                        v-model="productSearchQuery"
                        placeholder="Cari nama produk / pemilik..."
                        class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition"
                    />
                </div>
                <div class="relative w-full md:w-64 font-sans">
                    <select
                        v-model="selectedProductSector"
                        class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-350 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
                    >
                        <option value="">Semua Kategori</option>
                        <option value="Kuliner (Makanan & Minuman)">Kuliner</option>
                        <option value="Pertanian & Agribisnis">Pertanian & Agribisnis</option>
                        <option value="Perikanan & Olahan Laut">Perikanan & Olahan Laut</option>
                        <option value="Fashion & Tekstil">Fashion & Tekstil</option>
                        <option value="Kerajinan (Kriya)">Kerajinan (Kriya)</option>
                    </select>
                </div>
            </div>

            <!-- PRODUCT GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="(product, index) in filteredProducts"
                    :key="index"
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/90 overflow-hidden shadow-xs hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col group"
                >
                    <!-- Product Photo -->
                    <div class="h-52 overflow-hidden bg-slate-50 dark:bg-slate-950 relative shrink-0">
                        <img
                            :src="product.image"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            :alt="product.name"
                        />
                        <span class="absolute top-4 left-4 bg-blue-600/90 backdrop-blur-md text-white text-[10px] uppercase font-bold tracking-wider px-3 py-1 rounded-full shadow-sm">
                            {{ product.sector.split(' ')[0] }}
                        </span>
                    </div>

                    <!-- Product Details -->
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-1.5">
                            <h4 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-1">
                                {{ product.name }}
                            </h4>
                            <p class="text-xs text-slate-400 dark:text-slate-500 line-clamp-2">
                                {{ product.description }}
                            </p>
                        </div>

                        <div class="space-y-2 border-t border-slate-100 dark:border-slate-800/60 pt-3">
                            <!-- Owner Name -->
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Pemilik Usaha</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ product.owner }}</span>
                            </div>
                            <!-- Location -->
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Asal Wilayah</span>
                                <span class="font-medium text-slate-600 dark:text-slate-300">
                                    {{ product.village }}, Kec. {{ product.district }}
                                </span>
                            </div>
                        </div>

                        <!-- Price & WA Action -->
                        <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-4">
                            <div>
                                <span class="block text-[10px] uppercase tracking-wider font-semibold text-slate-450">Estimasi Harga</span>
                                <span class="text-sm font-extrabold text-blue-650 dark:text-blue-450 font-mono mt-0.5 block">
                                    {{ product.price }}
                                </span>
                            </div>
                            
                            <a
                                :href="`https://wa.me/${product.phone}?text=Halo%20${encodeURIComponent(product.owner)},%20saya%20tertarik%20dengan%20produk%20UMKM%2520${encodeURIComponent(product.name)}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition-colors duration-200 cursor-pointer"
                            >
                                <span>📱 Hubungi WA</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPTY state -->
            <div v-if="filteredProducts.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl">
                <span class="text-3xl block mb-2">🛍️</span>
                <p class="text-sm text-slate-450 dark:text-slate-550 font-medium">Tidak ada produk unggulan yang cocok dengan pencarian.</p>
            </div>
        </div>

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
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.custom-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 2.5rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem 1.25rem;
}

.dark .custom-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
}
</style>
