<template>
    <AppLayout>

        <Head title="View Paper" />

        <div class="max-w-4xl mx-auto w-full px-4 py-8 mt-20 sm:mt-8">
            <!-- Paper Header -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                <!-- School Logo and Name -->
                <div class="flex justify-between items-start mb-6">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-center text-gray-900 mb-2">
                            {{ paper.title?.toUpperCase() || 'PRACTICE EXAMINATION' }}
                        </h1>
                        <div class="text-center text-lg text-gray-700 mb-4">
                            {{ paper.subject_name || 'SUBJECT' }} ({{ paper.subject_code || 'CODE' }})
                        </div>
                        <div class="text-center text-lg font-semibold text-gray-800">
                            GRADE {{ paper.class?.name || '12' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mb-2">
                            <span class="text-white font-bold text-xl">S</span>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">SCHOOL NAME</div>
                        <div class="text-xs text-gray-600">Education for Life</div>
                    </div>
                </div>

                <!-- Exam Details -->
                <div class="flex justify-between items-center mb-6 text-lg">
                    <div class="text-gray-700">
                        <strong>Duration:</strong> {{ paper.time_duration || 90 }} Minutes
                    </div>
                    <div class="text-gray-700">
                        <strong>Maximum Marks:</strong> {{ paper.total_marks || calculateTotalMarks() }}
                    </div>
                </div>

                <!-- General Instructions -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">General Instructions:</h3>
                    <ol class="list-decimal list-inside space-y-1 text-gray-700">
                        <li>The Question Paper contains three sections.</li>
                        <li>Section A has {{ getSectionCount('objective') }} questions. Attempt any {{
                            Math.min(getSectionCount('objective'), 20) }} questions.</li>
                        <li>Section B has {{ getSectionCount('short_questions') }} questions. Attempt any {{
                            Math.min(getSectionCount('short_questions'), 20) }} questions.</li>
                        <li>Section C has {{ getSectionCount('long_questions') }} questions. Attempt any {{
                            Math.min(getSectionCount('long_questions'), 5) }} questions.</li>
                        <li>All questions carry equal marks.</li>
                        <li>There is no negative marking.</li>
                    </ol>
                </div>

                <hr class="border-gray-300 mb-6">
            </div>

            <!-- Questions by Section -->
            <div class="space-y-8">
                <!-- Section A: Objective Questions -->
                <div v-if="getSectionQuestions('objective').length > 0" class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-xl font-bold text-center text-gray-900 mb-4 underline">SECTION A</h2>
                    <p class="text-gray-700 mb-6 text-center">
                        This section consists of <strong>{{ getSectionCount('objective') }} Multiple Choice
                            Questions</strong>
                        with overall choice to attempt any <strong>{{ Math.min(getSectionCount('objective'), 20) }}
                            questions</strong>.
                        In case more than desirable number of questions are attempted, <strong>ONLY first {{
                            Math.min(getSectionCount('objective'), 20) }}</strong>
                        will be considered for evaluation.
                    </p>

                    <div class="space-y-6">
                        <div v-for="(question, index) in getSectionQuestions('objective')" :key="question.id"
                            class="border-b border-gray-200 pb-4">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="font-semibold text-gray-900 min-w-[30px]">{{ question.question_number ||
                                    index + 1 }}.</span>
                                <div class="flex-1">
                                    <p class="text-gray-900">{{ question.text }}</p>

                                    <!-- Multiple Choice Options -->
                                    <div v-if="question.type === 'multiple_choice' && question.options"
                                        class="mt-3 space-y-2">
                                        <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                                            class="flex items-center gap-2">
                                            <span class="text-gray-700 font-medium min-w-[20px]">
                                                {{ String.fromCharCode(97 + optionIndex) }})
                                            </span>
                                            <span class="text-gray-700">{{ option }}</span>
                                        </div>
                                    </div>

                                    <!-- True/False Options -->
                                    <div v-if="question.type === 'true_false'" class="mt-3 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-700 font-medium min-w-[20px]">a)</span>
                                            <span class="text-gray-700">True</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-700 font-medium min-w-[20px]">b)</span>
                                            <span class="text-gray-700">False</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section B: Short Questions -->
                <div v-if="getSectionQuestions('short_questions').length > 0" class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-xl font-bold text-center text-gray-900 mb-4 underline">SECTION B</h2>
                    <p class="text-gray-700 mb-6 text-center">
                        This section consists of <strong>{{ getSectionCount('short_questions') }} Short Answer
                            Questions</strong>
                        with overall choice to attempt any <strong>{{ Math.min(getSectionCount('short_questions'), 20)
                        }} questions</strong>.
                    </p>

                    <div class="space-y-6">
                        <div v-for="(question, index) in getSectionQuestions('short_questions')" :key="question.id"
                            class="border-b border-gray-200 pb-4">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="font-semibold text-gray-900 min-w-[30px]">{{ question.question_number ||
                                    index + 1 }}.</span>
                                <div class="flex-1">
                                    <p class="text-gray-900">{{ question.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section C: Long Questions -->
                <div v-if="getSectionQuestions('long_questions').length > 0" class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-xl font-bold text-center text-gray-900 mb-4 underline">SECTION C</h2>
                    <p class="text-gray-700 mb-6 text-center">
                        This section consists of <strong>{{ getSectionCount('long_questions') }} Long Answer
                            Questions</strong>
                        with overall choice to attempt any <strong>{{ Math.min(getSectionCount('long_questions'), 5) }}
                            questions</strong>.
                    </p>

                    <div class="space-y-6">
                        <div v-for="(question, index) in getSectionQuestions('long_questions')" :key="question.id"
                            class="border-b border-gray-200 pb-4">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="font-semibold text-gray-900 min-w-[30px]">{{ question.question_number ||
                                    index + 1 }}.</span>
                                <div class="flex-1">
                                    <p class="text-gray-900">{{ question.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                <div class="flex justify-between items-center text-sm text-gray-600">
                    <div>SCHOOL NAME, Location</div>
                    <div>{{ paper.title }} – Grade {{ paper.class?.name }} {{ paper.subject_name }} – {{ new
                        Date().toLocaleDateString() }}</div>
                    <div>Page 1</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4 mt-6">
                <Button variant="outline" @click="goBack">
                    Back to Papers
                </Button>
                <Button @click="printPaper">
                    Print Paper
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';

interface Question {
    id: number;
    text: string;
    type: string;
    section: string;
    marks: number;
    question_number?: number;
    options?: string[];
    answer?: string;
}

interface ClassModel {
    id: number;
    name: string;
}

interface Paper {
    id: number;
    title: string;
    class_id: number;
    class?: ClassModel;
    subject_name?: string;
    subject_code?: string;
    total_marks?: number;
    time_duration?: number;
    questions: Question[];
}

interface Props {
    paper: Paper;
}

const props = defineProps<Props>();

const paper = computed(() => props.paper);

function getSectionQuestions(section: string): Question[] {
    return paper.value.questions?.filter(q => q.section === section) || [];
}

function getSectionCount(section: string): number {
    return getSectionQuestions(section).length;
}

function calculateTotalMarks(): number {
    return paper.value.questions?.reduce((total, q) => total + q.marks, 0) || 0;
}

function goBack() {
    router.visit(route('papersquestions.index'));
}

function printPaper() {
    window.print();
}
</script>

<style scoped>
@media print {
    .max-w-4xl {
        max-width: none;
    }

    .shadow-lg {
        box-shadow: none;
    }

    .bg-white {
        background: white !important;
    }

    .text-gray-900 {
        color: black !important;
    }

    .text-gray-700 {
        color: black !important;
    }

    .text-gray-600 {
        color: black !important;
    }

    .border-gray-200 {
        border-color: black !important;
    }

    .border-gray-300 {
        border-color: black !important;
    }
}
</style>