<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import Footer from '@/Components/Front/Footer.vue'

const props = defineProps({
    appName: {
        type: String,
        default: 'Aplikasi Daerah'
    },
    targetUrl: {
        type: String,
        default: ''
    },
    checkedAt: {
        type: String,
        default: ''
    },
    reason: {
        type: String,
        default: 'Server sedang mengalami pemeliharaan rutin atau tidak dapat diakses saat ini.'
    }
})

const isChecking = ref(false)
const checkMessage = ref('')
const checkStatusType = ref('') // 'success' | 'error' | ''

const handleRetry = async () => {
    if (!props.targetUrl) {
        checkMessage.value = 'URL aplikasi tidak tersedia untuk diperiksa.'
        checkStatusType.value = 'error'
        return
    }

    isChecking.value = true
    checkMessage.value = 'Memeriksa status server aplikasi...'
    checkStatusType.value = ''

    try {
        const response = await fetch(`/api/check-app-status?url=${encodeURIComponent(props.targetUrl)}`)
        const data = await response.json()

        if (data.is_online) {
            checkMessage.value = 'Aplikasi sudah aktif! Mengalihkan Anda sekarang...'
            checkStatusType.value = 'success'
            setTimeout(() => {
                window.location.href = props.targetUrl
            }, 1200)
        } else {
            checkMessage.value = 'Aplikasi masih belum dapat dijangkau. Silakan coba beberapa saat lagi.'
            checkStatusType.value = 'error'
        }
    } catch (err) {
        checkMessage.value = 'Gagal memeriksa koneksi server.'
        checkStatusType.value = 'error'
    } finally {
        isChecking.value = false
    }
}
</script>

