<script setup>
</script>

<template>
    <footer
        id="kontak"
        class="bg-[#0F3D77] dark:bg-slate-950 text-white mt-0">

        <div class="max-w-7xl mx-auto px-6 py-14">

            <div class="grid lg:grid-cols-4 gap-10">

                <!-- Logo -->
                <div>

                    <img
                        src="/services/logobateng3.png"
                        alt="Logo"
                        class="w-16 mb-4"
                    >

                    <h3 class="text-2xl font-bold">
                        BEDULANG
                    </h3>

                    <p class="mt-3 text-blue-100 leading-7 text-sm">
                        Portal Data & Informasi Kabupaten Bangka Tengah
                        yang menyediakan berbagai data sektoral dan
                        layanan digital pemerintah daerah.
                    </p>

                </div>

                <!-- Kontak -->

                <div>

                    <h4 class="font-bold text-lg mb-5">
                        Kontak
                    </h4>

                    <div class="space-y-3 text-blue-100 text-sm">

                        <a href="tel:07187362032" class="flex items-center gap-2 hover:underline hover:text-white transition">
                            <span>📞</span>
                            <span>(0718) 7362032</span>
                        </a>

                        <a href="mailto:diskominfo@bangkatengah.go.id" class="flex items-center gap-2 hover:underline hover:text-white transition">
                            <span>✉</span>
                            <span>diskominfosta@bangkatengahkab.go.id</span>
                        </a>

                        <a href="https://bangkatengahkab.go.id" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:underline hover:text-white transition">
                            <span>🌐</span>
                            <span>bangkatengahkab.go.id</span>
                        </a>

                    </div>

                </div>

                <!-- Alamat -->

                <div>

                    <h4 class="font-bold text-lg mb-5">
                        Alamat
                    </h4>

                    <a 
                        href="https://maps.app.goo.gl/5VoQ2phAct9yi4mH8" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="text-blue-100 hover:text-white transition text-sm leading-7 block hover:underline group flex items-start gap-2"
                    >
                        <svg class="w-5 h-5 mt-0.5 text-blue-300 group-hover:text-white transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>
                            Komplek Perkantoran Pemerintah<br>
                            Kabupaten Bangka Tengah<br>
                            Provinsi Kepulauan Bangka Belitung
                        </span>
                    </a>

                </div>

                <!-- Menu -->

                <div>

                    <h4 class="font-bold text-lg mb-5">
                        Menu
                    </h4>

                    <ul class="space-y-3 text-sm">

                        <li>
                            <a href="#beranda" class="hover:text-blue-300 transition">
                                Beranda
                            </a>
                        </li>

                        <li>
                            <a href="#layanan" class="hover:text-blue-300 transition">
                                Layanan Bedulang
                            </a>
                        </li>

                        <li>
                            <a href="#aplikasi" class="hover:text-blue-300 transition">
                                Daftar Aplikasi
                            </a>
                        </li>

                        <li>
                            <a href="/login" class="hover:text-blue-300 transition">
                                Login
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

            <!-- Bottom -->

            <div
                class="border-t border-blue-700 dark:border-slate-800 mt-12 pt-6 flex flex-col md:flex-row justify-between items-center text-sm text-white">

                <p>
                    © 2026 Dinas Kominfosta. All rights reserved.
                </p>

                <p class="mt-3 md:mt-0">
                    Dikelola oleh Dinas Komunikasi, Informatika, dan Statistik Kabupaten Bangka Tengah
                </p>

            </div>

        </div>

    </footer>
</template>