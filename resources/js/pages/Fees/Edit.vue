<template>
    <AppLayout>

        <Head title="Edit Fee" />

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-8">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit Fee</h1>
                    <Button variant="outline" @click="goBack">Back to Fees</Button>
                </div>

                <!-- Student Information -->
                <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Student Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Name:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ fee.student?.name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Registration
                                Number:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ fee.student?.registration_number
                            }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Class:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ fee.student?.class?.name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">School:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ fee.student?.school?.name }}</span>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- First Row: Fee Type, Amount, Status -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Fee Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Fee Type *
                            </label>
                            <select v-model="form.type"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.type }">
                                <option value="admission">Admission Fee</option>
                                <option value="tuition">Tuition Fee</option>
                                <option value="papers">Papers Fee</option>
                            </select>
                            <InputError :message="errors.type" />
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Amount (PKR) *
                            </label>
                            <input v-model="form.amount" type="number" step="0.01" min="0"
                                placeholder="Enter fee amount"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.amount }" />
                            <InputError :message="errors.amount" />
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Status *
                            </label>
                            <select v-model="form.status"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.status }">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <!-- Second Row: Due Date, Voucher Number (if paid) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Due Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Due Date *
                            </label>
                            <input v-model="form.due_date" type="date"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.due_date }" />
                            <InputError :message="errors.due_date" />
                        </div>

                        <!-- Voucher Number (only show if status is paid) -->
                        <div v-if="form.status === 'paid'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Voucher Number
                            </label>
                            <input v-model="form.voucher_number" type="text" placeholder="Enter voucher number"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                :class="{ 'border-red-500': errors.voucher_number }" />
                            <InputError :message="errors.voucher_number" />
                        </div>
                    </div>

                    <!-- Third Row: Paid Date (only show if status is paid) -->
                    <div v-if="form.status === 'paid'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Paid Date
                        </label>
                        <input v-model="form.paid_at" type="datetime-local"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                            :class="{ 'border-red-500': errors.paid_at }" />
                        <InputError :message="errors.paid_at" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" @click="goBack">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Fee</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router, Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';

interface Student {
    id: number;
    name: string;
    registration_number: string;
    class?: {
        id: number;
        name: string;
    };
    school?: {
        id: number;
        name: string;
    };
}

interface Fee {
    id: number;
    student_id: number;
    type: string;
    amount: number;
    status: string;
    due_date: string;
    paid_at?: string;
    voucher_number?: string;
    student?: Student;
}

interface Props {
    fee: Fee;
}

const props = defineProps<Props>();

const form = useForm({
    type: props.fee.type,
    amount: props.fee.amount,
    status: props.fee.status,
    due_date: props.fee.due_date,
    paid_at: props.fee.paid_at ? new Date(props.fee.paid_at).toISOString().slice(0, 16) : '',
    voucher_number: props.fee.voucher_number || '',
});

const errors = computed(() => form.errors);

function goBack() {
    router.visit(route('fees.index'));
}

function submitForm() {
    form.put(route('fees.update', props.fee.id), {
        onSuccess: () => {
            toast.success('Fee updated successfully!');
        },
        onError: (errors) => {
            // Show validation errors as toast messages for non-field errors
            if (errors.error) {
                toast.error(errors.error);
            }
        },
    });
}
</script>
