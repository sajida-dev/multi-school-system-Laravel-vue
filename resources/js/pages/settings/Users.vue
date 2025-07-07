<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import { toast } from 'vue3-toastify';

interface User {
    id: number;
    name: string;
    email: string;
    roles: { id: number; name: string }[];
}
interface Role {
    id: number;
    name: string;
}

const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const loading = ref(true);
const showRoleModal = ref(false);
const selectedUser = ref<User | null>(null);
const selectedRoles = ref<number[]>([]);

const fetchUsersAndRoles = async () => {
    loading.value = true;
    const [usersRes, rolesRes] = await Promise.all([
        fetch('/admin/users'),
        fetch('/admin/roles'),
    ]);
    users.value = await usersRes.json();
    roles.value = await rolesRes.json();
    loading.value = false;
};

onMounted(fetchUsersAndRoles);

const openRoleModal = (user: User) => {
    selectedUser.value = user;
    selectedRoles.value = user.roles.map(r => r.id);
    showRoleModal.value = true;
};

const saveRoles = async () => {
    if (!selectedUser.value) return;
    const res = await fetch(`/admin/users/${selectedUser.value.id}/roles`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ roles: selectedRoles.value }),
    });
    if (res.ok) {
        toast.success(`Roles updated for ${selectedUser.value.name}!`);
        // Update local user roles
        const updatedRoles = roles.value.filter(r => selectedRoles.value.includes(r.id));
        const idx = users.value.findIndex(u => u.id === selectedUser.value!.id);
        if (idx !== -1) users.value[idx].roles = updatedRoles;
        showRoleModal.value = false;
    } else {
        toast.error('Failed to update roles!');
    }
};
</script>

<template>
    <AppLayout>

        <Head title="User Management" />
        <SettingsLayout>
            <div class="max-w-3xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
                <h1 class="text-2xl font-bold mb-6">User Management</h1>
                <div class="bg-white dark:bg-neutral-900 rounded-lg p-6">
                    <div v-if="loading" class="text-center py-8 text-muted-foreground">Loading users...</div>
                    <table v-else class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="py-2 px-4">Name</th>
                                <th class="py-2 px-4">Email</th>
                                <th class="py-2 px-4">Roles</th>
                                <th class="py-2 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td class="py-2 px-4">{{ user.name }}</td>
                                <td class="py-2 px-4">{{ user.email }}</td>
                                <td class="py-2 px-4">
                                    <span v-for="role in user.roles" :key="role.id"
                                        class="inline-block bg-gray-200 dark:bg-neutral-700 rounded px-2 py-1 text-xs mr-1">{{
                                        role.name }}</span>
                                </td>
                                <td class="py-2 px-4">
                                    <button @click="openRoleModal(user)"
                                        class="px-3 py-1 rounded bg-primary text-white text-sm">Edit Roles</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Dialog v-model:open="showRoleModal">
                    <DialogContent :title="selectedUser ? `Assign Roles to ${selectedUser.name}` : ''">
                        <form @submit.prevent="saveRoles" class="flex flex-col gap-3">
                            <div v-for="role in roles" :key="role.id" class="flex items-center gap-2">
                                <input type="checkbox" :id="`role-${role.id}`" :value="role.id"
                                    v-model="selectedRoles" />
                                <label :for="`role-${role.id}`">{{ role.name }}</label>
                            </div>
                            <div class="flex gap-2 justify-end mt-4">
                                <button type="button" @click="showRoleModal = false"
                                    class="px-3 py-1 rounded bg-gray-200 dark:bg-neutral-700">Cancel</button>
                                <button type="submit" class="px-3 py-1 rounded bg-primary text-white">Save</button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>