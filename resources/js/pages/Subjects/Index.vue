<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Subjects Management" />

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <!-- Header with responsive design -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Subjects Management
                </h1>

            </div>

            <!-- Responsive Tabs -->
            <div class="mb-6">
                <!-- Desktop Tabs -->
                <div class="hidden md:flex gap-4 border-b border-gray-200 dark:border-neutral-800">
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'subjects' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'subjects'">Subjects</button>
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'assign-classes' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'assign-classes'">Assign to Classes</button>
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'assign-teachers' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'assign-teachers'">Assign to Teachers</button>
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'assignments' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'assignments'">View Assignments</button>
                </div>

                <!-- Mobile Tabs (Dropdown) -->
                <div class="md:hidden">
                    <select v-model="activeTab"
                        class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100">
                        <option value="subjects">📚 Subjects</option>
                        <option value="assign-classes">🏫 Assign to Classes</option>
                        <option value="assign-teachers">👨‍🏫 Assign to Teachers</option>
                        <option value="assignments">📋 View Assignments</option>
                    </select>
                </div>
            </div>

            <!-- Subjects Tab -->
            <div v-if="activeTab === 'subjects'">
                <div class="flex justify-end items-center mb-4">
                    <Button @click="openCreateModal" class="w-full sm:w-auto">Add Subject</Button>
                </div>

                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 overflow-hidden">

                    <BaseDataTable :headers="subjectHeaders" :items="subjects" :loading="loading">
                        <template #item-actions="row">
                            <div class="flex gap-1">
                                <button
                                    class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    @click="openEditModal(row)" aria-label="Edit Subject" title="Edit Subject">
                                    <Icon name="edit" class="w-4 h-4" />
                                </button>
                                <button
                                    class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                                    @click="handleDelete(row)" aria-label="Delete Subject" title="Delete Subject">
                                    <Icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>
            </div>

            <!-- Assign to Classes Tab -->
            <div v-if="activeTab === 'assign-classes'">
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold mb-4">Assign Subjects to Classes</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                        <div>
                            <Label for="classSelect" class="block mb-2">Select Class</Label>
                            <select id="classSelect" v-model="selectedClass"
                                class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100">
                                <option value="">Choose a class...</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>
                        <div>
                            <Label for="subjectsSelect" class="block mb-2">Select Subjects</Label>
                            <select id="subjectsSelect" v-model="selectedSubjects" multiple
                                class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 min-h-[100px]">
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }} {{ subject.code ? `(${subject.code})` : '' }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple subjects</p>
                        </div>
                    </div>
                    <Button @click="assignToClass" :disabled="!selectedClass || selectedSubjects.length === 0"
                        class="w-full sm:w-auto">
                        Assign Subjects to Class
                    </Button>
                </div>
            </div>

            <!-- Assign to Teachers Tab -->
            <div v-if="activeTab === 'assign-teachers'">
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold mb-4">Assign Subjects to Teachers</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <div>
                            <Label for="teacherClassSelect" class="block mb-2">Select Class</Label>
                            <select id="teacherClassSelect" v-model="teacherAssignment.class_id"
                                class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100">
                                <option value="">Choose a class...</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>
                        <div>
                            <Label for="teacherSubjectSelect" class="block mb-2">Select Subject</Label>
                            <select id="teacherSubjectSelect" v-model="teacherAssignment.subject_id"
                                class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100">
                                <option value="">Choose a subject...</option>
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                    {{ subject.name }} {{ subject.code ? `(${subject.code})` : '' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <Label for="teacherSelect" class="block mb-2">Select Teacher</Label>
                            <select id="teacherSelect" v-model="teacherAssignment.teacher_id"
                                class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100">
                                <option value="">Choose a teacher...</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{
                                    teacher.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <Button @click="assignToTeacher"
                        :disabled="!teacherAssignment.class_id || !teacherAssignment.subject_id || !teacherAssignment.teacher_id"
                        class="w-full sm:w-auto">
                        Assign Subject to Teacher
                    </Button>
                </div>
            </div>

            <!-- View Assignments Tab -->
            <div v-if="activeTab === 'assignments'">
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold mb-4">Current Assignments</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        View and manage all current subject assignments to classes and teachers.
                    </p>
                    <div class="mb-4">
                        <Button @click="loadAssignments" class="w-full sm:w-auto">Refresh Assignments</Button>
                    </div>
                    <div v-if="assignments.length === 0" class="text-center py-8 text-gray-500">
                        <div class="text-4xl mb-2">📋</div>
                        <p>No assignments found</p>
                        <p class="text-sm">Create assignments using the other tabs above</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="assignment in assignments"
                            :key="`${assignment.class_id}-${assignment.subject_id}-${assignment.teacher_id}`"
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 border rounded-lg gap-2">
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="font-semibold text-blue-600">{{ assignment.class_name }}</span>
                                    <span class="hidden sm:inline">-</span>
                                    <span class="text-green-600">{{ assignment.subject_name }}</span>
                                    <span v-if="assignment.subject_code" class="text-gray-500 text-sm">({{
                                        assignment.subject_code
                                    }})</span>
                                    <span class="hidden sm:inline">-</span>
                                    <span class="text-purple-600">{{ assignment.teacher_name }}</span>
                                </div>
                            </div>
                            <Button variant="destructive" size="sm" @click="removeAssignment(assignment)"
                                class="w-full sm:w-auto">
                                Remove
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Subject Modal -->
        <Dialog v-model:open="modalOpen">
            <DialogContent class="sm:max-w-md max-w-[95vw] mx-4">
                <DialogHeader>
                    <DialogTitle>{{ isEdit ? 'Edit Subject' : 'Add Subject' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <Label for="name" class="block mb-2">Subject Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" type="text" required placeholder="e.g., Mathematics"
                            class="w-full p-2 text-sm" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <Label for="code" class="block mb-2">Subject Code</Label>
                        <Input id="code" v-model="form.code" type="text" placeholder="e.g., MATH101"
                            class="w-full p-2 text-sm" />
                        <InputError :message="form.errors.code" />
                    </div>
                    <div>
                        <Label for="description" class="block mb-2">Description</Label>
                        <textarea id="description" v-model="form.description"
                            class="w-full p-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 resize-none"
                            rows="3" placeholder="Subject description (optional)"></textarea>
                        <InputError :message="form.errors.description" />
                    </div>
                    <DialogFooter class="flex flex-col sm:flex-row gap-2">
                        <Button type="button" variant="outline" @click="closeModal"
                            class="w-full sm:w-auto">Cancel</Button>
                        <Button :disabled="loading" class="w-full sm:w-auto">{{ isEdit ? 'Update' : 'Create' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md max-w-[95vw] mx-4">
                <DialogHeader>
                    <DialogTitle>Delete Subject?</DialogTitle>
                </DialogHeader>
                <div class="mb-4">
                    <p>Are you sure you want to delete this subject?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">This action cannot be undone.</p>
                </div>
                <DialogFooter class="flex flex-col sm:flex-row gap-2">
                    <Button variant="outline" @click="cancelDelete" class="w-full sm:w-auto">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete" class="w-full sm:w-auto">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, defineProps } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';

const page = usePage()

const props = defineProps<{
    subjects: Array<{ id: number; name: string; code?: string; description?: string }>;
    classes: Array<{ id: number; name: string }>;
    teachers: Array<{ id: number; name: string }>;
    assignments: Array<any>;
}>();

const subjects = ref(props.subjects ? [...props.subjects] : []);
const classes = ref(props.classes ? [...props.classes] : []);
const teachers = ref(props.teachers ? [...props.teachers] : []);
const assignments = ref(props.assignments ? [...props.assignments] : []);

watch(() => props.subjects, (val) => {
    subjects.value = val ? [...val] : [];
});
watch(() => props.classes, (val) => {
    classes.value = val ? [...val] : [];
});
watch(() => props.teachers, (val) => {
    teachers.value = val ? [...val] : [];
});
watch(() => props.assignments, (val) => {
    assignments.value = val ? [...val] : [];
});

const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingSubject = ref<{ id: number; name: string; code?: string; description?: string } | null>(null);
const showDeleteDialog = ref(false);
const subjectToDelete = ref<{ id: number; name: string } | null>(null);
const activeTab = ref('subjects');

// Assignment states
const selectedClass = ref('');
const selectedSubjects = ref<number[]>([]);
const teacherAssignment = ref({
    class_id: '',
    subject_id: '',
    teacher_id: ''
});

const form = ref({
    name: '',
    code: '',
    description: '',
    errors: { name: '', code: '', description: '' } as { name: string; code: string; description: string }
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Subjects', href: '/subjects' }
];

const subjectHeaders = [
    { text: 'ID', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Code', value: 'code' },
    { text: 'Description', value: 'description' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

function openCreateModal() {
    isEdit.value = false;
    form.value = { name: '', code: '', description: '', errors: { name: '', code: '', description: '' } };
    modalOpen.value = true;
}

function openEditModal(subject: { id: number; name: string; code?: string; description?: string }) {
    isEdit.value = true;
    editingSubject.value = subject;
    form.value = {
        name: subject.name,
        code: subject.code || '',
        description: subject.description || '',
        errors: { name: '', code: '', description: '' }
    };
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
    form.value = { name: '', code: '', description: '', errors: { name: '', code: '', description: '' } };
    editingSubject.value = null;
}

async function handleSubmit() {
    loading.value = true;
    form.value.errors = { name: '', code: '', description: '' };

    try {
        if (isEdit.value && editingSubject.value) {
            router.put(`/subjects/${editingSubject.value.id}`, {
                name: form.value.name,
                code: form.value.code,
                description: form.value.description
            }, {
                onSuccess: () => {
                    closeModal();
                    router.reload({ only: ['subjects'] });
                    toast.success('Subject updated successfully!');
                },
                onError: (errors) => {
                    form.value.errors = {
                        name: errors.name || '',
                        code: errors.code || '',
                        description: errors.description || ''
                    };
                    console.error('Update errors:', errors);
                    toast.error(errors.message)
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        } else {
            router.post('/subjects', {
                name: form.value.name,
                code: form.value.code,
                description: form.value.description
            }, {
                onSuccess: () => {
                    toast.success('Subject created successfully!');
                    closeModal();
                    router.reload({ only: ['subjects'] });
                },
                onError: (errors) => {
                    form.value.errors = {
                        name: errors.name || '',
                        code: errors.code || '',
                        description: errors.description || ''
                    };
                    toast.error(errors.message)
                    console.error('Creation errors:', errors);
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        }
    } catch (e) {
        console.error('Error in handleSubmit:', e);
        toast.error('An error occurred: ' + (e as Error).message);
        loading.value = false;
    }
}

function handleDelete(subject: { id: number; name: string }) {
    subjectToDelete.value = subject;
    showDeleteDialog.value = true;
}

async function confirmDelete() {
    if (!subjectToDelete.value) return;
    loading.value = true;

    router.delete(`/subjects/${subjectToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Subject deleted!');
            showDeleteDialog.value = false;
            subjectToDelete.value = null;
            router.reload({ only: ['subjects'] });
        },
        onError: () => {
            toast.error('Failed to delete subject.');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
    subjectToDelete.value = null;
}

async function assignToClass() {
    if (!selectedClass.value || selectedSubjects.value.length === 0) {
        toast.error('Please select a class and at least one subject.');
        return;
    }

    loading.value = true;

    router.post(`/subjects/${selectedClass.value}/assign-to-class`, {
        subject_ids: selectedSubjects.value
    }, {
        onSuccess: () => {
            toast.success('Subjects assigned to class successfully!');
            selectedClass.value = '';
            selectedSubjects.value = [];
            router.reload({ only: ['assignments'] });
        },
        onError: (errors) => {
            toast.error('Failed to assign subjects: ' + (errors.message || 'Unknown error'));
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

async function assignToTeacher() {
    if (!teacherAssignment.value.class_id || !teacherAssignment.value.subject_id || !teacherAssignment.value.teacher_id) {
        toast.error('Please select class, subject, and teacher.');
        return;
    }

    loading.value = true;

    router.post('/subjects/assign-to-teacher', teacherAssignment.value, {
        onSuccess: () => {
            toast.success('Subject assigned to teacher successfully!');
            teacherAssignment.value = { class_id: '', subject_id: '', teacher_id: '' };
            router.reload({ only: ['assignments'] });
        },
        onError: (errors) => {
            toast.error('Failed to assign subject to teacher: ' + (errors.message || 'Unknown error'));
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

async function loadAssignments() {
    // Refresh the page to get updated assignments data
    router.reload({ only: ['assignments'] });
}

async function removeAssignment(assignment: any) {
    if (!confirm('Are you sure you want to remove this assignment?')) return;

    loading.value = true;

    router.delete('/subjects/remove-assignment', {
        data: {
            class_id: assignment.class_id,
            subject_id: assignment.subject_id,
            teacher_id: assignment.teacher_id
        },
        onSuccess: () => {
            toast.success('Assignment removed successfully!');
            router.reload({ only: ['assignments'] });
        },
        onError: () => {
            toast.error('Failed to remove assignment.');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

// Load assignments when assignments tab is opened
watch(activeTab, (newTab) => {
    if (newTab === 'assignments') {
        loadAssignments();
    }
});

</script>