<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Teachers" />
        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Teachers</h1>
            <!-- Filter UI -->
            <div class="flex flex-wrap gap-1 mb-4 items-end">
                <div class="flex flex-col">
                    <label for="role"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Role</label>
                    <select id="role" v-model="filtersForm.role"
                        class="w-40 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="teacher">Teacher</option>
                        <option value="principal">Principal</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="gender"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Gender</label>
                    <select id="gender" v-model="filtersForm.gender"
                        class="w-32 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="status"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                    <select id="status" v-model="filtersForm.status"
                        class="w-32 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="search"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Search</label>
                    <input id="search" v-model="filtersForm.search" type="text" placeholder="Name, Email, CNIC, Contact"
                        class="w-56 border border-gray-300 dark:border-gray-600 rounded px-2 py-0.5 bg-white dark:bg-neutral-900" />
                </div>
                <div class="flex flex-col">
                    <label for="school"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School</label>
                    <select id="school" v-model="filtersForm.school_id" :disabled="isSingleSchoolUser"
                        class="min-w-[220px] border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-neutral-900">
                        <option value="">All Schools</option>
                        <option v-for="school in schools" :key="school.id" :value="school.id">
                            {{ school.name }}
                        </option>
                    </select>
                </div>
                <Button variant="default" class="h-10 ml-auto" @click="goToCreate">Add Teacher</Button>
            </div>
            <BaseDataTable :headers="headers" :items="teachers.data" :loading="loading" :server-options="serverOptions"
                :server-items-length="teachers.total" :expandable="true"
                :expand-row-keys="expandedRow ? [expandedRow] : []" @update:server-options="onServerOptionsUpdate"
                table-class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-md hover:shadow-lg transition-all"
                row-class="hover:bg-purple-50 dark:hover:bg-purple-900/60 transition cursor-pointer border-b border-neutral-100 dark:border-neutral-800">
                <template #item-role="row">
                    <span class="capitalize">{{ row.teacher?.role || '-' }}</span>
                </template>
                <template #item-gender="row">
                    {{ row.teacher?.gender || '-' }}
                </template>
                <template #item-cnic="row">
                    {{ row.teacher?.cnic || '-' }}
                </template>
                <template #item-contact_no="row">
                    {{ row.teacher?.contact_no || '-' }}
                </template>
                <template #item-school_id="row">
                    {{ row.teacher?.school_id || '-' }}
                </template>
                <template #item-actions="row">
                    <button
                        class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                        @click="editTeacher(row.id)" aria-label="Edit Teacher" title="Edit Teacher">
                        <Icon name="edit" class="w-5 h-5" />
                    </button>
                    <button
                        class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                        @click="askDeleteTeacher(row.id)" aria-label="Delete Teacher" title="Delete Teacher">
                        <Icon name="trash" class="w-5 h-5" />
                    </button>
                </template>
                <template #item-expand="row">
                    <button @click="toggleRowExpansion(row)"
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
                    <div v-if="expandedRow === row.id" class="p-6 bg-gray-50 dark:bg-neutral-800 rounded shadow-inner">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="font-semibold text-blue-700 dark:text-blue-300 mb-2">Teacher Information</h3>
                                <div><strong>Name:</strong> {{ row.name }}</div>
                                <div><strong>Email:</strong> {{ row.email }}</div>
                                <div><strong>CNIC:</strong> {{ row.teacher?.cnic }}</div>
                                <div><strong>Gender:</strong> {{ row.teacher?.gender }}</div>
                                <div><strong>Marital Status:</strong> {{ row.teacher?.marital_status }}</div>
                                <div><strong>Role:</strong> <span class="capitalize">{{ row.teacher?.role }}</span>
                                </div>
                                <div><strong>Date of Birth:</strong> {{ row.teacher?.dob }}</div>
                                <div><strong>Salary:</strong> {{ row.teacher?.salary }}</div>
                                <div><strong>Contact No:</strong> {{ row.teacher?.contact_no }}</div>
                                <div><strong>Date of Joining:</strong> {{ row.teacher?.date_of_joining }}</div>
                                <div><strong>Experience (years):</strong> {{ row.teacher?.experience_years || '-' }}
                                </div>
                                <div><strong>School ID:</strong> {{ row.teacher?.school_id }}</div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-green-700 dark:text-green-300 mb-2">Roles</h3>
                                <div v-for="role in row.roles" :key="role.id">{{ role.name }}</div>
                            </div>
                        </div>
                    </div>
                </template>
            </BaseDataTable>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete Teacher"
            message="Are you sure you want to delete this teacher? This action cannot be undone."
            :confirm-text="'Delete'" :cancel-text="'Cancel'" @confirm="deleteTeacher">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteTeacher">Delete</Button>
                </div>
            </template>
        </AlertDialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { router, Head, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import Button from '@/components/ui/button/Button.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import Icon from '@/components/Icon.vue';
import { BreadcrumbItem } from '@/types';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';

// Extend the default Inertia page props with our custom props
type TeachersPageProps = any & {
    teachers: any;
    filters: any;
    schools: any[];
    auth: any;
};

const page = usePage<TeachersPageProps>();
const teachers = computed(() => page.props.teachers);
const initialFilters = page.props.filters || {};
const auth = computed(() => page.props.auth || {});
const schoolStore = useSchoolStore();
const { schools, selectedSchool } = storeToRefs(schoolStore);

const filtersForm = useForm({
    role: initialFilters.role || '',
    gender: initialFilters.gender || '',
    status: initialFilters.status || '',
    search: initialFilters.search || '',
    school_id: selectedSchool.value?.id || '',
});

const loading = ref(false);
const serverOptions = ref<{ page: number; perPage: number }>({
    page: teachers.value.current_page || 1,
    perPage: teachers.value.per_page || 12,
});
const expandedRow = ref<number | null>(null);
const showDeleteDialog = ref(false);
const teacherToDelete = ref<number | null>(null);

const headers = [
    { text: 'Name', value: 'name' },
    { text: 'Email', value: 'email' },
    { text: 'CNIC', value: 'cnic' },
    { text: 'Role', value: 'role' },
    { text: 'Gender', value: 'gender' },
    { text: 'Contact', value: 'contact_no' },
    { text: 'School', value: 'school_id' },
    { text: 'Actions', value: 'actions', sortable: false },
];

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Teachers', href: '/teachers' },
];

