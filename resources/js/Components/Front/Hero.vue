<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Slider state using virtual offset for smooth infinite scrolling
const virtualOffset = ref(300) // Start at a mid-point multiple of 3
const activeIndex = computed(() => ((virtualOffset.value % 3) + 3) % 3)

const animateWelcome = ref(false)
const animateTentang = ref(false)

const progress = ref(0)
const isWaiting = ref(true)

const slides = [
    {
        titleHtml: 'Filosofi <span class="text-blue-600">BEDULANG</span>',
        description: 'Sebagaimana tradisi bedulang menyatukan masyarakat dalam satu hidangan, superapp bedulang menyatukan seluruh layanan digital pemerintah Kab. Bangka Tengah dalam satu platform digital',
        image: '/services/filosofi_bedulang.jpg'
    },
    {
        titleHtml: '<span class="text-blue-600">Visi</span>',
        description: '"Satu Aplikasi, Satu Layanan Terpadu, Satu Inovasi untuk Bangka Tengah."',
        image: '/services/ketawai.jpg'
    },
    {
        titleHtml: '<span class="text-blue-600">Misi</span>',
        descriptionList: [
            'Integrasi seluruh layanan pemerintah.',
            'Inovasi pelayanan berbasis digital.',
            'Kolaborasi antar perangkat daerah.',
            'Kemudahan akses layanan bagi masyarakat.'
        ],
        image: '/services/misi_kaolin.jpg'
    }
]


// Logic for calculating style for each card to achieve "peeking edges" infinite layout
const getCardStyle = (index) => {
    const len = slides.length
    let diff = index - activeIndex.value
    
    // Normalize diff to the range [-1, 1] for 3 slides
    if (diff > 1) diff -= len
    if (diff < -1) diff += len
    
    if (diff === 0) {
        return {
            transform: 'translateX(0) scale(1)',
            opacity: 1,
            zIndex: 20,
            pointerEvents: 'auto'
        }
    } else if (diff === 1 || diff === -len + 1) {
        return {
            transform: 'translateX(82%) scale(0.9)',
            opacity: 0.4,
            zIndex: 10,
            pointerEvents: 'none'
        }
    } else {
        return {
            transform: 'translateX(-82%) scale(0.9)',
            opacity: 0.4,
            zIndex: 10,
            pointerEvents: 'none'
        }
    }
}

let timerId = null
const slideDuration = 5000 // 5 seconds progress bar
const delayDuration = 3000 // 3 seconds delay
let state = 'waiting' // 'waiting' or 'progressing'
let waitTime = 0
let progressTime = 0
const intervalMs = 30

const startTimers = () => {
    stopTimers()
    state = 'waiting'
    isWaiting.value = true
    progress.value = 0
    waitTime = 0
    progressTime = 0
    
    timerId = setInterval(() => {
        if (state === 'waiting') {
            waitTime += intervalMs
            if (waitTime >= delayDuration) {
                state = 'progressing'
                isWaiting.value = false
            }
        } else if (state === 'progressing') {
            progressTime += intervalMs
            progress.value = Math.min((progressTime / slideDuration) * 100, 100)
            if (progressTime >= slideDuration) {
                nextSlide()
            }
        }
    }, intervalMs)
}

const stopTimers = () => {
    if (timerId) {
        clearInterval(timerId)
        timerId = null
    }
}

const nextSlide = () => {
    virtualOffset.value++
    startTimers()
}

const prevSlide = () => {
    virtualOffset.value--
    startTimers()
}

const goToSlide = (targetOffset) => {
    // Find shortest step to target offset to keep sliding transition smooth
    const targetIdx = ((targetOffset % 3) + 3) % 3
    const currentIdx = activeIndex.value
    let diff = targetIdx - currentIdx
    if (diff > 1) diff -= 3
    if (diff < -1) diff += 3
    virtualOffset.value += diff
    startTimers()
}

// Custom event handler for Navbar sub-menu clicks
const handleAboutSlideSelect = (e) => {
    if (e.detail && typeof e.detail.index === 'number') {
        // Map navbar clicks: 1 -> 0 (Filosofi), 2 -> 1 (Visi), 3 -> 2 (Misi)
        const targetIdx = e.detail.index - 1
        goToSlide(targetIdx)
        
        // Smooth scroll to Tentang section
        const tentangSection = document.getElementById('tentang')
        if (tentangSection) {
            tentangSection.scrollIntoView({ behavior: 'smooth' })
        }
    }
}

