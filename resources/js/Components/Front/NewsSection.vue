<script setup>
import { ref } from 'vue'

const news = [
    {
        title: 'Berita Hari Ini',
        date: '10 Juli 2026',
        image: '/services/voting.svg',
        url: '#'
    },
    {
        title: 'Berita Hari Ini',
        date: '10 Juli 2026',
        image: '/services/voting.svg',
        url: '#'
    },
    {
        title: 'Berita Hari Ini',
        date: '10 Juli 2026',
        image: '/services/voting.svg',
        url: '#'
    },
    {
        title: 'Berita Hari Ini',
        date: '10 Juli 2026',
        image: '/services/voting.svg',
        url: '#'
    },
    {
        title: 'Berita Hari Ini',
        date: '10 Juli 2026',
        image: '/services/voting.svg',
        url: '#'
    },
]

const slider = ref(null)

const scrollLeft = () => {
    slider.value.scrollBy({
        left: -360,
        behavior: 'smooth'
    })
}

const scrollRight = () => {
    slider.value.scrollBy({
        left: 360,
        behavior: 'smooth'
    })
}
</script>

<template>

<section
    id="berita"
    data-aos="fade-up"
    data-aos-duration="900"
    class="scroll-mt-24 py-16 bg-white dark:bg-gray-900"
>

<div class="max-w-7xl mx-auto px-6">

    <!-- Header -->
    <div
        class="flex items-center justify-between mb-8"
        data-aos="fade-up"
        data-aos-delay="100"
    >

        <div>

            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">

                Berita
                <span class="text-blue-600 dark:text-blue-400">
                    Terbaru
                </span>

            </h2>

            <p class="mt-3 text-gray-600 dark:text-gray-400">

                Informasi dan kegiatan terbaru Pemerintah Kabupaten Bangka Tengah.

            </p>

        </div>

        <a
            href="#"
            class="hidden md:flex items-center gap-2 text-blue-600 dark:text-blue-400 font-semibold hover:gap-3 transition-all"
        >
            Lihat Semua →
        </a>

    </div>

    <!-- Slider -->
    <div
        class="relative"
        data-aos="fade-up"
        data-aos-delay="200"
    >

        <!-- Left -->
        <button
            @click="scrollLeft"
            class="hidden lg:flex absolute left-0 top-1/2 -translate-y-1/2 -translate-x-5 z-20 w-11 h-11 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg items-center justify-center hover:bg-blue-600 hover:text-white transition"
        >
            ←
        </button>

        <!-- Right -->
        <button
            @click="scrollRight"
            class="hidden lg:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-5 z-20 w-11 h-11 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg items-center justify-center hover:bg-blue-600 hover:text-white transition"
        >
            →
        </button>

        <!-- Gradient kiri -->
        <div class="hidden lg:block absolute left-0 top-0 w-12 h-full bg-gradient-to-r from-white dark:from-gray-900 to-transparent z-10 pointer-events-none"></div>

        <!-- Gradient kanan -->
        <div class="hidden lg:block absolute right-0 top-0 w-12 h-full bg-gradient-to-l from-white dark:from-gray-900 to-transparent z-10 pointer-events-none"></div>

        <!-- Carousel -->
        <div
            ref="slider"
            class="flex gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-3 scrollbar-hide"
        >

            <a
                v-for="(item,index) in news"
                :key="index"
                :href="item.url"
                class="group min-w-[320px] max-w-[320px] snap-start overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
            >

                <!-- Image -->
                <div class="overflow-hidden bg-gray-100 dark:bg-gray-900">

                    <img
                        :src="item.image"
                        :alt="item.title"
                        class="w-full h-52 object-cover transition-transform duration-500 group-hover:scale-105"
                    >

                </div>

                <!-- Content -->
                <div class="p-5">

                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">

                        {{ item.date }}

                    </p>

                    <h3
                        class="mt-3 text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition"
                    >

                        {{ item.title }}

                    </h3>

                    <div
                        class="mt-6 flex items-center gap-2 text-blue-600 dark:text-blue-400 font-medium"
                    >

                        Baca Selengkapnya

                        <span
                            class="transition-transform duration-300 group-hover:translate-x-1"
                        >
                            →
                        </span>

                    </div>

                </div>

            </a>

        </div>

    </div>

</div>

</section>

</template>

<style scoped>

.scrollbar-hide::-webkit-scrollbar{
    display:none;
}

.scrollbar-hide{
    -ms-overflow-style:none;
    scrollbar-width:none;
}

</style>