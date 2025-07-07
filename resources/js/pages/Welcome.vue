<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Welcome" />
    <div class="min-h-screen bg-purple-50 flex flex-col items-center justify-center">
        <main class="w-full max-w-sm mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-purple-700 mb-4">Welcome to Multi School System</h1>
            <p class="text-gray-700 mb-6">A modern, responsive, and powerful school management platform.</p>
            <div class="flex flex-col gap-4">
                <Link :href="route('login')"
                    class="bg-purple-700 text-white px-6 py-2 rounded shadow hover:bg-purple-800 transition">Login
                </Link>
                <Link :href="route('register')"
                    class="bg-white border border-purple-700 text-purple-700 px-6 py-2 rounded shadow hover:bg-purple-50 transition">
                Register</Link>
            </div>
        </main>
    </div>
</template>
