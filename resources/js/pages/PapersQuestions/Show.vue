<template>
    <AppLayout>

        <Head title="View Paper" />

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-8 mt-20 sm:mt-8">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Paper Details</h1>
                    <div class="flex space-x-3">
                        <Button variant="outline" @click="editPaper">Edit Paper</Button>
                        <Button variant="outline" @click="goBack">Back to Papers</Button>
                    </div>
                </div>

                <!-- Paper Information -->
                <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Paper Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Title:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ paper.title }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Class:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ paper.class?.name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Section:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">
                                {{ paper.section?.name || 'Not specified' }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Teacher:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ paper.teacher?.name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status:</span>
                            <span :class="{
                                'ml-2 inline-block rounded-full px-2 py-0.5 text-xs font-semibold': true,
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': paper.published,
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': !paper.published,
                            }">
                                {{ paper.published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Questions:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ paper.questions?.length || 0 }}
                                questions</span>
                        </div>
                    </div>
                </div>

                <!-- Questions Section -->
                <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Questions</h3>

                    <div v-if="!paper.questions || paper.questions.length === 0"
                        class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No questions found for this paper.
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="(question, index) in paper.questions" :key="index"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-neutral-900">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Question {{ index + 1 }}</h4>
                                <span :class="{
                                    'inline-block rounded-full px-2 py-0.5 text-xs font-semibold': true,
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': question.type === 'multiple_choice',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': question.type === 'true_false',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': question.type === 'short_answer',
                                    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200': question.type === 'essay',
                                }">
                                    {{ formatQuestionType(question.type) }}
                                </span>
                            </div>

                            <!-- Question Text -->
                            <div class="mb-4">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Question:</span>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ question.text }}</p>
                            </div>

                            <!-- Options for Multiple Choice -->
                            <div v-if="question.type === 'multiple_choice' && question.options && question.options.length > 0"
                                class="mb-4">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Options:</span>
                                <div class="mt-2 space-y-2">
                                    <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                                        class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ String.fromCharCode(65
                                            + optionIndex) }}.</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ option }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Correct Answer -->
                            <div v-if="question.answer" class="mb-4">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Correct
                                    Answer:</span>
                                <p class="mt-1 text-gray-900 dark:text-gray-100 font-medium">{{ question.answer }}</p>
                            </div>
                        </div>
                    </div>
                </div>
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
    options?: string[];
    answer?: string;
}

interface ClassModel {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
}

interface Teacher {
    id: number;
    name: string;
}

interface Paper {
    id: number;
    title: string;
    published: boolean;
    class?: ClassModel;
    section?: Section;
    teacher?: Teacher;
    questions?: Question[];
}

interface Props {
    paper: Paper;
}

const props = defineProps<Props>();

const paper = computed(() => props.paper);

function formatQuestionType(type: string): string {
    const types = {
        'multiple_choice': 'Multiple Choice',
        'true_false': 'True/False',
        'short_answer': 'Short Answer',
        'essay': 'Essay'
    };
    return types[type as keyof typeof types] || type;
}

function editPaper() {
    router.visit(route('papersquestions.edit', paper.value.id));
}

function goBack() {
    router.visit(route('papersquestions.index'));
}
</script>