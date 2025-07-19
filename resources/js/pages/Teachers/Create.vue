<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Add Teacher" />
        <div class="max-w-2xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Add New Teacher</h1>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Teacher Name<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" class="form-input w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Teacher CNIC<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.cnic" type="text" class="form-input w-full" required />
                        <InputError :message="form.errors.cnic" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email<span class="text-red-500">*</span></label>
                        <input v-model="form.email" type="email" class="form-input w-full" required />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Password<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.password" type="password" class="form-input w-full" required />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Gender<span class="text-red-500">*</span></label>
                        <select v-model="form.gender" class="form-input w-full" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        <InputError :message="form.errors.gender" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Marital Status<span
                                class="text-red-500">*</span></label>
                        <select v-model="form.marital_status" class="form-input w-full" required>
                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                        </select>
                        <InputError :message="form.errors.marital_status" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Role<span
                                class="text-red-500">*</span></label>
                        <select v-model="form.role" class="form-input w-full" required>
                            <option value="">Please Select</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Date of Birth<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.dob" type="date" class="form-input w-full" required />
                        <InputError :message="form.errors.dob" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Salary<span class="text-red-500">*</span></label>
                        <input v-model="form.salary" type="number" class="form-input w-full" required min="0" />
                        <InputError :message="form.errors.salary" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Teacher Contact No<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.contact_no" type="text" class="form-input w-full" required />
                        <InputError :message="form.errors.contact_no" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Date of Joining<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.date_of_joining" type="date" class="form-input w-full" required />
                        <InputError :message="form.errors.date_of_joining" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Current Experience in Years</label>
                        <input v-model="form.experience_years" type="number" class="form-input w-full" min="0" />
                        <InputError :message="form.errors.experience_years" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">School<span class="text-red-500">*</span></label>
                        <select v-model="form.school_id" class="form-input w-full" required>
                            <option value="">Select School</option>
                            <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.school_id" />
                    </div>
                </div>
                <div class="mt-6 flex gap-2 justify-end">
                    <Button variant="outline" type="button" @click="goBack">Cancel</Button>
                    <Button variant="default" type="submit" :disabled="form.processing">Add Teacher</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/components/AppShell.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';

const page = usePage<any>();
const roles = page.props.roles || [];
const schools = page.props.schools || [];

const form = useForm({
    name: '',
    email: '',
    password: '',
    cnic: '',
    gender: '',
    marital_status: '',
    role: '',
    dob: '',
    salary: '',
    contact_no: '',
    date_of_joining: '',
    experience_years: '',
    school_id: '',
});

const breadcrumbItems = [
    { label: 'Dashboard', href: '/' },
    { label: 'Teachers', href: '/teachers' },
    { label: 'Add Teacher', href: '/teachers/create' },
];

function submit() {
    form.post('/teachers', {
        onSuccess: () => {
            router.visit('/teachers');
        },
    });
}
function goBack() {
    router.visit('/teachers');
}
</script>