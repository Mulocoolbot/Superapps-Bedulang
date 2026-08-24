<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'
import * as XLSX from 'xlsx'

const props = defineProps({
    service: { type: Object, required: true },
    dashboard: { type: Object, default: () => ({}) },
})

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)

// Only 2 admins can import — all other roles can only VIEW
const isAuthorizedAdmin = computed(() => {
    if (!currentUser.value) return false
    const u = currentUser.value
    const allowedEmails = ['adminbedulang@gmail.com', 'bedulangsuperadmin@gmail.com', 'dindukcapil@bangkatengahkab.go.id', 'adminkependudukan@gmail.com']
    return u.role === 'super_admin' || u.role === 'admin_kependudukan' || u.service_slug === 'kependudukan' || allowedEmails.includes(u.email?.toLowerCase())
})

const tabs = [
    { key: 'status_perkawinan', label: 'Status Perkawinan', desc: 'Status perkawinan penduduk' },
    { key: 'pendidikan',        label: 'Pendidikan',        desc: 'Tingkat pendidikan terakhir' },
    { key: 'pekerjaan',         label: 'Pekerjaan',         desc: 'Bidang pekerjaan / mata pencaharian' },
    { key: 'kelompok_umur',     label: 'Kelompok Umur',     desc: 'Distribusi kelompok rentang usia' },
    { key: 'jenis_kelamin',     label: 'Jenis Kelamin',     desc: 'Rasio jenis kelamin' },
    { key: 'golongan_darah',    label: 'Golongan Darah',    desc: 'Sebaran golongan darah' },
    { key: 'agama',             label: 'Agama',             desc: 'Distribusi penganut agama & kepercayaan' },
    { key: 'kepala_keluarga',   label: 'Kepala Keluarga',   desc: 'Statistik Kepala Keluarga & Anggota KK' },
]

const activeTab    = ref('status_perkawinan')
const chartViewMode = ref('bar')   // 'bar' | 'table'

const selectedPeriodKey  = ref('2026-1')
const selectedKecamatan  = ref('Semua')
const selectedDesa       = ref('Semua')

const periodOptions = computed(() => {
    if (props.dashboard?.period_options?.length) return props.dashboard.period_options
    return [
        { key: '2026-1', label: 'Semester 1 — 2026 (Terbaru)' },
        { key: '2026-2', label: 'Semester 2 — 2026' },
        { key: '2025-2', label: 'Semester 2 — 2025' },
        { key: '2025-1', label: 'Semester 1 — 2025' },
    ]
})

const districts = computed(() => {
    if (props.dashboard?.periods?.[selectedPeriodKey.value]) return props.dashboard.periods[selectedPeriodKey.value]
    return props.dashboard?.districts || []
})

const kecamatanOptions = computed(() => ['Semua', ...districts.value.map(d => d.name)])

const desaOptions = computed(() => {
    if (selectedKecamatan.value === 'Semua') return ['Semua']
    const d = districts.value.find(d => d.name === selectedKecamatan.value)
    return d?.villages ? ['Semua', ...d.villages.map(v => v.name)] : ['Semua']
})

watch(selectedKecamatan, () => { selectedDesa.value = 'Semua' })

const activeVillages = computed(() => {
    const result = []
    districts.value.forEach(district => {
        if (selectedKecamatan.value !== 'Semua' && district.name !== selectedKecamatan.value) return
        district.villages.forEach(village => {
            if (selectedDesa.value !== 'Semua' && village.name !== selectedDesa.value) return
            result.push({ ...village, districtName: district.name })
        })
    })
    return result
})

const fmt = (val) => val == null ? '0' : Number(val).toLocaleString('id-ID')
const pct = (val, total) => !total ? '0%' : ((val / total) * 100).toFixed(1) + '%'

// Website brand colors — blue palette consistent with Bangka Tengah SuperApp
const colorPalette = [
    '#1d4ed8', // blue-700
    '#0891b2', // cyan-600
    '#059669', // emerald-600
    '#d97706', // amber-600
    '#9333ea', // purple-600
    '#db2777', // pink-600
    '#ea580c', // orange-600
    '#0284c7', // sky-600
    '#65a30d', // lime-600
    '#dc2626', // red-600
    '#7c3aed', // violet-600
    '#0d9488', // teal-600
    '#be185d', // pink-700
    '#92400e', // amber-800
]

