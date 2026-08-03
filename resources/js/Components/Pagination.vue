<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useSlidingPagination } from '@/Composables/useSlidingPagination'

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    }
})

const { visiblePageLinks, prevLink, nextLink, hasPagination } = useSlidingPagination(() => props.pagination)
</script>

<template>
    <div v-if="hasPagination" class="flex items-center justify-between gap-4 w-full p-4">
        <div class="text-xs text-slate-500 dark:text-slate-400">
            Halaman <span class="font-bold text-slate-900 dark:text-white font-mono">{{ pagination.current_page }}</span>
            dari <span class="font-bold text-slate-900 dark:text-white font-mono">{{ pagination.last_page }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            <!-- Prev Arrow -->
            <Link
                v-if="prevLink?.url"
                :href="prevLink.url"
                class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all duration-200 transform hover:scale-105 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700"
                aria-label="Previous Page"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </Link>
            <span v-else class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-300 opacity-50 cursor-not-allowed dark:bg-slate-800/50 dark:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </span>

            <!-- Numeric Page Bubbles -->
            <template v-for="(link, k) in visiblePageLinks" :key="k">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center transition-all duration-200 transform hover:scale-105"
                    :class="link.active
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 dark:bg-indigo-600 dark:shadow-indigo-500/20'
                        : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700'"
                >
                    {{ link.label }}
                </Link>
                <span v-else class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center bg-slate-50 text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">
                    {{ link.label }}
                </span>
            </template>

            <!-- Next Arrow -->
            <Link
                v-if="nextLink?.url"
                :href="nextLink.url"
                class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-all duration-200 transform hover:scale-105 shadow-sm dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700"
                aria-label="Next Page"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </Link>
            <span v-else class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 text-slate-300 opacity-50 cursor-not-allowed dark:bg-slate-800/50 dark:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </span>
        </div>
    </div>
</template>
