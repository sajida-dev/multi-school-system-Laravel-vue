<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Students" />
        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Students</h1>

            <!-- Standalone Search Input -->
            <div class="mb-6">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input v-model="filtersForm.search" type="text"
                        placeholder="Search students by name, registration number..."
                        class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 text-sm"
                        @input="onSearchInput" />
                    <button v-if="filtersForm.search" @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Filter Icon with Tooltip and Label -->
            <div class="flex lg:hidden justify-end mb-4">
                <button @click="openFilterSheet"
                    class="flex items-center gap-2 p-2 rounded-full bg-primary-100 dark:bg-primary-900 hover:bg-primary-200 dark:hover:bg-primary-800 shadow transition"
                    title="Show filters for student records">
                    <FilterIcon class="w-6 h-6 text-primary-700 dark:text-primary-200" />
                    <span class="text-primary-700 dark:text-primary-200 font-medium text-base">Filters</span>
                </button>
            </div>
            <!-- Filter UI (hidden on mobile, shown in bottom sheet) -->
            <div class=" grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-4 items-end hidden lg:grid">
                <div class="flex flex-col">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                    <select v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="s in schools" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Class</label>
                    <select v-model="filtersForm.class_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Section</label>
                    <select v-model="filtersForm.section_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}
                        </option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Gender</label>
                    <select v-model="filtersForm.gender"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Shift</label>
                    <select v-model="filtersForm.class_shift"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="Morning">Morning</option>
                        <option value="Evening">Evening</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <BaseDataTable :headers="headers" :items="items" :loading="loading" :server-options="serverOptions"
                :server-items-length="students.total" :expandable="true"
                :expand-row-keys="expandedRow ? [expandedRow] : []"
                @update:server-options="(opts: Record<string, any>) => Object.assign(serverOptions, opts)"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #item-profile_photo_path="row">
                    <img v-if="row && row.profile_photo_url" :src="row.profile_photo_url" alt="Profile Photo"
                        class="w-10 h-10 rounded-full object-cover border" />
                    <div v-else
                        class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-medium text-gray-600 dark:text-gray-300">
                        {{ row?.initials || 'NA' }}
                    </div>
                </template>
                <template #item-class="row">
                    {{ row.class || '-' }}
                </template>
                <template #item-school="row">
                    {{ row.school || '-' }}
                </template>
                <template #item-actions="row">
                    <button v-if="row && row.id !== undefined"
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="editStudent(row.id)" aria-label="Edit Student" title="Edit Student">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button v-if="row && row.id !== undefined"
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="askDeleteStudent(row.id)" aria-label="Delete Student" title="Delete Student">
                        <Icon name="trash" class="w-5 h-5" />
                    </button>
                </template>
                <template #item-expand="row">
                    <button v-if="row && row.id !== undefined" @click="toggleRowExpansion(row)"
                        class="text-gray-500 hover:text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary rounded p-1 transition">
                        <svg v-if="expandedRow === row.id" class="w-4 h-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </template>
                <template #expand="row">
                    <div v-if="row && row.id !== undefined && expandedRow === row.id"
                        class="p-6 bg-gray-50 dark:bg-neutral-800 rounded shadow-inner">
                        <div class="flex flex-col md:flex-row gap-6 mb-6">
                            <div class="flex flex-col items-center md:w-1/4">
                                <img v-if="row.profile_photo_url" :src="row.profile_photo_url" alt="Profile Photo"
                                    class="w-28 h-28 rounded-full object-cover border-2 border-purple-400 mb-2" />
                                <div class="text-xs text-gray-500 dark:text-gray-400">Profile Photo</div>
                            </div>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h3 class="font-semibold text-blue-700 dark:text-blue-300 mb-2">Student Information
                                    </h3>
                                    <div><strong>Name:</strong> {{ row.name }}</div>
                                    <div><strong>Registration #:</strong> {{ row.registration_number }}</div>
                                    <div><strong>Class:</strong> {{ row.class }}</div>
                                    <div><strong>School:</strong> {{ row.school }}</div>
                                    <div><strong>Nationality:</strong> {{ row.nationality }}</div>
                                    <div><strong>B-Form Number:</strong> {{ row.b_form_number }}</div>
                                    <div><strong>Admission Date:</strong> {{ row.admission_date }}</div>
                                    <div><strong>Date of Birth:</strong> {{ row.date_of_birth }}</div>
                                    <div><strong>Gender:</strong> {{ row.gender }}</div>
                                    <div><strong>Class Shift:</strong> {{ row.class_shift }}</div>
                                    <div><strong>Status:</strong> {{ row.status }}</div>
                                    <div><strong>Previous School:</strong> {{ row.previous_school || 'N/A' }}</div>
                                    <div><strong>Religion:</strong> {{ row.religion }}</div>
                                    <div><strong>Inclusive:</strong> {{ row.inclusive }}</div>
                                    <div v-if="row.other_inclusive_type"><strong>Other Inclusive Type:</strong> {{
                                        row.other_inclusive_type }}</div>
                                    <div><strong>Orphan:</strong> {{ row.is_orphan ? 'Yes' : 'No' }}</div>
                                    <div><strong>Bricklin:</strong> {{ row.is_bricklin ? 'Yes' : 'No' }}</div>
                                    <div><strong>QSC:</strong> {{ row.is_qsc ? 'Yes' : 'No' }}</div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-green-700 dark:text-green-300 mb-2">Family Information
                                    </h3>
                                    <div><strong>Father Name:</strong> {{ row.father_name }}</div>
                                    <div><strong>Guardian Name:</strong> {{ row.guardian_name || 'N/A' }}</div>
                                    <div><strong>Father CNIC:</strong> {{ row.father_cnic }}</div>
                                    <div><strong>Mother CNIC:</strong> {{ row.mother_cnic || 'N/A' }}</div>
                                    <div><strong>Father Profession:</strong> {{ row.father_profession }}</div>
                                    <div><strong>Mother Profession:</strong> {{ row.mother_profession }}</div>
                                    <div><strong>No. of Children:</strong> {{ row.no_of_children || 'N/A' }}</div>
                                    <div><strong>Job Type:</strong> {{ row.job_type || 'N/A' }}</div>
                                    <div><strong>Father Education:</strong> {{ row.father_education }}</div>
                                    <div><strong>Mother Education:</strong> {{ row.mother_education }}</div>
                                    <div><strong>Father Income:</strong> {{ row.father_income }}</div>
                                    <div><strong>Mother Income:</strong> {{ row.mother_income || 'N/A' }}</div>
                                    <div><strong>Household Income:</strong> {{ row.household_income }}</div>
                                    <div><strong>Permanent Address:</strong> {{ row.permanent_address }}</div>
                                    <div><strong>Phone No:</strong> {{ row.phone_no || 'N/A' }}</div>
                                    <div><strong>Mobile No:</strong> {{ row.mobile_no }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </BaseDataTable>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete Student"
            message="Are you sure you want to delete this student? This action cannot be undone."
            :confirm-text="'Delete'" :cancel-text="'Cancel'" @confirm="deleteStudent">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteStudent">Delete</Button>
                </div>
            </template>
        </AlertDialog>
        <!-- Touch-friendly Bottom Sheet for Mobile Filters -->
        <vue-bottom-sheet :overlay="true" :can-swipe="true" :overlay-click-close="true" :transition-duration="0.5"
            ref="filterSheet" class="dark:bg-neutral-900">
            <div class="sheet-content dark:bg-neutral-900">
                <h2 class="text-lg font-semibold mb-4">Student Filters</h2>

                <!-- Search Input in Bottom Sheet -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input v-model="filtersForm.search" type="text"
                            placeholder="Search students by name, registration number..."
                            class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 text-sm"
                            @input="onSearchInput" />
                        <button v-if="filtersForm.search" @click="clearSearch"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex flex-col">
                        <label for="school-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                        <select id="school-mobile" v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option v-for="s in schools" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="class-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Class</label>
                        <select id="class-mobile" v-model="filtersForm.class_id"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="section-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Section</label>
                        <select id="section-mobile" v-model="filtersForm.section_id"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="gender-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Gender</label>
                        <select id="gender-mobile" v-model="filtersForm.gender"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label for="shift-mobile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Shift</label>
                        <select id="shift-mobile" v-model="filtersForm.class_shift"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                            <option value="">All</option>
                            <option value="Morning">Morning</option>
                            <option value="Evening">Evening</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                </div>
            </div>
        </vue-bottom-sheet>
    </AppLayout>
</template>

<script setup lang="ts">
import VueBottomSheet from "@webzlodimir/vue-bottom-sheet";
import "@webzlodimir/vue-bottom-sheet/dist/style.css";
import { ref, computed, watch, onMounted } from 'vue';
import { router, Head, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import Button from '@/components/ui/button/Button.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import Icon from '@/components/Icon.vue';
import { storeToRefs } from 'pinia';
import { useSchoolStore } from '@/stores/school';
import { BreadcrumbItem } from '@/types';
import { FilterIcon } from 'lucide-vue-next';
const defaultProfileImage = '/storage/default-avatar.png';

const filterSheet = ref<InstanceType<typeof VueBottomSheet>>();

function openFilterSheet() {
    filterSheet.value?.open();
}

function closeFilterSheet() {
    filterSheet.value?.close();
}

// --- TypeScript interfaces ---
interface School {
    id: number;
    name: string;
}
interface ClassModel {
    id: number;
    name: string;
}
export interface Student {
    id: number;
    name: string;
    registration_number: string;
    b_form_number: string;
    class_id: number;
    class?: ClassModel;
    school_id: number;
    school?: School;
    gender: string;
    mobile_no: string;
    profile_photo_path?: string | null;
    profile_photo_url?: string;
    initials?: string;
    nationality: string;
    admission_date: string;
    date_of_birth: string;
    class_shift: string;
    previous_school?: string;
    inclusive: string;
    other_inclusive_type?: string;
    religion: string;
    is_bricklin: boolean;
    is_orphan: boolean;
    is_qsc: boolean;
    father_name: string;
    guardian_name?: string;
    father_cnic: string;
    mother_cnic?: string;
    father_profession: string;
    no_of_children?: number;
    job_type?: string;
    father_education: string;
    mother_education: string;
    mother_profession: string;
    father_income: string;
    mother_income?: string;
    household_income: string;
    permanent_address: string;
    phone_no?: string;
    status: string;
    fee?: any;
}
interface PaginatedStudents {
    data: Student[];
    current_page: number;
    per_page: number;
    total: number;
}
interface Filters {
    class_id: string;
    section_id: string;
    school_id: string;
    gender: string;
    class_shift: string;
    search: string;
}

type StudentsPageProps = any & {
    students: PaginatedStudents;
    classes: ClassModel[];
    schools: School[];
    filters: Partial<Filters>;
    auth: any;
};

const page = usePage<StudentsPageProps>();
const students = computed(() => page.props.students);
const initialFilters = page.props.filters || {};
const schools = computed(() => page.props.schools || []);
const auth = computed(() => page.props.auth || {});
const schoolStore = useSchoolStore();
const { selectedSchool } = storeToRefs(schoolStore);

const classes = computed(() => page.props.classes || []);
const sections = computed(() => page.props.sections || []);

const items = computed(() => {
    const data = Array.isArray(students.value?.data) ? students.value.data : [];
    return data.map((student: Student) => ({
        ...student,
        class: student.class ? student.class.name : '',
        school: student.school ? student.school.name : '',
        profile_photo_url: student.profile_photo_url || defaultProfileImage,
        fee: student.fee || null,
    }));
});

const filtersForm = useForm({
    class_id: initialFilters.class_id || '',
    section_id: initialFilters.section_id || '',
    school_id: selectedSchool.value?.id || '',
    gender: initialFilters.gender || '',
    class_shift: initialFilters.class_shift || '',
    search: initialFilters.search || '',
    page: 1,
    per_page: 12,
});

const loading = ref(false);
const serverOptions = ref({
    page: 1,
    rowsPerPage: 12,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

// Initialize serverOptions with actual data when students data is available
watch(() => students.value, (newStudents) => {
    if (newStudents && newStudents.current_page) {
        serverOptions.value.page = newStudents.current_page;
        serverOptions.value.rowsPerPage = newStudents.per_page || 12;
    }
}, { immediate: true });
const expandedRow = ref<number | null>(null);
const showDeleteDialog = ref(false);
const studentToDelete = ref<number | null>(null);

const headers = [
    { text: 'Photo', value: 'profile_photo_path', sortable: false },
    { text: 'Name', value: 'name' },
    { text: 'Reg #', value: 'registration_number' },
    { text: 'Class', value: 'class' },
    { text: 'School', value: 'school' },
    { text: 'Gender', value: 'gender' },
    { text: 'Mobile', value: 'mobile_no' },
    { text: 'Actions', value: 'actions', sortable: false },
];

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Students', href: '/students' },
];

const userSchools = computed(() => auth.value.user?.schools || []);
const isSingleSchoolUser = computed(() => userSchools.value.length === 1 && !auth.value.user?.isSuperAdmin);

onMounted(() => {
    if (isSingleSchoolUser.value) {
        filtersForm.school_id = userSchools.value[0].id;
    } else if (selectedSchool.value?.id) {
        filtersForm.school_id = selectedSchool.value.id;
    }
});

// Watch for filter changes with debounce
let debounceTimer: number;
type FilterField = 'school_id' | 'class_id' | 'section_id' | 'gender' | 'class_shift' | 'search';
const watchedFields: FilterField[] = ['school_id', 'class_id', 'section_id', 'gender', 'class_shift', 'search'];

watchedFields.forEach((field: FilterField) => {
    watch(() => filtersForm[field], () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchData();
        }, 500); // 500ms debounce
    });
});

// Watch serverOptions for pagination changes
watch(serverOptions, fetchData, { deep: true });

function fetchData() {
    loading.value = true;

    // Set pagination parameters in the form
    filtersForm.page = serverOptions.value.page;
    filtersForm.per_page = serverOptions.value.rowsPerPage;

    filtersForm.get('/students', {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => (loading.value = false),
    });
}

function toggleRowExpansion(row: Student) {
    expandedRow.value = expandedRow.value === row.id ? null : row.id;
}

function editStudent(id: number) {
    router.visit(`/students/${id}/edit`);
}

function askDeleteStudent(id: number) {
    studentToDelete.value = id;
    showDeleteDialog.value = true;
}

function deleteStudent() {
    if (!studentToDelete.value) return;
    loading.value = true;
    router.delete(`/students/${studentToDelete.value}`, {
        preserveState: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            studentToDelete.value = null;
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
}
function goToCreate() {
    router.visit('/students/create');
}

function onSearchInput(event: Event) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchData();
    }, 500); // 500ms debounce
}

function clearSearch() {
    filtersForm.search = '';
    fetchData();
}
</script>

<style scoped>
.sheet-content {
    padding: 20px;
}
</style>
