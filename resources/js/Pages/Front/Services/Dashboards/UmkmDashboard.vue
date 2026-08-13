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
    // Bahek Ewaki
    {
        brand: 'Bahek Ewaki',
        owner: 'Andreas Maulana',
        sector: 'Olahan Perikanan',
        district: 'Koba',
        village: 'Kurau Barat',
        phone: '6281273880001',
        description: 'Produsen getas dan ampiang ikan tenggiri khas Kurau yang renyah, gurih, dan diproses secara higienis.',
        items: [
            { name: 'Getas Ikan 60gr', image: '/services/products/BW_Getas Ikan.jpeg', price: 'Rp 15.000 / bungkus' },
            { name: 'Getas Udang 60gr', image: '/services/products/BW_Getas Udang.jpeg', price: 'Rp 15.000 / bungkus' },
            { name: 'Getas Telor Kepiting 60gr', image: '/services/products/BW_Telor Kepiting.jpeg', price: 'Rp 15.000 / bungkus' },
            { name: 'Getas Cumi 60gr', image: '/services/products/BW_Getas Cumi.jpeg', price: 'Rp 15.000 / bungkus' },
            { name: 'Getas Ikan 90gr', image: '/services/products/BW_Getas Ikan 90gr.jpeg', price: 'Rp 10.000 / bungkus' },
            { name: 'Getas Tinta Cumi 60gr', image: '/services/products/BW_Getas Tinta Cumi.jpeg', price: 'Rp 15.000 / bungkus' },
            { name: 'Ampiang Ikan 90gr', image: '/services/products/BW_Ampiang Ikan 90gr.jpeg', price: 'Rp 10.000 / bungkus' },
            { name: 'Getas Cumi 80gr', image: '/services/products/BW_Getas Cumi 80gr.jpeg', price: 'Rp - / bungkus' },
            { name: 'Getas Udang 80gr', image: '/services/products/BW_Getas Udang 80gr.jpeg', price: 'Rp - / bungkus' },
            { name: 'Ampiang Udang 80gr', image: '/services/products/BW_Ampiang Udang 80gr.jpeg', price: 'Rp - / bungkus' },
            { name: 'Getas Telor Kepiting 80gr', image: '/services/products/BW_Getas Telor Kepiting 80gr.jpeg', price: 'Rp - / bungkus' },
            { name: 'Ampiang Cumi 80gr', image: '/services/products/BW_Ampiang Cumi 80gr.jpeg', price: 'Rp - / bungkus' },
            { name: 'Getas Tinta Cumi 80gr', image: '/services/products/BW_Getas Tinta Cumi 80gr.jpeg', price: 'Rp - / bungkus' },
            { name: 'Ampiang Tinta Cumi 80gr', image: '/services/products/BW_Ampiang Tinta Cumi 80gr.jpeg', price: 'Rp - / bungkus' }
        ]
    },
    // Mama Nyongs
    {
        brand: 'Mama Nyongs',
        owner: 'Matthew',
        sector: 'Olahan Perikanan',
        district: 'Koba',
        village: 'Simpang Perlang',
        phone: '6281367273219',
        description: 'Spesialis sambelingkung (abon ikan) khas Bangka yang terbuat dari bahan-bahan segar pilihan berkualitas.',
        items: [
            { name: 'Sambelingkung Tenggiri 100gr', image: '/services/products/MN_Sambelingkung Tenggiri 100gr.jpeg', price: 'Rp 52.000 / bungkus' },
            { name: 'Sambelingkung Tenggiri 250gr', image: '/services/products/MN_Sambelingkung Tenggiri 250gr.jpeg', price: 'Rp 130.000 / bungkus' },
            { name: 'Sambelingkung Ciu 250gr', image: '/services/products/MN_Sambelingkung Ciu 250gr.jpeg', price: 'Rp 92.500 / bungkus' }
        ]
    },
    // Aliana
    {
        brand: 'Aliana',
        owner: 'Hj. Elis Nurbaini',
        sector: 'Olahan Pertanian & Perkebunan',
        district: 'Koba',
        village: 'Simpang Perlang',
        phone: '6281278095282',
        description: 'Memproduksi aneka olahan jeruk kunci segar dan teh daun gaharu yang menyehatkan.',
        items: [
            { name: 'Gaharu Tubruk', image: '/services/products/AL_Gaharu Tubruk.jpeg', price: 'Rp - / bungkus' },
            { name: 'Jeruk Kunci', image: '/services/products/AL_Jeruk Kunci.jpeg', price: 'Rp - / botol' },
            { name: 'Jeruk Kunci', image: '/services/products/AL_Jeruk Kunci 2.jpeg', price: 'Rp - / botol' },
            { name: 'Gaharu Jeruk', image: '/services/products/AL_Gaharu Jeruk.jpeg', price: 'Rp - / botol' },
            { name: 'Teh Celup Gaharu', image: '/services/products/AL_Teh Celup Gaharu.jpeg', price: 'Rp - / kotak' }
        ]
    },
    // Haita
    {
        brand: 'Haita',
        owner: 'Tuti Herawati',
        sector: 'Olahan Pertanian & Perkebunan',
        district: 'Koba',
        village: 'Arung Dalam',
        phone: '628117171897',
        description: 'Menyediakan bubuk jahe merah instan dan minuman jahe merah segar yang menghangatkan tubuh.',
        items: [
            { name: 'Jahe Merah Bubuk', image: '/services/products/HT_Jahe Merah Bubuk.jpeg', price: 'Rp - / pack' },
            { name: 'Jahe Merah Bubuk 250gr', image: '/services/products/HT_Jahe Merah Bubuk 250gr.jpeg', price: 'Rp - / pack' },
            { name: 'Minuman Jahe Merah 250ml', image: '/services/products/HT_Minuman Jahe Merah 250ml.jpeg', price: 'Rp - / botol' }
        ]
    },
    // Gurih DD
    {
        brand: 'Gurih DD',
        owner: 'Daddi',
        sector: 'Olahan Perikanan',
        district: 'Koba',
        village: 'Kurau Barat',
        phone: '6285769206830',
        description: 'Produsen ampiang dan kericu olahan cumi, udang, serta kepiting dengan cita rasa seafood asli.',
        items: [
            { name: 'Ampiang Cumi', image: '/services/products/GD_Ampiang Cumi.jpeg', price: 'Rp - / bungkus' },
            { name: 'Kericu Cumi', image: '/services/products/GD_Kericu Cumi.jpeg', price: 'Rp - / bungkus' },
            { name: 'Ampiang Kepiting', image: '/services/products/GD_Ampiang Kepiting.jpeg', price: 'Rp - / bungkus' },
            { name: 'Sarang Burung', image: '/services/products/GD_Sarang Burung.jpeg', price: 'Rp - / bungkus' },
            { name: 'Kericu Udang', image: '/services/products/GD_Kericu Udang.jpeg', price: 'Rp - / bungkus' }
        ]
    },
    // Coffee Pading
    {
        brand: 'Coffee Pading',
        owner: 'Basori',
        sector: 'Olahan Pertanian & Perkebunan',
        district: 'Lubuk Pabrik',
        village: 'Lubuk Besar',
        phone: '6282279316256',
        description: 'Menghadirkan kopi robusta khas lereng Bukit Pading yang diproses secara tradisional.',
        items: [
            { name: 'Kopi Bubuk Robusta', image: '/services/products/CP_Kopi Bubuk Robusta.jpeg', price: 'Rp - / pack' },
            { name: 'Kopi Bubuk Robusta 200gr', image: '/services/products/CP_Kopi Bubuk Robusta 200gr.jpeg', price: 'Rp - / pack' }
        ]
    },
    // Madu Pelawan
    {
        brand: 'Madu Pelawan',
        owner: 'Madu Pelawan',
        sector: 'Olahan Pertanian & Perkebunan',
        district: 'Namang',
        village: 'Namang',
        phone: '6281373257160',
        description: 'Madu langka berkhasiat dari lebah liar penghisap nektar bunga pohon Pelawan di Hutan Namang.',
        items: [
            { name: 'Madu Pelawan', image: '/services/products/MP_Madu Pelawan.jpeg', price: 'Rp - / botol' },
            { name: 'Madu Pelawan', image: '/services/products/MP_Madu Pelawan 2.jpeg', price: 'Rp - / botol' }
        ]
    },
    // Subiarti
    {
        brand: 'Rusip Subiarti',
        owner: 'Subiarti',
        sector: 'Olahan Perikanan',
        district: 'Pangkalan Baru',
        village: 'Batu Belubang',
        phone: '6285373756651',
        description: 'Spesialis rusip tradisional Bangka dari fermentasi ikan bilis segar yang kaya rasa.',
        items: [
            { name: 'Rusip', image: '/services/products/SB_Rusip.jpeg', price: 'Rp - / botol' },
            { name: 'Rusip Bilis', image: '/services/products/SB_Rusip Bilis.jpeg', price: 'Rp - / botol' }
        ]
    },
    // Ai Studio
    {
        brand: 'Ai Collection',
        owner: 'Ega',
        sector: 'Fashion & Tekstil',
        district: 'Koba',
        village: 'Lubuk Besar',
        phone: '6283160094248',
        description: 'Menghasilkan aneka tas rajut (crochet) buatan tangan yang modern, trendi, dan fungsional.',
        items: [
            { name: 'Tas Pink Kuning', image: '/services/products/AS_Yellow Pink.jpeg', price: 'Rp -' },
            { name: 'Tas Bebek', image: '/services/products/AS_Duck.jpeg', price: 'Rp -' },
            { name: 'Tas Crochet', image: '/services/products/AS_Tas Crochet.jpeg', price: 'Rp -' },
            { name: 'Tas Orange', image: '/services/products/AS_Tas Orange.jpeg', price: 'Rp -' }
        ]
    },
    // TG
    {
        brand: 'Andi Craft',
        owner: 'Andi',
        sector: 'Fashion & Tekstil',
        district: 'Sungaiselan',
        village: 'Sungaiselan',
        phone: '6281373257902',
        description: 'Menyediakan aneka model tas fashion wanita yang elegan, kekinian, dan berkualitas tinggi.',
        items: [
            { name: 'Tas Coklat', image: '/services/products/TG_Coklat.jpeg', price: 'Rp -' },
            { name: 'Tas White Flower', image: '/services/products/TG_White Flower.jpeg', price: 'Rp -' },
            { name: 'Tas Biru', image: '/services/products/TG_Blue.jpeg', price: 'Rp -' },
            { name: 'Tas Yellow', image: '/services/products/TG_Yellow.jpeg', price: 'Rp -' }
        ]
    },
    // DB
    {
        brand: 'Dambus Mang Kus',
        owner: 'Kusnadi',
        sector: 'Kerajinan (Kriya)',
        district: 'Koba',
        village: 'Namang',
        phone: '6281995687916',
        description: 'Pengrajin alat musik petik dambus tradisional khas Bangka dari kayu berkualitas.',
        items: [
            { name: 'Dambus', image: '/services/products/DB_Dambus 1.jpeg', price: 'Rp -' },
            { name: 'Dambus', image: '/services/products/DB_Dambus 2.jpeg', price: 'Rp -' },
            { name: 'Alat Musik Tradisional', image: '/services/products/DB_Alat Musik.jpeg', price: 'Rp -' }
        ]
    },
    // MP
    {
        brand: 'Kerajinan Lidi Kelapa Hamsa',
        owner: 'H. Hamsa',
        sector: 'Kerajinan (Kriya)',
        district: 'Koba',
        village: 'Kurau Barat',
        phone: '6285378222834',
        description: 'Memproduksi miniatur perahu kayu tradisional khas daerah pesisir Bangka Tengah.',
        items: [
            { name: 'Miniatur Perahu', image: '/services/products/MP_Miniatur Perahu.jpeg', price: 'Rp -' }
        ]
    },
    // RS
    {
        brand: 'Kopiah Resam Hesti',
        owner: 'Hesti',
        sector: 'Kerajinan (Kriya)',
        district: 'Simpang Katis',
        village: 'Lampur',
        phone: '6282374381176',
        description: 'Spesialis produk kerajinan anyaman tradisional dari bahan alami serat tanaman resam.',
        items: [
            { name: 'Resam', image: '/services/products/RS_Resam 1.jpeg', price: 'Rp -' },
            { name: 'Resam', image: '/services/products/RS_Resam 2.jpeg', price: 'Rp -' }
        ]
    },
    // AM
    {
        brand: 'Kerajinan Lidi Nipah Radea Craft',
        owner: 'Linda',
        sector: 'Kerajinan (Kriya)',
        district: 'Koba',
        village: 'Kurau',
        phone: '6283173032642',
        description: 'Pengrajin aneka produk anyaman bambu dan serat alam khas desa Belilik.',
        items: [
            { name: 'Produk Anyaman', image: '/services/products/AM_Produk Anyaman.jpeg', price: 'Rp -' },
            { name: 'Produk Anyaman', image: '/services/products/AM_Produk Anyaman 2.jpeg', price: 'Rp -' },
            { name: 'Produk Anyaman', image: '/services/products/AM_Produk Anyaman 3.jpeg', price: 'Rp -' }
        ]
    },
    // AB
    {
        brand: 'Kerajinan Akar Kekembat',
        owner: 'Rojali',
        sector: 'Kerajinan (Kriya)',
        district: 'Namang',
        village: 'Namang',
        phone: '6281244440015',
        description: 'Menghasilkan aneka kap lampu, topi, dan vas bunga estetik dari kerajinan anyaman tangan.',
        items: [
            { name: 'Anyaman Vas Bunga', image: '/services/products/AB_Anyaman Vas Bunga.jpeg', price: 'Rp -' },
            { name: 'Anyaman Vas Daun', image: '/services/products/AB_Anyaman Vas Daun.jpeg', price: 'Rp -' },
            { name: 'Anyaman Vas Bunga Kecil', image: '/services/products/AB_Anyaman Vas Bunga Kecil.jpeg', price: 'Rp -' },
            { name: 'Anyaman Topi', image: '/services/products/AB_Anyaman Topi.jpeg', price: 'Rp -' },
            { name: 'Anyaman Topi Besar', image: '/services/products/AB_Anyaman Topi Besar.jpeg', price: 'Rp -' },
            { name: 'Anyaman Lampu', image: '/services/products/AB_Anyaman Lampu.jpeg', price: 'Rp -' },
            { name: 'Anyaman Lampu Gantung', image: '/services/products/AB_Anyaman Lampu Gantung.jpeg', price: 'Rp -' }
        ]
    }
]