<template>
    <FrontLayout>
        <Head :title="`Pemeliharaan - ${appName}`" />

        <div class="relative min-h-screen bg-slate-950 text-white flex flex-col justify-between overflow-hidden selection:bg-amber-500 selection:text-slate-950">
            <!-- Dynamic ambient background lighting -->
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-tr from-amber-600/20 via-blue-600/20 to-indigo-600/10 rounded-full blur-[140px] pointer-events-none"></div>
            <div class="absolute bottom-10 right-10 w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>

            <!-- Background SVG Pattern -->
            <div class="absolute inset-0 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none"></div>

            <div class="relative z-10 mx-auto max-w-4xl px-6 pt-28 pb-16 w-full flex-1 flex flex-col justify-center items-center text-center">
                
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-500/10 border border-amber-500/30 backdrop-blur-md mb-8 animate-fade-in">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400">
                        Status: Pemeliharaan Sistem
                    </span>
                </div>

                <!-- Modern Graphic Illustration -->
                <div class="relative mb-10 group">
                    <!-- Glowing aura -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-amber-500 to-blue-600 rounded-full blur-2xl opacity-40 group-hover:opacity-60 transition duration-1000"></div>
                    
                    <div class="relative flex items-center justify-center w-32 h-32 md:w-40 md:h-40 rounded-3xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl shadow-2xl">
                        <!-- Wrench & Server SVG Icon with smooth gear rotation -->
                        <div class="relative">
                            <svg class="w-16 h-16 md:w-20 md:h-20 text-amber-400 animate-spin-slow" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.374.207.74.453 1.096.738.312.25.733.328 1.095.197l1.246-.445c.52-.186 1.098.053 1.347.545l.547.947c.25.492.128 1.095-.28 1.45l-1.01.875c-.297.257-.428.663-.342 1.042.067.297.106.602.115.912.012.386.183.743.486.974l1.048.795c.44.334.61 .928.406 1.442l-.4.998c-.204.514-.764.832-1.312.748l-1.3-.198c-.382-.059-.76.082-1.024.356-.308.319-.64.61-.992.871-.307.228-.485.597-.47.978l.056 1.306c.023.551-.357 1.03-.896 1.144l-1.07.227c-.54.114-1.07-.184-1.258-.696l-.448-1.22c-.13-.356-.44-.616-.816-.688-.415-.08-.82-.206-1.211-.377-.354-.155-.765-.11-1.075.116l-1.066.776c-.447.325-1.05.27-1.44-.127l-.77-.77c-.397-.39-.452-.993-.127-1.44l.776-1.066c.226-.31.271-.72.116-1.075-.17-.39-.297-.796-.377-1.211-.072-.376-.332-.685-.688-.816l-1.22-.448c-.512-.188-.81-.718-.696-1.258l.227-1.07c.114-.539.593-.919 1.144-.896l1.306.056c.381.015.75-.163.978-.47.26-.352.552-.684.871-.992.274-.264.415-.642.356-1.024l-.198-1.3c-.084-.548.234-1.108.748-1.312l.998-.4c.514-.204 1.108-.034 1.442.406l.795 1.048c.23.303.588.474.974.486.31.009.615.048.912.115.379.086.785-.045 1.042-.342l.875-1.01z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div class="absolute -bottom-1 -right-1 bg-amber-500 text-slate-950 p-1.5 rounded-full shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Titles -->
                <h1 class="text-3xl md:text-5xl font-black tracking-tight text-white mb-4">
                    Website Sedang Dalam Pemeliharaan
                </h1>
                
                <p class="text-base md:text-lg text-slate-400 font-medium max-w-2xl leading-relaxed mb-8">
                    Layanan portal <span class="text-amber-400 font-bold underline decoration-amber-500/40 decoration-2 underline-offset-4">{{ appName }}</span> sedang dalam proses pemeliharaan sistem atau peningkatan kualitas koneksi.
                </p>

                <!-- Information Details Box -->
                <div class="w-full max-w-xl bg-slate-900/80 border border-slate-800 rounded-2xl p-5 md:p-6 backdrop-blur-md mb-8 text-left space-y-3.5 shadow-xl">
                    <div class="flex items-center justify-between text-xs md:text-sm border-b border-slate-800 pb-3">
                        <span class="text-slate-400 font-medium">Aplikasi Target</span>
                        <span class="font-bold text-white">{{ appName }}</span>
                    </div>
                    
                    <div v-if="targetUrl" class="flex items-center justify-between text-xs md:text-sm border-b border-slate-800 pb-3">
                        <span class="text-slate-400 font-medium">Alamat Website</span>
                        <a :href="targetUrl" target="_blank" class="font-mono text-blue-400 hover:text-blue-300 truncate max-w-[240px] md:max-w-[320px] transition">
                            {{ targetUrl }}
                        </a>
                    </div>

                    <div class="flex items-center justify-between text-xs md:text-sm border-b border-slate-800 pb-3">
                        <span class="text-slate-400 font-medium">Waktu Pemeriksaan</span>
                        <span class="font-mono text-slate-300">{{ checkedAt || 'Baru saja' }}</span>
                    </div>

                    <div class="flex flex-col gap-1 text-xs md:text-sm pt-1">
                        <span class="text-slate-400 font-medium">Keterangan Status:</span>
                        <span class="text-slate-300 font-medium leading-relaxed bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 text-amber-300/90">
                            {{ reason }}
                        </span>
                    </div>
                </div>

                <!-- Live Status Message Alert -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-2 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-2 opacity-0"
                >
                    <div 
                        v-if="checkMessage" 
                        class="mb-6 p-4 rounded-xl text-sm font-semibold max-w-xl w-full flex items-center justify-center gap-2 shadow-lg"
                        :class="checkStatusType === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/60' : 'bg-rose-950/80 text-rose-300 border border-rose-800/60'"
                    >
                        <svg v-if="checkStatusType === 'success'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ checkMessage }}</span>
                    </div>
                </Transition>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full max-w-md">
                    <!-- Recheck Button -->
                    <button
                        @click="handleRetry"
                        :disabled="isChecking"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm transition-all duration-200 shadow-lg shadow-amber-500/20 active:scale-[0.98] disabled:opacity-60 cursor-pointer"
                    >
                        <svg 
                            class="w-4 h-4" 
                            :class="{ 'animate-spin': isChecking }" 
                            fill="none" 
                            stroke="currentColor" 
                            stroke-width="2.5" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>{{ isChecking ? 'Pemeriksaan...' : 'Coba Lagi' }}</span>
                    </button>

                    <!-- Back to Home -->
                    <Link
                        href="/"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-white font-bold text-sm transition-all duration-200 shadow-md active:scale-[0.98]"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </Link>
                </div>

            </div>

            <!-- Footer -->
            <Footer />
        </div>
    </FrontLayout>
</template>

<style scoped>
@keyframes spinSlow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
.animate-spin-slow {
    animation: spinSlow 20s linear infinite;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in {
    animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
