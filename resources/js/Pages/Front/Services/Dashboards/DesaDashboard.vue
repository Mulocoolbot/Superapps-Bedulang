<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

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

// Data Kecamatan, Desa, Koordinat, dan URL Google Maps
const villages = [
    // KECAMATAN KOBA
    { name: 'Nibung', kecamatan: 'Koba', domain: 'nibung-bateng.desa.id', lat: -2.514576, lng: 106.389985, address: 'Desa Nibung, Kecamatan Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/uz5UKESqko7xpUAK6' },
    { name: 'Penyak', kecamatan: 'Koba', domain: 'penyak.desa.id', lat: -2.410665, lng: 106.289992, address: 'Desa Penyak, Kecamatan Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/obCmj5Hf1d4jjpqH6' },
    { name: 'Kurau', kecamatan: 'Koba', domain: 'kurau.desa.id', lat: -2.333846, lng: 106.235032, address: 'Desa Kurau, Kecamatan Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/DpdDsS4Wnevv7GBn7' },
    { name: 'Guntung', kecamatan: 'Koba', domain: 'guntung-bateng.desa.id', lat: -2.468029, lng: 106.359331, address: 'Desa Guntung, Kecamatan Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/Cnr7yLg4zzuw4ujX9' },
    { name: 'Kurau Barat', kecamatan: 'Koba', domain: 'kuraubarat.desa.id', lat: -2.326067146053784, lng: 106.2290965759435, address: 'Desa Kurau Barat, Kecamatan Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/uSSsWnVmisVKnkzK6' },
    { name: 'Terentang III', kecamatan: 'Koba', domain: 'terentang-bateng.desa.id', lat: -2.4537153179487112, lng: 106.33391525319212, address: 'Desa Terentang III, Kecamatan Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/iB682LYh6Tdenkmo6' },

    // KECAMATAN PANGKALANBARU
    { name: 'Air Mesu', kecamatan: 'Pangkalanbaru', domain: 'airmesu.desa.id', lat: -2.212901, lng: 106.141920, address: 'Desa Air Mesu, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/U5F8bc1oGAiC4ZGW8' },
    { name: 'Air Mesu Timur', kecamatan: 'Pangkalanbaru', domain: 'airmesutimur.desa.id', lat: -2.206802, lng: 106.145684, address: 'Desa Air Mesu Timur, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/81Jp1Qahaj4GUwvg8' },
    { name: 'Batu Belubang', kecamatan: 'Pangkalanbaru', domain: 'batubelubang-bateng.desa.id', lat: -2.171411, lng: 106.187626, address: 'Desa Batu Belubang, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/gXjXm8G5vpjpxV3z9' },
    { name: 'Beluluk', kecamatan: 'Pangkalanbaru', domain: 'beluluk.desa.id', lat: -2.168468, lng: 106.137072, address: 'Desa Beluluk, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/w4BWJ45y79zzPzkZ7' },
    { name: 'Benteng', kecamatan: 'Pangkalanbaru', domain: 'benteng-bateng.desa.id', lat: -2.169847, lng: 106.179749, address: 'Desa Benteng, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/eZr3qyvNRgV8oZweA' },
    { name: 'Jeruk', kecamatan: 'Pangkalanbaru', domain: 'jeruk-bateng.desa.id', lat: -2.195757, lng: 106.150871, address: 'Desa Jeruk, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/MfcoMYh7UMJMbeLN8' },
    { name: 'Kebintik', kecamatan: 'Pangkalanbaru', domain: 'kebintik.desa.id', lat: -2.163361, lng: 106.177058, address: 'Desa Kebintik, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/F3m6GNRvXA1TpTZ56' },
    { name: 'Mangkol', kecamatan: 'Pangkalanbaru', domain: 'mangkol.desa.id', lat: -2.156727, lng: 106.089207, address: 'Desa Mangkol, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/ruR2z527GV27LgQw6' },
    { name: 'Padang Baru', kecamatan: 'Pangkalanbaru', domain: 'padangbaru-bateng.desa.id', lat: -2.168423, lng: 106.159768, address: 'Desa Padang Baru, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/5GG5eqfNTf38cQPJ8' },
    { name: 'Pedindang', kecamatan: 'Pangkalanbaru', domain: 'pedindang.desa.id', lat: -2.1690281827680087, lng: 106.08761761840807, address: 'Desa Pedindang, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/dvuNoBWfwBoAdKYE9' },
    { name: 'Tanjung Gunung', kecamatan: 'Pangkalanbaru', domain: 'tanjunggunung.desa.id', lat: -2.205938, lng: 106.195245, address: 'Desa Tanjung Gunung, Kecamatan Pangkalanbaru, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/sJ9nZEsb4bDftTxa7' },

    // KECAMATAN NAMANG
    { name: 'Belilik', kecamatan: 'Namang', domain: 'belilik.desa.id', lat: -2.323678355117629, lng: 106.20676379807439, address: 'Desa Belilik, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/wtDRrRQhNtrmGv5c8' },
    { name: 'Bhaskara Bhakti', kecamatan: 'Namang', domain: 'bhaskarabhkati.desa.id', lat: -2.241980, lng: 106.211731, address: 'Desa Bhaskara Bhakti, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/mPDWzJUtoMC2bn2G9' },
    { name: 'Bukit Kijang', kecamatan: 'Namang', domain: 'bukitkijang.desa.id', lat: -2.230617047458136, lng: 106.18535511888822, address: 'Desa Bukit Kijang, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/LeTSgUh1yGZnYZD38' },
    { name: 'Cambai', kecamatan: 'Namang', domain: 'cambai.desa.id', lat: -2.243468, lng: 106.154809, address: 'Desa Cambai, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/fBdpC27xYTZEicRb6' },
    { name: 'Jelutung', kecamatan: 'Namang', domain: 'jelutung.desa.id', lat: -2.2761712412433908, lng: 106.16417318673449, address: 'Desa Jelutung, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/PRW3Kdn3D4ocBM7M7' },
    { name: 'Kayu Besi', kecamatan: 'Namang', domain: 'kayubesi-bateng.desa.id', lat: -2.227001, lng: 106.195198, address: 'Desa Kayu Besi, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/ttV4nwzaRTxiVkC78' },
    { name: 'Namang', kecamatan: 'Namang', domain: 'namang.desa.id', lat: -2.321601381650471, lng: 106.19790491682699, address: 'Desa Namang, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/NvRSTNdzRV1UQJCa9' },
    { name: 'Cambai Selatan', kecamatan: 'Namang', domain: 'cambaiselatan.desa.id', lat: -2.243441, lng: 106.154817, address: 'Desa Cambai Selatan, Kecamatan Namang, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/MaNYYZNqJ9TXvNCd7' },

    // KECAMATAN LUBUK BESAR
    { name: 'Batu Beriga', kecamatan: 'Lubuk Besar', domain: 'batuberiga.desa.id', lat: -2.5984707038389754, lng: 106.77806782976305, address: 'Desa Batu Beriga, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/Wdz3z9YyLmW2pHe5A' },
    { name: 'Belimbing', kecamatan: 'Lubuk Besar', domain: 'belimbing-bateng.desa.id', lat: -2.599293, lng: 106.472297, address: 'Desa Belimbing, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/2TWbCJMN8qEejgG38' },
    { name: 'Kulur', kecamatan: 'Lubuk Besar', domain: 'kulur-bateng.desa.id', lat: -2.545418, lng: 106.452241, address: 'Desa Kulur, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/MVMwGiiwUz3V9f2B8' },
    { name: 'Kulur Ilir', kecamatan: 'Lubuk Besar', domain: 'kulurilir.desa.id', lat: -2.5272512458879985, lng: 106.46543973691993, address: 'Desa Kulur Ilir, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/UCAfQvWKKYBY4AiH6' },
    { name: 'Lubuk Besar', kecamatan: 'Lubuk Besar', domain: 'lubukbesar.desa.id', lat: -2.569622, lng: 106.654568, address: 'Desa Lubuk Besar, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/EjcSEFppVMyoQ7c77' },
    { name: 'Lubuk Lingkuk', kecamatan: 'Lubuk Besar', domain: 'lubuklingkuk.desa.id', lat: -2.556038, lng: 106.647502, address: 'Desa Lubuk Lingkuk, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/gDxipzsv6TaLb7gv5' },
    { name: 'Lubuk Pabrik', kecamatan: 'Lubuk Besar', domain: 'lubukpabrik.desa.id', lat: -2.579998, lng: 106.649909, address: 'Desa Lubuk Pabrik, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/bSyjULmaUu2y5AWz8' },
    { name: 'Perlang', kecamatan: 'Lubuk Besar', domain: 'perlang-bateng.desa.id', lat: -2.547164320768243, lng: 106.5065543619438, address: 'Desa Perlang, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/geNSxyKgy3XYcwtNA' },
    { name: 'Trubus', kecamatan: 'Lubuk Besar', domain: 'trubus-bateng.desa.id', lat: -2.550813, lng: 106.487600, address: 'Desa Trubus, Kecamatan Lubuk Besar, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/D5JMnpNGLpJFWcK96' },

    // KECAMATAN SIMPANG KATIS
    { name: 'Beruas', kecamatan: 'Simpang Katis', domain: 'beruas.desa.id', lat: -2.257997396755729, lng: 106.07639857693012, address: 'Desa Beruas, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/8y57qNS16bFEsK1L9' },
    { name: 'Terak', kecamatan: 'Simpang Katis', domain: 'terak.desa.id', lat: -2.175478830773744, lng: 106.08182524529032, address: 'Desa Terak, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/o9s9MbDxmf9bzrSU8' },
    { name: 'Celuak', kecamatan: 'Simpang Katis', domain: 'celuak.desa.id', lat: -2.3265813177535826, lng: 106.1440186135008, address: 'Desa Celuak, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/P1PC3N6qpQoyL5mc7' },
    { name: 'Pasir Garam', kecamatan: 'Simpang Katis', domain: 'pasirgaram.desa.id', lat: -2.1989190542652914, lng: 106.06763599692721, address: 'Desa Pasir Garam, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/ERtHesx3GQCa8NMV8' },
    { name: 'Puput', kecamatan: 'Simpang Katis', domain: 'puput-bateng.desa.id', lat: -2.314607, lng: 106.087270, address: 'Desa Puput, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/k18HVECxrGrCLV2q8' },
    { name: 'Pinang Sebatang', kecamatan: 'Simpang Katis', domain: 'pinangsebatang-bateng.desa.id', lat: -2.327723088728696, lng: 106.12929343451746, address: 'Desa Pinang Sebatang, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/TTVTLTmhhG1DP3vv8' },
    { name: 'Simpang Katis', kecamatan: 'Simpang Katis', domain: 'simpangkatis.desa.id', lat: -2.283188, lng: 106.065417, address: 'Desa Simpang Katis, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/uNkLfaxdE72dyewS6' },
    { name: 'Sungkap', kecamatan: 'Simpang Katis', domain: 'sungkap.desa.id', lat: -2.2581, lng: 106.0281, address: 'Desa Sungkap, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/hpVLSQFArb4RXkau6' },
    { name: 'Katis', kecamatan: 'Simpang Katis', domain: 'katis.desa.id', lat: -2.29394119201844, lng: 106.07012493582624, address: 'Desa Katis, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/cu2xgZBoHHiMm67n7' },
    { name: 'Teru', kecamatan: 'Simpang Katis', domain: 'teru.desa.id', lat: -2.225644, lng: 106.071363, address: 'Desa Teru, Kecamatan Simpang Katis, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/73dPzJJJWKr5p9ag9' },

    // KECAMATAN SUNGAI SELAN
    { name: 'Lampur', kecamatan: 'Sungai Selan', domain: 'lampur.desa.id', lat: -2.380554265083782, lng: 106.05102095136883, address: 'Desa Lampur, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/qyhB8jqTRnY5fy4t9' },
    { name: 'Sungai Selan Atas', kecamatan: 'Sungai Selan', domain: 'sungaiselanatas.desa.id', lat: -2.3725127155325687, lng: 105.99592574735881, address: 'Desa Sungai Selan Atas, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/yvxqGDRB1DR7p3tt8' },
    { name: 'Romadhon', kecamatan: 'Sungai Selan', domain: 'romadhon.desa.id', lat: -2.297423, lng: 105.996622, address: 'Desa Romadhon, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/7cBrcstzBMV5WSW49' },
    { name: 'Kerantai', kecamatan: 'Sungai Selan', domain: 'kerantai.desa.id', lat: -2.378049982037058, lng: 106.09827743012816, address: 'Desa Kerantai, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/ZBbQz19QBKVTbmub7' },
    { name: 'Kerakas', kecamatan: 'Sungai Selan', domain: 'kerakas.desa.id', lat: -2.410193, lng: 106.122942, address: 'Desa Kerakas, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/CVR4WvPaRwW3E6tc6' },
    { name: 'Kemingking', kecamatan: 'Sungai Selan', domain: 'kemingking.desa.id', lat: -2.458246422212818, lng: 106.13230349595946, address: 'Desa Kemingking, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/o2wHsiJJEMSgxv9a9' },
    { name: 'Keretak', kecamatan: 'Sungai Selan', domain: 'keretak.desa.id', lat: -2.3164940158105627, lng: 106.03813920078589, address: 'Desa Keretak, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/2RP5AovXqy9FrrZ36' },
    { name: 'Keretak Atas', kecamatan: 'Sungai Selan', domain: 'keretakatas.desa.id', lat: -2.30292297579303, lng: 106.04381748040859, address: 'Desa Keretak Atas, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/danSEcjcG1i7hk398' },
    { name: 'Sarang Mandi', kecamatan: 'Sungai Selan', domain: 'sarangmandi.desa.id', lat: -2.332920, lng: 106.019654, address: 'Desa Sarang Mandi, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/EFJBeZ2UNLuobDe49' },
    { name: 'Tanjung Pura', kecamatan: 'Sungai Selan', domain: 'tanjungpura.desa.id', lat: -2.369635000303666, lng: 105.81661214916893, address: 'Desa Tanjung Pura, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/Am98VU9zHEm9qPo87' },
    { name: 'Munggu', kecamatan: 'Sungai Selan', domain: 'munggu-bateng.desa.id', lat: -2.424215, lng: 106.022157, address: 'Desa Munggu, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/UzLByEyFVshKNVtX6' },
    { name: 'Melabun', kecamatan: 'Sungai Selan', domain: 'melabun.desa.id', lat: -2.340614, lng: 106.012847, address: 'Desa Melabun, Kecamatan Sungai Selan, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung', gmaps: 'https://maps.app.goo.gl/PVf3fuTrnDAeQ4qs9' }
]