const activeImageIndexes = ref({})

const prevImage = (product) => {
    const idx = activeImageIndexes.value[product.brand] || 0
    const len = product.items.length
    activeImageIndexes.value[product.brand] = (idx - 1 + len) % len
}

const nextImage = (product) => {
    const idx = activeImageIndexes.value[product.brand] || 0
    const len = product.items.length
    activeImageIndexes.value[product.brand] = (idx + 1) % len
}

const setImageIndex = (product, idx) => {
    activeImageIndexes.value[product.brand] = idx
}

const selectedBrandForDetail = ref(null)
const activeDetailImageIndex = ref(0)

const openBrandDetail = (brand) => {
    selectedBrandForDetail.value = brand
    activeDetailImageIndex.value = activeImageIndexes.value[brand.brand] || 0
}

const closeBrandDetail = () => {
    selectedBrandForDetail.value = null
}

const prevDetailImage = () => {
    if (!selectedBrandForDetail.value) return
    const len = selectedBrandForDetail.value.items.length
    activeDetailImageIndex.value = (activeDetailImageIndex.value - 1 + len) % len
}

const nextDetailImage = () => {
    if (!selectedBrandForDetail.value) return
    const len = selectedBrandForDetail.value.items.length
    activeDetailImageIndex.value = (activeDetailImageIndex.value + 1) % len
}