// Determine if user is single-school (not superadmin, only one school linked)
const userSchools = computed(() => auth.value.user?.schools || []);
const isSingleSchoolUser = computed(() => userSchools.value.length === 1 && !auth.value.user?.isSuperAdmin);

// If single-school user, auto-select and lock school filter
onMounted(() => {
    if (!schools.value.length) {
        schoolStore.fetchSchools();
    }
    if (isSingleSchoolUser.value) {
        filtersForm.school_id = userSchools.value[0].id;
    } else if (selectedSchool.value?.id) {
        filtersForm.school_id = selectedSchool.value.id;
    }
});

// Watch for filter changes and auto-fetch
watch(() => filtersForm.role, fetchData);
watch(() => filtersForm.gender, fetchData);
watch(() => filtersForm.status, fetchData);
watch(() => filtersForm.search, fetchData);
watch(() => filtersForm.school_id, fetchData);

// Watch for global school change and update filter + fetch
watch(
    () => selectedSchool.value?.id,
    (newSchoolId) => {
        if (newSchoolId && (!isSingleSchoolUser.value || filtersForm.school_id !== newSchoolId)) {
            filtersForm.school_id = newSchoolId;
            fetchData();
        }
    }
);

function fetchData() {
    loading.value = true;
    // Always set school_id to selected global school if not superadmin or multi-school
    if (!auth.value.user?.isSuperAdmin && selectedSchool.value?.id) {
        filtersForm.school_id = selectedSchool.value.id;
    }
    filtersForm.get('/teachers', {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => (loading.value = false),
    });
}

function onServerOptionsUpdate(opts: { page: number; perPage: number }) {
    if (opts.page !== serverOptions.value.page || opts.perPage !== serverOptions.value.perPage) {
        serverOptions.value = opts;
        fetchData();
    }
}

function toggleRowExpansion(row: any) {
    expandedRow.value = expandedRow.value === row.id ? null : row.id;
}

function editTeacher(id: number) {
    router.visit(`/teachers/${id}/edit?school_id=${selectedSchool.value?.id || ''}`);
}

function askDeleteTeacher(id: number) {
    teacherToDelete.value = id;
    showDeleteDialog.value = true;
}

function deleteTeacher() {
    if (!teacherToDelete.value) return;
    loading.value = true;
    router.delete(`/teachers/${teacherToDelete.value}`, {
        preserveState: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            teacherToDelete.value = null;
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
}
function goToCreate() {
    router.visit(`/teachers/create?school_id=${selectedSchool.value?.id || ''}`);
}
</script>