const activeTabInfo = computed(() => tabs.find(t => t.key === activeTab.value) || tabs[0])

const activeTabData = computed(() => {
    const key = activeTab.value
    if (key === 'kepala_keluarga') {
        let map = {}
        if (selectedKecamatan.value === 'Semua') {
            districts.value.forEach(d => {
                map[`Kec. ${d.name}`] = d.villages.reduce((s, v) => s + (v.kk_stat?.kk || 0), 0)
            })
        } else {
            const d = districts.value.find(d => d.name === selectedKecamatan.value)
            d?.villages?.forEach(v => { map[v.name] = v.kk_stat?.kk || 0 })
        }
        const items = Object.keys(map).map((label, i) => ({ label, count: map[label], color: colorPalette[i % colorPalette.length] })).sort((a, b) => b.count - a.count)
        return { items, total: items.reduce((s, i) => s + i.count, 0), dominantItem: items[0] || null, unit: 'KK' }
    }

    const sums = {}
    activeVillages.value.forEach(v => {
        const m = v[key]
        if (m && typeof m === 'object') Object.keys(m).forEach(k => { sums[k] = (sums[k] || 0) + Number(m[k] || 0) })
    })
    const items = Object.keys(sums).map((label, i) => ({ label, count: sums[label], color: colorPalette[i % colorPalette.length] })).sort((a, b) => b.count - a.count)
    return { items, total: items.reduce((s, i) => s + i.count, 0), dominantItem: items[0] || null, unit: 'jiwa' }
})

// Give enough vertical space per bar row — 52px per item, min 320
const chartHeight = computed(() => Math.max(320, activeTabData.value.items.length * 52 + 60))

const barOptions = computed(() => {
    const { items, unit, total } = activeTabData.value
    const maxVal = Math.max(...items.map(i => i.count), 1)
    return {
        chart: { type: 'bar', background: 'transparent', toolbar: { show: false }, fontFamily: 'Inter, ui-sans-serif, system-ui', animations: { enabled: true, easing: 'easeinout', speed: 350 } },
        plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 5, distributed: true, dataLabels: { position: 'top' } } },
        colors: items.map(i => i.color),
        dataLabels: {
            enabled: true, textAnchor: 'start', offsetX: 10,
            style: { fontSize: '11px', fontWeight: '700', colors: ['#1e293b'] },
            formatter: v => fmt(v),
        },
        legend: { show: false },
        grid: { borderColor: '#e2e8f0', strokeDashArray: 4, padding: { right: 50, left: 5 }, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
        xaxis: {
            categories: items.map(i => i.label),
            max: maxVal * 1.22,
            labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: '600' }, formatter: v => fmt(v) },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { maxWidth: 220, style: { colors: '#334155', fontSize: '12px', fontWeight: '600' } } },
        tooltip: { theme: 'light', style: { fontSize: '12px' }, y: { formatter: v => `${fmt(v)} ${unit} (${pct(v, total)})` } },
    }
})

const barSeries = computed(() => [{ name: activeTabInfo.value.label, data: activeTabData.value.items.map(i => i.count) }])

// Dark-mode aware versions of chart options (ApexCharts reads theme from root `dark` class)
const barOptionsDark = computed(() => ({
    ...barOptions.value,
    dataLabels: { ...barOptions.value.dataLabels, style: { ...barOptions.value.dataLabels.style, colors: ['#f1f5f9'] } },
    grid: { ...barOptions.value.grid, borderColor: '#1e293b' },
    xaxis: { ...barOptions.value.xaxis, labels: { ...barOptions.value.xaxis.labels, style: { colors: '#94a3b8', fontSize: '11px', fontWeight: '600' } } },
    yaxis: { labels: { maxWidth: 220, style: { colors: '#cbd5e1', fontSize: '12px', fontWeight: '600' } } },
    tooltip: { theme: 'dark', style: { fontSize: '12px' }, y: { formatter: v => `${fmt(v)} ${barSeries.value[0]?.name || ''} (${pct(v, activeTabData.value.total)})` } },
}))

