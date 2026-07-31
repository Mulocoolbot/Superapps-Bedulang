<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ComplaintModal from '@/Components/ComplaintModal.vue'
import Footer from '@/Components/Front/Footer.vue'

// Import Segmented Dashboard components
import PerindagkopDashboard from './Dashboards/PerindagkopDashboard.vue'
import MbgDashboard from './Dashboards/MbgDashboard.vue'
import KepegawaianDashboard from './Dashboards/KepegawaianDashboard.vue'
import DesaDashboard from './Dashboards/DesaDashboard.vue'
import InformasiPublikDashboard from './Dashboards/InformasiPublikDashboard.vue'
import PemerintahanDashboard from './Dashboards/PemerintahanDashboard.vue'
import KdmpDashboard from './Dashboards/KdmpDashboard.vue'
import KesehatanDashboard from './Dashboards/KesehatanDashboard.vue'
import PendidikanDashboard from './Dashboards/PendidikanDashboard.vue'
import PariwisataDashboard from './Dashboards/PariwisataDashboard.vue'
import DefaultDashboard from './Dashboards/DefaultDashboard.vue'

const isComplaintModalOpen = ref(false)

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

// Dynamically resolve dashboard component by slug
const dashboardComponent = computed(() => {
    switch (props.service?.slug) {
        case 'perindagkop-ukm':
            return PerindagkopDashboard
        case 'makanan-bergizi-gratis':
            return MbgDashboard
        case 'kepegawaian':
            return KepegawaianDashboard
        case 'pertanian':
        case 'desa':
            return DesaDashboard
        case 'informasi-publik':
            return InformasiPublikDashboard
        case 'pemerintahan':
            return PemerintahanDashboard
        case 'kesehatan':
            return KesehatanDashboard
        case 'pendidikan':
            return PendidikanDashboard
        case 'hukum':
        case 'kdmp':
            return KdmpDashboard
        case 'pariwisata':
            return PariwisataDashboard
        default:
            return DefaultDashboard
    }
})

const title = computed(() => `Dashboard ${props.service?.name ?? ''}`)
const subtitle = computed(() => props.service?.description ?? '')
const serviceIcon = computed(() => props.service?.icon ?? '📊')

const serviceSourceUrl = computed(() => {
    switch (props.service?.slug) {
        case 'perindagkop-ukm':
            return 'https://disperindagkop-ukm.bangkatengahkab.go.id/'
        case 'makanan-bergizi-gratis':
            return 'https://mbg.pdm.kemendikdasmen.go.id/portal'
        case 'pertanian':
        case 'desa':
            return 'https://dpkp.bangkatengahkab.go.id/'
        case 'informasi-publik':
            return 'https://bangkatengahkab.go.id/'
        case 'kepegawaian':
            return 'https://bkpsdmd.bangkatengahkab.go.id/'
        default:
            return props.dashboard?.metadata?.source_url || ''
    }
})

const serviceSourceLabel = computed(() => {
    switch (props.service?.slug) {
        case 'perindagkop-ukm':
            return 'disperindagkop-ukm.bangkatengahkab.go.id'
        case 'makanan-bergizi-gratis':
            return 'mbg.pdm.kemendikdasmen.go.id/portal'
        case 'pertanian':
        case 'desa':
            return 'dpkp.bangkatengahkab.go.id'
        case 'informasi-publik':
            return 'bangkatengahkab.go.id'
        case 'kepegawaian':
            return 'bkpsdmd.bangkatengahkab.go.id'
        default:
            return props.dashboard?.metadata?.source || 'Portal Satu Data Bangka Tengah'
    }
})

const activeSubTab = ref('')
const handleTabChange = (tabId) => {
    activeSubTab.value = tabId
}
</script>

