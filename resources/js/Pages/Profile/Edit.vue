<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();

const isAdminPanelUser = computed(() => {
    const user = page.props.auth?.user;
    if (!user) return false;

    const adminRoles = [
        'admin',
        'super_admin',
        'pimpinan',
        'admin_koperasi',
        'admin_kependudukan',
        'admin_kdmp',
        'admin_kecamatan',
        'admin_kmp',
    ];

    return (
        adminRoles.includes(user.role) ||
        ['koperasi', 'kependudukan'].includes(user.service_slug) ||
        Boolean(user.kdmp_role)
    );
});

const layoutComponent = computed(() => {
    return isAdminPanelUser.value ? AdminLayout : FrontLayout;
});
</script>

<template>
    <Head title="Profil Pengguna"></Head>

    <component :is="layoutComponent">
        <!-- Admin Panel Layout View -->
        <div v-if="isAdminPanelUser" class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Profil</h1>
                    <p class="text-xs text-gray-500 mt-1">Kelola informasi akun, kata sandi, dan keamanan profil Anda.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200/80">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200/80">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200/80">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>

        <!-- User Panel / Public Citizen View (FrontLayout) -->
        <div v-else class="pt-24 sm:pt-28 pb-12 bg-slate-50/60 dark:bg-gray-900/60 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Navigation & Page Title -->
                <div>
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                        <Link href="/" class="hover:text-blue-600 font-medium transition">Beranda</Link>
                        <span>/</span>
                        <span class="text-slate-800 dark:text-slate-200 font-semibold">Pengaturan Profil</span>
                    </div>
                    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Pengaturan Profil Saya</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Kelola informasi akun, kata sandi, dan data pribadi Anda.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-200/80 dark:border-gray-700/80">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-200/80 dark:border-gray-700/80">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-slate-200/80 dark:border-gray-700/80">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </component>
</template>
