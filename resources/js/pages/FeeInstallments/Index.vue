<!-- <template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Installments Management" />

        <div class="container mx-auto p-6">
            <div class="mb-6 bg-white dark:bg-neutral-900 p-6 rounded-lg">
                <label class="block mb-2">Registration No</label>
                <input v-model="form.registration_number" type="text" class="input" placeholder="e.g. STU1234" />
                <button @click="searchStudent" class="btn mt-2">Search Student</button>
                <p v-if="error" class="text-red-600 mt-2">{{ error }}</p>
            </div>

            <div v-if="student" class="mb-6">
                <h2 class="text-xl font-semibold">{{ student.name }} ({{ student.registration_number }})</h2>
                <p class="mb-4"><strong>Total Fee:</strong> Rs {{ fee?.amount }}</p>

                <div class="mb-4 max-w-xs">
                    <label class="block mb-2">No. of Installments</label>
                    <input type="number" v-model.number="numInstallments" min="1" @input="generateInstallments"
                        class="input" />
                </div>

                <form @submit.prevent="submitInstallments" class="bg-white dark:bg-neutral-900 p-6 rounded-lg">
                    <table class="w-full table-auto mb-4">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-neutral-800">
                                <th class="p-2">Due Date</th>
                                <th class="p-2">Amount</th>
                                <th class="p-2">Fee Items Breakdown</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(inst, i) in installments" :key="i" class="border-b">
                                <td class="p-2">
                                    <input type="date" v-model="inst.due_date" required class="input" />
                                </td>
                                <td class="p-2">
                                    <input type="number" v-model.number="inst.amount" step="0.01" min="0" required
                                        class="input" />
                                </td>
                                <td class="p-2">
                                    <div v-for="(item, idx) in inst.fee_items_breakdown" :key="idx" class="mb-1">
                                        <span class="font-medium">{{ item.type }}: </span>
                                        <input type="number" v-model.number="item.amount" step="0.01" min="0"
                                            class="w-24 inline-block input" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="btn">Save Installments</button>
                </form>

                <div v-if="existingInstallments.length" class="mt-6 overflow-x-auto">
                    <h3 class="font-semibold mb-2">Existing Installments</h3>
                    <table class="w-full table-auto">
                        <thead class="bg-gray-100 dark:bg-neutral-800">
                            <tr>
                                <th class="p-2">Due Date</th>
                                <th class="p-2">Amount</th>
                                <th class="p-2">Status</th>
                                <th class="p-2">Paid At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inst in existingInstallments" :key="inst.id" class="border-b">
                                <td class="p-2">{{ inst.due_date }}</td>
                                <td class="p-2">Rs {{ inst.amount }}</td>
                                <td class="p-2">{{ inst.status }}</td>
                                <td class="p-2">{{ inst.paid_at ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="text-center py-12 text-gray-600 dark:text-gray-400">
                <p>Use the form above to search for a student.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Student {
    id: number;
    name: string;
    registration_number: string;
}
interface Fee {
    amount: number;
}
interface FeeItem {
    id: number;
    type: string;
    amount: number;
}
interface Installment {
    due_date: string;
    amount: number;
    fee_items_breakdown: FeeItem[];
    id?: number;
    status?: string;
    paid_at?: string | null;
}

const form = useForm({ registration_number: '' });
const { student: initStudent, fee: initFee, fee_items, installments: existingInsts, error } = defineProps<{
    student: Student | null;
    fee: Fee | null;
    fee_items: FeeItem[];
    installments: Installment[];
    error: string | null;
}>();

const student = ref(initStudent);
const fee = ref(initFee);
const feeItems = ref(fee_items);
const existingInstallments = ref(existingInsts);

const numInstallments = ref(1);
const installments = ref<Installment[]>([]);

const searchStudent = () => {
    if (!form.registration_number) return;
    router.get(route('installments.index'), form.data(), {
        preserveScroll: true,
    });
};

const generateInstallments = () => {
    if (!fee.value) return;
    const n = numInstallments.value || 1;
    installments.value = [];
    const perInstFeeItems = feeItems.value.map(item => ({
        ...item,
        amount: parseFloat((item.amount / n).toFixed(2))
    }));
    for (let i = 0; i < n; i++) {
        installments.value.push({
            due_date: '',
            amount: perInstFeeItems.reduce((sum, x) => sum + x.amount, 0),
            fee_items_breakdown: perInstFeeItems.map(item => ({ ...item })),
        });
    }
};

const submitInstallments = () => {
    if (!student.value) return;
    router.post(route('installments.store'), {
        student_id: student.value.id,
        installments: installments.value,
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Installments', href: '/fee/installments' },
];
</script>

<style scoped>
.input {
    width: 100%;
    padding: 0.5rem;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    background: white;
    color: #1f2937;
}

.btn {
    background: #3b82f6;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
}
</style> -->


<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Installments Management" />

        <div class="max-w-7xl mx-auto w-full px-4 py-6 sm:py-8">

            <!-- Header Section -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
                        <Calendar class="w-8 h-8 text-blue-600" />
                        Installments Management
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        Search a student and set up their fee installments
                    </p>
                </div>
            </div>

            <!-- Search Section -->
            <div
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <Label for="reg_number" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Registration No
                        </Label>
                        <input v-model="form.registration_number" type="text" id="reg_number" placeholder="e.g. STU1234"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                    </div>
                    <div class="sm:col-span-2 flex justify-start">
                        <Button @click="searchStudent" class="px-4 py-2 text-sm">
                            <Search class="w-4 h-4 mr-2" />
                            Search Student
                        </Button>
                    </div>
                </div>
                <p v-if="error" class="text-red-600 dark:text-red-400 text-sm mt-2">
                    {{ error }}
                </p>
            </div>

            <!-- Student and Fee Info -->
            <div v-if="student"
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ student.name }} ({{ student.registration_number }})
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Total Fee: <span class="font-medium text-gray-900 dark:text-gray-100">Rs {{ fee?.amount
                        }}</span>
                    </p>
                </div>

                <!-- Installment Generator -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <Label for="num_installments"
                            class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            No. of Installments
                        </Label>
                        <input v-model.number="numInstallments" @input="generateInstallments" type="number" min="1"
                            id="num_installments"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                    </div>
                </div>

                <!-- Installment Form -->
                <form @submit.prevent="submitInstallments" class="divide-y divide-gray-200 dark:divide-neutral-700">
                    <div v-for="(inst, i) in installments" :key="i" class="py-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            <div>
                                <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Due
                                    Date</Label>
                                <input type="date" v-model="inst.due_date" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                            </div>
                            <div>
                                <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Amount
                                    (Rs)</Label>
                                <input type="number" v-model.number="inst.amount" step="0.01" min="0" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                            </div>

                        </div>
                        <div class="mt-4">
                            <Label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Fee Items
                                Breakdown</Label>
                            <div class="grid grid-cols-3 space-y-1">
                                <div v-for="(item, idx) in inst.fee_items_breakdown" :key="idx"
                                    class="flex flex-col items-center space-x-2 w-full">
                                    <span class="text-sm text-gray-900 dark:text-gray-100 w-24">{{ item.type
                                        }}:</span>
                                    <input type="number" v-model.number="item.amount" step="0.01" min="0"
                                        class="px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all w-24" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Button type="submit" class="px-4 py-2 text-sm">
                            Save Installments
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Existing Installments -->
            <div v-if="existingInstallments.length"
                class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Existing Installments</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-200 dark:border-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="p-3 uppercase tracking-wider">Due Date</th>
                                <th class="p-3 uppercase tracking-wider">Amount</th>
                                <th class="p-3 uppercase tracking-wider">Status</th>
                                <th class="p-3 uppercase tracking-wider">Paid At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                            <tr v-for="inst in existingInstallments" :key="inst.id"
                                class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                <td class="p-3">{{ inst.due_date }}</td>
                                <td class="p-3">Rs {{ inst.amount }}</td>
                                <td class="p-3">{{ inst.status }}</td>
                                <td class="p-3">{{ inst.paid_at ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!student" class="text-center py-12 text-gray-600 dark:text-gray-400">
                <p>Use the search above to find a student.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, Search } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

interface Student { id: number; name: string; registration_number: string; }
interface Fee { amount: number; }
interface FeeItem { id: number; type: string; amount: number; }
interface Installment { due_date: string; amount: number; fee_items_breakdown: FeeItem[]; status?: string; paid_at?: string; id?: number; }

const form = useForm({ registration_number: '' });

const props = defineProps<{
    student: Student | null;
    fee: Fee | null;
    fee_items: FeeItem[];
    installments: Installment[];
    error: string | null;
}>();

const student = ref(props.student);
const fee = ref(props.fee);
const feeItems = ref(props.fee_items);
const existingInstallments = ref(props.installments);

const numInstallments = ref(1);
const installments = ref<Installment[]>([]);

const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Installments', href: '/fee/installments' },
];

function searchStudent() {
    if (!form.registration_number) return;
    router.get(route('installments.index'), form.data(), { preserveScroll: true });
}

function generateInstallments() {
    if (!fee.value) return;
    const n = numInstallments.value || 1;
    const dividedItems = feeItems.value.map(item => ({
        ...item,
        amount: parseFloat((item.amount / n).toFixed(2)),
    }));
    installments.value = Array.from({ length: n }, () => ({
        due_date: '',
        amount: dividedItems.reduce((s, f) => s + f.amount, 0),
        fee_items_breakdown: dividedItems.map(f => ({ ...f })),
    }));
}

function submitInstallments() {
    if (!student.value) return;
    router.post(route('installments.store'), {
        student_id: student.value.id,
        installments: installments.value,
    });
}
</script>