// Detect dark mode
const isDark = computed(() => typeof document !== 'undefined' && document.documentElement.classList.contains('dark'))
const activeBarOptions = computed(() => isDark.value ? barOptionsDark.value : barOptions.value)

// District breakdown
const districtBreakdown = computed(() => {
    const key = activeTab.value
    return districts.value.map(district => {
        let total = 0
        district.villages.forEach(v => {
            if (key === 'kepala_keluarga') { total += (v.kk_stat?.kk || 0) }
            else { const m = v[key] || {}; Object.values(m).forEach(c => { total += Number(c || 0) }) }
        })
        return { name: district.name, total }
    }).sort((a, b) => b.total - a.total)
})

const districtBarOptions = computed(() => {
    const maxVal = Math.max(...districtBreakdown.value.map(d => d.total), 1)
    const baseColors = ['#1d4ed8','#0891b2','#059669','#d97706','#9333ea','#db2777']
    const unit = activeTabData.value.unit
    const grandTotal = districtBreakdown.value.reduce((s, d) => s + d.total, 0)
    return {
        chart: { type: 'bar', background: 'transparent', toolbar: { show: false }, fontFamily: 'Inter, ui-sans-serif, system-ui' },
        plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 5, distributed: true, dataLabels: { position: 'top' } } },
        colors: baseColors,
        dataLabels: {
            enabled: true, textAnchor: 'start', offsetX: 8,
            style: { fontSize: '11px', fontWeight: '700', colors: [isDark.value ? '#f1f5f9' : '#1e293b'] },
            formatter: (v) => `${fmt(v)} ${unit}  (${pct(v, grandTotal)})`,
        },
        legend: { show: false },
        grid: { borderColor: isDark.value ? '#1e293b' : '#e2e8f0', strokeDashArray: 4, padding: { right: 70 } },
        xaxis: {
            categories: districtBreakdown.value.map(d => `Kec. ${d.name}`),
            max: maxVal * 1.32,
            labels: { style: { colors: isDark.value ? '#94a3b8' : '#64748b', fontSize: '11px' }, formatter: v => fmt(v) },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { maxWidth: 160, style: { colors: isDark.value ? '#cbd5e1' : '#334155', fontSize: '12px', fontWeight: '600' } } },
        tooltip: { theme: isDark.value ? 'dark' : 'light', y: { formatter: v => `${fmt(v)} ${unit}  (${pct(v, grandTotal)})` } },
    }
})

const districtBarSeries = computed(() => [{ name: 'Total', data: districtBreakdown.value.map(d => d.total) }])

// Grand totals
const totalPenduduk = computed(() => {
    let s = 0
    activeVillages.value.forEach(v => { if (v.jenis_kelamin) Object.values(v.jenis_kelamin).forEach(c => { s += Number(c || 0) }) })
    return s
})
const totalKK = computed(() => activeVillages.value.reduce((s, v) => s + (v.kk_stat?.kk || 0), 0))

// Import modal
const showImportModal  = ref(false)
const isDragging       = ref(false)
const selectedFileName = ref('')
const fileSizeFormatted = ref('')
const showPreview      = ref(false)
const previewHeaders   = ref([])
const previewRows      = ref([])
const totalParsedRows  = ref(0)

const importForm = useForm({ category_key: 'status_perkawinan', tahun: new Date().getFullYear(), semester: 1, file: null })

const openImportModal = () => {
    importForm.category_key = activeTab.value
    importForm.file = null; selectedFileName.value = ''; showPreview.value = false
    previewHeaders.value = []; previewRows.value = []; showImportModal.value = true
}
const closeImportModal = () => { showImportModal.value = false; importForm.reset('file'); selectedFileName.value = ''; showPreview.value = false }
const fmtSize = b => !b ? '0 B' : `${parseFloat((b / Math.pow(1024, Math.floor(Math.log(b)/Math.log(1024)))).toFixed(2))} ${'BKMG'[Math.floor(Math.log(b)/Math.log(1024))]}B`

