<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="School Details" />
        <div class="max-w-xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">School Details</h1>
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
                <div class="mb-4"><span class="font-semibold">Name:</span> {{ school.name }}</div>
                <div class="mb-4"><span class="font-semibold">Address:</span> {{ school.address }}</div>
                <div class="mb-4"><span class="font-semibold">Contact:</span> {{ school.contact }}</div>
                <button @click="goBack" class="bg-blue-500 text-white px-4 py-2 rounded">Back</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { usePage, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
const page = usePage();
const school = page.props.school as any;
const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
    { title: 'School Details', href: `/schools/${school.id}` },
];
const goBack = () => {
    router.visit(route('schools.index'));
};
</script>