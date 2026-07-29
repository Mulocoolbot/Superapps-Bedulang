<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import Hls from 'hls.js'

const props = defineProps({
    cctv: {
        type: Object,
        required: true
    }
})

const videoRef = ref(null)
const status = ref('loading') // 'loading' | 'online' | 'offline'
let hls = null
let timeoutId = null

const resolveStreamUrl = (streamUrl) => {
    if (!streamUrl) return ''
    try {
        const url = new URL(streamUrl)
        return `${url.protocol}//${window.location.hostname}:8888${url.pathname}${url.search}`
    } catch (e) {
        return streamUrl
    }
}

const initPlayer = () => {
    nextTick(() => {
        if (!videoRef.value) return
        const streamUrl = resolveStreamUrl(props.cctv.stream)

        // Set safety timeout of 5 seconds to detect offline if loadedmetadata or play doesn't trigger
        timeoutId = setTimeout(() => {
            if (status.value === 'loading') {
                status.value = 'offline'
                destroyHls()
            }
        }, 5000)

        if (hls) {
            hls.destroy()
        }

        if (Hls.isSupported()) {
            hls = new Hls({
                maxBufferLength: 5,
                maxMaxBufferLength: 10,
                manifestLoadingTimeOut: 3000,
                manifestLoadingMaxRetry: 1,
                levelLoadingTimeOut: 3000,
                levelLoadingMaxRetry: 1,
                fragLoadingTimeOut: 3000,
                fragLoadingMaxRetry: 1,
            })
            hls.loadSource(streamUrl)
            hls.attachMedia(videoRef.value)
            
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (status.value !== 'offline') {
                    videoRef.value.play().then(() => {
                        status.value = 'online'
                    }).catch(() => {
                        // Playback might be blocked by browser autoplay policy, but manifest is loaded, so it's online
                        status.value = 'online'
                    })
                }
            })

            hls.on(Hls.Events.ERROR, (event, data) => {
                if (data.fatal) {
                    status.value = 'offline'
                    destroyHls()
                }
            })
        } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
            videoRef.value.src = streamUrl
            videoRef.value.addEventListener('loadedmetadata', () => {
                if (status.value !== 'offline') {
                    videoRef.value.play().then(() => {
                        status.value = 'online'
                    }).catch(() => {
                        status.value = 'online'
                    })
                }
            })
            videoRef.value.addEventListener('error', () => {
                status.value = 'offline'
            })
        }
    })
}

const destroyHls = () => {
    if (hls) {
        hls.destroy()
        hls = null
    }
}

onMounted(() => {
    initPlayer()
})

onBeforeUnmount(() => {
    destroyHls()
    if (timeoutId) {
        clearTimeout(timeoutId)
    }
})
</script>

<template>
    <div class="relative bg-slate-950 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col group transition hover:shadow-md">
        <!-- Top Overlay Info Bar -->
        <div class="absolute top-3 left-3 right-3 z-10 flex items-center justify-between pointer-events-none">
            <!-- Camera Name -->
            <span class="px-2.5 py-1 text-xs font-bold text-white bg-slate-900/80 backdrop-blur-md rounded-lg shadow-sm">
                {{ cctv.name }}
            </span>

            <!-- Status Indicator -->
            <span 
                class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-sm backdrop-blur-md flex items-center gap-1.5"
                :class="{
                    'bg-emerald-950/80 text-emerald-400 border border-emerald-500/30': status === 'online',
                    'bg-rose-950/80 text-rose-400 border border-rose-500/30': status === 'offline',
                    'bg-amber-950/80 text-amber-400 border border-amber-500/30': status === 'loading'
                }"
            >
                <span 
                    class="w-2 h-2 rounded-full"
                    :class="{
                        'bg-emerald-500 animate-pulse': status === 'online',
                        'bg-rose-505': status === 'offline', // fix potential typos
                        'bg-rose-500': status === 'offline',
                        'bg-amber-500 animate-pulse': status === 'loading'
                    }"
                ></span>
                {{ status === 'online' ? 'Online' : status === 'offline' ? 'Mati' : 'Loading' }}
            </span>
        </div>

        <!-- Video Aspect Screen -->
        <div class="aspect-video w-full bg-slate-950 relative flex items-center justify-center overflow-hidden">
            <video
                ref="videoRef"
                muted
                playsinline
                class="w-full h-full object-cover transition-opacity duration-300"
                :class="{ 'opacity-0 pointer-events-none': status !== 'online', 'opacity-100': status === 'online' }"
            ></video>

            <!-- Loading State View -->
            <div 
                v-if="status === 'loading'" 
                class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center gap-2"
            >
                <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs text-slate-400 font-medium">Menghubungkan kamera...</span>
            </div>

            <!-- Offline / Dead State View -->
            <div 
                v-if="status === 'offline'" 
                class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center gap-2.5 px-4 text-center"
            >
                <div class="p-3 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M12 18.75H4.5a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25H12A2.25 2.25 0 0 1 14.25 9v7.5A2.25 2.25 0 0 1 12 18.75Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-rose-500">CCTV Mati</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kamera tidak aktif atau stream tidak dapat dijangkau</p>
                </div>
            </div>
        </div>
    </div>
</template>
