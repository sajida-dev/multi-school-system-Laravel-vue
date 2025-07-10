<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="User Management" />
        <SettingsLayout>
            <div class="max-w-4xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
                <h1 class="text-2xl font-bold mb-6">User Management</h1>
                <p class="mb-4 text-sm text-muted-foreground">Manage users and their roles. Search, filter, and assign
                    roles to users.</p>

                <!-- Filters -->
                <div class="mb-6 p-4 rounded shadow bg-gray-100 dark:bg-neutral-800">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search
                                Users</label>
                            <input v-model="filters.search" type="text" placeholder="Search by name or email..."
                                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                                @input="debouncedSearch" />
                        </div>
                        <div class="w-full sm:w-48">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by
                                Role</label>
                            <select v-model="filters.role"
                                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                                @change="applyFilters">
                                <option value="">All Roles</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                        <div class="w-full sm:w-32">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Per
                                Page</label>
                            <select v-model="filters.per_page"
                                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                                @change="applyFilters">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div
                    class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800">
                    <div class="p-6">
                        <div v-if="loading" class="text-center py-8 text-muted-foreground">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                            <p class="mt-2">Loading users...</p>
                        </div>

                        <div v-else-if="users.data && users.data.length === 0"
                            class="text-center py-8 text-muted-foreground">
                            <p>No users found</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-neutral-800">
                                        <th class="text-left py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                            Name</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                            Email</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                            Roles</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                            Joined</th>
                                        <th class="text-left py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                            Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="user in users.data" :key="user.id"
                                        class="border-b border-gray-200 dark:border-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-3">
                                                <div
                                                    class="h-8 w-8 rounded-full bg-gray-200 dark:bg-neutral-700 flex items-center justify-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ getInitials(user.name) }}
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ user.name
                                                    }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                                        <td class="py-3 px-4">
                                            <div class="space-y-1">
                                                <div class="flex flex-wrap gap-1.5 max-w-xs">
                                                    <span v-for="role in user.roles" :key="role.id" :class="[
                                                        'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shadow-sm',
                                                        role.name === 'admin' && isCurrentUser(user.id)
                                                            ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800'
                                                            : role.name === 'admin'
                                                                ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800'
                                                                : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800'
                                                    ]">
                                                        <span class="truncate max-w-16">{{ role.name }}</span>
                                                        <button v-if="!isCurrentUser(user.id) || role.name !== 'admin'"
                                                            @click="removeRole(user.id, role.id)"
                                                            class="ml-1.5 text-current hover:opacity-75 transition-opacity"
                                                            title="Remove role">
                                                            <svg class="w-3 h-3" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                        <span v-else class="ml-1.5 text-xs opacity-60"
                                                            title="Cannot remove your own admin role">
                                                            <svg class="w-3 h-3" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </span>
                                                    <span v-if="user.roles.length === 0"
                                                        class="text-gray-400 dark:text-gray-500 text-sm italic">No roles
                                                        assigned</span>
                                                </div>

                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <button @click="openAssignRoleModal(user)"
                                                class="px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition text-sm">
                                                Assign Role
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.data && users.data.length > 0" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                            </div>
                            <div class="flex space-x-2">
                                <button :disabled="!users.prev_page_url" @click="changePage(users.current_page - 1)"
                                    class="px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                    Previous
                                </button>
                                <button :disabled="!users.next_page_url" @click="changePage(users.current_page + 1)"
                                    class="px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assign Role Modal -->
                <Dialog v-model="showAssignModal">
                    <template #header>Assign Role</template>
                    <template #body>
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Select School</label>
                            <select v-model="assignRoleForm.school_id"
                                class="w-full px-2 py-1 rounded border dark:bg-neutral-800 dark:text-white">
                                <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Select Role</label>
                            <select v-model="assignRoleForm.role_id"
                                class="w-full px-2 py-1 rounded border dark:bg-neutral-800 dark:text-white">
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                            </select>
                        </div>
                    </template>
                    <template #footer>
                        <Button @click="assignRole"
                            :disabled="!assignRoleForm.school_id || !assignRoleForm.role_id || assigningRole">Assign</Button>
                        <Button variant="outline" @click="closeAssignRoleModal">Cancel</Button>
                    </template>
                </Dialog>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { toast } from 'vue3-toastify'
import { type BreadcrumbItem } from '@/types'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import Button from '@/components/ui/button/Button.vue'

// Props from Inertia
const props = defineProps<{
    users: any
    roles: any[]
    filters: {
        search: string
        role: string
        per_page: number
    }
    auth: {
        user: {
            id: number
            name: string
            email: string
        }
    }
}>()

