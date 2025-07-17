<template>
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-6 border border-gray-200 dark:border-neutral-700">
        <h2 class="text-xl font-semibold mb-4">Assign Classes to Schools</h2>
        <div v-if="loading" class="text-center py-8">Loading...</div>
        <div v-else>
            <div v-for="school in schools" :key="school.id" class="mb-4 border rounded">
                <button
                    class="w-full flex justify-between items-center px-4 py-3 bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 focus:outline-none"
                    @click="toggleAccordion(school.id)">
                    <span class="font-semibold">{{ school.name }}</span>
                    <span>{{ isAccordionOpen(school.id) ? '▲' : '▼' }}</span>
                </button>
                <div v-show="isAccordionOpen(school.id)" class="p-4 bg-white dark:bg-neutral-900">
                    <div class="mb-2 text-sm text-gray-500">Check the classes you want to assign to <b>{{ school.name
                            }}</b>:</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mb-4">
                        <label v-for="cls in classes" :key="cls.id" class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" :value="cls.id" v-model="selectedClasses[school.id]" />
                            <span>{{ cls.name }}</span>
                        </label>
                    </div>
                    <button class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark"
                        :disabled="saving[school.id]" @click="saveAssignments(school.id)">
                        {{ saving[school.id] ? 'Saving...' : 'Save Assignments' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { toast } from 'vue3-toastify';

interface School {
    id: number;
    name: string;
    classes: { id: number; name: string }[];
}
interface ClassItem {
    id: number;
    name: string;
}

const schools = ref<School[]>([]);
const classes = ref<ClassItem[]>([]);
const loading = ref(true);
const saving = reactive<{ [key: number]: boolean }>({});
const openAccordions = ref<number[]>([]);
const selectedClasses = reactive<{ [key: number]: number[] }>({});

function isAccordionOpen(schoolId: number) {
    return openAccordions.value.includes(schoolId);
}
function toggleAccordion(schoolId: number) {
    if (isAccordionOpen(schoolId)) {
        openAccordions.value = openAccordions.value.filter(id => id !== schoolId);
    } else {
        openAccordions.value.push(schoolId);
    }
}

async function fetchData() {
    loading.value = true;
    try {
        const res = await fetch('/class-assignment');
        const data = await res.json();
        schools.value = data.schools;
        classes.value = data.classes;
        // Initialize selectedClasses for each school
        for (const school of data.schools) {
            selectedClasses[school.id] = (school.classes || []).map((cls: { id: number }) => cls.id);
        }
    } catch (e) {
        toast.error('Failed to load data.');
    } finally {
        loading.value = false;
    }
}

function getCsrfToken(): string {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function saveAssignments(schoolId: number) {
    saving[schoolId] = true;
    try {
        const res = await fetch(`/class-assignment/${schoolId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ class_ids: selectedClasses[schoolId] }),
        });
        if (res.ok) {
            toast.success('Assignments saved!');
        } else {
            toast.error('Failed to save assignments.');
        }
    } catch (e) {
        toast.error('An error occurred.');
    } finally {
        saving[schoolId] = false;
    }
}

onMounted(fetchData);
</script>

<style scoped>
.bg-primary {
    background-color: #2563eb;
}

.bg-primary-dark {
    background-color: #1e40af;
}
</style>