const processFile = (file) => {
    if (!file) return
    fileSizeFormatted.value = fmtSize(file.size)
    const ext = file.name.split('.').pop().toLowerCase()
    const isXlsx = ext === 'xlsx' || ext === 'xls'
    const reader = new FileReader()
    reader.onload = (e) => {
        let matrix = []
        if (isXlsx) {
            try { const wb = XLSX.read(new Uint8Array(e.target.result), { type: 'array' }); matrix = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1, defval: '' }) }
            catch (err) { alert('Gagal membaca Excel: ' + err.message); return }
        } else {
            matrix = e.target.result.split(/\r\n|\n/).filter(l => l.trim()).map(l => l.split(',').map(c => c.replace(/^"|"$/g, '').trim()))
        }
        if (!matrix?.length) { alert('File kosong.'); return }
        let headerRow = 0, isMulti = false
        for (let i = 0; i < Math.min(10, matrix.length); i++) {
            if (matrix[i].join(' ').toUpperCase().includes('WILAYAH')) { headerRow = i; isMulti = true; break }
        }
        let headers = []
        if (isMulti) {
            const top = matrix[headerRow] || [], sub = matrix[headerRow + 1] || []
            let cat = ''
            for (let c = 0; c < Math.max(top.length, sub.length); c++) {
                if (String(top[c]||'').trim()) cat = String(top[c]).trim()
                const s = String(sub[c]||'').trim()
                headers.push(c === 0 ? 'KECAMATAN' : c === 1 ? 'DESA/KELURAHAN' : (cat ? cat + ' ' : '') + s)
            }
        } else { headers = matrix[0].map(h => String(h).trim()) }
        previewHeaders.value = headers
        const start = isMulti ? headerRow + 2 : 1
        const rows = []
        for (let i = start; i < matrix.length; i++) { if (matrix[i]?.some(c => String(c).trim())) rows.push(matrix[i].map(c => String(c).trim())) }
        totalParsedRows.value = rows.length; previewRows.value = rows.slice(0, 8); showPreview.value = true
    }
    if (isXlsx) reader.readAsArrayBuffer(file); else reader.readAsText(file)
}

const handleFileSelect = (e) => { const f = e.target.files[0]; if (f) { importForm.file = f; selectedFileName.value = f.name; processFile(f) } }
const handleDrop = (e) => { isDragging.value = false; const f = e.dataTransfer.files[0]; if (f) { importForm.file = f; selectedFileName.value = f.name; processFile(f) } }
const submitImport = () => { if (!importForm.file) return; importForm.post('/admin/kependudukan/import', { preserveScroll: true, onSuccess: () => closeImportModal() }) }
</script>

