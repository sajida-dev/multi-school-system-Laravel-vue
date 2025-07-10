<template>
    <AppLayout :breadcrumbs="[{ title: 'Classes', href: '/classes' }, { title: 'Create', href: '#' }]">

        <Head title="Create Class" />
        <div class="max-w-xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-neutral-900 dark:text-neutral-100">Create Class</h1>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block mb-1 font-medium text-neutral-700 dark:text-neutral-200">Name</label>
                    <input v-model="form.name" type="text" class="form-input w-full" required />
                </div>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" type="button" @click="goBack">Cancel</Button>
                    <Button variant="default" type="submit">Create</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

const form = useForm({ name: '' });

function submit() {
    form.post(route('classes.store'), {
        onSuccess: () => {
            toast.success('Class created successfully.');
            router.get(route('classes.index'));
        },
        onError: () => {
            toast.error('Failed to create class!');
        },
    });
}
function goBack() {
    router.get(route('classes.index'));
}
</script>