// Reactive state
const loading = ref(false)
const showAssignModal = ref(false)
const selectedUser = ref<any>(null)
const selectedRoleId = ref<number | null>(null)
const assigningRole = ref(false)

// Filters state
const filters = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
    per_page: props.filters.per_page || 15
})

// Users data
const users = ref(props.users)
const roles = ref(props.roles)
const schools = computed(() => (usePage().props.schools as Array<{ id: number; name: string }>) || []);

const assignRoleForm = useForm({
    user_id: null as number | null,
    role_id: null as number | null,
    school_id: null as number | null,
});

// Check if user is current user
const isCurrentUser = (userId: number) => {
    return props.auth?.user?.id === userId
}

// Computed
const availableRoles = computed(() => {
    if (!selectedUser.value) return roles.value
    const userRoleIds = selectedUser.value.roles.map((r: any) => r.id)
    let filteredRoles = roles.value.filter(role => !userRoleIds.includes(role.id))

    // Prevent current user from assigning admin role to themselves
    if (isCurrentUser(selectedUser.value.id)) {
        filteredRoles = filteredRoles.filter(role => role.name !== 'admin')
    }

    return filteredRoles
})

// Debounced search
let searchTimeout: number
const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 300)
}

// Apply filters and fetch data
const applyFilters = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams({
            search: filters.search,
            role: filters.role,
            per_page: filters.per_page.toString(),
            page: '1' // Reset to first page when filtering
        })

        // Use Inertia to navigate with new filters
        router.get('/settings/users', Object.fromEntries(params))
    } catch (error) {
        console.error('Error applying filters:', error)
        toast.error('Failed to apply filters')
    } finally {
        loading.value = false
    }
}

// Change page
const changePage = async (page: number) => {
    loading.value = true
    try {
        const params = new URLSearchParams({
            search: filters.search,
            role: filters.role,
            per_page: filters.per_page.toString(),
            page: page.toString()
        })

        // Use Inertia to navigate to new page
        router.get('/settings/users', Object.fromEntries(params))
    } catch (error) {
        console.error('Error changing page:', error)
        toast.error('Failed to change page')
    } finally {
        loading.value = false
    }
}

// Open assign role modal
const openAssignRoleModal = (user: any) => {
    selectedUser.value = user
    selectedRoleId.value = null
    showAssignModal.value = true
}

// Assign role
const assignRole = () => {
    if (!assignRoleForm.school_id || !assignRoleForm.role_id || !selectedUser.value) return;

    assignRoleForm.user_id = selectedUser.value.id;
    assignRoleForm.school_id = assignRoleForm.school_id;
    assignRoleForm.role_id = assignRoleForm.role_id;

    assigningRole.value = true;
    assignRoleForm.post('/settings/user-management/assign-role', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Role assigned successfully');
            // Update local state
            const userIndex = users.value.data.findIndex((u: any) => u.id === selectedUser.value.id);
            const assignedRole = roles.value.find((r: any) => r.id === assignRoleForm.role_id);
            if (userIndex !== -1 && assignedRole) {
                if (!users.value.data[userIndex].roles.some((r: any) => r.id === assignedRole.id)) {
                    users.value.data[userIndex].roles.push(assignedRole);
                }
            }
            closeAssignRoleModal();
            selectedUser.value = null;
            assignRoleForm.school_id = null;
            assignRoleForm.role_id = null;
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message));
        },
        onFinish: () => {
            assignRoleForm.reset();
            assignRoleForm.user_id = null;
            assignRoleForm.school_id = null;
            assignRoleForm.role_id = null;
        }
    });
};

// Remove role
const removeRoleForm = useForm({
    user_id: null as number | null,
    role_id: null as number | null,
});

const removeRole = (userId: number, roleId: number) => {
    removeRoleForm.user_id = userId;
    removeRoleForm.role_id = roleId;

    removeRoleForm.delete('/settings/user-management/remove-role', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Role removed successfully');
            // Update local state
            const userIndex = users.value.data.findIndex((u: any) => u.id === userId);
            if (userIndex !== -1) {
                users.value.data[userIndex].roles = users.value.data[userIndex].roles.filter((r: any) => r.id !== roleId);
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message));
        },
        onFinish: () => {
            removeRoleForm.reset();
            removeRoleForm.user_id = null;
            removeRoleForm.role_id = null;
        }
    });
};

// Utility functions
const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(word => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'User management',
        href: '/settings/users',
    },
];

const closeAssignRoleModal = () => {
    assignRoleForm.reset();
    showAssignModal.value = false;
};
</script>