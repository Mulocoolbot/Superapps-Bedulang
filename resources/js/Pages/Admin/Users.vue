<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    },
    currentUserId: {
        type: Number,
        default: null
    }
})

const page = usePage()
const searchQuery = ref('')
const deleteTargetUser = ref(null)
const isDeleting = ref(false)

const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) return props.users
    const q = searchQuery.value.toLowerCase()
    return props.users.filter(u => 
        (u.name && u.name.toLowerCase().includes(q)) ||
        (u.email && u.email.toLowerCase().includes(q)) ||
        (u.role && u.role.toLowerCase().includes(q)) ||
        (u.service_slug && u.service_slug.toLowerCase().includes(q))
    )
})

const confirmDelete = (user) => {
    deleteTargetUser.value = user
}

const cancelDelete = () => {
    deleteTargetUser.value = null
}

const executeDelete = () => {
    if (!deleteTargetUser.value) return
    isDeleting.value = true
    router.delete(`/admin/users/${deleteTargetUser.value.id}`, {
        onFinish: () => {
            isDeleting.value = false
            deleteTargetUser.value = null
        }
    })
}

const getRoleBadgeClass = (role, slug) => {
    if (role === 'super_admin') return 'bg-purple-100 text-purple-700 border-purple-200'
    if (role === 'admin_kependudukan' || slug === 'kependudukan') return 'bg-blue-100 text-blue-700 border-blue-200'
    if (role === 'admin_koperasi' || slug === 'koperasi') return 'bg-emerald-100 text-emerald-700 border-emerald-200'
    if (slug === 'kesehatan') return 'bg-rose-100 text-rose-700 border-rose-200'
    if (slug === 'informasi-publik') return 'bg-cyan-100 text-cyan-700 border-cyan-200'
    if (slug === 'pendidikan') return 'bg-amber-100 text-amber-700 border-amber-200'
    if (slug === 'kdmp') return 'bg-teal-100 text-teal-700 border-teal-200'
    return 'bg-slate-100 text-slate-700 border-slate-200'
}

const formatRoleLabel = (role, slug) => {
    if (role === 'super_admin') return 'Super Admin'
    if (role === 'admin_kependudukan' || slug === 'kependudukan') return 'Admin Layanan Kependudukan'
    if (role === 'admin_koperasi' || slug === 'koperasi') return 'Admin Layanan Koperasi'
    if (slug === 'pendidikan') return 'Admin Layanan Pendidikan'
    if (slug === 'perindagkop-ukm') return 'Admin Layanan Perindagkop-UKM'
    if (slug === 'desa' || slug === 'pertanian') return 'Admin Layanan Pertanian & Desa'
    if (slug === 'informasi-publik') return 'Admin Layanan Informasi Publik'
    if (slug === 'pemerintahan') return 'Admin Layanan Pemerintahan'
    if (slug === 'kepegawaian') return 'Admin Layanan Kepegawaian'
    if (slug === 'makanan-bergizi-gratis') return 'Admin Layanan Makanan Bergizi Gratis'
    if (slug === 'kesehatan') return 'Admin Layanan Kesehatan'
    if (slug === 'sosial') return 'Admin Layanan Sosial'
    if (slug === 'pariwisata') return 'Admin Layanan Pariwisata'
    if (slug === 'data-sektoral') return 'Admin Layanan Data Sektoral'
    if (slug === 'kdmp') return 'Admin Layanan KDMP'
    if (slug === 'hukum') return 'Admin Layanan Hukum'
    if (slug === 'cctv' || slug === 'monitoring-cctv') return 'Admin Layanan Monitoring CCTV'
    if (slug === 'agenda') return 'Admin Layanan Agenda'
    if (slug === 'event') return 'Admin Layanan Event'
    if (slug === 'inovasi-daerah') return 'Admin Layanan Inovasi Daerah'
    if (slug === 'perizinan-terpadu') return 'Admin Layanan Perizinan Terpadu'
    if (slug === 'pelayanan-kesehatan') return 'Admin Layanan Pelayanan Kesehatan'
    if (slug === 'pengaduan-ppid') return 'Admin Layanan Pengaduan PPID'
    if (slug === 'administrasi-warga') return 'Admin Layanan Administrasi Warga'
    if (role === 'admin') return slug ? `Admin Layanan ${slug}` : 'Admin Layanan'
    return role
}

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    })
}
</script>

<template>
    <Head title="Kelola Admin Sistem" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Alert Flash Messages -->
            <div v-if="page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                <span>{{ page.props.flash.success }}</span>
            </div>
            <div v-if="page.props.flash?.error" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between">
                <span>{{ page.props.flash.error }}</span>
            </div>

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Kelola Admin Sistem</h1>
                    <p class="text-sm text-gray-500 mt-1">Daftar administrator sistem dan pengelola layanan/OPD BEDULANG.</p>
                </div>
                <Link
                    href="/admin/users/create"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm transition shadow-blue-500/10 shrink-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Admin Baru</span>
                </Link>
            </div>

            <!-- Search & Filter Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <div class="relative w-full sm:w-80">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama, email, atau layanan..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 transition"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>

            <!-- Admin Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase font-bold text-gray-500 tracking-wider">
                            <tr>
                                <th class="p-4 pl-6">Pengguna</th>
                                <th class="p-4">Role</th>
                                <th class="p-4">Layanan / Domain</th>
                                <th class="p-4">Tanggal Dibuat</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="5" class="p-8 text-center text-gray-400">
                                    Tidak ada data admin sistem yang ditemukan.
                                </td>
                            </tr>
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="hover:bg-gray-50/50 transition"
                            >
                                <td class="p-4 pl-6">
                                    <div class="font-bold text-gray-900">{{ user.name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ user.email }}</div>
                                </td>
                                <td class="p-4">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider', getRoleBadgeClass(user.role, user.service_slug)]">
                                        {{ formatRoleLabel(user.role, user.service_slug) }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span v-if="user.service_slug" class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium border border-gray-200">
                                        {{ user.service_slug }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400 italic">
                                        Semua Layanan (Global)
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-gray-500">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                            class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 transition"
                                            title="Edit Admin"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </Link>

                                        <button
                                            v-if="user.id !== props.currentUserId"
                                            @click="confirmDelete(user)"
                                            class="p-2 rounded-xl text-red-600 hover:bg-red-50 transition"
                                            title="Hapus Admin"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteTargetUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center gap-3 text-red-600">
                    <div class="p-3 bg-red-100 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Hapus Admin Sistem</h3>
                </div>

                <p class="text-sm text-gray-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun admin <span class="font-bold text-gray-900">{{ deleteTargetUser.name }}</span> ({{ deleteTargetUser.email }})? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        @click="cancelDelete"
                        :disabled="isDeleting"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        @click="executeDelete"
                        :disabled="isDeleting"
                        class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-sm font-semibold text-white shadow-sm transition disabled:opacity-50"
                    >
                        {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>