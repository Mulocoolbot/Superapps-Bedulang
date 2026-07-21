<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const dropdownOpen = ref(false)
const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
}
</script>

<template>

<div class="min-h-screen bg-gray-100 flex">

    <!-- Sidebar -->

    <aside class="w-64 bg-white shadow-lg">

        <div class="p-6 border-b">

            <img
                src="/services/logobateng3.png"
                class="w-14 mx-auto"
            >

            <h2 class="mt-3 text-center font-bold text-blue-700">
                BEDULANG
            </h2>

        </div>

        <nav class="p-4 space-y-2 flex flex-col h-full justify-between">

            <div>
                <Link
                    href="/admin"
                    class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                    Dashboard

                </Link>

                <Link
                    href="admin/cctv"
                    class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                    CCTV

                </Link>

                <Link
                    href="/admin/complaints"
                    class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                    Pengaduan

                </Link>

                <Link
                    href="/profile"
                    class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                    Profil

                </Link>

                <Link
                    v-if="$page.props.auth.user && $page.props.auth.user.role === 'super_admin'"
                    href="/admin/users"
                    class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                    👥 Kelola Admin

                </Link>
            </div>

        </nav>

    </aside>

    <!-- Main -->

    <div class="flex-1">

        <!-- Navbar -->

        <header
            class="bg-white shadow px-8 py-4 flex justify-between items-center">

            <h1
                class="font-bold text-xl">

                Admin Panel

            </h1>

            <div class="relative">
                <button
                    type="button"
                    @click="toggleDropdown"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Admin
                    <span class="text-gray-400">▾</span>
                </button>

                <div
                    v-show="dropdownOpen"
                    class="absolute right-0 mt-2 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
                >
                    <Link
                        href="/profile"
                        class="block w-full px-4 py-3 text-left text-gray-700 hover:bg-gray-50">
                        Profil
                    </Link>
                    <div class="border-t border-gray-100"></div>
                    <Link
                        method="post"
                        href="/logout"
                        as="button"
                        class="block w-full px-4 py-3 text-left text-red-600 hover:bg-gray-50">
                        Logout
                    </Link>
                </div>
            </div>

        </header>

        <main class="p-8">

            <slot />

        </main>

    </div>

</div>

</template>
