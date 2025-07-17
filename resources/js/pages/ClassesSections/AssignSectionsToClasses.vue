<template>
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-6 border border-gray-200 dark:border-neutral-700">
        <h2 class="text-xl font-semibold mb-2">Assign Sections to Classes</h2>
        <div
            class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded text-blue-900 dark:text-blue-100 text-sm">
            <b>How to use this page:</b><br>
            <ul class="list-disc pl-5 mt-1">
                <li><b>Select a school</b> from the school switcher at the top of the page.</li>
                <li>You will see a list of <b>classes</b> for the selected school.</li>
                <li>Click a class to expand and view its <b>sections</b>.</li>
                <li>Check or uncheck sections to assign or remove them from the class.</li>
                <li>Click <b>Save Assignments</b> to save your changes for that class.</li>
                <li>Switch schools at any time to manage classes and sections for another school. The list will update
                    automatically.</li>
            </ul>
            <span class="block mt-2 text-xs text-blue-700 dark:text-blue-200">Note: All changes you make here only
                affect the currently selected school. You cannot accidentally change classes or sections for other
                schools.</span>
        </div>
        <div v-if="loading" class="text-center py-8">Loading...</div>
        <div v-else>
            <div v-for="cls in classes" :key="cls.id" class="mb-4 border rounded">
                <button
                    class="w-full flex justify-between items-center px-4 py-3 bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 focus:outline-none"
                    @click="toggleAccordion(cls.id)">
                    <span class="font-semibold">{{ cls.name }}</span>
                    <span>{{ isAccordionOpen(cls.id) ? '▲' : '▼' }}</span>
                </button>
                <div v-show="isAccordionOpen(cls.id)" class="p-4 bg-white dark:bg-neutral-900">
                    <div class="mb-2 text-sm text-gray-500">Check the sections you want to assign to <b>{{ cls.name
                            }}</b>:</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2 mb-4">
                        <label v-for="section in sections" :key="section.id"
                            class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" :value="section.id" v-model="selectedSections[cls.id]" />
                            <span>{{ section.name }}</span>
                        </label>
                    </div>
                    <button class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark"
                        :disabled="saving[cls.id]" @click="saveAssignments(cls.id)">
                        {{ saving[cls.id] ? 'Saving...' : 'Save Assignments' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import { toast } from 'vue3-toastify';
import { useSchoolStore } from '@/stores/school';
import { storeToRefs } from 'pinia';

interface ClassItem {
    id: number;
    name: string;
    sections: { id: number; name: string }[];
}
interface SectionItem {
    id: number;
    name: string;
}

const classes = ref<ClassItem[]>([]);
const sections = ref<SectionItem[]>([]);
const loading = ref(true);
const saving = reactive<{ [key: number]: boolean }>({});
const openAccordions = ref<number[]>([]);
const selectedSections = reactive<{ [key: number]: number[] }>({});

const schoolStore = useSchoolStore();
const { selectedSchool } = storeToRefs(schoolStore);

function isAccordionOpen(classId: number) {
    return openAccordions.value.includes(classId);
}
function toggleAccordion(classId: number) {
    if (isAccordionOpen(classId)) {
        openAccordions.value = openAccordions.value.filter(id => id !== classId);
    } else {
        openAccordions.value.push(classId);
    }
}

async function fetchData() {
    if (!selectedSchool.value?.id) {
        classes.value = [];
        sections.value = [];
        return;
    }
    loading.value = true;
    try {
        const res = await fetch(`/section-assignment?school_id=${selectedSchool.value.id}`);
        const data = await res.json();
        classes.value = data.classes;
        sections.value = data.sections;
        // Initialize selectedSections for each class
        for (const cls of data.classes) {
            selectedSections[cls.id] = (cls.sections || []).map((section: { id: number }) => section.id);
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

async function saveAssignments(classId: number) {
    saving[classId] = true;
    try {
        const res = await fetch(`/section-assignment/${classId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ section_ids: selectedSections[classId] }),
        });
        if (res.ok) {
            toast.success('Assignments saved!');
        } else {
            toast.error('Failed to save assignments.');
        }
    } catch (e) {
        toast.error('An error occurred.');
    } finally {
        saving[classId] = false;
    }
}

onMounted(fetchData);
watch(() => selectedSchool.value?.id, fetchData);
</script>

<style scoped>
.bg-primary {
    background-color: #2563eb;
}

.bg-primary-dark {
    background-color: #1e40af;
}
</style>