// State management
const selectedKecamatan = ref('')
const searchQuery = ref('')
const selectedDesa = ref(villages[0]) // Default selection (Nibung)
const mapContainer = ref(null)

const isMobile = ref(typeof window !== 'undefined' && window.innerWidth < 768)
const isMapInteractive = ref(false)

const toggleMapInteraction = () => {
    isMapInteractive.value = !isMapInteractive.value
    if (mapInstance) {
        if (isMapInteractive.value) {
            mapInstance.dragging.enable()
            if (mapInstance.touchZoom) mapInstance.touchZoom.enable()
        } else {
            mapInstance.dragging.disable()
            if (mapInstance.touchZoom) mapInstance.touchZoom.disable()
        }
    }
}

const showSuggestions = ref(false)
let mapInstance = null
let markersLayer = null
const villageMarkers = ref({})

// List of unique kecamatan for filter
const kecamatanList = computed(() => {
    const list = new Set(villages.map(v => v.kecamatan))
    return Array.from(list).sort()
})

// Filtered villages list based on kecamatan and search query
const filteredVillages = computed(() => {
    let result = villages
    if (selectedKecamatan.value) {
        result = result.filter(v => v.kecamatan === selectedKecamatan.value)
    }
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter(v => 
            v.name.toLowerCase().includes(query) || 
            v.kecamatan.toLowerCase().includes(query)
        )
    }
    return result
})