const goToServicesSection = (tab) => {
    const servicesSection = document.getElementById('layanan')
    if (servicesSection) {
        servicesSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }

    window.dispatchEvent(new CustomEvent('select-services-tab', {
        detail: { tab }
    }))
}

onMounted(() => {
    startTimers()
    window.addEventListener('select-about-slide', handleAboutSlideSelect)

    // Scroll entrance animations via Intersection Observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                if (entry.target.id === 'beranda') {
                    animateWelcome.value = true
                } else if (entry.target.id === 'tentang') {
                    animateTentang.value = true
                }
                observer.unobserve(entry.target)
            }
        })
    }, { threshold: 0.08 })

    const welcomeEl = document.getElementById('beranda')
    const tentangEl = document.getElementById('tentang')
    if (welcomeEl) observer.observe(welcomeEl)
    if (tentangEl) observer.observe(tentangEl)

    // Check URL parameters for sliding or highlighting when redirected from other pages
    const urlParams = new URLSearchParams(window.location.search)
    const slideParam = urlParams.get('slide')
    if (slideParam) {
        setTimeout(() => {
            const idx = parseInt(slideParam) - 1
            goToSlide(idx)
            // Clear URL param to prevent slide popping on subsequent manual slides
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '#tentang'
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl)
        }, 500)
    }
})

onUnmounted(() => {
    stopTimers()
    window.removeEventListener('select-about-slide', handleAboutSlideSelect)
})
</script>

