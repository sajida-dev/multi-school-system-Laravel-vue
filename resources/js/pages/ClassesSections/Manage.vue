<template>
    <AppLayout :breadcrumbs="[{ title: 'Manage Classes & Sections', href: '/manage/classes-sections' }]">

        <Head title="Manage Classes & Sections" />
        <div class="max-w-5xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
            <h1 class="text-2xl font-bold mb-6">Manage Classes & Sections</h1>
            <div class="mb-6 flex gap-4 border-b border-gray-200 dark:border-neutral-800">
                <button
                    :class="['px-4 py-2 font-semibold', activeTab === 'classes' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                    @click="activeTab = 'classes'">Classes</button>
                <button
                    :class="['px-4 py-2 font-semibold', activeTab === 'sections' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                    @click="activeTab = 'sections'">Sections</button>
                <button
                    :class="['px-4 py-2 font-semibold', activeTab === 'assign-classes' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                    @click="activeTab = 'assign-classes'">Assign Classes to Schools</button>
                <button
                    :class="['px-4 py-2 font-semibold', activeTab === 'assign-sections' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                    @click="activeTab = 'assign-sections'">Assign Sections to Classes</button>
            </div>
            <div v-if="activeTab === 'classes'">
                <ClassesIndex :classes="classes" />
            </div>
            <div v-else-if="activeTab === 'sections'">
                <SectionsIndex :sections="sections" />
            </div>
            <div v-else-if="activeTab === 'assign-classes'">
                <AssignClassesToSchools />
            </div>
            <div v-else-if="activeTab === 'assign-sections'">
                <AssignSectionsToClasses />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClassesIndex from '@/pages/Classes/Index.vue';
import SectionsIndex from '@/pages/Sections/Index.vue';
import AssignClassesToSchools from './AssignClassesToSchools.vue';
import AssignSectionsToClasses from './AssignSectionsToClasses.vue';

const page = usePage();
const flash = (page.props as any).flash || {};
const initialTab = flash.initialTab || 'classes';
const activeTab = ref(initialTab);
const classes = computed(() => page.props.classes as { id: number; name: string }[]);
const sections = computed(() => page.props.sections as { id: number; name: string }[]);
</script>