// Autocomplete suggestions based on search query
const suggestions = computed(() => {
    if (!searchQuery.value) return []
    const query = searchQuery.value.toLowerCase()
    return villages.filter(v => 
        v.name.toLowerCase().includes(query) || 
        v.kecamatan.toLowerCase().includes(query)
    ).slice(0, 6)
})

// Dynamic statistics
const totalKecamatan = computed(() => {
    if (selectedKecamatan.value) return 1
    const unique = new Set(filteredVillages.value.map(v => v.kecamatan))
    return unique.size
})

const totalDesa = computed(() => {
    return filteredVillages.value.length
})

// Select village handler
const selectVillage = (desa, pan = true) => {
    selectedDesa.value = desa
    
    // Update all markers to highlight the selected one
    villages.forEach(v => {
        const markerKey = `${v.kecamatan}-${v.name}`
        const marker = villageMarkers.value[markerKey]
        if (marker) {
            const isSel = v.name === desa.name && v.kecamatan === desa.kecamatan
            marker.setStyle({
                fillColor: isSel ? '#ef4444' : '#3b82f6', // Red for selected, blue for default
                radius: isSel ? 11 : 8,
                weight: isSel ? 3 : 2,
                color: '#ffffff'
            })
            if (isSel) {
                marker.openPopup()
            }
        }
    })

    if (pan && mapInstance) {
        mapInstance.setView([desa.lat, desa.lng], 13)
    }
}

