<template>
    <AppLayout>

        <Head title="View Paper" />

        <div class="max-w-4xl mx-auto w-full px-4 py-8">
            <!-- Paper Header -->
            <div class="header">
                <!-- School Logo and Name -->
                <div class="school-info">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-center text-gray-900 mb-2">
                            {{ paper.title?.toUpperCase() || 'PRACTICE EXAMINATION' }}
                        </h1>
                        <div class="subject-info text-center text-lg text-gray-700 mb-4">
                            {{ paper.subject_name || 'SUBJECT' }} ({{ paper.subject_code || 'CODE' }})
                        </div>
                        <div class="grade-info text-center text-lg font-semibold text-gray-800">
                            GRADE {{ paper.class?.name || '12' }}
                        </div>
                    </div>
                    <div class="school-details">
                        <!-- School Logo -->
                        <div v-if="activeSchool?.logo_url" class="school-logo mb-2">
                            <img :src="activeSchool.logo_url" :alt="activeSchool.name"
                                class="w-full h-full object-contain" />
                        </div>
                        <div v-else class="school-logo mb-2">
                            <span class="text-white font-bold text-xl">{{ getSchoolInitials(activeSchool?.name)
                                }}</span>
                        </div>
                        <div class="school-name">{{ activeSchool?.name || 'SCHOOL NAME' }}</div>
                        <div class="school-address">{{ activeSchool?.address || 'Location' }}</div>
                    </div>
                </div>

                <!-- Exam Details -->
                <div class="exam-details">
                    <div class="text-gray-700">
                        <strong>Duration:</strong> {{ paper.time_duration || 90 }} Minutes
                    </div>
                    <div class="text-gray-700">
                        <strong>Maximum Marks:</strong> {{ paper.total_marks || calculateTotalMarks() }}
                    </div>
                </div>

                <!-- General Instructions -->
                <div class="instructions">
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
                <div v-if="getSectionQuestions('objective').length > 0" class="section">
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
                            class="question">
                            <div class="question-header">
                                <span class="question-number">{{ question.question_number ||
                                    index + 1 }}.</span>
                                <div class="question-text">
                                    <p class="text-gray-900">{{ question.text }}</p>

                                    <!-- Multiple Choice Options -->
                                    <div v-if="question.type === 'multiple_choice' && question.options" class="options">
                                        <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                                            class="option">
                                            <span class="option-label">
                                                {{ String.fromCharCode(97 + optionIndex) }})
                                            </span>
                                            <span class="text-gray-700">{{ option }}</span>
                                        </div>
                                    </div>

                                    <!-- True/False Options -->
                                    <div v-if="question.type === 'true_false'" class="options">
                                        <div class="option">
                                            <span class="option-label">a)</span>
                                            <span class="text-gray-700">True</span>
                                        </div>
                                        <div class="option">
                                            <span class="option-label">b)</span>
                                            <span class="text-gray-700">False</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section B: Short Questions -->
                <div v-if="getSectionQuestions('short_questions').length > 0" class="section">
                    <h2 class="text-xl font-bold text-center text-gray-900 mb-4 underline">SECTION B</h2>
                    <p class="text-gray-700 mb-6 text-center">
                        This section consists of <strong>{{ getSectionCount('short_questions') }} Short Answer
                            Questions</strong>
                        with overall choice to attempt any <strong>{{ Math.min(getSectionCount('short_questions'), 20)
                            }} questions</strong>.
                    </p>

                    <div class="space-y-6">
                        <div v-for="(question, index) in getSectionQuestions('short_questions')" :key="question.id"
                            class="question">
                            <div class="question-header">
                                <span class="question-number">{{ question.question_number ||
                                    index + 1 }}.</span>
                                <div class="question-text">
                                    <p class="text-gray-900">{{ question.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section C: Long Questions -->
                <div v-if="getSectionQuestions('long_questions').length > 0" class="section">
                    <h2 class="text-xl font-bold text-center text-gray-900 mb-4 underline">SECTION C</h2>
                    <p class="text-gray-700 mb-6 text-center">
                        This section consists of <strong>{{ getSectionCount('long_questions') }} Long Answer
                            Questions</strong>
                        with overall choice to attempt any <strong>{{ Math.min(getSectionCount('long_questions'), 5) }}
                            questions</strong>.
                    </p>

                    <div class="space-y-6">
                        <div v-for="(question, index) in getSectionQuestions('long_questions')" :key="question.id"
                            class="question">
                            <div class="question-header">
                                <span class="question-number">{{ question.question_number ||
                                    index + 1 }}.</span>
                                <div class="question-text">
                                    <p class="text-gray-900">{{ question.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div>{{ activeSchool?.name || 'SCHOOL NAME' }}, {{ activeSchool?.address || 'Location' }}</div>
                <div>{{ paper.title }} – Grade {{ paper.class?.name }} {{ paper.subject_name }} – {{ new
                    Date().toLocaleDateString() }}</div>
                <div>Page 1</div>
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
import { router, Head, usePage } from '@inertiajs/vue3';
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

interface School {
    id: number;
    name: string;
    address: string;
    contact: string;
    logo?: string;
    main_image?: string;
    logo_url?: string;
    main_image_url?: string;
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
const page = usePage();

const paper = computed(() => props.paper);
const activeSchool = computed(() => page.props.activeSchool as School);

function getSchoolInitials(schoolName?: string): string {
    if (!schoolName) return 'S';
    return schoolName
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function getSectionQuestions(section: string): Question[] {
    return paper.value.questions?.filter(q => q.section === section) || [];
}

function getSectionCount(section: string): number {
    return getSectionQuestions(section).length;
}

function calculateTotalMarks(): number {
    return paper.value.questions?.reduce((total, q) => total + q.marks, 0) || 0;
}

function calculatePageBreaks(): { section: string; questions: Question[]; pageNumber: number }[] {
    const questionsPerPage = 8; // Approximate questions that fit on one page
    const pages: { section: string; questions: Question[]; pageNumber: number }[] = [];
    let currentPage = 1;
    let currentQuestions = 0;

    const sections = ['objective', 'short_questions', 'long_questions'];

    sections.forEach(section => {
        const sectionQuestions = getSectionQuestions(section);
        if (sectionQuestions.length === 0) return;

        // Check if we need a new page for this section
        if (currentQuestions > 0 && currentQuestions + sectionQuestions.length > questionsPerPage) {
            currentPage++;
            currentQuestions = 0;
        }

        // Split questions if they don't fit on current page
        let remainingQuestions = [...sectionQuestions];
        while (remainingQuestions.length > 0) {
            const questionsForThisPage = remainingQuestions.splice(0, questionsPerPage - currentQuestions);
            pages.push({
                section,
                questions: questionsForThisPage,
                pageNumber: currentPage
            });

            currentQuestions += questionsForThisPage.length;

            if (remainingQuestions.length > 0) {
                currentPage++;
                currentQuestions = 0;
            }
        }
    });

    return pages;
}

function goBack() {
    router.visit(route('papersquestions.index'));
}

function printPaper() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Please allow popups to print the paper');
        return;
    }

    // Get the paper content
    const paperContent = document.querySelector('.max-w-4xl');
    if (!paperContent) return;

    // Create the print HTML with improved styling
    const printHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${paper.value.title || 'Exam Paper'}</title>
            <style>
                @page {
                    size: A4;
                    margin: 1in;
                }
                
                * {
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Times New Roman', serif;
                    margin: 0;
                    padding: 0;
                    line-height: 1.6;
                    color: black;
                    background: white;
                    font-size: 12pt;
                }
                
                .paper-container {
                    max-width: 100%;
                    margin: 0 auto;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                
                .header h1 {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                }
                
                .header .subject-info {
                    font-size: 14pt;
                    margin-bottom: 10px;
                }
                
                .header .grade-info {
                    font-size: 14pt;
                    font-weight: bold;
                }
                
                .school-info {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                
                .school-logo {
                    width: 60px;
                    height: 60px;
                    background: #3b82f6;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 18px;
                }
                
                .school-details {
                    text-align: center;
                }
                
                .school-name {
                    font-weight: bold;
                    font-size: 12pt;
                }
                
                .school-address {
                    font-size: 10pt;
                }
                
                .exam-details {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    font-size: 12pt;
                    page-break-after: avoid;
                }
                
                .instructions {
                    margin-bottom: 30px;
                    page-break-after: avoid;
                }
                
                .instructions h3 {
                    font-size: 14pt;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                
                .instructions ol {
                    margin-left: 20px;
                    font-size: 11pt;
                }
                
                .instructions li {
                    margin-bottom: 5px;
                }
                
                .section {
                    margin-bottom: 40px;
                    page-break-inside: avoid;
                }
                
                .section h2 {
                    text-align: center;
                    text-decoration: underline;
                    margin-bottom: 15px;
                    font-size: 14pt;
                    font-weight: bold;
                    page-break-after: avoid;
                }
                
                .section p {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 11pt;
                    page-break-after: avoid;
                }
                
                .question {
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
                
                .question-header {
                    display: flex;
                    align-items: flex-start;
                    gap: 10px;
                    margin-bottom: 10px;
                }
                
                .question-number {
                    font-weight: bold;
                    min-width: 30px;
                    font-size: 11pt;
                }
                
                .question-text {
                    flex: 1;
                    font-size: 11pt;
                }
                
                .options {
                    margin-left: 20px;
                    margin-top: 10px;
                }
                
                .option {
                    margin-bottom: 5px;
                    font-size: 11pt;
                }
                
                .option-label {
                    font-weight: bold;
                    min-width: 20px;
                    display: inline-block;
                }
                
                .footer {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                    font-size: 10pt;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                    page-break-before: avoid;
                }
                
                hr {
                    border: none;
                    border-top: 1px solid #ccc;
                    margin: 20px 0;
                }
                
                @media print {
                    body { 
                        margin: 0; 
                        -webkit-print-color-adjust: exact;
                        color-adjust: exact;
                    }
                    .section { page-break-inside: avoid; }
                    .question { page-break-inside: avoid; }
                    .header { page-break-after: avoid; }
                    .instructions { page-break-after: avoid; }
                    .footer { page-break-before: avoid; }
                }
            </style>
        </head>
        <body>
            <div class="paper-container">
                ${paperContent.innerHTML}
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printHTML);
    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function () {
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    };
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