<template>
    <!-- 1. SECTION WELCOME (FULL-WIDTH BANNER WITH BLUE GRADIENT OVERLAY & SVG WAVE) -->
    <section 
        id="beranda"
        class="relative bg-cover bg-center bg-no-repeat pt-28 pb-36 min-h-[650px] overflow-hidden flex items-center"
        style="background-image: url('/services/bg2.png');"
    >
        <!-- Blue opacity overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#0F3D77]/95 via-[#0F3D77]/85 to-[#0b2b52]/95 dark:from-slate-950/95 dark:via-slate-950/90 dark:to-slate-955 z-0"></div>

        <div 
            class="container mx-auto px-6 relative z-10 transition-all duration-[1200ms] transform ease-out"
            :class="animateWelcome ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
        >
            <div class="grid lg:grid-cols-2 items-center gap-12">
                <!-- Left Column -->
                <div class="z-10 flex flex-col justify-center text-white max-w-xl">
                    <h1 class="text-5xl font-bold leading-tight text-white">
                        Selamat Datang di
                        <span class="text-blue-300 dark:text-blue-400">BEDULANG</span>
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-blue-100/90">
                        BEDULANG merupakan platform layanan digital Pemerintah Kabupaten Bangka Tengah yang menghadirkan berbagai layanan publik dalam satu aplikasi. Masyarakat dapat mengakses informasi, pelayanan, serta berbagai kebutuhan administrasi dengan lebih mudah, cepat, dan transparan.
                    </p>

                    <div class="mt-10 flex gap-4">
                        <a
                            href="#layanan"
                            @click.prevent="goToServicesSection('publik')"
                            class="px-7 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold transition shadow-lg shadow-blue-900/40 hover:shadow-xl active:scale-[0.98]"
                        >
                            Masuk Layanan
                        </a>

                        <a
                            href="#layanan"
                            @click.prevent="goToServicesSection('info')"
                            class="px-7 py-3 rounded-xl border border-white/20 bg-white/10 text-white hover:bg-white/20 transition shadow-sm active:scale-[0.98]"
                        >
                            Lihat Informasi
                        </a>
                    </div>
                </div>

                <!-- Right Column (Empty placeholder to allow absolute positioned image) -->
                <div class="hidden lg:block h-[450px]"></div>
            </div>
        </div>

        <!-- Bupati & Wakil Bupati absolute bottom right -->
        <img
            src="/services/bupat-wakil.png"
            alt="Bupati dan Wakil Bupati Bangka Tengah"
            class="hidden lg:block absolute bottom-0 right-0 left-70 h-[680px] w-auto object-contain transition-all duration-300"
            />

        
    </section>

    <!-- 2. SECTION TENTANG (DYNAMIC INFINITE CAROUSEL WITH GRADIENT BACKGROUND) -->
    <section
        id="tentang"
        class="bg-gradient-to-b from-white via-blue-50/40 to-blue-50/10 dark:from-gray-950 dark:via-slate-900/50 dark:to-gray-950 border-b border-gray-100/50 dark:border-gray-900/50 py-20"
    >
        <div 
            class="container mx-auto px-6 relative transition-all duration-[1200ms] transform ease-out"
            :class="animateTentang ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
        >
            
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Tentang <span class="text-blue-600 dark:text-blue-400">BEDULANG</span>
                </h2>
            </div>

            <!-- CAROUSEL TRACK VIEWPORT CONTAINER -->
            <div class="relative w-full overflow-hidden py-4 h-[550px] lg:h-[480px]">
                <div class="relative w-full h-full flex justify-center items-center">
                    
                    <!-- Left/Right Manual Arrow Buttons (Absolute at viewport edges) -->
                    <button
                        @click="prevSlide"
                        class="absolute left-2 lg:left-8 top-1/2 -translate-y-1/2 z-30 flex items-center justify-center w-12 h-12 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 transition focus:outline-none"
                        aria-label="Previous Slide"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    
                    <button
                        @click="nextSlide"
                        class="absolute right-2 lg:right-8 top-1/2 -translate-y-1/2 z-30 flex items-center justify-center w-12 h-12 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 transition focus:outline-none"
                        aria-label="Next Slide"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                    <!-- Infinite Loop Cards -->
                    <div 
                        v-for="(slide, index) in slides" 
                        :key="index"
                        class="absolute w-[82%] md:w-[85%] lg:w-[75%] h-full transition-all duration-700 ease-in-out rounded-3xl bg-gradient-to-r from-blue-50 via-white to-blue-100 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden"
                        :style="getCardStyle(index)"
                    >
                        <div class="grid lg:grid-cols-2 items-center h-full w-full">
                            <!-- Left Column -->
                            <div class="px-8 py-10 lg:px-14 flex flex-col justify-center h-full">
                                <h3 
                                    class="text-3xl lg:text-4xl font-extrabold leading-tight text-slate-900 dark:text-white"
                                    v-html="slide.titleHtml"
                                ></h3>

                                <p 
                                    class="mt-6 text-base lg:text-lg leading-relaxed text-slate-600 dark:text-slate-350 min-h-[120px] font-medium"
                                    v-if="slide.description"
                                >
                                    {{ slide.description }}
                                </p>
                                <ul 
                                    class="mt-6 text-base lg:text-lg leading-relaxed text-slate-600 dark:text-slate-350 min-h-[120px] list-decimal pl-6 space-y-1 font-medium"
                                    v-else-if="slide.descriptionList"
                                >
                                    <li v-for="(item, idx) in slide.descriptionList" :key="idx" class="pl-1">{{ item }}</li>
                                </ul>
                                <div class="mt-6 min-h-[120px]" v-else></div>
                            </div>

                            <!-- Right Column (Image) -->
                            <div class="relative h-full hidden lg:block overflow-hidden">
                                <img
                                    :src="slide.image"
                                    alt="Slide Image"
                                    class="absolute inset-0 h-full w-full object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-r from-white via-white/40 to-transparent dark:from-gray-900 dark:via-gray-900/40"></div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- ROTATING DOTS INDICATOR -->
            <div class="flex justify-center items-center gap-3 mt-8">
                <button
                    v-for="i in slides.length"
                    :key="i"
                    @click="goToSlide(i - 1)"
                    class="h-3 rounded-full transition-all duration-500 ease-in-out relative overflow-hidden focus:outline-none flex-shrink-0"
                    :class="activeIndex === (i - 1) ? 'w-10 bg-blue-100 dark:bg-blue-900/30 border border-blue-200/50' : 'w-3 bg-gray-300 dark:bg-gray-600'"
                    :aria-label="`Slide ${i}`"
                >
                    <!-- Progress fill (active slide only) -->
                    <div
                        v-if="activeIndex === (i - 1)"
                        class="absolute top-0 left-0 h-full bg-blue-600 rounded-full"
                        :style="{ width: `${progress}%` }"
                    ></div>
                </button>
            </div>
            
        </div>
    </section>
</template>