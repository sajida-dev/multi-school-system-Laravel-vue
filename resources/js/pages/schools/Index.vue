<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Schools" />
        <div class="max-w-6xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Schools</h1>
            <div class="mb-4 flex justify-end">
                <button @click="router.get(route('schools.create'))"
                    class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600 transition">
                    Add School
                </button>
            </div>
            <div v-if="items.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="mb-4">No schools found.</p>

            </div>
            <div v-else>
                <EasyDataTable :headers="headers" :items="items" :server-items-length="total"
                    :server-options="serverOptions" :loading="loading" server-mode
                    @update:server-options="(opts: any) => serverOptions = opts">
                    <template #item-actions="{ row }">
                        <button class="text-blue-500 mr-2" @click="editSchool(row.id)">Edit</button>
                        <button class="text-red-500" @click="askDeleteSchool(row.id)">Delete</button>
                    </template>
                </EasyDataTable>
            </div>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete School"
            message="Are you sure you want to delete this school? This action cannot be undone." confirmText="Delete"
            cancelText="Cancel" @confirm="deleteSchool" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import AlertDialog from '@/components/AlertDialog.vue';

const breadcrumbItems = [
    { title: 'Schools', href: '/schools' },
];

const page = usePage();
const schools = ref<any>(page.props.schools);
const headers = [
    { key: 'id', title: 'ID' },
    { key: 'name', title: 'Name' },
    { key: 'address', title: 'Address' },
    { key: 'contact', title: 'Contact' },
    { key: 'actions', title: 'Actions' }
];

const items = computed(() =>
    Array.isArray(schools.value?.data)
        ? schools.value.data.map((school: any) => ({
            id: school?.id?.toString() ?? '',
            name: school?.name ?? '',
            address: school?.address ?? '',
            contact: school?.contact ?? ''
        }))
        : []
);

console.log('DataTable items:', items.value);

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

const showDeleteDialog = ref(false);
const schoolToDelete = ref<number | null>(null);

const fetchData = () => {
    loading.value = true;
    router.get(route('schools.index'), serverOptions.value, {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            schools.value = page.props.schools;
            total.value = (page.props.schools as any).total;
            loading.value = false;
        },
    });
};

watch(serverOptions, fetchData, { deep: true });
onMounted(fetchData);

const editSchool = (id: number) => {
    router.get(route('schools.edit', id));
};

const askDeleteSchool = (id: number) => {
    schoolToDelete.value = id;
    showDeleteDialog.value = true;
};

const deleteSchool = () => {
    if (schoolToDelete.value !== null) {
        router.delete(route('schools.destroy', schoolToDelete.value), {
            onSuccess: () => {
                toast.success('School deleted successfully.');
            },
        });
    }
    showDeleteDialog.value = false;
    schoolToDelete.value = null;
};

onMounted(() => {
    const toastProp = page.props.toast as { message?: string; type?: string };
    if (toastProp && toastProp.message && toastProp.type) {
        toast(toastProp.message, { type: toastProp.type as 'success' | 'error' });
    }
});

watch(
    () => page.props.schools,
    (newVal) => {
        schools.value = newVal;
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