<template>
    <FrontLayout>
        <Head :title="title"></Head>

        <!-- Page background with bg layanan.svg -->
        <div 
            class="relative bg-cover bg-center bg-no-repeat min-h-screen"
            style="background-image: url('/services/bg layanan.svg');"
        >

            <div class="relative z-10 mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8 lg:pb-24 pt-24 sm:pt-28">

                <Link
                    href="/"
                    class="group mb-6 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold text-white transition bg-white/10 hover:bg-white/20 backdrop-blur-md shadow-xs border border-white/10"
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
                    Kembali
                </Link>

                <!-- Header -->
                <div class="relative overflow-hidden rounded-3xl border border-blue-100/80 dark:border-slate-800 bg-white/75 dark:bg-slate-900/85 backdrop-blur-md p-8 shadow-md sm:p-10 dark:shadow-slate-950/20 mb-10 animate-fade-in-up">
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-100/50 blur-3xl dark:bg-blue-950/20" />
                    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-blue-700 dark:text-blue-400">
                                <span>{{ serviceIcon }}</span>
                                <span>Data & Informasi</span>
                            </div>
                            <h1 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-3 text-sm md:text-base text-slate-500 dark:text-slate-400 font-medium">
                                {{ subtitle }}
                            </p>
                        </div>

                        <!-- Complaint Action Button -->
                        <div class="flex shrink-0 items-center">
                            <button
                                @click="isComplaintModalOpen = true"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-sm font-bold text-white px-5 py-3 transition-all duration-200 shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] cursor-pointer shrink-0"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                Ajukan Pengaduan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dynamically Rendered Segmented Dashboard with Smooth Transition -->
                <Transition name="page-fade" mode="out-in">
                    <component 
                        :is="dashboardComponent" 
                        :key="service?.slug"
                        :service="service" 
                        :dashboard="dashboard" 
                        @tab-change="handleTabChange"
                    />
                </Transition>

                <!-- Sumber Data Section (Footer data note inside page margin - Hidden for tab DPRD and Desa in Pemerintahan) -->
                <div v-if="service?.slug !== 'informasi-publik' && service?.slug !== 'pertanian' && service?.slug !== 'desa' && !(service?.slug === 'pemerintahan' && (activeSubTab === 'dprd' || activeSubTab === 'desa'))" class="mt-12 pt-6 border-t border-white/20 flex flex-col sm:flex-row items-center justify-between text-xs text-white">
                    <div class="flex items-center gap-1.5">
                        <span>Sumber Data:</span>
                        <a 
                            v-if="serviceSourceUrl"
                            :href="serviceSourceUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-bold text-blue-300 hover:text-blue-200 underline"
                        >
                            {{ serviceSourceLabel }}
                        </a>
                        <span v-else-if="dashboard.metadata?.source" class="font-bold text-white">
                            {{ dashboard.metadata.source }}
                        </span>
                        <span v-else class="font-bold text-white">
                            Portal Satu Data Bangka Tengah
                        </span>
                    </div>
                    <div class="mt-2 sm:mt-0 text-white font-semibold" v-if="dashboard.metadata?.updated_at">
                        Terakhir diperbarui: {{ dashboard.metadata.updated_at }}
                    </div>
                </div>

            </div>

            <!-- Gradient transition to footer -->
            <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-[#0F3D77] dark:from-slate-950 to-transparent z-0 pointer-events-none"></div>
        </div>

        <!-- Footer component from main page -->
        <Footer></Footer>

        <!-- Complaint Modal -->
        <ComplaintModal
            :is-open="isComplaintModalOpen"
            :service-name="service.name"
            :service-slug="service.slug"
            @close="isComplaintModalOpen = false"></ComplaintModal>
    </FrontLayout>
</template>

<style>
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

@keyframes growWidth {
    from {
        width: 0%;
    }
}

@keyframes growHeight {
    from {
        height: 0%;
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

/* Page / Dashboard Transition Animations */
.page-fade-enter-active,
.page-fade-leave-active {
    transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.page-fade-enter-from {
    opacity: 0;
    transform: translateY(14px) scale(0.99);
}

.page-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.99);
}

.animate-grow-width {
    animation: growWidth 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.animate-grow-height {
    animation: growHeight 1.2s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
