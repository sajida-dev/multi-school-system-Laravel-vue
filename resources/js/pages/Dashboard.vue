<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import { ref } from 'vue';
// import axios from 'axios';
import { router } from '@inertiajs/vue3';
import StatCard from '@/components/StatCard.vue';

interface School {
  id: string | number;
  name: string;
}

const props = defineProps<{
  schools: School[];
  activeSchoolId: string | number;
}>();

const selectedSchool = ref<string | number>(props.activeSchoolId);

function switchSchool() {
  router.post('/switch-school', { school_id: selectedSchool.value }, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['schools', 'activeSchoolId'] });
    }
  });
}

// Dummy student data
const student = {
  name: 'Jason Black',
  roll: '1406',
  email: 'jasonblack@gmail.com',
  phone: '+88 9856418',
  gender: 'Male',
  father: 'Alex Black',
  mother: 'Jesica Black',
  dob: '14, June 2006',
  religion: 'Christian',
  occupation: 'Banker',
  admission: '05, June 2012',
  address: 'House 10, Road 6, Australia.',
  class: '11th',
  section: 'Pink',
  about: 'Hi there! My name is Jason, and I am a 11th standard student. I love going to school and learning new things every day. My favourite subject is maths, and I enjoy playing with my friends.'
};

// Stats cards with icons
const stats = [
  { label: 'Events', value: 6 },
  { label: 'Growth', value: '72%' },
];

// Attendance chart data
const attendanceSeries = [60, 10, 20, 10];
const attendanceOptions = {
  chart: { type: 'donut' },
  labels: ['Present', 'Half Day Present', 'Late Coming', 'Absent'],
  colors: ['#4f46e5', '#fbbf24', '#34d399', '#f87171'],
  legend: { position: 'bottom' },
  dataLabels: { enabled: false },
};

// Calendar events
const calendarEvents = ref([
  { title: 'Maths Exam', date: '2023-02-16' },
  { title: 'Sports Day', date: '2023-02-10' },
]);

// Exam results
const examResults = [
  { id: '#mat21', type: 'Class Test', subject: 'Maths', grade: 'A', percent: 89, date: '21 Jul 2022' },
  { id: '#mat21', type: 'Quarterly Test', subject: 'English', grade: 'A+', percent: 93, date: '14 Jun 2022' },
  { id: '#mat21', type: 'Oral Test', subject: 'Physics', grade: 'B', percent: 78, date: '10 Mar 2022' },
  { id: '#mat21', type: 'Class Test', subject: 'Chemistry', grade: 'A', percent: 88, date: '06 Jan 2022' },
];

// Calendar config
const calendarOptions = {
  plugins: [dayGridPlugin],
  initialView: 'dayGridMonth',
  events: calendarEvents.value,
  height: 350,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: ''
  },
};
</script>

