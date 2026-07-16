<script setup>
import { ref, onMounted } from 'vue'

const animate = ref(false)

onMounted(() => {
    // Delay slightly to trigger smooth animations after mount
    setTimeout(() => {
        animate.value = true
    }, 150)
})

const scrollToContent = () => {
    const welcomeSection = document.getElementById('beranda')
    if (welcomeSection) {
        welcomeSection.scrollIntoView({ behavior: 'smooth' })
    }
}
</script>

<template>
    <!-- LIGHT GRADIENT CONTAINER TO PREVENT BLACK BLANK SLOT -->
    <div class="relative w-full h-screen overflow-hidden flex flex-col justify-center items-center bg-gradient-to-br from-blue-50 via-white to-blue-100 dark:from-gray-950 dark:via-gray-950 dark:to-gray-900 transition-colors duration-300">
        <!-- BACKGROUND VIDEO -->
        <video
            autoplay
            loop
            muted
            playsinline
            class="absolute inset-0 w-full h-full object-cover z-0 opacity-70 dark:opacity-70"
        >
            <source src="/videos/hero-bg.mp4" type="video/mp4" />
        </video>

        <!-- LIGHT OVERLAY IN LIGHT MODE, DARK OVERLAY IN DARK MODE -->
        <div class="absolute inset-0 bg-white/50 dark:bg-black/40 z-0"></div>

        <!-- CENTER CONTENT (LOGO ONLY) -->
        <div class="relative z-10 flex flex-col items-center text-center px-6">
            <!-- Logo with entrance transition - Full centerpiece size -->
            <img
                src="/services/logobateng3.png"
                alt="Logo Bedulang"
                class="w-64 h-64 lg:w-80 lg:h-80 object-contain transition-all duration-1000 transform ease-out"
                :class="animate ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-8 scale-95'"
            />
        </div>

        <!-- BOTTOM GRADIENT TRANSITION TO SITE BACKGROUND -->
        <div class="absolute bottom-0 left-0 right-0 h-36 bg-gradient-to-t from-white dark:from-gray-900 to-transparent z-10 pointer-events-none"></div>

        <!-- SCROLL DOWN INDICATOR - High contrast color scheme -->
        <div 
            @click="scrollToContent"
            class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center cursor-pointer select-none transition-all duration-1000 delay-700 transform ease-out"
            :class="animate ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
        >
            <span class="text-xs text-gray-500 dark:text-white/50 uppercase tracking-widest mb-2 font-bold">Scroll Down</span>
            <div class="animate-bounce p-1.5 rounded-full bg-white/70 dark:bg-white/10 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 hover:bg-white/90 hover:scale-105 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-700 dark:text-white/80">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </div>
        </div>
    </div>
</template>