// SelectSuggestion callback
const selectSuggestion = (desa) => {
    selectVillage(desa, true)
    searchQuery.value = desa.name
    showSuggestions.value = false
}

// Blur suggestion callback
const handleBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false
    }, 250)
}

// Search bar Enter callback
const handleSearchEnter = () => {
    if (filteredVillages.value.length > 0) {
        selectVillage(filteredVillages.value[0], true)
        searchQuery.value = filteredVillages.value[0].name
        showSuggestions.value = false
    }
}

// Draw/Update markers on map
const updateMapMarkers = () => {
    if (!markersLayer) return
    markersLayer.clearLayers()
    
    // Reset markers registry
    villageMarkers.value = {}
    
    filteredVillages.value.forEach(desa => {
        if (!desa.lat || !desa.lng) return
        
        const isSelected = selectedDesa.value && 
                           selectedDesa.value.name === desa.name && 
                           selectedDesa.value.kecamatan === desa.kecamatan

        const marker = L.circleMarker([desa.lat, desa.lng], {
            radius: isSelected ? 11 : 8,
            fillColor: isSelected ? '#ef4444' : '#3b82f6',
            color: '#ffffff',
            weight: isSelected ? 3 : 2,
            opacity: 1,
            fillOpacity: 0.85
        })
        
        // Hover interactions
        marker.on('mouseover', function () {
            this.setStyle({
                fillColor: '#10b981', // Emerald-500
                radius: 12
            })
        })
        
        marker.on('mouseout', function () {
            const isSel = selectedDesa.value && 
                          selectedDesa.value.name === desa.name && 
                          selectedDesa.value.kecamatan === desa.kecamatan
            this.setStyle({
                fillColor: isSel ? '#ef4444' : '#3b82f6',
                radius: isSel ? 11 : 8
            })
        })
        
        // Click interaction
        marker.on('click', () => {
            selectVillage(desa, false)
        })

        // Popup content
        const popupContent = `
            <div class="p-2.5 font-sans min-w-[210px] space-y-1.5">
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h4 class="font-bold text-slate-800 text-sm tracking-tight leading-none">${desa.name}</h4>
                </div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kec. ${desa.kecamatan}</p>
                <div class="text-[11px] text-slate-650 space-y-1 font-mono bg-slate-50 p-2 rounded-lg border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                    <div><b class="text-slate-400 font-sans">Lat:</b> ${desa.lat}</div>
                    <div><b class="text-slate-400 font-sans">Lng:</b> ${desa.lng}</div>
                </div>
                <div class="flex flex-col gap-1.5 pt-1">
                    <a href="${desa.gmaps}" target="_blank" class="flex items-center justify-center gap-1 py-1.5 rounded-lg text-[11px] font-bold bg-blue-600 hover:bg-blue-700 !text-white transition-all shadow-xs">
                        🗺️ Petunjuk Google Maps
                    </a>
                    <a href="/app/launch?name=${encodeURIComponent('Website Desa ' + desa.name)}&url=${encodeURIComponent('https://' + desa.domain)}" target="_blank" class="flex items-center justify-center gap-1 py-1.5 rounded-lg text-[11px] font-bold border border-slate-200 hover:bg-slate-50 text-slate-750 transition-all dark:border-slate-700 dark:text-slate-205 dark:hover:bg-slate-800">
                        🌐 Kunjungi Website Desa
                    </a>
                </div>
            </div>
        `
        
        marker.bindPopup(popupContent)
        marker.addTo(markersLayer)
        
        const markerKey = `${desa.kecamatan}-${desa.name}`
        villageMarkers.value[markerKey] = marker
    })
}

