<template>
    <AppLayout :title="'Results Management'">

        <Head title="Results Management" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Results Management</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage student results and academic
                                performance</p>
                        </div>
                        <Button @click="router.visit(route('results.create'))" class="px-4 py-2 text-sm">
                            <Plus class="w-4 h-4 mr-2" />
                            Add Results
                        </Button>
                    </div>
                </div>

                <!-- Filters Section -->
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-6 mb-6">
                    <!-- Desktop Filters -->
                    <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Class Selection -->
                        <div>
                            <Label for="class_id"
                                class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <Building2 class="w-4 h-4 inline mr-2" />
                                Class
                            </Label>
                            <select id="class_id" v-model="selectedClass"
                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">Select Class</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>

                        <!-- Section Selection -->
                        <div>
                            <Label for="section_id"
                                class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <Users class="w-4 h-4 inline mr-2" />
                                Section
                            </Label>
                            <select id="section_id" v-model="selectedSection"
                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">All Sections</option>
                                <option v-for="section in sections" :key="section.id" :value="section.id">{{
                                    section.name }}</option>
                            </select>
                        </div>

                        <!-- Term Selection -->
                        <div>
                            <Label for="term" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <Calendar class="w-4 h-4 inline mr-2" />
                                Term
                            </Label>
                            <select id="term" v-model="selectedTerm"
                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option v-for="(label, value) in terms" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>

                        <!-- Load Results Button -->
                        <div class="flex items-end">
                            <Button @click="loadResults" :disabled="!selectedClass" class="w-full px-3 py-2 text-sm">
                                <RefreshCw class="w-4 h-4 mr-2" />
                                Load Results
                            </Button>
                        </div>
                    </div>

                    <!-- Mobile Filter Button -->
                    <div class="md:hidden">
                        <Button @click="openFilterSheet" variant="outline" class="w-full px-3 py-2 text-sm">
                            <Filter class="w-4 h-4 mr-2" />
                            Filters
                        </Button>
                    </div>
                </div>

                <!-- Results Table -->
                <div v-if="results.length > 0"
                    class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-neutral-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Results for {{ selectedClassName }} - {{ terms[selectedTerm] }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ results.length }} student{{ results.length !== 1 ? 's' : '' }} found
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Student
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Roll No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Marks
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Percentage
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="result in results" :key="result.student.id"
                                    class="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                        {{ getInitials(result.student.user?.name || 'Student') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ result.student.user?.name || 'Unknown' }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ result.student.section?.name || 'No Section' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ result.student.roll_number || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ result.total_marks }}/{{ result.total_possible_marks }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            result.percentage >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200' :
                                                result.percentage >= 60 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200' :
                                                    'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200'
                                        ]">
                                            {{ result.percentage }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <Button @click="router.visit(route('results.show', result.student.id))"
                                                variant="outline" size="sm" class="px-2 py-1 text-xs">
                                                <Eye class="w-3 h-3 mr-1" />
                                                View
                                            </Button>
                                            <Button @click="router.visit(route('results.edit', result.student.id))"
                                                variant="outline" size="sm" class="px-2 py-1 text-xs">
                                                <Edit class="w-3 h-3 mr-1" />
                                                Edit
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="selectedClass" class="text-center py-12">
                    <div
                        class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                        <BarChart3 class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No results found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        No results are available for the selected class and term.
                    </p>
                </div>

                <!-- Instructions -->
                <div v-else class="text-center py-12">
                    <div
                        class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                        <BarChart3 class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Select Class and Term</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Choose a class and term to view student results.
                    </p>
                </div>
            </div>
        </div>

        <!-- Touch-friendly Bottom Sheet for Mobile Filters -->
        <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
            ref="filterSheet" class="dark:bg-neutral-900">
            <div class="sheet-content dark:bg-neutral-900">
                <h2 class="text-lg font-semibold mb-4">Results Filters</h2>
                <div class="flex flex-col gap-4">
                    <!-- Class Selection -->
                    <div class="flex flex-col">
                        <label for="class_id_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Class
                        </label>
                        <select id="class_id_mobile" v-model="selectedClass"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900">
                            <option value="">Select Class</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                    </div>

                    <!-- Section Selection -->
                    <div class="flex flex-col">
                        <label for="section_id_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Section
                        </label>
                        <select id="section_id_mobile" v-model="selectedSection"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900">
                            <option value="">All Sections</option>
                            <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Term Selection -->
                    <div class="flex flex-col">
                        <label for="term_mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Term
                        </label>
                        <select id="term_mobile" v-model="selectedTerm"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-neutral-900">
                            <option v-for="(label, value) in terms" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>

                    <!-- Load Results Button -->
                    <div class="pt-2">
                        <Button @click="loadResultsAndClose" :disabled="!selectedClass"
                            class="w-full px-3 py-2 text-sm">
                            <RefreshCw class="w-4 h-4 mr-2" />
                            Load Results
                        </Button>
                    </div>
                </div>
            </div>
        </vue-bottom-sheet>
    </AppLayout>
</template>

<script setup lang="ts">
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Building2, Users, Calendar, RefreshCw, Plus, BarChart3, Filter, Eye, Edit } from 'lucide-vue-next';

interface Student {
    id: number;
    roll_number?: string;
    user?: {
        name: string;
    };
    class?: {
        name: string;
    };
    section?: {
        name: string;
    };
}

interface Class {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
}

interface Result {
    student: Student;
    results: any[];
    total_marks: number;
    total_possible_marks: number;
    percentage: number;
}

interface Props {
    classes: Class[];
    sections: Section[];
    results: Result[];
    selectedClass?: string;
    selectedSection?: string;
    selectedTerm: string;
    terms: Record<string, string>;
}

const props = defineProps<Props>();

const filterSheet = ref<InstanceType<typeof VueBottomSheet>>();

function openFilterSheet() {
    filterSheet.value?.open();
}

function closeFilterSheet() {
    filterSheet.value?.close();
}

const selectedClass = ref(props.selectedClass || '');
const selectedSection = ref(props.selectedSection || '');
const selectedTerm = ref(props.selectedTerm);

const selectedClassName = computed(() => {
    const cls = props.classes.find(c => c.id.toString() === selectedClass.value.toString());
    return cls ? cls.name : '';
});

function getInitials(name: string): string {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function loadResults() {
    if (!selectedClass.value) {
        return;
    }

    router.visit(route('results.index'), {
        data: {
            class_id: selectedClass.value,
            section_id: selectedSection.value,
            term: selectedTerm.value
        },
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}

function loadResultsAndClose() {
    loadResults();
    closeFilterSheet();
}
</script>