<template>

  <Head title="Dashboard" />
  <AppLayout>
    <div class="p-4 md:p-8 bg-gradient-to-br from-indigo-50 via-white to-pink-50 min-h-screen">
      <!-- School Switcher -->
      <div v-if="props.schools && props.schools.length" class="mb-6 flex justify-end">
        <select v-model="selectedSchool" @change="switchSchool" class="border rounded px-3 py-2">
          <option v-for="school in props.schools" :key="(school as School).id" :value="(school as School).id">
            {{ (school as School).name }}
          </option>
        </select>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Profile Card -->
        <div
          class="col-span-1 bg-white/90 rounded-2xl shadow-xl p-6 flex flex-col items-center relative overflow-hidden border border-indigo-100">
          <div class="absolute -top-8 -left-8 w-32 h-32 bg-indigo-100 rounded-full opacity-30 z-0"></div>
          <div class="relative mb-4 z-10 flex items-center justify-center">
            <svg width="130" height="130" viewBox="0 0 130 130" class="absolute">
              <defs>
                <linearGradient id="profileRingGradient" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0%" stop-color="#6366f1" />
                  <stop offset="100%" stop-color="#06b6d4" />
                </linearGradient>
              </defs>
              <circle cx="65" cy="65" r="58" fill="none" stroke="url(#profileRingGradient)" stroke-width="6"
                stroke-dasharray="32 8 32 8 32 8 32 8" stroke-linecap="round" />
            </svg>
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile"
              class="w-28 h-28 rounded-full border-4 border-white shadow-lg relative z-10" />
          </div>
          <h2 class="text-xl font-bold z-10">{{ student.name }} <span class="text-indigo-500">({{ student.roll
          }})</span></h2>
          <p class="text-sm text-gray-500 z-10">{{ student.email }}</p>
          <p class="text-sm text-gray-500 mb-2 z-10">{{ student.phone }}</p>
          <div class="w-full mt-4 z-10">
            <div class="bg-indigo-50 rounded p-2 mb-2 text-xs font-semibold">Personal Details</div>
            <div class="text-xs text-gray-700 space-y-1">
              <div><b>Gender:</b> {{ student.gender }}</div>
              <div><b>Father's Name:</b> {{ student.father }}</div>
              <div><b>Mother's Name:</b> {{ student.mother }}</div>
              <div><b>Date Of Birth:</b> {{ student.dob }}</div>
              <div><b>Religion:</b> {{ student.religion }}</div>
              <div><b>Father Occupation:</b> {{ student.occupation }}</div>
              <div><b>Admission Date:</b> {{ student.admission }}</div>
              <div><b>Address:</b> {{ student.address }}</div>
              <div><b>Class:</b> {{ student.class }}</div>
              <div><b>Section:</b> {{ student.section }}</div>
            </div>
          </div>
          <div class="flex gap-3 mt-4 z-10">
            <a href="#" class="text-blue-500 hover:text-blue-700 transition"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="text-sky-400 hover:text-sky-600 transition"><i class="fab fa-twitter fa-lg"></i></a>
            <a href="#" class="text-pink-500 hover:text-pink-700 transition"><i class="fab fa-instagram fa-lg"></i></a>
          </div>
          <div class="mt-4 text-xs text-gray-600 z-10 text-center">{{ student.about }}</div>
        </div>

        <!-- Main Content -->
        <div class="col-span-1 md:col-span-3 flex flex-col gap-8">
          <!-- Top Stats Cards -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <StatCard v-for="stat in stats" :key="stat.label" :label="stat.label" :value="stat.value" />
          </div>

          <!-- Middle Section: Attendance Chart & Calendar -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Attendance Chart -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center border border-yellow-100">
              <div class="font-bold text-yellow-600 text-lg mb-2 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie"></i> Attendance
              </div>
              <VueApexCharts type="donut" width="250" :options="attendanceOptions" :series="attendanceSeries" />
            </div>
            <!-- Spacer -->
            <div></div>
            <!-- Event Calendar -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-pink-100">
              <div class="font-bold text-pink-600 text-lg mb-2 flex items-center gap-2">
                <i class="fa-solid fa-calendar-days"></i> Event Calendar
              </div>
              <FullCalendar :options="calendarOptions" />
            </div>
          </div>

          <!-- Exam Results Table -->
          <div class="bg-white rounded-2xl shadow-lg p-6 overflow-x-auto border border-indigo-100">
            <div class="font-bold text-indigo-600 text-lg mb-2 flex items-center gap-2">
              <i class="fa-solid fa-table"></i> All Exam Results
            </div>
            <table class="min-w-full text-xs rounded-xl overflow-hidden">
              <thead>
                <tr class="text-left text-gray-700 border-b font-bold bg-indigo-50">
                  <th class="py-2 px-2">Exam Id</th>
                  <th class="py-2 px-2">Type</th>
                  <th class="py-2 px-2">Subject</th>
                  <th class="py-2 px-2">Grade</th>
                  <th class="py-2 px-2">%</th>
                  <th class="py-2 px-2">Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(result, idx) in examResults" :key="result.subject"
                  :class="idx % 2 === 0 ? 'bg-white' : 'bg-indigo-50' + ' hover:bg-indigo-100'">
                  <td class="py-2 px-2 font-mono">{{ result.id }}</td>
                  <td class="py-2 px-2">{{ result.type }}</td>
                  <td class="py-2 px-2">{{ result.subject }}</td>
                  <td class="py-2 px-2 font-bold">{{ result.grade }}</td>
                  <td class="py-2 px-2">{{ result.percent }}</td>
                  <td class="py-2 px-2">{{ result.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
</style>