// Watchers
watch(filteredVillages, (newList) => {
    // Automatically select the first village of the filtered kecamatan/search list if the current selection is no longer valid
    if (newList.length > 0) {
        const stillExists = newList.some(
            v => v.name === selectedDesa.value?.name && v.kecamatan === selectedDesa.value?.kecamatan
        )
        if (!stillExists) {
            // Select first matching village without panning map, keeping map zoom focus on bounds
            selectVillage(newList[0], false)
        }
    } else {
        selectedDesa.value = null
    }
}, { deep: true })

watch(selectedKecamatan, () => {
    updateMapMarkers()
    
    // Fit map bounds to show only filtered markers
    if (mapInstance && filteredVillages.value.length > 0) {
        const markers = Object.values(villageMarkers.value)
        if (markers.length > 0) {
            const group = L.featureGroup(markers)
            mapInstance.fitBounds(group.getBounds(), { padding: [50, 50] })
        }
    }
})

watch(searchQuery, () => {
    updateMapMarkers()
})

onMounted(() => {
    // Initialize map
    mapInstance = L.map(mapContainer.value, {
        scrollWheelZoom: false,
        dragging: !isMobile.value,
        touchZoom: !isMobile.value
    }).setView([-2.38, 106.20], 10)
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapInstance)
    
    markersLayer = L.layerGroup().addTo(mapInstance)
    
    // Draw initial markers
    updateMapMarkers()
    
    // Select default village marker on map ( Nibung )
    if (selectedDesa.value) {
        setTimeout(() => {
            selectVillage(selectedDesa.value, true)
        }, 600)
    }
})
</script>

