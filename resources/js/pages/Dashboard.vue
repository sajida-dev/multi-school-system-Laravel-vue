<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import StatCard from '@/components/StatCard.vue';
import { ref, computed } from 'vue';

interface School {
  id: string | number;
  name: string;
}

interface Stat {
  label: string;
  value: string | number;
  icon: string;
  color: string;
  change: string;
}

const props = defineProps<{
  user: any;
  userRoles: string[];
  activeSchoolId: string | number;
  schools: School[];
  stats: Stat[];
  recentData: any;
  errors: string[];
  activeSchool?: any;
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

// Get role display name
const getRoleDisplayName = (roles: string[]) => {
  if (roles.includes('superadmin')) return 'Super Admin';
  if (roles.includes('admin')) return 'Admin';
  if (roles.includes('principal')) return 'Principal';
  if (roles.includes('teacher')) return 'Teacher';
  return 'User';
};

// Get role-specific welcome message
const getWelcomeMessage = (roles: string[]) => {
  if (roles.includes('superadmin')) return 'System Overview';
  if (roles.includes('admin')) return 'School Management';
  if (roles.includes('principal')) return 'Academic Overview';
  if (roles.includes('teacher')) return 'My Classes';
  return 'Dashboard';
};

// Check if there's any recent data to display
const hasRecentData = computed(() => {
  if (!props.recentData) return false;

  const dataKeys = [
    'recentSchools', 'recentTeachers', 'recentStudents', 'recentFees', 'recentApplicants',
    'myStudents', 'myPapers', 'myAdmissions', 'recentPapers'
  ];

  return dataKeys.some(key =>
    props.recentData[key] &&
    Array.isArray(props.recentData[key]) &&
    props.recentData[key].length > 0
  );
});
</script>

<template>

  <Head title="Dashboard" />
  <AppLayout>
    <div class="p-4 md:p-8 bg-gradient-to-br from-indigo-50 via-white to-pink-50 min-h-screen">
      <!-- Error Messages -->
      <div v-if="props.errors && props.errors.length > 0" class="mb-6">
        <div v-for="error in props.errors" :key="error"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-2">
          {{ error }}
        </div>
      </div>

      <!-- Header Section -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ getWelcomeMessage(props.userRoles) }}</h1>
            <p class="text-gray-600">Welcome back, {{ props.user.name }} ({{ getRoleDisplayName(props.userRoles) }})</p>
          </div>

          <!-- School Switcher -->
          <div v-if="props.schools && props.schools.length > 0" class="flex items-center gap-4">
            <span class="text-sm text-gray-600">Active School:</span>
            <select v-model="selectedSchool" @change="switchSchool"
              class="border rounded-lg px-3 py-2 bg-white shadow-sm">
              <option v-for="school in props.schools" :key="school.id" :value="school.id">
                {{ school.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Active School Info -->
        <div v-if="props.activeSchool" class="bg-white rounded-lg p-4 shadow-sm border">
          <h3 class="font-semibold text-gray-800 mb-2">Current School: {{ props.activeSchool.name }}</h3>
          <p class="text-sm text-gray-600">{{ props.activeSchool.address || 'No address available' }}</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard v-for="stat in props.stats" :key="stat.label" :label="stat.label" :value="stat.value"
          :icon="stat.icon" :color="stat.color" :change="stat.change" />
      </div>

      <!-- Role-specific content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6 border">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
          <div class="space-y-3">
            <div v-if="props.userRoles.includes('superadmin')" class="space-y-2">
              <button class="w-full text-left p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors">
                <i class="fas fa-school mr-2 text-blue-600"></i>
                Manage Schools
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-green-50 hover:bg-green-100 transition-colors">
                <i class="fas fa-users mr-2 text-green-600"></i>
                System Users
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-purple-50 hover:bg-purple-100 transition-colors">
                <i class="fas fa-cog mr-2 text-purple-600"></i>
                System Settings
              </button>
            </div>

            <div v-if="props.userRoles.includes('admin')" class="space-y-2">
              <button class="w-full text-left p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors">
                <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                New Admission
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-green-50 hover:bg-green-100 transition-colors">
                <i class="fas fa-chalkboard-teacher mr-2 text-green-600"></i>
                Manage Teachers
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-purple-50 hover:bg-purple-100 transition-colors">
                <i class="fas fa-money-bill mr-2 text-purple-600"></i>
                Fee Management
              </button>
            </div>

            <div v-if="props.userRoles.includes('principal')" class="space-y-2">
              <button class="w-full text-left p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                Academic Reports
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-green-50 hover:bg-green-100 transition-colors">
                <i class="fas fa-calendar-check mr-2 text-green-600"></i>
                Attendance Overview
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-purple-50 hover:bg-purple-100 transition-colors">
                <i class="fas fa-trophy mr-2 text-purple-600"></i>
                Performance Analysis
              </button>
            </div>

            <div v-if="props.userRoles.includes('teacher')" class="space-y-2">
              <button class="w-full text-left p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors">
                <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                Create Paper
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-green-50 hover:bg-green-100 transition-colors">
                <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                Publish Results
              </button>
              <button class="w-full text-left p-3 rounded-lg bg-purple-50 hover:bg-purple-100 transition-colors">
                <i class="fas fa-users mr-2 text-purple-600"></i>
                My Students
              </button>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6 border">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
          <div class="space-y-3">
            <!-- Recent Schools (for Super Admin) -->
            <div v-if="props.recentData?.recentSchools && props.recentData.recentSchools.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Schools</h4>
              <div v-for="school in props.recentData.recentSchools.slice(0, 3)" :key="school.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ school.name || 'Unknown School' }}</p>
                  <p class="text-xs text-gray-500">Created: {{ school.created_at ? new
                    Date(school.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Teachers (for Super Admin) -->
            <div v-if="props.recentData?.recentTeachers && props.recentData.recentTeachers.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Teachers</h4>
              <div v-for="teacher in props.recentData.recentTeachers.slice(0, 3)" :key="teacher.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ teacher.name || 'Unknown Teacher' }}</p>
                  <p class="text-xs text-gray-500">{{ teacher.school?.name || 'School not assigned' }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Students -->
            <div v-if="props.recentData?.recentStudents && props.recentData.recentStudents.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Students</h4>
              <div v-for="student in props.recentData.recentStudents.slice(0, 3)" :key="student.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ student.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-gray-500">Admitted: {{ student.created_at ? new
                    Date(student.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Fees -->
            <div v-if="props.recentData?.recentFees && props.recentData.recentFees.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Fees</h4>
              <div v-for="fee in props.recentData.recentFees.slice(0, 3)" :key="fee.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ fee.student?.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-gray-500">Amount: ${{ fee.amount }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Applicants -->
            <div v-if="props.recentData?.recentApplicants && props.recentData.recentApplicants.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Applicants</h4>
              <div v-for="applicant in props.recentData.recentApplicants.slice(0, 3)" :key="applicant.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ applicant.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-gray-500">Applied: {{ applicant.created_at ? new
                    Date(applicant.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- My Students (for teachers) -->
            <div v-if="props.recentData?.myStudents && props.recentData.myStudents.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">My Students</h4>
              <div v-for="student in props.recentData.myStudents.slice(0, 3)" :key="student.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ student.name }}</p>
                  <p class="text-xs text-gray-500">Class: {{ student.class?.name || 'Not assigned' }}</p>
                </div>
              </div>
            </div>

            <!-- My Papers (for teachers) -->
            <div v-if="props.recentData?.myPapers && props.recentData.myPapers.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">My Papers</h4>
              <div v-for="paper in props.recentData.myPapers.slice(0, 3)" :key="paper.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ paper.title || 'Untitled Paper' }}</p>
                  <p class="text-xs text-gray-500">Subject: {{ paper.subject || 'Not specified' }}</p>
                </div>
              </div>
            </div>

            <!-- My Admissions (for teachers) -->
            <div v-if="props.recentData?.myAdmissions && props.recentData.myAdmissions.length > 0">
              <h4 class="text-sm font-medium text-gray-700 mb-2">My Admissions</h4>
              <div v-for="admission in props.recentData.myAdmissions.slice(0, 3)" :key="admission.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ admission.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-gray-500">Admitted: {{ admission.created_at ? new
                    Date(admission.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- No recent activity -->
            <div v-if="!hasRecentData" class="text-center py-8">
              <div class="text-gray-400 mb-3">
                <i class="fas fa-clock text-4xl"></i>
              </div>
              <p class="text-sm font-medium text-gray-600 mb-2">No Recent Activity</p>
              <p class="text-xs text-gray-500 mb-4">Recent students, fees, and applications will appear here once data
                is added</p>

              <!-- Role-specific suggestions -->
              <div v-if="props.userRoles.includes('superadmin')" class="text-xs text-gray-400">
                <p>• Add schools to see school management activity</p>
                <p>• Create teachers and students to see recent activity</p>
              </div>
              <div v-else-if="props.userRoles.includes('admin')" class="text-xs text-gray-400">
                <p>• Add students to see recent admissions</p>
                <p>• Create fees to see payment activity</p>
              </div>
              <div v-else-if="props.userRoles.includes('principal')" class="text-xs text-gray-400">
                <p>• Review pending applications</p>
                <p>• Monitor student admissions and papers</p>
              </div>
              <div v-else-if="props.userRoles.includes('teacher')" class="text-xs text-gray-400">
                <p>• Create papers to see your recent work</p>
                <p>• Handle admissions to see your activity</p>
              </div>

              <!-- Quick Action Buttons -->
              <div class="mt-4 space-y-2">
                <button v-if="props.userRoles.includes('superadmin')" @click="router.visit('/admin/schools')"
                  class="inline-flex items-center px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                  <i class="fas fa-plus mr-1"></i>
                  Add School
                </button>
                <button v-if="props.userRoles.includes('admin')" @click="router.visit('/admin/admissions')"
                  class="inline-flex items-center px-3 py-2 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                  <i class="fas fa-user-plus mr-1"></i>
                  Add Student
                </button>
                <button v-if="props.userRoles.includes('teacher')" @click="router.visit('/teacher/papers')"
                  class="inline-flex items-center px-3 py-2 text-xs font-medium text-purple-700 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                  <i class="fas fa-file-alt mr-1"></i>
                  Create Paper
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
</style>
