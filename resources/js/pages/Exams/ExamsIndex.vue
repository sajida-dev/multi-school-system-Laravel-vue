<template>

    <ManageLayout>

        <Head title="Exams" />
        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Exams</h1>
                <Button v-can="'create-exams'" variant="default" size="lg" @click="openCreateModal">
                    Add Exam
                </Button>
            </div>

            <BaseDataTable :headers="headers" :items="exams" :loading="loading"
                class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700">
                <template #item-exam_type_name="row">
                    {{ row.exam_type?.name }}
                </template>
                <template #item-class_name="row">
                    {{ row.class?.name ?? '-' }}
                </template>
                <template #item-section_name="row">
                    {{ row.section?.name ?? '-' }}
                </template>
                <template #item-dates="row">
                    {{ row.start_date }} – {{ row.end_date }}
                </template>
                <template #item-actions="row">
                    <button v-can="'update-exams'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="openEditModal(row)" aria-label="Edit Exam" title="Edit">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button v-can="'delete-exams'"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="handleDelete(row)" aria-label="Delete Exam" title="Delete">
                        <Icon name="trash" class="w-5 h-5" />
                    </button>
                </template>
            </BaseDataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="modalOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isEdit ? 'Edit Exam' : 'Add Exam' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Title -->
                        <TextInput id="title" v-model="form.title" label="Title" placeholder="Exam Title" required
                            :error="form.errors.title" />

                        <!-- Exam Type -->
                        <SelectInput id="exam_type_id" v-model="form.exam_type_id" label="Exam Type" required
                            :options="examTypes.map((et) => ({ label: et.name, value: et.id }))"
                            placeholder="Select Type" :error="form.errors.exam_type_id" />

                        <!-- Class -->
                        <SelectInput id="class_id" v-model="form.class_id" label="Class"
                            :options="classes.map((cls) => ({ label: cls.name, value: cls.id }))" required
                            placeholder="Select Class" :error="form.errors.class_id" />

                        <!-- Section -->
                        <SelectInput id="section_id" v-model="form.section_id" label="Section"
                            :options="[...sections.map((sec) => ({ label: sec.name, value: sec.id }))]" required
                            placeholder="Select Section" :error="form.errors.section_id" />

                        <!-- Academic Year -->
                        <TextInput id="academic_year" v-model="form.academic_year" label="Academic Year" required
                            placeholder="2024-2025" :error="form.errors.academic_year" />

                        <!-- Start Date -->
                        <TextInput id="start_date" v-model="form.start_date" label="Start Date" type="date" required
                            :error="form.errors.start_date" />

                        <!-- End Date -->
                        <TextInput id="end_date" v-model="form.end_date" label="End Date" type="date" required
                            :error="form.errors.end_date" />

                        <!-- Instructions -->
                        <div class="md:col-span-2">
                            <label for="instructions"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                Instructions
                            </label>
                            <textarea id="instructions" v-model="form.instructions" rows="3"
                                placeholder="Optional instructions" required
                                class="w-full px-3 py-2 rounded-md border bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"></textarea>
                            <p v-if="form.errors.instructions" class="text-red-500 text-sm mt-1">
                                {{ form.errors.instructions }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                        <Button type="submit" :disabled="loading">
                            {{ isEdit ? 'Update Exam' : 'Create Exam' }}
                        </Button>
                    </div>
                </form>

            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Exam?</DialogTitle>
                </DialogHeader>
                <div class="mb-4">Are you sure you want to delete this exam? This action can’t be undone.</div>
                <DialogFooter>
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ManageLayout>
</template>

<script setup lang="ts">
import { ref, watch, defineProps } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import ManageLayout from './ManageLayout.vue';
import axios from 'axios';
import { ExamType } from '@/types';

interface SelectOption { id: number; name: string; }
interface Class {
    id: number;
    name: string;
}
interface Section {
    id: number;
    name: string;
}
interface Exam {
    id: number;
    title: string;
    academic_year: string;
    start_date: string;
    end_date: string;
    status: string;
    instructions?: string;
    exam_type: ExamType;
    class: Class;
    section?: Section;
}

const props = defineProps<{
    exams: Exam[];
    examTypes: SelectOption[];
    classes: SelectOption[];
}>();
const exams = ref<Exam[]>([...props.exams]);
const examTypes = ref<SelectOption[]>([...props.examTypes]);
const classes = ref<SelectOption[]>([...props.classes]);
const sections = ref<SelectOption[]>([]);
const sectionCache = new Map();

watch(() => props.exams, (val) => (exams.value = [...val]));
watch(() => props.examTypes, (val) => (examTypes.value = [...val]));
watch(() => props.classes, (val) => (classes.value = [...val]));

async function fetchSections(classId: string | number) {
    if (sectionCache.has(classId)) {
        sections.value = sectionCache.get(classId);
        return;
    }

    try {
        if (classId) {
            const res = await axios.get(`/api/classes/${classId}/sections`);
            sectionCache.set(classId, res.data);
            sections.value = res.data;
        } else {
            console.error('classId is missing');
        }

    } catch (error) {
        console.error('Failed to load sections:', error);
        sections.value = [];
    }
}

const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingItem = ref<Exam | null>(null);
const showDeleteDialog = ref(false);
const itemToDelete = ref<Exam | null>(null);

// useForm from Inertia for reactive form with errors and submission helpers
const form = useForm({
    id: null as number | null,
    title: '',
    exam_type_id: '' as string | number | '',
    class_id: '' as string | number | '',
    section_id: '' as string | number | '',
    academic_year: '',
    start_date: '',
    end_date: '',
    instructions: '',
});

watch(() => form.class_id, (newClassId) => {
    form.section_id = '';
    fetchSections(newClassId);
}, { immediate: true });

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Title', value: 'title' },
    { text: 'Type', value: 'exam_type_name', sortable: false, slotName: 'item-exam_type_name' },
    { text: 'Class', value: 'class_name', sortable: false, slotName: 'item-class_name' },
    { text: 'Section', value: 'section_name', sortable: false, slotName: 'item-section_name' },
    { text: 'Dates', value: 'dates', sortable: false, slotName: 'item-dates' },
    { text: 'Status', value: 'status' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

function openCreateModal() {
    isEdit.value = false;
    editingItem.value = null;
    form.reset();  // resets all fields and errors
    modalOpen.value = true;
}

function openEditModal(row: { value: Exam }) {
    isEdit.value = true;
    editingItem.value = row.value;
    form.reset(); // clear errors and reset to defaults
    Object.assign(form.data, {
        id: row.value.id,
        title: row.value.title,
        exam_type_id: row.value.exam_type.id ?? '',
        class_id: row.value.class.id ?? '',
        section_id: row.value.section?.id ?? '',
        academic_year: row.value.academic_year,
        start_date: row.value.start_date,
        end_date: row.value.end_date,
        instructions: row.value.instructions ?? '',
    });
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
}

function handleSubmit() {
    loading.value = true;

    // Normalize empty string to null for backend
    const payload = {
        title: form.title,
        exam_type_id: form.exam_type_id === '' ? null : form.exam_type_id,
        class_id: form.class_id === '' ? null : form.class_id,
        section_id: form.section_id === '' ? null : form.section_id,
        academic_year: form.academic_year,
        start_date: form.start_date,
        end_date: form.end_date,
        instructions: form.instructions,
    };

    if (isEdit.value && form.id !== null) {
        form.put(`/exams/${form.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Exam updated!');
                closeModal();
                router.reload({ only: ['exams'] });
            },
            onError: () => {
                // errors are automatically populated on form.errors
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } else {
        form.post(route('exams.store'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Exam created!');
                closeModal();
                router.reload({ only: ['exams'] });
            },
            onError: () => {
                // errors are automatically populated on form.errors
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    }
}

function handleDelete(row: { value: Exam }) {
    itemToDelete.value = row.value;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!itemToDelete.value) return;
    loading.value = true;
    router.delete(`/exams/${itemToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Exam deleted!');
            showDeleteDialog.value = false;
            router.reload({ only: ['exams'] });
        },
        onError: () => {
            toast.error('Failed to delete exam.');
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
}
</script>
