<template>
    <div class="p-4 bg-gray-100 dark:bg-gray-900 min-h-screen">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Add New Student</h1>
        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <SelectInput label="Nationality" v-model="form.nationality" :options="nationalities" :required="true" />
                <TextInput label="Registration Number" v-model="form.registration_number" :required="true" />
                <TextInput label="Name" v-model="form.name" :required="true" />
                <TextInput label="B-Form Number" v-model="form.b_form_number" :required="true" />
                <TextInput label="Admission Date" v-model="form.admission_date" type="date" :required="true" />
                <TextInput label="Date of Birth" v-model="form.date_of_birth" type="date" :required="true" />
                <SelectInput label="Class" v-model="form.class" :options="classes" :required="true" />
                <SelectInput label="Gender" v-model="form.gender" :options="genders" :required="true" />
                <SelectInput label="Class Shift" v-model="form.class_shift" :options="classShifts" :required="true" />
                <TextInput label="Previous School" v-model="form.previous_school" :required="false" />
                <SelectInput label="Inclusive" v-model="form.inclusive" :options="inclusives" :required="true" />
                <TextInput label="Other Inclusive Type" v-model="form.other_inclusive_type" :required="false" />
                <SelectInput label="Religion" v-model="form.religion" :options="religions" :required="true" />
                <CheckboxInput label="Is Bricklin" v-model="form.is_bricklin" :required="false" />
                <CheckboxInput label="Is Orphan" v-model="form.is_orphan" :required="false" />
                <CheckboxInput label="Is QSC" v-model="form.is_qsc" :required="false" />
                <FileInput label="Profile Photo" v-model="form.profile_photo_path" :required="false" />
                <TextInput label="Father Name" v-model="form.father_name" :required="true" />
                <TextInput label="Guardian Name" v-model="form.guardian_name" :required="false" />
                <TextInput label="Father CNIC" v-model="form.father_cnic" :required="true" />
                <TextInput label="Mother CNIC" v-model="form.mother_cnic" :required="false" />
                <SelectInput label="Father Profession" v-model="form.father_profession" :options="fatherProfessions"
                    :required="true" />
                <TextInput label="No of Children" v-model="form.no_of_children" type="number" :required="false" />
                <SelectInput label="Job Type" v-model="form.job_type" :options="jobTypes" :required="false" />
                <SelectInput label="Father Education" v-model="form.father_education" :options="fatherEducations"
                    :required="true" />
                <SelectInput label="Mother Education" v-model="form.mother_education" :options="motherEducations"
                    :required="true" />
                <SelectInput label="Mother Profession" v-model="form.mother_profession" :options="motherProfessions"
                    :required="true" />
                <SelectInput label="Father Income" v-model="form.father_income" :options="fatherIncomes"
                    :required="true" />
                <SelectInput label="Mother Income" v-model="form.mother_income" :options="motherIncomes"
                    :required="false" />
                <SelectInput label="Household Income" v-model="form.household_income" :options="householdIncomes"
                    :required="true" />
                <TextInput label="Permanent Address" v-model="form.permanent_address" :required="true" />
                <TextInput label="Phone No" v-model="form.phone_no" :required="false" />
                <TextInput label="Mobile No" v-model="form.mobile_no" :required="true" />
            </div>
            <div class="mt-6 flex gap-2">
                <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 transition">Add
                    Student</button>
                <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 transition"
                    @click="goBack">Cancel</button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import CheckboxInput from '@/components/form/CheckboxInput.vue';
import FileInput from '@/components/form/FileInput.vue';

const nationalities = [
    'Pakistan', 'India', 'Bangladesh', 'Afghanistan', 'China', 'Saudi Arabia', 'United Arab Emirates', 'United States', 'United Kingdom', 'Canada', 'Australia', 'Turkey', 'Iran', 'Indonesia', 'Malaysia', 'Egypt', 'South Africa', 'Germany', 'France', 'Italy', 'Other'
];
const classes = [
    'NURSERY', 'PREP', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN'
];
const genders = ['Male', 'Female', 'Other'];
const classShifts = ['Morning', 'Evening', 'Other'];
const inclusives = ['No Disability', 'Physical', 'Visual', 'Hearing', 'Intellectual', 'Other'];
const religions = ['Islam', 'Christianity', 'Hinduism', 'Other'];
const fatherProfessions = ['Unemployed', 'Private/Self Employed', 'Government', 'Other'];
const jobTypes = ['Private/Self Employed', 'Government', 'Other'];
const fatherEducations = ['None', 'Primary', 'Middle', 'Matric', 'Intermediate', 'Graduate', 'Post Graduate'];
const motherEducations = ['None', 'Primary', 'Middle', 'Matric', 'Intermediate', 'Graduate', 'Post Graduate'];
const motherProfessions = ['House Wife', 'Private/Self Employed', 'Government', 'Other'];
const fatherIncomes = [
    'INCOME LEVEL BETWEEN RS. 0 - 20,000',
    'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
    'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
    'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
    'INCOME LEVEL ABOVE RS. 50,000',
];
const motherIncomes = [
    'NOT APPLICABLE',
    'INCOME LEVEL BETWEEN RS. 0 - 20,000',
    'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
    'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
    'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
    'INCOME LEVEL ABOVE RS. 50,000',
];
const householdIncomes = [
    'INCOME LEVEL BETWEEN RS. 0 - 20,000',
    'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
    'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
    'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
    'INCOME LEVEL ABOVE RS. 50,000',
];

const form = ref({
    nationality: '',
    registration_number: '',
    name: '',
    b_form_number: '',
    admission_date: '',
    date_of_birth: '',
    class: '',
    gender: '',
    class_shift: '',
    previous_school: '',
    inclusive: '',
    other_inclusive_type: '',
    religion: '',
    is_bricklin: false,
    is_orphan: false,
    is_qsc: false,
    profile_photo_path: null,
    father_name: '',
    guardian_name: '',
    father_cnic: '',
    mother_cnic: '',
    father_profession: '',
    no_of_children: '',
    job_type: '',
    father_education: '',
    mother_education: '',
    mother_profession: '',
    father_income: '',
    mother_income: '',
    household_income: '',
    permanent_address: '',
    phone_no: '',
    mobile_no: '',
});

const submit = () => {
    const data = new FormData();
    Object.keys(form.value).forEach((key) => {
        data.append(key, form.value[key] ?? '');
    });
    router.post(route('admissions.store'), data, {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Student added successfully!');
            router.visit(route('admissions.index'));
        },
        onError: (errors) => {
            toast.error('Please fix the errors in the form.');
        },
    });
};

const goBack = () => {
    router.visit(route('admissions.index'));
};
</script>

<style scoped></style>