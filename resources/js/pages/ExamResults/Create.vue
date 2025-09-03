<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
interface Student {
    id: number;
    name: string;
    registration_number: string;
}

interface ExamPaper {
    id: number;
    exam: { id: number; title: string };
    paper: { id: number; title: string };
}

interface ExamResult {
    student_id: number;
    obtained_marks: string;
    total_marks: string;
    status: 'pass' | 'fail' | 'absent';
    promotion_status: 'promoted' | 'failed' | 'pending';
    remarks: string;
}

interface ExamResultForm {
    school_id: string;
    class_id: string;
    exam_paper_id: string;
    results: ExamResult[];
    [key: string]: any;
}

interface Props {
    schools: { id: number; name: string }[];
    classes: { id: number; name: string }[];
    students: Student[];
    examPapers: ExamPaper[];
    selectedSchoolId?: number;
    selectedClassId?: number;
    selectedExamPaperId?: number;
}
const props = defineProps<Props>();

const form = useForm<ExamResultForm>({
    school_id: props.selectedSchoolId ? String(props.selectedSchoolId) : '',
    class_id: props.selectedClassId ? String(props.selectedClassId) : '',
    exam_paper_id: props.selectedExamPaperId ? String(props.selectedExamPaperId) : '',
    results: props.students.map(student => ({
        student_id: student.id,
        obtained_marks: '',
        total_marks: '100',
        status: 'pass',
        promotion_status: 'pending',
        remarks: '',
    })),
});


const errors = computed(() => form.errors as Record<string, string | undefined>);

function onSchoolChange() {
    form.class_id = '';
    form.exam_paper_id = '';
    router.get(route('exam-results.create'), {
        school_id: form.school_id,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function onClassChange() {
    form.exam_paper_id = '';
    router.get(route('exam-results.create'), {
        school_id: form.school_id,
        class_id: form.class_id,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function onExamPaperChange() {
    router.get(route('exam-results.create'), {
        school_id: form.school_id,
        class_id: form.class_id,
        exam_paper_id: form.exam_paper_id,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

function submitForm() {
    let valid = true;

    form.results.forEach(result => {
        if (!result.obtained_marks || !result.total_marks) {
            valid = false;
            toast.error('Please enter all required marks.');
        }
    });

    if (!valid) return;

    form.post(route('exam-results.store'), {
        onSuccess: () => toast.success('Results saved successfully!'),
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => {
                if (typeof message === 'string') toast.error(message);
            });
        },
    });
}

function getResultFieldError(index: number, field: keyof ExamResult): string | undefined {
    const key = `results.${index}.${field}` as keyof typeof form.errors;
    return form.errors[key] as string | undefined;
}
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full px-4 py-8">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Enter Subject Exam Results</h1>
                    <Button variant="outline" @click="$inertia.visit(route('exam-results.index'))">Back</Button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- School -->
                        <SelectInput id="school_id" v-model="form.school_id" label="School"
                            :options="props.schools.map(s => ({ label: s.name, value: String(s.id) }))"
                            placeholder="Select School" :error="errors.school_id" @change="onSchoolChange" />

                        <!-- Class -->
                        <SelectInput id="class_id" v-model="form.class_id" label="Class"
                            :options="props.classes.map(c => ({ label: c.name, value: String(c.id) }))"
                            placeholder="Select Class" :disabled="!form.school_id" :error="errors.class_id"
                            @change="onClassChange" />

                        <!-- Exam Paper -->
                        <SelectInput id="exam_paper_id" v-model="form.exam_paper_id" label="Exam Paper"
                            :options="props.examPapers.map(e => ({ label: `${e.exam.title} - ${e.paper.title}`, value: String(e.id) }))"
                            placeholder="Select Exam Paper" :disabled="!form.class_id" :error="errors.exam_paper_id"
                            @change="onExamPaperChange" />
                    </div>

                    <!-- Students Table -->
                    <div v-if="props.students.length" class="overflow-x-auto mt-6">
                        <table class="w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-neutral-800 text-left">
                                    <th class="p-2 text-sm">#</th>
                                    <th class="p-2 text-sm">Student</th>
                                    <th class="p-2 text-sm">Obtained Marks</th>
                                    <th class="p-2 text-sm">Total Marks</th>
                                    <th class="p-2 text-sm">Status</th>
                                    <th class="p-2 text-sm">Promotion</th>
                                    <th class="p-2 text-sm">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(result, index) in form.results" :key="index"
                                    class="border-b dark:border-neutral-700">
                                    <td class="p-2 text-sm">{{ index + 1 }}</td>
                                    <td class="p-2 text-sm">
                                        {{ props.students[index].name }} (Reg# {{
                                            props.students[index].registration_number }})
                                    </td>

                                    <!-- Obtained Marks -->
                                    <td class="p-2">
                                        <TextInput v-model="result.obtained_marks" type="number" min="0" class="w-24"
                                            :error="getResultFieldError(index, 'obtained_marks')" placeholder="Marks" />
                                    </td>

                                    <!-- Total Marks -->
                                    <td class="p-2">
                                        <TextInput v-model="result.total_marks" type="number" min="0" class="w-24"
                                            :error="getResultFieldError(index, 'total_marks')" placeholder="100" />
                                    </td>

                                    <!-- Status -->
                                    <td class="p-2">
                                        <SelectInput v-model="result.status" :options="[
                                            { label: 'Pass', value: 'pass' },
                                            { label: 'Fail', value: 'fail' },
                                            { label: 'Absent', value: 'absent' }
                                        ]" placeholder="Select Status" :error="getResultFieldError(index, 'status')" />
                                    </td>

                                    <!-- Promotion Status -->
                                    <td class="p-2">
                                        <SelectInput v-model="result.promotion_status" :options="[
                                            { label: 'Pending', value: 'pending' },
                                            { label: 'Promoted', value: 'promoted' },
                                            { label: 'Failed', value: 'failed' }
                                        ]" placeholder="Promotion"
                                            :error="getResultFieldError(index, 'promotion_status')" />
                                    </td>

                                    <!-- Remarks -->
                                    <td class="p-2">
                                        <TextInput v-model="result.remarks" type="text" class="w-full"
                                            :error="getResultFieldError(index, 'remarks')" placeholder="Remarks" />
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <div v-else
                        class="text-center py-4 px-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                        <p class="text-yellow-700 dark:text-yellow-200 font-medium">⚠️ No Students Found</p>
                        <p class="text-yellow-600 dark:text-yellow-300 text-sm">Please select a class with admitted
                            students.</p>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('exam-results.index'))">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing || !props.students.length">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else-if="!props.students.length">No Students Available</span>
                            <span v-else>Save Results</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
