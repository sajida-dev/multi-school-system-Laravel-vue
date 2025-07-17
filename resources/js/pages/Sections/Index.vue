<template>

    <Head title="Sections" />
    <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Sections</h1>
            <Button @click="openCreateModal">Add Section</Button>
        </div>
        <BaseDataTable :headers="headers" :items="sections" :loading="loading"
            class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700">
            <template #item-actions="row">
                <button
                    class="inline-flex items-center justify-center rounded-full p-2 text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 mr-1"
                    @click="openEditModal(row)" aria-label="Edit Section" title="Edit Section">
                    <Icon name="edit" class="w-5 h-5" />
                </button>
                <button
                    class="inline-flex items-center justify-center rounded-full p-2 text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400"
                    @click="handleDelete(row)" aria-label="Delete Section" title="Delete Section">
                    <Icon name="trash" class="w-5 h-5" />
                </button>
            </template>
        </BaseDataTable>
    </div>
    <!-- Create/Edit Modal -->
    <Dialog v-model:open="modalOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ isEdit ? 'Edit Section' : 'Add Section' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="handleSubmit">
                <div class="mb-4">
                    <Label for="name">Section Name</Label>
                    <Input id="name" v-model="form.name" type="text" required placeholder="Section name" />
                    <InputError :message="form.errors.name" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                    <Button :disabled="loading">{{ isEdit ? 'Update' : 'Create' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete Section?</DialogTitle>
            </DialogHeader>
            <div class="mb-4">Are you sure you want to delete this section? This action cannot be undone.</div>
            <DialogFooter>
                <Button variant="outline" @click="cancelDelete">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, defineProps } from 'vue';
import { Head } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';

const props = defineProps<{ sections: Array<{ id: number; name: string }> }>();
const sections = ref(props.sections ? [...props.sections] : []);
watch(() => props.sections, (val) => {
    sections.value = val ? [...val] : [];
});
const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingSection = ref<{ id: number; name: string } | null>(null);
const showDeleteDialog = ref(false);
const sectionToDelete = ref<{ id: number; name: string } | null>(null);
const form = ref({ name: '', errors: { name: '' } });

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

const breadcrumbItems = [
    { title: 'Sections', href: '/sections' },
];

const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Section Name', value: 'name' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

function openCreateModal() {
    isEdit.value = false;
    form.value.name = '';
    form.value.errors.name = '';
    modalOpen.value = true;
}

function openEditModal(section: { id: number; name: string }) {
    isEdit.value = true;
    editingSection.value = section;
    form.value.name = section.name;
    form.value.errors.name = '';
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
    form.value.name = '';
    form.value.errors.name = '';
    editingSection.value = null;
}

async function handleSubmit() {
    loading.value = true;
    form.value.errors.name = '';
    try {
        if (isEdit.value && editingSection.value) {
            const response = await fetch(`/sections/${editingSection.value.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ name: form.value.name }),
            });
            const data = await response.json();
            const idx = sections.value.findIndex(s => s.id === editingSection.value!.id);
            if (data && data.section && idx !== -1) {
                sections.value[idx] = data.section;
                toast.success('Section updated!');
                closeModal();
            } else if (data.errors) {
                form.value.errors.name = data.errors.name?.[0] || 'Update failed.';
            }
        } else {
            const response = await fetch('/sections', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ name: form.value.name }),
            });
            const data = await response.json();
            if (data && data.section) {
                sections.value.push(data.section);
                toast.success('Section created!');
                closeModal();
            } else if (data.errors) {
                form.value.errors.name = data.errors.name?.[0] || 'Creation failed.';
            }
        }
    } catch (e) {
        toast.error('An error occurred.');
    } finally {
        loading.value = false;
    }
}

function handleDelete(section: { id: number; name: string }) {
    sectionToDelete.value = section;
    showDeleteDialog.value = true;
}

async function confirmDelete() {
    if (!sectionToDelete.value) return;
    loading.value = true;
    try {
        const response = await fetch(`/sections/${sectionToDelete.value.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });
        const data = await response.json();
        if (data && data.success) {
            sections.value = sections.value.filter(s => s.id !== sectionToDelete.value!.id);
            toast.success('Section deleted!');
            showDeleteDialog.value = false;
            sectionToDelete.value = null;
        } else {
            toast.error('Failed to delete section.');
        }
    } catch (e) {
        toast.error('An error occurred.');
    } finally {
        loading.value = false;
    }
}

function cancelDelete() {
    showDeleteDialog.value = false;
    sectionToDelete.value = null;
}
</script>