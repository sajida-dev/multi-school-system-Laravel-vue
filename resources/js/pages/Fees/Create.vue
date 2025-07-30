<template>
    <AppLayout>

        <Head title="Create Fee" />

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-8">
            <div class="bg-white  dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New Fee</h1>
                    <Button variant="outline" @click="goBack">Back to Fees</Button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- First Row: Fee Type, School, Class -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Fee Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Fee Type <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.type"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.type }">
                                <option value="">Select Fee Type</option>
                                <option value="admission">Admission Fee</option>
                                <option value="tuition">Tuition Fee</option>
                                <option value="papers">Papers Fee</option>
                            </select>
                            <InputError :message="errors.type" />
                        </div>
                        <!-- School Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                School <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.school_id" @change="onSchoolChange"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.school_id }">
                                <option value="">Select School</option>
                                <option v-for="school in schools" :key="school.id" :value="school.id">
                                    {{ school.name }}
                                </option>
                            </select>
                            <InputError :message="errors.school_id" />
                        </div>

                        <!-- Class Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.class_id" @change="onClassChange"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.class_id }" :disabled="!form.school_id">
                                <option value="">Select Class</option>
                                <option v-for="classItem in props.classes" :key="classItem.id" :value="classItem.id">
                                    {{ classItem.name }}
                                </option>
                            </select>
                            <InputError :message="errors.class_id" />
                        </div>
                    </div>

                    <!-- Second Row: Amount, Due Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Amount (PKR) <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.amount" type="number" step="0.01" min="0"
                                placeholder="Enter fee amount"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.amount }" />
                            <InputError :message="errors.amount" />
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.due_date" type="date" :min="today"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.due_date }" />
                            <InputError :message="errors.due_date" />
                        </div>
                    </div>

                    <!-- Third Row: Description (Full Width) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Description (Optional)
                        </label>
                        <textarea v-model="form.description" rows="3" placeholder="Enter fee description..."
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                            :class="{ 'border-red-500': errors.description }"></textarea>
                        <InputError :message="errors.description" />
                    </div>

                    <!-- Students Preview -->
                    <div v-if="props.students.length > 0" class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                            Students in Selected Class ({{ props.students.length }} students)
                        </h3>
                        <div class="max-h-40 overflow-y-auto">
                            <div class="grid grid-cols-3 sm:grid-cols-1 gap-2">
                                <div v-for="student in props.students" :key="student.id"
                                    class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ student.id }} : {{ student.name }} (Reg# {{ student.registration_number }})
                                </div>
                            </div>

                        </div>
                    </div>
                    <div v-else
                        class="text-center py-4 px-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="text-yellow-800 dark:text-yellow-200 font-medium mb-1">⚠️ No Students Found</div>
                        <div class="text-yellow-700 dark:text-yellow-300 text-sm">
                            No admitted students found in the selected class. Please ensure there are admitted students
                            before creating fees.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" @click="goBack">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing || props.students.length === 0">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else-if="props.students.length === 0">No Students Available</span>
                            <span v-else>Create Fee</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';

interface School {
    id: number;
    name: string;
}

interface ClassModel {
    id: number;
    name: string;
}

interface Student {
    id: number;
    name: string;
    registration_number: string;
}

interface Props {
    schools: School[];
    classes: ClassModel[];
    students: Student[];
    selectedSchoolId?: number;
    selectedClassId?: number;
}

const props = defineProps<Props>();

const form = useForm({
    type: '',
    amount: '',
    due_date: '',
    school_id: props.selectedSchoolId ? String(props.selectedSchoolId) : '',
    class_id: props.selectedClassId ? String(props.selectedClassId) : '',
    description: '',
} as {
    type: string;
    amount: string;
    due_date: string;
    school_id: string;
    class_id: string;
    description: string;
});

const errors = computed(() => form.errors);

const today = computed(() => {
    const date = new Date();
    return date.toISOString().split('T')[0];
});

function goBack() {
    router.visit(route('fees.index'));
}

function onSchoolChange() {
    form.class_id = '';

    if (form.school_id) {
        // Use Inertia to get classes for the selected school
        router.get(route('fees.create'), {
            school_id: form.school_id
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }
}

function onClassChange() {
    if (form.school_id && form.class_id) {
        // Use Inertia to get students for the selected class
        router.get(route('fees.create'), {
            school_id: form.school_id,
            class_id: form.class_id
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }
}

function submitForm() {
    form.post(route('fees.store'), {
        onSuccess: (response) => {
            if (response.props.errors && Object.keys(response.props.errors).length > 0) {
                console.log('Errors found in response:', response.props.errors);
                Object.values(response.props.errors).flat().forEach(message => {
                    if (typeof message === 'string') {
                        toast.error(message);
                    }
                });
            } else {
                toast.success('Fee created successfully!');
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => {
                if (typeof message === 'string') {
                    toast.error(message);
                }
            });
        },
    });
}

onMounted(() => {
    // Initialize form with selected school if available
    if (props.selectedSchoolId && !form.school_id) {
        form.school_id = String(props.selectedSchoolId);
    }
});
</script>