<template>
    
    <div class="mt-2 space-y-6 animate-fade-in-up font-sans text-slate-700 dark:text-slate-350">

        <!-- STATS & FILTER ROW -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12 items-center bg-slate-50/50 dark:bg-slate-950/20 p-4 rounded-3xl border border-slate-200/60 dark:border-slate-800/80">
            <!-- Dynamic Stats (Left, Icons Removed) -->
            <div class="grid grid-cols-2 gap-3.5 md:col-span-6">
                <!-- Stat 1: Kecamatan -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/70 dark:border-slate-800/85 p-4 shadow-xs flex flex-col justify-center transition-all hover:scale-[1.01]">
                    <div class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">Kecamatan</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                        {{ totalKecamatan }}
                    </div>
                </div>

                <!-- Stat 2: Desa -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/70 dark:border-slate-800/85 p-4 shadow-xs flex flex-col justify-center transition-all hover:scale-[1.01]">
                    <div class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">Desa Aktif</div>
                    <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                        {{ totalDesa }}
                    </div>
                </div>
            </div>

            <!-- Filters (Right) -->
            <div class="flex flex-col sm:flex-row gap-3.5 md:col-span-6 justify-end w-full">
                <!-- Dropdown Filter Kecamatan (Icon Removed, Fixed double arrow) -->
                <div class="relative w-full sm:w-60">
                    <select
                        v-model="selectedKecamatan"
                        class="custom-select w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition cursor-pointer"
                    >
                        <option value="">Semua Kecamatan</option>
                        <option v-for="kec in kecamatanList" :key="kec" :value="kec">
                            Kecamatan {{ kec }}
                        </option>
                    </select>

                </div>

                <!-- Search Input with interactive suggestions -->
                <div class="relative w-full sm:w-64">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama desa..."
                        @focus="showSuggestions = true"
                        @blur="handleBlur"
                        @keydown.enter="handleSearchEnter"
                        class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-hidden focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>

                    <!-- Autocomplete Suggestions List -->
                    <div
                        v-if="showSuggestions && suggestions.length > 0"
                        class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg z-50 overflow-hidden max-h-60 overflow-y-auto"
                    >
                        <button
                            v-for="desa in suggestions"
                            :key="desa.name + desa.kecamatan"
                            @mousedown="selectSuggestion(desa)"
                            class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 transition flex items-center justify-between border-b border-slate-100 last:border-0 dark:border-slate-800"
                        >
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ desa.name }}</span>
                                <span class="text-[10px] text-slate-400 ml-2">Kec. {{ desa.kecamatan }}</span>
                            </div>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase">Pilih</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN LAYOUT: MAP + DETAILS -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Map Container (Left) -->
            <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Peta Lokasi Desa Bangka Tengah
                        </h3>
                    </div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">
                        Arahkan kursor atau klik titik marker
                    </span>
                </div>
                <!-- Leaflet container -->
                <div class="h-[460px] w-full relative z-0">
                    <!-- Mobile Overlay hint to prevent scroll hijacking -->
                    <div
                        v-if="isMobile && !isMapInteractive"
                        @click="toggleMapInteraction"
                        class="absolute inset-0 bg-slate-900/10 backdrop-blur-[1px] z-10 flex items-center justify-center cursor-pointer p-4 transition-all"
                    >
                        <button
                            type="button"
                            class="px-4 py-2 bg-slate-900/85 text-white text-xs font-bold rounded-xl shadow-lg border border-white/20 flex items-center gap-2"
                        >
                            <span>👆 Klik untuk Interaksi Peta</span>
                        </button>
                    </div>

                    <!-- Toggle back button when map is unlocked on mobile -->
                    <button
                        v-if="isMobile && isMapInteractive"
                        @click="toggleMapInteraction"
                        type="button"
                        class="absolute top-3 right-3 z-10 px-3 py-1.5 bg-white/90 dark:bg-slate-800/90 text-slate-800 dark:text-white text-xs font-extrabold rounded-lg shadow-md border border-slate-200 dark:border-slate-700 flex items-center gap-1.5"
                    >
                        <span>🔒 Kunci Peta</span>
                    </button>

                    <div class="h-full w-full" ref="mapContainer"></div>
                </div>
            </div>

            <!-- Detail Info Card (Right) -->
            <div class="lg:col-span-4 flex flex-col">
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 flex-1 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-20 -bottom-20 h-48 w-48 rounded-full bg-blue-50/30 blur-2xl pointer-events-none" />
                    
                    <div class="space-y-6">
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Profil Lengkap Desa
                            </h3>
                        </div>

                        <!-- Content wrapper (Consistent font sizes) -->
                        <div v-if="selectedDesa" class="space-y-6 animate-fade-in-up">
                            <div>
                                <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-950/50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 mb-2.5">
                                    Kecamatan {{ selectedDesa.kecamatan }}
                                </span>
                                <h4 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight leading-tight">
                                    {{ selectedDesa.name }}
                                </h4>
                            </div>

                            <div class="space-y-4">
                                <!-- Coordinates Box -->
                                <div class="bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 space-y-2">
                                    <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Koordinat Geografis</div>
                                    <div class="grid grid-cols-2 gap-4 text-sm font-semibold">
                                        <div>
                                            <span class="block text-[10px] text-slate-400 font-bold">Latitude</span>
                                            <span class="font-mono text-slate-800 dark:text-white">{{ selectedDesa.lat }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] text-slate-400 font-bold">Longitude</span>
                                            <span class="font-mono text-slate-800 dark:text-white">{{ selectedDesa.lng }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alamat -->
                                <div class="space-y-1">
                                    <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Alamat Desa</div>
                                    <p class="text-sm text-slate-600 dark:text-slate-450 leading-relaxed font-semibold">
                                        {{ selectedDesa.address }}
                                    </p>
                                </div>

                                <!-- Web Address Info -->
                                <div class="space-y-1">
                                    <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Domain Resmi</div>
                                    <p class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400 select-all">
                                        {{ selectedDesa.domain }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-20 text-slate-400 dark:text-slate-500 space-y-3">
                            <div class="text-5xl">📍</div>
                            <p class="text-xs font-bold leading-relaxed max-w-[220px] mx-auto">Silakan pilih salah satu desa di peta atau tabel untuk menampilkan profil lengkap.</p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div v-if="selectedDesa" class="pt-6 border-t border-slate-100 dark:border-slate-800 mt-6 space-y-3 relative z-10">
                        <a
                            :href="selectedDesa.gmaps"
                            target="_blank"
                            class="flex items-center justify-center gap-2 w-full py-3 rounded-2xl text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all duration-300 shadow-xs cursor-pointer shadow-blue-500/10"
                        >
                            <span>🗺️ Penunjuk Arah Google Maps</span>
                            <svg xmlns="http://www.w3.org/2500/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>

                        <a
                            :href="'/app/launch?name=' + encodeURIComponent('Website Desa ' + selectedDesa.name) + '&url=' + encodeURIComponent('https://' + selectedDesa.domain)"
                            target="_blank"
                            class="flex items-center justify-center gap-2 w-full py-3 rounded-2xl text-sm font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-750 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-300 cursor-pointer shadow-xs"
                        >
                            <span>🌐 Kunjungi Website Desa</span>
                            <svg xmlns="http://www.w3.org/2500/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0l-3-3m3 3h-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- VILLAGES TABLE CARD -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Daftar Domain Desa.id Bangka Tengah
                </h3>
            </div>
            
            <div class="overflow-x-auto max-h-[420px] overflow-y-auto scrollbar-thin">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-950 z-10">
                        <tr class="text-[12px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/70 dark:border-slate-800">
                            <th class="py-3.5 px-6">No</th>
                            <th class="py-3.5 px-6">Nama Desa</th>
                            <th class="py-3.5 px-6">Kecamatan</th>
                            <th class="py-3.5 px-6">Domain Website Desa</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <tr
                            v-for="(desa, index) in filteredVillages"
                            :key="desa.name + desa.kecamatan"
                            @click="selectVillage(desa, true)"
                            class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-all cursor-pointer border-l-4 border-l-transparent hover:bg-blue-50/20 dark:hover:bg-slate-800/40"
                            :class="{ 
                                'bg-blue-50/40 dark:bg-blue-950/20 text-blue-700 dark:text-white border-l-blue-600 font-bold': 
                                selectedDesa && selectedDesa.name === desa.name && selectedDesa.kecamatan === desa.kecamatan 
                            }"
                        >
                            <td class="py-3.5 px-6 font-mono text-[12px] text-slate-400">{{ index + 1 }}</td>
                            <td class="py-3.5 px-6 text-xs text-slate-800 dark:text-slate-100">
                                <span class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ desa.name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[12px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ desa.kecamatan }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-[12px] text-blue-600 dark:text-blue-400 select-all">{{ desa.domain }}</td>
                            <td class="py-3.5 px-6 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-3 text-[12px]">
                                    <button
                                        @click="selectVillage(desa, true)"
                                        class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 cursor-pointer"
                                    >
                                        📍 Lihat Peta
                                    </button>
                                    <a
                                        :href="'/app/launch?name=' + encodeURIComponent('Website Desa ' + desa.name) + '&url=' + encodeURIComponent('https://' + desa.domain)"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 font-bold text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
                                    >
                                        🌐 Buka Web
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredVillages.length === 0">
                            <td colspan="5" class="py-12 text-center text-[12px] text-slate-400 dark:text-slate-500 font-medium">
                                Tidak ada desa yang cocok dengan kriteria pencarian Anda.
                            </td>
                        </tr>
                    </tbody>
                </table>
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
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Custom select to fix double arrow */
.custom-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 2.5rem; /* space for arrow icon */
}

