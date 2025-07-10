<template>
    <AppLayout :breadcrumbs="[{ title: 'Classes', href: '/classes' }]">

        <Head title="Classes" />
        <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6 text-neutral-900 dark:text-neutral-100">Classes</h1>
            <div class="mb-4 flex justify-end">
                <Button variant="default" size="lg" @click="goToCreate">Add Class</Button>
            </div>
            <BaseDataTable :headers="headers" :items="items" :loading="loading">
                <template #item-actions="{ row }">
                    <Button variant="secondary" size="sm" class="mr-2" @click="goToEdit(row.id)">Edit</Button>
                    <Button variant="destructive" size="sm" @click="askDelete(row.id)">Delete</Button>
                </template>
            </BaseDataTable>
        </div>
        <AlertDialog v-model="showDeleteDialog" title="Delete Class"
            message="Are you sure you want to delete this class? This action cannot be undone." :confirm-text="'Delete'"
            :cancel-text="'Cancel'" @confirm="deleteClass">
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteClass">Delete</Button>
                </div>
            </template>
        </AlertDialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import AlertDialog from '@/components/AlertDialog.vue';

const props = defineProps<{ classes: any[] }>();
const loading = ref(false);
const showDeleteDialog = ref(false);
const classToDelete = ref<number | null>(null);

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

const items = computed(() => props.classes.map(c => ({ id: c.id, name: c.name })));

function goToCreate() {
    router.get(route('classes.create'));
}
function goToEdit(id: number) {
    router.get(route('classes.edit', id));
}
function askDelete(id: number) {
    classToDelete.value = id;
    showDeleteDialog.value = true;
}
function deleteClass() {
    if (!classToDelete.value) return;
    loading.value = true;
    router.delete(route('classes.destroy', classToDelete.value), {
        onSuccess: () => {
            toast.success('Class deleted successfully.');
            showDeleteDialog.value = false;
            classToDelete.value = null;
            loading.value = false;
        },
        onError: () => {
            toast.error('Failed to delete class!');
            loading.value = false;
        },
    });
}
</script>