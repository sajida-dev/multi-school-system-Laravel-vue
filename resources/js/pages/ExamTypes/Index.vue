<template>
    <AppLayout :title="'Results Management'">

        <Head title="Results Management" />


        <div class="p-6 max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Exam Types</h1>

            <button @click="openModalForCreate" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                + Add New Exam Type
            </button>

            <table class="min-w-full border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="border border-gray-300 p-2">Name</th>
                        <th class="border border-gray-300 p-2">Code</th>
                        <th class="border border-gray-300 p-2">Final Term?</th>
                        <th class="border border-gray-300 p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="examType in examTypes" :key="examType.id" class="hover:bg-gray-100">
                        <td class="border border-gray-300 p-2">{{ examType.name }}</td>
                        <td class="border border-gray-300 p-2">{{ examType.code }}</td>
                        <td class="border border-gray-300 p-2">{{ examType.is_final_term ? 'Yes' : 'No' }}</td>
                        <td class="border border-gray-300 p-2 space-x-2">
                            <button @click="openModalForEdit(examType)"
                                class="px-2 py-1 bg-yellow-400 text-black rounded hover:bg-yellow-500">
                                Edit
                            </button>
                            <button @click="destroyExamType(examType.id)"
                                class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="examTypes.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">No exam types found.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Modal -->
            <transition name="fade">
                <div v-if="showModal"
                    class="fixed inset-0 bg-neutral-500 bg-opacity-50 flex items-center justify-center z-50"
                    @click.self="closeModal">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                        <h2 class="text-xl font-semibold mb-4">
                            {{ form.id ? 'Edit Exam Type' : 'Add Exam Type' }}
                        </h2>

                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block mb-1 font-medium">Name</label>
                                <input v-model="form.name" id="name" type="text" class="w-full border rounded px-3 py-2"
                                    :class="{ 'border-red-500': errors.name }" />
                                <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
                            </div>

                            <div class="mb-4">
                                <label for="code" class="block mb-1 font-medium">Code</label>
                                <input v-model="form.code" id="code" type="text" class="w-full border rounded px-3 py-2"
                                    :class="{ 'border-red-500': errors.code }" />
                                <p v-if="errors.code" class="text-red-600 text-sm mt-1">{{ errors.code }}</p>
                            </div>

                            <div class="mb-4 flex items-center">
                                <input type="checkbox" id="is_final_term" v-model="form.is_final_term" class="mr-2" />
                                <label for="is_final_term" class="font-medium">Is Final Term?</label>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button type="button" @click="closeModal"
                                    class="px-4 py-2 border rounded hover:bg-gray-100">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    :disabled="processing">
                                    {{ processing ? 'Saving...' : 'Save' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage, Head, router } from '@inertiajs/vue3';

// Props from Inertia
const props = usePage().props.value
const examTypes = ref(props?.examTypes || [])

const showModal = ref(false)
const processing = ref(false)

const form = ref({
    id: null,
    name: '',
    code: '',
    is_final_term: false,
})

const errors = ref({})

// Open modal for creating new exam type
function openModalForCreate() {
    form.value = {
        id: null,
        name: '',
        code: '',
        is_final_term: false,
    }
    errors.value = {}
    showModal.value = true
}

// Open modal for editing existing exam type
function openModalForEdit(examType) {
    form.value = {
        id: examType.id,
        name: examType.name,
        code: examType.code,
        is_final_term: examType.is_final_term,
    }
    errors.value = {}
    showModal.value = true
}

// Close modal
function closeModal() {
    showModal.value = false
    errors.value = {}
}

// Submit form (Create or Update)
function submit() {
    processing.value = true
    errors.value = {}

    if (form.value.id) {
        // Update existing
        router.put(
            route('exam-types.update', form.value.id),
            {
                name: form.value.name,
                code: form.value.code,
                is_final_term: form.value.is_final_term,
            },
            {
                onSuccess: () => {
                    closeModal()
                    processing.value = false
                },
                onError: (errs) => {
                    errors.value = errs
                    processing.value = false
                },
            }
        )
    } else {
        // Create new
        router.post(
            route('exam-types.store'),
            {
                name: form.value.name,
                code: form.value.code,
                is_final_term: form.value.is_final_term,
            },
            {
                onSuccess: () => {
                    closeModal()
                    processing.value = false
                },
                onError: (errs) => {
                    errors.value = errs
                    processing.value = false
                },
            }
        )
    }
}

// Delete ExamType with confirmation
function destroyExamType(id) {
    if (confirm('Are you sure you want to delete this exam type?')) {
        Inertia.delete(route('exam-types.destroy', id))
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
