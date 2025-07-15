import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export interface School {
  id: number;
  name: string;
  logo?: string;
  phone?: string;
  address?: string;
  contact?: string;
  mainImage?: string;
}

export const useSchoolStore = defineStore('school', () => {
    const schools = ref<School[]>([]);
    const selectedSchool = ref<School | null>(null);

    async function fetchSchools() {
        const cached = localStorage.getItem('schools');
        if (cached) {
            schools.value = JSON.parse(cached);
        }
        try {
            const { data } = await axios.get('/admin/schools');
            if (Array.isArray(data)) {
                schools.value = data;
                localStorage.setItem('schools', JSON.stringify(data));
                // If no schools, clear selectedSchool
                if (schools.value.length === 0) {
                    selectedSchool.value = null;
                    localStorage.removeItem('selectedSchool');
                } else if (!selectedSchool.value || !schools.value.some(s => s.id === selectedSchool.value?.id)) {
                    // If no selected school or selected school is not in the list, select the first
                    selectedSchool.value = schools.value[0];
                    localStorage.setItem('selectedSchool', JSON.stringify(schools.value[0]));
                }
            }
        } catch (e) {
            // handle error, fallback to cached if needed
        }
    }

    function setSchool(school: School | null) {
        selectedSchool.value = school;
        if (school) {
            localStorage.setItem('selectedSchool', JSON.stringify(school));
        } else {
            localStorage.removeItem('selectedSchool');
        }
    }

    function loadSelectedSchool() {
        const cached = localStorage.getItem('selectedSchool');
        if (cached) {
            selectedSchool.value = JSON.parse(cached);
        }
    }

    // Fetch schools and selected school on store init
    fetchSchools();
    loadSelectedSchool();

    return { schools, selectedSchool, fetchSchools, setSchool };
});
