<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Edit School" />
        <div class="max-w-xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Edit School</h1>
            <form @submit.prevent="submit"
                class="space-y-6 bg-white dark:bg-neutral-900 p-6 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-md">
                <div>
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" required placeholder="School name" autocomplete="off" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <Label for="address">Address</Label>
                    <Input id="address" v-model="form.address" placeholder="School address" autocomplete="off" />
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
                <div>
                    <Label for="contact">Contact</Label>
                    <Input id="contact" v-model="form.contact" placeholder="Contact info" autocomplete="off" />
                    <InputError class="mt-2" :message="form.errors.contact" />
                </div>
                <div class="flex items-center gap-4 mt-6">
                    <Button :disabled="form.processing">Update</Button>
                    <Button type="button" variant="outline" @click="goBack">Cancel</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm, usePage, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

const page = usePage();
const school = page.props.school as any;
const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
    { title: 'Edit School', href: `/schools/${school.id}/edit` },
];

const form = useForm({
    name: school.name || '',
    address: school.address || '',
    contact: school.contact || '',
});

const submit = () => {
    form.patch(route('schools.update', school.id), {
        onSuccess: () => {
            toast.success('School updated successfully.');
            router.visit(route('schools.index'));
        },
        onError: () => {
            toast.error('Please fix the errors and try again.');
        },
    });
};

const goBack = () => {
    router.visit(route('schools.index'));
};
</script>