<template>
<div class="mt-8 space-y-8 animate-fade-in-up">

    <!-- ══════════════════════════════════════════════════════════
         1. TAB NAVIGATION BAR
         ══════════════════════════════════════════════════════════ -->
    <div class="animate-fade-in-up [animation-delay:50ms] overflow-x-auto scrollbar-none pb-1">
        <div class="flex items-center gap-1.5 min-w-max p-1.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <button
                v-for="t in tabs"
                :key="t.key"
                @click="activeTab = t.key"
                :class="[
                    'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer select-none whitespace-nowrap',
                    activeTab === t.key
                        ? 'bg-blue-700 text-white shadow-md shadow-blue-700/20'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'
                ]"
            >
                <span>{{ t.icon }}</span>
                <span>{{ t.label }}</span>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         2. HERO STAT BANNER — single wide card, top accent strip
         ══════════════════════════════════════════════════════════ -->
    <div class="animate-fade-in-up [animation-delay:100ms] overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md border-t-4 border-t-blue-700">
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 divide-y-2 sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-800">

            <!-- Total Penduduk (HERO — wider feel via double text) -->
            <div class="p-5 sm:p-6 flex flex-col gap-1 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total Penduduk</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                    {{ fmt(totalPenduduk) }}<span class="ml-1 text-sm font-semibold text-slate-400 dark:text-slate-500">Jiwa</span>
                </div>
                <div class="text-[11px] font-semibold text-blue-700 dark:text-blue-400">
                    {{ selectedKecamatan === 'Semua' ? 'Kab. Bangka Tengah (Semua Kecamatan)' : selectedDesa === 'Semua' ? `Kec. ${selectedKecamatan} · Semua Desa` : `Kec. ${selectedKecamatan} · ${selectedDesa}` }}
                </div>
            </div>

            <!-- Total KK -->
            <div class="p-5 sm:p-6 flex flex-col gap-1 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total Kepala Keluarga</div>
                <div class="text-3xl font-black text-blue-700 dark:text-blue-400 tracking-tight leading-none">
                    {{ fmt(totalKK) }}<span class="ml-1 text-sm font-semibold text-slate-400">KK</span>
                </div>
                <div class="text-[11px] font-semibold text-blue-700 dark:text-blue-400">Kartu Keluarga terdaftar</div>
            </div>

            <!-- Volume Data Tab Aktif -->
            <div class="p-5 sm:p-6 flex flex-col gap-1 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">Data {{ activeTabInfo.label }}</div>
                <div class="text-3xl font-black text-cyan-700 dark:text-cyan-400 tracking-tight leading-none">
                    {{ fmt(activeTabData.total) }}<span class="ml-1 text-sm font-semibold text-slate-400">{{ activeTabData.unit }}</span>
                </div>
                <div class="text-[11px] font-semibold text-cyan-700 dark:text-cyan-400">{{ activeTabInfo.desc }}</div>
            </div>

            <!-- Kategori Dominan -->
            <div class="p-5 sm:p-6 flex flex-col gap-1 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400">Kategori Dominan</div>
                <div class="text-xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight leading-tight truncate">
                    {{ activeTabData.dominantItem?.label || '—' }}
                </div>
                <div class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">
                    {{ activeTabData.dominantItem ? `${fmt(activeTabData.dominantItem.count)} ${activeTabData.unit} · ${pct(activeTabData.dominantItem.count, activeTabData.total)}` : 'Belum ada data' }}
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         3. FILTER ROW + VIEW TOGGLE
         ══════════════════════════════════════════════════════════ -->
    <div class="animate-fade-in-up [animation-delay:150ms] overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md">
        <div class="p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <!-- Left: Filters -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-wrap">
                <!-- Periode -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📅 Periode</label>
                    <select v-model="selectedPeriodKey"
                        class="w-full sm:w-52 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        <option v-for="p in periodOptions" :key="p.key" :value="p.key">{{ p.label }}</option>
                    </select>
                </div>

                <!-- Kecamatan -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📍 Kecamatan</label>
                    <select v-model="selectedKecamatan"
                        class="w-full sm:w-56 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        <option v-for="opt in kecamatanOptions" :key="opt" :value="opt">
                            {{ opt === 'Semua' ? 'Kab. Bangka Tengah (Semua)' : `Kec. ${opt}` }}
                        </option>
                    </select>
                </div>

                <!-- Desa -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🏡 Desa/Kelurahan</label>
                    <select v-model="selectedDesa" :disabled="selectedKecamatan === 'Semua'"
                        class="w-full sm:w-48 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <option v-for="opt in desaOptions" :key="opt" :value="opt">
                            {{ opt === 'Semua' ? (selectedKecamatan === 'Semua' ? 'Semua Desa' : 'Semua Desa/Kelurahan') : opt }}
                        </option>
                    </select>
                </div>

                <!-- Reset -->
                <div class="self-end pb-0.5">
                    <button v-if="selectedKecamatan !== 'Semua' || selectedDesa !== 'Semua'"
                        @click="selectedKecamatan = 'Semua'; selectedDesa = 'Semua'"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        🔄 Reset
                    </button>
                </div>
            </div>

            <!-- Right: View toggle + Import -->
            <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                <!-- Chart / Table toggle -->
                <div class="flex items-center gap-0.5 p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <button @click="chartViewMode = 'bar'"
                        :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer', chartViewMode === 'bar' ? 'bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white']">
                        <span>📊</span> Grafik
                    </button>
                    <button @click="chartViewMode = 'table'"
                        :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer', chartViewMode === 'table' ? 'bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white']">
                        <span>📋</span> Tabel
                    </button>
                </div>

                <!-- Import button (admin only) -->
                <button v-if="isAuthorizedAdmin" @click="openImportModal"
                    class="flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 px-4 py-2 text-xs font-extrabold text-white shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                    📥 Impor Data
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         4. MAIN CHART / TABLE  +  SIDE PANEL
         ══════════════════════════════════════════════════════════ -->
    <div class="animate-fade-in-up [animation-delay:200ms] grid grid-cols-1 gap-6 lg:grid-cols-12">

        <!-- LEFT: Main Visualization (full width — right panel removed) -->
        <div class="lg:col-span-12 flex flex-col gap-6">

            <!-- Visualization Card -->
            <div class="overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md">
                <!-- Card header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>{{ activeTabInfo.icon }}</span>
                            <span>Data {{ activeTabInfo.label }}</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ chartViewMode === 'bar' ? 'Diagram batang distribusi per sub-kategori' : 'Tabel rincian per sub-kategori' }}
                            · {{ activeTabData.items.length }} varian · {{ fmt(activeTabData.total) }} {{ activeTabData.unit }}
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-100 dark:border-blue-900/50 uppercase tracking-wider">
                        Tab {{ tabs.findIndex(t => t.key === activeTab) + 1 }}/8
                    </span>
                </div>

                <!-- Chart view -->
                <div v-if="chartViewMode === 'bar'" class="px-4 py-6">
                    <VueApexCharts v-if="activeTabData.items.length && activeTabData.total"
                        type="bar" :height="chartHeight" width="100%"
                        :options="activeBarOptions" :series="barSeries" />
                    <div v-else class="flex items-center justify-center h-48 text-sm text-slate-400 font-medium">
                        Belum ada data untuk filter yang dipilih.
                    </div>
                </div>

                <!-- Table view -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Sub-Kategori</th>
                                <th class="py-3 px-4 text-right">Jumlah</th>
                                <th class="py-3 px-4 text-right">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200">
                            <tr v-for="(item, idx) in activeTabData.items" :key="item.label"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="py-3 px-4 text-slate-400 dark:text-slate-500 font-mono font-bold">{{ idx + 1 }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="h-3 w-3 rounded-full shrink-0" :style="{ backgroundColor: item.color }"></span>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ item.label }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="font-extrabold text-slate-900 dark:text-white">{{ fmt(item.count) }}</span>
                                    <span class="ml-1 text-slate-400 text-xs">{{ activeTabData.unit }}</span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50">
                                        {{ pct(item.count, activeTabData.total) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!activeTabData.items.length">
                                <td colspan="4" class="py-12 text-center text-slate-400 font-medium">Belum ada data untuk filter yang dipilih.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 bg-slate-50/60 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-semibold">
                    <span>{{ activeTabData.items.length }} varian sub-kategori</span>
                    <span>Total: <strong class="text-slate-900 dark:text-white font-black">{{ fmt(activeTabData.total) }} {{ activeTabData.unit }}</strong></span>
                </div>
            </div>

            <!-- Kecamatan Breakdown Bar (only shown when Semua Kecamatan is selected) -->
            <div v-if="selectedKecamatan === 'Semua'" class="overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-md">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        🗺️ Distribusi Per Kecamatan — Kab. Bangka Tengah
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbandingan total data {{ activeTabInfo.label }} per kecamatan di Kabupaten Bangka Tengah</p>
                </div>
                <div class="px-4 py-5">
                    <VueApexCharts type="bar" :height="districtBreakdown.length * 56 + 40" width="100%"
                        :options="districtBarOptions" :series="districtBarSeries" />
                </div>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════
         5. IMPORT MODAL  (admin only)
         ══════════════════════════════════════════════════════════ -->
    <Teleport to="body">
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-200 dark:border-slate-800">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-xl border border-emerald-200 dark:border-emerald-800">📥</div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Impor Data Kependudukan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Unggah file Excel/CSV per Semester</p>
                        </div>
                    </div>
                    <button @click="closeImportModal" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-white transition cursor-pointer">✕</button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto space-y-5">
                    <form @submit.prevent="submitImport" class="space-y-5">

                        <!-- Target Tab -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Target Kategori</label>
                            <select v-model="importForm.category_key"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                <option v-for="t in tabs" :key="t.key" :value="t.key">{{ t.icon }} {{ t.label }}</option>
                            </select>
                            <p v-if="importForm.errors.category_key" class="mt-1 text-xs text-rose-600">{{ importForm.errors.category_key }}</p>
                        </div>

                        <!-- Tahun + Semester -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Tahun</label>
                                <input v-model="importForm.tahun" type="number" min="2000" max="2099"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" />
                                <p v-if="importForm.errors.tahun" class="mt-1 text-xs text-rose-600">{{ importForm.errors.tahun }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Semester</label>
                                <select v-model="importForm.semester"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    <option :value="1">Semester 1</option>
                                    <option :value="2">Semester 2</option>
                                </select>
                            </div>
                        </div>

                        <!-- Template link -->
                        <div class="flex items-center justify-between rounded-xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 px-4 py-3 text-xs">
                            <span class="font-medium text-blue-800 dark:text-blue-200">Download template format Excel:</span>
                            <a :href="`/admin/kependudukan/template/${importForm.category_key}`" target="_blank"
                                class="font-extrabold text-blue-700 dark:text-blue-400 hover:underline flex items-center gap-1">
                                📄 Download
                            </a>
                        </div>

                        <!-- Drop zone -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">File Excel / CSV</label>
                            <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop" @click="$refs.fileInput.click()"
                                :class="['flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center cursor-pointer transition',
                                    isDragging ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30' : selectedFileName ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/20' : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600 bg-slate-50 dark:bg-slate-800/60']">
                                <input ref="fileInput" type="file" accept=".csv,.xlsx,.xls" class="hidden" @change="handleFileSelect" />
                                <div v-if="!selectedFileName" class="space-y-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-200 dark:bg-slate-700 text-2xl mx-auto">📁</div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Klik atau seret file ke sini</p>
                                    <p class="text-xs text-slate-400">.xlsx · .xls · .csv (maks 10 MB)</p>
                                </div>
                                <div v-else class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <div class="text-left">
                                        <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate max-w-xs">{{ selectedFileName }}</p>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">{{ fileSizeFormatted }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="importForm.errors.file" class="mt-1 text-xs text-rose-600">{{ importForm.errors.file }}</p>
                        </div>

                        <!-- Preview -->
                        <div v-if="showPreview">
                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-2">✅ {{ totalParsedRows }} baris data terdeteksi (preview 8 baris pertama)</p>
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 max-h-40">
                                <table class="w-full text-[10px] border-collapse">
                                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold sticky top-0">
                                        <tr>
                                            <th class="py-2 px-3">#</th>
                                            <th v-for="h in previewHeaders" :key="h" class="py-2 px-3 whitespace-nowrap">{{ h }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                        <tr v-for="(row, ri) in previewRows" :key="ri">
                                            <td class="py-1.5 px-3 text-slate-400 font-mono">{{ ri + 1 }}</td>
                                            <td v-for="(cell, ci) in row" :key="ci" class="py-1.5 px-3 whitespace-nowrap font-mono">{{ cell }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 px-6 py-4 bg-slate-50/60 dark:bg-slate-900">
                    <button type="button" @click="closeImportModal"
                        class="rounded-xl border border-slate-200 dark:border-slate-700 px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="submitImport" :disabled="importForm.processing || !importForm.file"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-xs font-extrabold text-white shadow-sm transition cursor-pointer disabled:opacity-50">
                        <span v-if="importForm.processing" class="h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                        <span v-else>🚀</span>
                        Import Sekarang
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

</div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
