import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import axios from 'axios';

export interface School {
  id: number;
  name: string;
  logo?: string;
  phone?: string;
  address?: string;
  contact?: string;
  mainImage?: string;
  classes?: any[]; // [{ id, name, sections: [...] }]
}

export const useSchoolStore = defineStore('school', () => {
    const schools = ref<School[]>([]);
    const selectedSchool = ref<School | null>(null);
    const classes = ref<any[]>([]); // [{ id, name, sections: [...] }]
    const sections = ref<any[]>([]);

    async function fetchSchools() {
        try {
            // Always fetch all schools with their linked classes and sections
            const { data } = await axios.get('/admin/schools');
            if (data.schools) {
                schools.value = data.schools;
            }
            // If no schools, clear selectedSchool
            if (schools.value.length === 0) {
                selectedSchool.value = null;
                localStorage.removeItem('selectedSchool');
            } else if (!selectedSchool.value || !schools.value.some(s => s.id === selectedSchool.value?.id)) {
                // If no selected school or selected school is not in the list, select the first
                selectedSchool.value = schools.value[0];
                localStorage.setItem('selectedSchool', JSON.stringify(schools.value[0]));
            }
            // Set classes and sections for the selected school
            updateClassesAndSections();
        } catch (e) {
            // handle error, fallback to cached if needed
        }
    }

    // Call this after any create/update/delete of a school
    async function refreshSchools() {
        await fetchSchools();
    }

    // Call this after any create/update/delete of a class
    async function refreshClasses() {
        await fetchSchools();
    }

    // Call this after any create/update/delete of a section
    async function refreshSections() {
        await fetchSchools();
    }

    function setSchool(school: School | null) {
        selectedSchool.value = school;
        if (school) {
            localStorage.setItem('selectedSchool', JSON.stringify(school));
        } else {
            localStorage.removeItem('selectedSchool');
        }
        // Fetch classes and sections for the new school
        updateClassesAndSections();
    }

    function loadSelectedSchool() {
        const cached = localStorage.getItem('selectedSchool');
        if (cached) {
            selectedSchool.value = JSON.parse(cached);
        }
    }

    function updateClassesAndSections() {
        if (!selectedSchool.value || !selectedSchool.value.id) {
            classes.value = [];
            sections.value = [];
            return;
        }
        // Find the selected school in the schools array by id
        const school = schools.value.find(s => s.id === selectedSchool.value?.id);
        if (school && school.classes) {
            classes.value = school.classes;
            // Gather all sections from all classes
            const allSections = school.classes.flatMap((cls: any) => cls.sections || []);
            // Remove duplicates by section id
            const uniqueSections = Array.from(new Map(allSections.map(s => [s.id, s])).values());
            sections.value = uniqueSections;
        } else {
            classes.value = [];
            sections.value = [];
        }
    }

    // Fetch schools, classes, and sections on store init
    fetchSchools();
    loadSelectedSchool();

    // Watch for changes in selectedSchool and update classes/sections
    watch(selectedSchool, () => {
        updateClassesAndSections();
    });

    return { schools, selectedSchool, fetchSchools, setSchool, classes, sections, refreshSchools, refreshClasses, refreshSections };
});