const setDetailImageIndex = (idx) => {
    activeDetailImageIndex.value = idx
}

const filteredProducts = computed(() => {
    let list = mockProducts
    if (productSearchQuery.value) {
        const query = productSearchQuery.value.toLowerCase()
        list = list.filter(p => 
            p.brand.toLowerCase().includes(query) ||
            p.owner.toLowerCase().includes(query) || 
            p.description.toLowerCase().includes(query) ||
            p.items.some(item => item.name.toLowerCase().includes(query))
        )
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
        <div class="flex border-b border-white/20 gap-6">
            <button
                @click="activeUmkmTab = 'sebaran'"
                class="pb-3.5 text-sm font-semibold transition-all relative cursor-pointer"
                :class="activeUmkmTab === 'sebaran'
                    ? 'text-white border-b-2 border-white'
                    : 'text-blue-100/75 hover:text-white'"
            >
                Sebaran Wilayah UMKM
            </button>
            <button
                @click="activeUmkmTab = 'produk'"
                class="pb-3.5 text-sm font-semibold transition-all relative cursor-pointer"
                :class="activeUmkmTab === 'produk'
                    ? 'text-white border-b-2 border-white'
                    : 'text-blue-100/75 hover:text-white'"
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
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">Filter Data UMKM</h4>
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
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
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
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
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
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
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
                                class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
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
                        <option value="Olahan Pertanian & Perkebunan">Olahan Pertanian & Perkebunan</option>
                        <option value="Olahan Perikanan">Olahan Perikanan</option>
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
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/90 overflow-hidden shadow-xs hover:-translate-y-1 hover:shadow-md hover:border-blue-500/50 dark:hover:border-blue-500/50 transition-all duration-300 flex flex-col group cursor-pointer"
                    @click="openBrandDetail(product)"
                >
                    <!-- Product Photo -->
                    <div class="h-52 overflow-hidden bg-slate-50 dark:bg-slate-950 relative shrink-0">
                        <img
                            :src="product.items[activeImageIndexes[product.brand] || 0].image"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            :alt="product.items[activeImageIndexes[product.brand] || 0].name"
                        />
                        <template v-if="product.items.length > 1">
                            <!-- Left Arrow -->
                            <button
                                @click.stop="prevImage(product)"
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity focus:outline-hidden z-10 cursor-pointer"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <!-- Right Arrow -->
                            <button
                                @click.stop="nextImage(product)"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity focus:outline-hidden z-10 cursor-pointer"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                            <!-- Dots -->
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10 bg-black/35 backdrop-blur-md py-1.5 px-2.5 rounded-full max-w-[90%] overflow-x-auto scrollbar-none">
                                <span
                                    v-for="(item, idx) in product.items"
                                    :key="idx"
                                    @click.stop="setImageIndex(product, idx)"
                                    class="w-1.5 h-1.5 rounded-full transition-all duration-300 cursor-pointer shrink-0"
                                    :class="idx === (activeImageIndexes[product.brand] || 0) ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/70'"
                                />
                            </div>
                        </template>
                        <span class="absolute top-4 left-4 bg-blue-600/90 backdrop-blur-md text-white text-[10px] uppercase font-bold tracking-wider px-3 py-1 rounded-full shadow-sm">
                            {{ product.sector }}
                        </span>
                    </div>

                    <!-- Product Details -->
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ product.brand }}
                                </h4>
                                <span class="text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 px-2.5 py-0.5 rounded-full">
                                    {{ product.items.length }} Produk
                                </span>
                            </div>
                            <h5 class="text-[11px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                                Owner: {{ product.owner }}
                            </h5>
                            <h5 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-200 mt-1.5 line-clamp-1">
                                {{ product.items[activeImageIndexes[product.brand] || 0].name }}
                            </h5>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mt-1">
                                {{ product.description }}
                            </p>
                        </div>

                        <div class="space-y-2 border-t border-slate-100 dark:border-slate-800/60 pt-3">
                            <!-- Selected Product -->
                            <div class="flex items-center justify-between text-xs sm:text-sm">
                                <span class="text-slate-450 font-medium">Nama Produk</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 line-clamp-1 max-w-[65%]">
                                    {{ product.items[activeImageIndexes[product.brand] || 0].name }}
                                </span>
                            </div>
                            <!-- Location -->
                            <div class="flex items-center justify-between text-xs sm:text-sm">
                                <span class="text-slate-450 font-medium">Asal Wilayah</span>
                                <span class="font-semibold text-slate-650 dark:text-slate-300">
                                    {{ product.village }}, Kec. {{ product.district }}
                                </span>
                            </div>
                        </div>

                        <!-- Price & WA Action -->
                        <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-4">
                            <div>
                                <span class="block text-[10px] sm:text-xs uppercase tracking-wider font-semibold text-slate-450">Estimasi Harga</span>
                                <span class="text-sm sm:text-base font-extrabold text-blue-600 dark:text-blue-400 font-mono mt-0.5 block">
                                    {{ product.items[activeImageIndexes[product.brand] || 0].price }}
                                </span>
                            </div>
                            
                            <a
                                :href="`https://wa.me/${product.phone}?text=Halo%20${encodeURIComponent(product.owner)},%20saya%20tertarik%2520dengan%20produk%20UMKM%20${encodeURIComponent(product.items[activeImageIndexes[product.brand] || 0].name)}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-xs transition-colors duration-200 cursor-pointer"
                                @click.stop
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

        <!-- 🔍 BRAND DETAIL MODAL (LIGHTBOX) -->
        <Teleport to="body">
            <div 
                v-if="selectedBrandForDetail"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-all duration-300 animate-fade-in"
                @click="closeBrandDetail"
            >
                <div 
                    class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden shadow-2xl max-w-6xl w-full h-[85vh] max-h-[800px] flex flex-col lg:flex-row relative animate-scale-up border border-slate-200/50 dark:border-slate-800/80"
                    @click.stop
                >
                    <!-- Close Button -->
                    <button 
                        @click="closeBrandDetail"
                        class="absolute top-4 right-4 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full p-2 z-20 cursor-pointer transition-colors backdrop-blur-xs shadow-xs"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Left Side: Image Viewer (Seamless background) -->
                    <div class="h-1/2 lg:h-full lg:w-3/5 bg-white dark:bg-slate-900 flex flex-col justify-between relative overflow-hidden">
                        <div class="flex-1 flex items-center justify-center overflow-hidden p-8 relative group/modal">
                            <img 
                                :src="selectedBrandForDetail.items[activeDetailImageIndex].image"
                                class="max-w-full max-h-full object-contain rounded-2xl transition-all duration-300"
                                :alt="selectedBrandForDetail.items[activeDetailImageIndex].name"
                            />

                            <!-- Modal Carousel Arrows -->
                            <template v-if="selectedBrandForDetail.items.length > 1">
                                <button
                                    @click="prevDetailImage"
                                    class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2.5 transition-all focus:outline-hidden cursor-pointer"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                                <button
                                    @click="nextDetailImage"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2.5 transition-all focus:outline-hidden cursor-pointer"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </template>
                        </div>

                        <!-- Slide Info Overlay (Top Left inside image) -->
                        <div class="absolute top-4 left-4 bg-black/45 backdrop-blur-md text-white text-[10px] uppercase font-bold tracking-wider px-3.5 py-1.5 rounded-full shadow-xs">
                            Foto {{ activeDetailImageIndex + 1 }} dari {{ selectedBrandForDetail.items.length }}
                        </div>
                    </div>

                    <!-- Right Side: Details Pane with scrollable variants -->
                    <div class="h-1/2 lg:h-full lg:w-2/5 flex flex-col justify-between overflow-hidden bg-white dark:bg-slate-900 border-t lg:border-t-0 lg:border-l border-slate-100 dark:border-slate-800">
                        <!-- Scrollable Details Section -->
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6 scrollbar-none">
                            <!-- Brand Info Header -->
                            <div class="space-y-1.5">
                                <span class="inline-flex text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-3 py-1 rounded-full uppercase tracking-wider">
                                    {{ selectedBrandForDetail.sector }}
                                </span>
                                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white pt-1 leading-tight">
                                    {{ selectedBrandForDetail.brand }}
                                </h3>
                                <h4 class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                                    Pemilik (Owner): {{ selectedBrandForDetail.owner }}
                                </h4>
                                <div class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 font-medium">
                                    <span>📍 Desa {{ selectedBrandForDetail.village }}, Kec. {{ selectedBrandForDetail.district }}</span>
                                </div>
                            </div>

                            <!-- Brand Description -->
                            <div class="space-y-2">
                                <h4 class="text-[10px] uppercase font-extrabold text-slate-455 dark:text-slate-500 tracking-wider">Tentang UMKM</h4>
                                <p class="text-xs sm:text-sm text-slate-605 dark:text-slate-350 leading-relaxed font-normal">
                                    {{ selectedBrandForDetail.description }}
                                </p>
                            </div>

                            <!-- Product Variants Selector (Neat list-card styling) -->
                            <div class="space-y-3">
                                <h4 class="text-[10px] uppercase font-extrabold text-slate-455 dark:text-slate-500 tracking-wider">Pilih Varian Produk</h4>
                                <div class="space-y-2">
                                    <div 
                                        v-for="(item, idx) in selectedBrandForDetail.items"
                                        :key="idx"
                                        @click="setDetailImageIndex(idx)"
                                        class="flex items-center gap-3.5 p-3 rounded-2xl border transition-all duration-200 cursor-pointer"
                                        :class="idx === activeDetailImageIndex 
                                            ? 'border-blue-600 bg-blue-50/20 dark:border-blue-500 dark:bg-blue-950/20 shadow-xs' 
                                            : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50/50 dark:border-slate-800 dark:hover:border-slate-750 dark:hover:bg-slate-800/30'"
                                    >
                                        <!-- Thumbnail -->
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800/50 shrink-0">
                                            <img :src="item.image" class="w-full h-full object-cover" />
                                        </div>
                                        <!-- Meta -->
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">
                                                {{ item.name }}
                                            </h5>
                                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 font-mono mt-0.5 block">
                                                {{ item.price }}
                                            </span>
                                        </div>
                                        <!-- Indicator Checkmark -->
                                        <div v-if="idx === activeDetailImageIndex" class="text-blue-600 dark:text-blue-400 pr-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Action Bar (Fixed at bottom of right column) -->
                        <div class="border-t border-slate-100 dark:border-slate-800/80 p-6 bg-white dark:bg-slate-900 shrink-0 shadow-lg relative z-10">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                                <div class="space-y-0.5">
                                    <span class="block text-[10px] uppercase font-bold text-slate-455 tracking-wider">Harga Varian Terpilih</span>
                                    <span class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 font-mono">
                                        {{ selectedBrandForDetail.items[activeDetailImageIndex].price }}
                                    </span>
                                </div>

                                <a
                                    :href="`https://wa.me/${selectedBrandForDetail.phone}?text=Halo%20${encodeURIComponent(selectedBrandForDetail.owner)},%20saya%20tertarik%20dengan%20produk%20UMKM%20${encodeURIComponent(selectedBrandForDetail.items[activeDetailImageIndex].name)}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm py-3.5 px-6 rounded-2xl shadow-xs transition-colors duration-200 cursor-pointer"
                                >
                                    <span>📱 Hubungi via WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

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

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleUp {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.animate-fade-in {
    animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.animate-scale-up {
    animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
