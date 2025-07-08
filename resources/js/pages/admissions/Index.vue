<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Student Admissions" />
        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Student Admissions</h1>
            <EasyDataTable :columns="columns" :rows="rows" :server-items-length="total" :server-options="serverOptions"
                :loading="loading" server-mode @update:server-options="(opts: any) => serverOptions = opts" />
        </div>

    </AppLayout>

</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';

import { usePage, router, Head } from '@inertiajs/vue3';
import { useAdmissionsStore } from '@/stores/admissions';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import AppLayout from '@/layouts/AppLayout.vue';
import StudentTableRow from '@/components/admissions/StudentTableRow.vue';


declare global {
    interface Window {
        Echo: any;
    }
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Student Admissions',
        href: '/admissions',
    },
];

const page = usePage();
const admissionsStore = useAdmissionsStore();
const students = ref<any>(page.props.students);
const search = ref('');
const defaultProfileImage = '/storage/default-profile.png';

const columns = [
    { field: 'name', label: 'Name' },
    { field: 'registration_number', label: 'Reg #' },
    { field: 'class', label: 'Class' },
    { field: 'status', label: 'Status' },
    // Add more columns as needed
];

const rows = ref([]);
const total = ref(0);
const loading = ref(false);
const serverOptions = ref({
    page: 1,
    rowsPerPage: 10,
    sortBy: '',
    sortType: '',
    search: '',
    filters: {},
});

const fetchData = () => {
    loading.value = true;
    router.get(route('admissions.index'), serverOptions.value, {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            rows.value = (page.props.students as any).data;
            total.value = (page.props.students as any).total;
            loading.value = false;
        },
    });
};

watch(serverOptions, fetchData, { deep: true });
onMounted(fetchData);

const onSearch = () => {
    router.get(route('admissions.index'), { search: search.value }, { preserveState: true, replace: true });
};

const goToPage = (pageNum: number) => {
    router.get(route('admissions.index'), { page: pageNum, search: search.value }, { preserveState: true, replace: true });
};

const editStudent = (id: number) => {
    router.get(route('admissions.edit', id));
};

const deleteStudent = (id: number) => {
    if (confirm('Are you sure you want to delete this student?')) {
        router.delete(route('admissions.destroy', id), {
            onSuccess: () => {
                toast.success('Student deleted successfully.');
            },
        });
    }
};

const getStudentMoreFields = (student: any) => [
    { label: 'DOB', value: student.date_of_birth },
    { label: 'Gender', value: student.gender },
    { label: 'Class', value: student.class },
    { label: 'Shift', value: student.class_shift },
    { label: 'Father', value: student.father_name },
    { label: 'Guardian', value: student.guardian_name },
    { label: 'Permanent Address', value: student.permanent_address },
];

const handleEdit = (id: number) => editStudent(id);
const handleDelete = (id: number) => deleteStudent(id);
const handleView = (id: number) => {
    // TODO: navigate to details page
    editStudent(id); // placeholder, replace with router.get to details page
};

// Real-time updates
onMounted(() => {
    if (window.Echo) {
        window.Echo.channel('students')
            .listen('student.created', (e: any) => {
                admissionsStore.addStudent(e.student);
            })
            .listen('student.updated', (e: any) => {
                admissionsStore.updateStudent(e.student);
            })
            .listen('student.deleted', (e: any) => {
                admissionsStore.removeStudent(e.id);
            });
    }
    // Show toast from Inertia props if present
    const toastProp = page.props.toast as { message?: string; type?: string };
    if (toastProp && toastProp.message && toastProp.type) {
        toast(toastProp.message, { type: toastProp.type as 'success' | 'error' });
    }
});

watch(
    () => page.props.students,
    (newVal) => {
        students.value = newVal;
    }
);

watch(
    () => page.props.toast,
    (newVal) => {
        const toastVal = newVal as { message?: string; type?: string };
        if (toastVal && toastVal.message && toastVal.type) {
            toast(toastVal.message, { type: toastVal.type as 'success' | 'error' });
        }
    }
);
</script>

<style scoped>
/* Dynamic theme color support: set --color-primary and --color-primary-dark in your global CSS or via a theme switcher. */
.bg-primary {
    background: var(--color-primary, #7c3aed);
}

.bg-primary-dark {
    background: var(--color-primary-dark, #6d28d9);
}

.text-primary {
    color: var(--color-primary, #7c3aed);
}

.text-primary-dark {
    color: var(--color-primary-dark, #6d28d9);
}

.btn {
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    background: var(--color-primary, #7c3aed);
    color: #fff;
    transition: background 0.2s;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: var(--color-primary-dark, #6d28d9);
}

.btn-danger {
    background: #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-primary {
    background: var(--color-primary, #7c3aed);
}

.btn-primary:hover {
    background: var(--color-primary-dark, #6d28d9);
}
</style>