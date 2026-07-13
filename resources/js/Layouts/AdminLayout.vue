<script setup>
import { Link } from '@inertiajs/vue3'
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

        <nav class="p-4 space-y-2">

            <Link
                href="/admin"
                class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                Dashboard

            </Link>

            <Link
                href="/admin/cctv"
                class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                CCTV

            </Link>

            <Link
                href="/profile"
                class="block px-4 py-3 rounded-lg hover:bg-blue-50">

                Profil

            </Link>

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

            <Link
                method="post"
                href="/logout"
                as="button"
                class="text-red-600 font-semibold">

                Logout

            </Link>

        </header>

        <main class="p-8">

            <slot />

        </main>

    </div>

</div>

</template>