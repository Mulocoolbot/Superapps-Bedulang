<script setup>

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
    users:Array
})

</script>

<template>

<Head title="Kelola Admin"></Head>

<AdminLayout>

<h1 class="text-3xl font-bold mb-6">

Kelola Admin

</h1>

<div class="bg-white rounded-xl shadow overflow-hidden">

<table class="w-full">

<thead class="bg-gray-100">

<tr>

<th class="p-4 text-left">Nama</th>

<th class="p-4 text-left">Email</th>

<th class="p-4 text-left">Role</th>

</tr>

</thead>

<tbody>

<tr
v-for="user in users"
:key="user.id"
class="border-t">

<td class="p-4">

{{ user.name }}

</td>

<td class="p-4">

{{ user.email }}

</td>

<td class="p-4 capitalize">

{{ user.role }}

</td>

</tr>

</tbody>

</table>

</div>

</AdminLayout>

</template>