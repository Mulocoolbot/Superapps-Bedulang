<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontLayout from '@/Layouts/FrontLayout.vue'
import ComplaintModal from '@/Components/ComplaintModal.vue'
import Footer from '@/Components/Front/Footer.vue'

// Import Segmented Dashboard components
import PerindagkopDashboard from './Dashboards/PerindagkopDashboard.vue'
import MbgDashboard from './Dashboards/MbgDashboard.vue'
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
        default:
            return props.dashboard?.metadata?.source || 'Portal Satu Data Bangka Tengah'
    }
})
</script>

<template>
    <FrontLayout>
        <Head :title="title" />

        <div class="mx-auto max-w-7xl px-4 pt-24 pb-6 sm:px-6 lg:px-8 lg:pt-28 lg:pb-10">

            <!-- Back Button - subtle ghost style -->
            <Link
                href="/"
                class="group mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
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
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900 animate-fade-in-up">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-50/60 blur-2xl dark:bg-cyan-950/20" />
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400">
                            <span>{{ serviceIcon }}</span>
                            <span>Data & Informasi</span>
                        </div>
                        <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white font-sans">
                            {{ title }}
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ subtitle }}
                        </p>
                    </div>

                    <!-- Complaint Action Button -->
                    <div class="flex shrink-0 items-center">
                        <button
                            @click="isComplaintModalOpen = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20 cursor-pointer"
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

            <!-- Dynamically Rendered Segmented Dashboard -->
            <component 
                :is="dashboardComponent" 
                :service="service" 
                :dashboard="dashboard" 
            />

            <!-- Sumber Data Section (Footer data note inside page margin) -->
            <div class="mt-12 pt-6 border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span>Sumber Data:</span>
                    <a 
                        v-if="serviceSourceUrl"
                        :href="serviceSourceUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300 underline"
                    >
                        {{ serviceSourceLabel }}
                    </a>
                    <span v-else-if="dashboard.metadata?.source" class="font-semibold text-slate-600 dark:text-slate-300">
                        {{ dashboard.metadata.source }}
                    </span>
                    <span v-else class="font-semibold text-slate-600 dark:text-slate-300">
                        Portal Satu Data Bangka Tengah
                    </span>
                </div>
                <div class="mt-2 sm:mt-0" v-if="dashboard.metadata?.updated_at">
                    Terakhir diperbarui: {{ dashboard.metadata.updated_at }}
                </div>
            </div>

        </div>

        <!-- Footer component from main page -->
        <Footer />

        <!-- Complaint Modal -->
        <ComplaintModal
            :is-open="isComplaintModalOpen"
            :service-name="service.name"
            :service-slug="service.slug"
            @close="isComplaintModalOpen = false"
        />
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

.animate-grow-width {
    animation: growWidth 1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.animate-grow-height {
    animation: growHeight 1.2s cubic-bezier(0.16, 1, 0.3, 1) both;
}
</style>
