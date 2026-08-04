<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import ComplaintModal from '@/Components/ComplaintModal.vue'

const isComplaintModalOpen = ref(false)
const isSidebarOpen = ref(false)

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
}

const closeSidebar = () => {
    isSidebarOpen.value = false
}

const notificationDropdownRef = ref(null)
const notificationBellRef = ref(null)
import Footer from '@/Components/Front/Footer.vue'

const page = usePage()
const notificationsOpen = ref(false)
const activeNotificationTab = ref('unread')

watch(() => page.url, () => {
    isSidebarOpen.value = false
})

const markAllAsRead = () => {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
    })
}

const authUser = computed(() => page.props.auth?.user)
const notifications = computed(() => authUser.value?.notifications || [])
const unreadCount = computed(() => authUser.value?.unread_notifications_count || 0)

const unreadList = computed(() => notifications.value.filter(n => !n.read_at))
const readList = computed(() => notifications.value.filter(n => !!n.read_at))

const markAsRead = (notification) => {
    router.post(`/notifications/${notification.id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (notification.data?.url) {
                router.visit(notification.data.url)
            }
        }
    })
}

import { onMounted, onUnmounted, onBeforeUnmount } from 'vue'
import { NotificationService } from '@/Services/NotificationService'

let pollTimer = null
const seenIdsSet = new Set()
const isBellRinging = ref(false)

const triggerBellRing = () => {
    isBellRinging.value = true
    setTimeout(() => {
        isBellRinging.value = false
    }, 1000)
}

const handleClickOutside = (e) => {
    if (
        notificationsOpen.value &&
        notificationDropdownRef.value &&
        !notificationDropdownRef.value.contains(e.target) &&
        notificationBellRef.value &&
        !notificationBellRef.value.contains(e.target)
    ) {
        notificationsOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside)

    if (authUser.value) {
        NotificationService.requestPermission()
        notifications.value.forEach(n => seenIdsSet.add(n.id))

        pollTimer = setInterval(() => {
            router.reload({
                only: ['auth'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    const hasNew = NotificationService.processNewNotifications(notifications.value, seenIdsSet)
                    if (hasNew) {
                        triggerBellRing()
                    }
                }
            })
        }, 10000)
    }
})

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside)
    if (pollTimer) {
        clearInterval(pollTimer)
    }
})
</script>

<template>
<div class="min-h-screen bg-gray-100 flex flex-col justify-between">
    <div class="flex flex-1 relative">
        <!-- Mobile Sidebar Dark Overlay -->
        <div
            v-if="isSidebarOpen"
            @click="closeSidebar"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 md:hidden transition-opacity"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-2xl flex flex-col justify-between transform transition-transform duration-300 ease-in-out md:static md:translate-x-0 md:shadow-lg shrink-0"
            :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        >
            <div>
                <div class="p-6 border-b flex justify-between items-center">
                    <Link href="/" @click="closeSidebar">
                        <img
                            src="/services/logobateng3.png"
                            class="w-24 h-auto object-contain hover:scale-105 transition duration-300"
                            alt="Logo Bangka Tengah"
                        />
                    </Link>

                    <!-- Mobile Close Button -->
                    <button
                        @click="closeSidebar"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 md:hidden transition"
                        title="Tutup Menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="p-4 space-y-2">
                    <Link
                        :href="$page.props.auth?.user?.role === 'pimpinan' ? '/pimpinan' : (($page.props.auth?.user?.role === 'admin_koperasi' || $page.props.auth?.user?.service_slug === 'koperasi') ? '/admin/koperasi' : '/admin')"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="(page.url === '/admin' || page.url === '/pimpinan' || page.url === '/admin/koperasi') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </Link>

                    <Link
                        href="/admin/cctv"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="page.url.startsWith('/admin/cctv') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span>CCTV</span>
                    </Link>

                    <Link
                        href="/admin/complaints"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="page.url.startsWith('/admin/complaints') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span>Monitoring Pengaduan</span>
                    </Link>

                    <Link
                        href="/profile"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="page.url.startsWith('/profile') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span>Profil</span>
                    </Link>

                    <Link
                        v-if="$page.props.auth?.user && $page.props.auth.user.role === 'super_admin'"
                        href="/admin/users"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="page.url.startsWith('/admin/users') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6 0 3.375 3.375 0 0 1 6 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span>Kelola Admin Sistem</span>
                    </Link>

                    <!-- Kelola KDMP Khusus Admin KDMP -->
                    <Link
                        v-if="$page.props.auth?.user && ['admin_kdmp', 'admin_kecamatan', 'admin_kmp'].includes($page.props.auth.user.kdmp_role)"
                        :href="(() => {
                            if ($page.props.auth.user.kdmp_role === 'admin_kdmp') return '/admin/kdmp';
                            if ($page.props.auth.user.kdmp_role === 'admin_kecamatan') return '/admin/kdmp/kecamatan';
                            if ($page.props.auth.user.kdmp_role === 'admin_kmp') return '/admin/kdmp/kmp';
                            return '#';
                        })()"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="page.url.startsWith('/admin/kdmp') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Kelola KDMP</span>
                    </Link>

                    <!-- Panel Admin Koperasi (Hanya muncul jika admin koperasi / superadmin) -->
                    <Link
                        v-if="$page.props.auth?.user && ($page.props.auth.user.role === 'admin_koperasi' || $page.props.auth.user.service_slug === 'koperasi' || $page.props.auth.user.role === 'super_admin')"
                        href="/admin/koperasi"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        :class="page.url.startsWith('/admin/koperasi') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.999 2.999 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.999 2.999 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75Z" />
                        </svg>
                        <span>Kelola Koperasi</span>
                    </Link>
                </nav>
            </div>

            <!-- Profile summary & Logout feature in sidebar bottom -->
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <div v-if="$page.props.auth?.user" class="mb-3 px-2">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Signed in as</p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $page.props.auth.user.name }}</p>
                </div>
                <Link
                    method="post"
                    href="/logout"
                    as="button"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-semibold transition text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    <span>Logout</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Navbar Header -->
            <header class="bg-white shadow px-4 sm:px-8 py-3.5 flex justify-between items-center relative z-20">
                <!-- Hamburger Button for Mobile Sidebar Toggle -->
                <div class="flex items-center gap-3">
                    <button
                        @click="toggleSidebar"
                        class="p-2 rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none transition md:hidden"
                        title="Toggle Sidebar Menu"
                        aria-label="Toggle Sidebar"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <span class="text-sm font-bold text-gray-700 md:hidden">Menu Admin</span>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Ajukan Pengaduan Button -->
                    <button
                        @click="isComplaintModalOpen = true"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition shadow-sm cursor-pointer shadow-blue-500/10 hover:shadow-md"
                    >
                        Ajukan Pengaduan
                    </button>

                    <!-- Admin Notification Bell Component -->
                    <div v-if="authUser" class="relative">
                    <button
                        ref="notificationBellRef"
                        @click="notificationsOpen = !notificationsOpen"
                        class="relative inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 p-2.5 transition cursor-pointer"
                        :class="{ 'animate-bell-ring text-rose-600': isBellRinging }"
                        aria-label="Notifikasi Admin"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>

                        <!-- Unread Count Badge -->
                        <span
                            v-if="unreadCount > 0"
                            class="absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow ring-2 ring-white"
                        >
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown Box with Transition -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                        enter-to-class="transform opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="transform opacity-100 scale-100 translate-y-0"
                        leave-to-class="transform opacity-0 scale-95 -translate-y-2"
                    >
                        <div
                            ref="notificationDropdownRef"
                            v-show="notificationsOpen"
                            class="absolute right-0 mt-3 w-80 sm:w-96 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl z-50 transition-all"
                        >
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Notifikasi Panel</h3>
                                    <span v-if="unreadCount > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                        {{ unreadCount }} Baru
                                    </span>
                                </div>

                                <button
                                    v-if="unreadCount > 0"
                                    @click="markAllAsRead"
                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition"
                                >
                                    Tandai Semua Dibaca
                                </button>
                            </div>

                            <!-- 2 Tabs Bar (Belum Dibaca | Sudah Dibaca) -->
                            <div class="flex border-b border-gray-100 bg-gray-50/50">
                                <button
                                    @click="activeNotificationTab = 'unread'"
                                    class="flex-1 py-2 px-3 text-center text-xs font-bold transition border-b-2 flex items-center justify-center gap-1.5"
                                    :class="activeNotificationTab === 'unread' ? 'border-blue-600 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                >
                                    <span>Belum Dibaca</span>
                                    <span v-if="unreadList.length > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-rose-600 text-white">
                                        {{ unreadList.length }}
                                    </span>
                                </button>

                                <button
                                    @click="activeNotificationTab = 'read'"
                                    class="flex-1 py-2 px-3 text-center text-xs font-bold transition border-b-2 flex items-center justify-center gap-1.5"
                                    :class="activeNotificationTab === 'read' ? 'border-blue-600 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                >
                                    <span>Sudah Dibaca</span>
                                    <span v-if="readList.length > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-gray-200 text-gray-700">
                                        {{ readList.length }}
                                    </span>
                                </button>
                            </div>

                            <!-- Notifications List (Filtered by active tab) -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                <!-- Tab: Belum Dibaca -->
                                <template v-if="activeNotificationTab === 'unread'">
                                    <div v-if="unreadList.length === 0" class="p-6 text-center text-xs text-gray-400">
                                        Tidak ada notifikasi yang belum dibaca.
                                    </div>

                                    <div
                                        v-for="item in unreadList"
                                        :key="item.id"
                                        @click="markAsRead(item)"
                                        class="p-3.5 hover:bg-slate-50 cursor-pointer transition flex items-start gap-3 bg-blue-50/50"
                                    >
                                        <div class="p-2 rounded-xl shrink-0 mt-0.5 bg-blue-100 text-blue-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                            </svg>
                                        </div>

                                        <div class="space-y-1 min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <h4 class="text-xs font-bold text-gray-900 truncate">
                                                    {{ item.data.title || 'Notifikasi Admin' }}
                                                </h4>
                                                <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                                            </div>
                                            <p class="text-xs text-gray-600 leading-snug line-clamp-2">
                                                {{ item.data.message }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ new Date(item.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <!-- Tab: Sudah Dibaca -->
                                <template v-else>
                                    <div v-if="readList.length === 0" class="p-6 text-center text-xs text-gray-400">
                                        Belum ada riwayat notifikasi yang telah dibaca.
                                    </div>

                                    <div
                                        v-for="item in readList"
                                        :key="item.id"
                                        @click="markAsRead(item)"
                                        class="p-3.5 hover:bg-slate-50 cursor-pointer transition flex items-start gap-3 opacity-80"
                                    >
                                        <div class="p-2 rounded-xl shrink-0 mt-0.5 bg-gray-100 text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>

                                        <div class="space-y-1 min-w-0 flex-1">
                                            <h4 class="text-xs font-bold text-gray-800 truncate">
                                                {{ item.data.title || 'Notifikasi Admin' }}
                                            </h4>
                                            <p class="text-xs text-gray-500 leading-snug line-clamp-2">
                                                {{ item.data.message }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">
                                                Dibaca: {{ new Date(item.read_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </Transition>
                </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8 flex-1 animate-fade-in-up">
                <slot />
            </main>
        </div>
    </div>

    <!-- Homepage Footer at bottom -->
    <Footer ></Footer>
    <ComplaintModal
        :is-open="isComplaintModalOpen"
        @close="isComplaintModalOpen = false"
    />
</div>
</template>

<style>
@keyframes bellRing {
    0% { transform: rotate(0); }
    15% { transform: rotate(18deg); }
    30% { transform: rotate(-18deg); }
    45% { transform: rotate(12deg); }
    60% { transform: rotate(-12deg); }
    75% { transform: rotate(6deg); }
    85% { transform: rotate(-6deg); }
    100% { transform: rotate(0); }
}

.animate-bell-ring {
    animation: bellRing 0.8s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
}
</style>