/* Custom scrollbar styling for comfortable desktop list viewing */
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #334155;
}

/* Custom Leaflet style overrides to match dark/light theme */
:deep(.leaflet-bar) {
    border: 1px solid rgb(226, 232, 240) !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
    border-radius: 12px !important;
    overflow: hidden;
}
:deep(.leaflet-bar a) {
    background-color: white !important;
    color: rgb(51, 65, 85) !important;
    border-bottom: 1px solid rgb(241, 245, 249) !important;
    transition: background-color 0.2s;
}
:deep(.leaflet-bar a:hover) {
    background-color: rgb(248, 250, 252) !important;
}
.dark :deep(.leaflet-bar) {
    border-color: rgb(30, 41, 59) !important;
}
.dark :deep(.leaflet-bar a) {
    background-color: rgb(15, 23, 42) !important;
    color: rgb(226, 232, 240) !important;
    border-bottom-color: rgb(30, 41, 59) !important;
}
.dark :deep(.leaflet-bar a:hover) {
    background-color: rgb(30, 41, 59) !important;
}
:deep(.leaflet-popup-content-wrapper) {
    border-radius: 16px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
    border: 1px solid rgb(241, 245, 249) !important;
    padding: 2px !important;
}
.dark :deep(.leaflet-popup-content-wrapper) {
    background: rgb(15, 23, 42) !important;
    color: white !important;
    border-color: rgb(30, 41, 59) !important;
}
:deep(.leaflet-popup-tip) {
    background: white !important;
}
.dark :deep(.leaflet-popup-tip) {
    background: rgb(15, 23, 42) !important;
}
</style>
