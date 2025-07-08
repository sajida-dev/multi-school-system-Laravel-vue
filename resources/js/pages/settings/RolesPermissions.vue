<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import Collapsible from '@/components/ui/collapsible/Collapsible.vue'
import CollapsibleTrigger from '@/components/ui/collapsible/CollapsibleTrigger.vue'
import CollapsibleContent from '@/components/ui/collapsible/CollapsibleContent.vue'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import Switch from '@/components/ui/switch/Switch.vue'
import { toast } from 'vue3-toastify'
import { Trash2, Plus } from 'lucide-vue-next'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import { type BreadcrumbItem } from '@/types';

interface Role {
    id: number
    name: string
    permissions: { id: number; name: string }[]
}
interface Permission {
    id: number
    name: string
}

const roles = ref<Role[]>([])
const permissions = ref<Permission[]>([])
const loading = ref(true)
const expandedRole = ref<number | null>(null)
const updating = ref<{ [key: string]: boolean }>({})
const updated = ref<{ [key: string]: boolean }>({})
const errorMsg = ref('')
const permissionForms = ref<{ [roleId: number]: any }>({})
const showAddRole = ref(false)
const showAddPermission = ref(false)
const showAddPermissionModal = ref(false)
const newRoleForm = useForm({ name: '' })
const newPermissionForm = useForm({ name: '', userName: '', phoneNumber: '' })
const deletingRoleId = ref<number | null>(null)
const deletingPermissionId = ref<number | null>(null)
const activeTab = ref<'roles' | 'permissions'>('roles')

const fetchRolesAndPermissions = async () => {
    loading.value = true
    const [rolesRes, permsRes] = await Promise.all([
        fetch('/admin/roles'),
        fetch('/admin/permissions'),
    ])
    roles.value = await rolesRes.json()
    permissions.value = await permsRes.json()
    loading.value = false
}

onMounted(fetchRolesAndPermissions)

const hasPermission = (role: Role, permId: number) => {
    return Array.isArray(role.permissions) && role.permissions.some(p => p.id === permId)
}

const getPermissionForm = (role: Role) => {
    if (!permissionForms.value[role.id]) {
        permissionForms.value[role.id] = useForm({
            name: role.name,
            permissions: Array.isArray(role.permissions) ? role.permissions.map(p => p.id) : []
        })
    }
    return permissionForms.value[role.id]
}

const togglePermission = async (role: Role, perm: Permission) => {
    const key = `${role.id}-${perm.id}`
    updating.value[key] = true
    errorMsg.value = ''

    const form = getPermissionForm(role)
    const hasPerm = hasPermission(role, perm.id)
    if (hasPerm) {
        form.permissions = form.permissions.filter((id: number) => id !== perm.id)
    } else {
        form.permissions = [...form.permissions, perm.id]
    }

    form.put(`/admin/roles/${role.id}`, {
        preserveScroll: true,
        onSuccess: (response: any) => {
            // Update the role in the local roles array
            if (response && response.props && response.props.role) {
                const updatedRole = response.props.role
                const idx = roles.value.findIndex(r => r.id === role.id)
                if (idx !== -1) {
                    roles.value[idx].permissions = updatedRole.permissions
                }
            } else {
                // fallback: update from form
                const idx = roles.value.findIndex(r => r.id === role.id)
                if (idx !== -1) {
                    roles.value[idx].permissions = permissions.value.filter(p => form.permissions.includes(p.id))
                }
            }
            if (!hasPerm) {
                toast.success(`'${capitalize(perm.name)}' assigned to '${capitalize(role.name)}'`)
            } else {
                toast.info(`'${capitalize(perm.name)}' removed from '${capitalize(role.name)}'`)
            }
            updating.value[key] = false
            updated.value[key] = true
            setTimeout(() => { updated.value[key] = false }, 1200)
        },
        onError: (errors: any) => {
            updating.value[key] = false
            errorMsg.value = 'Failed to update. Please refresh and try again.'
            toast.error('Failed to update permissions!')
        }
    })
}

function capitalize(str: string) {
    return str.charAt(0).toUpperCase() + str.slice(1)
}

const addRole = () => {
    newRoleForm.post('/admin/roles', {
        preserveScroll: true,
        onSuccess: (response) => {
            toast.success('Role added!')
            showAddRole.value = false
            newRoleForm.reset()
            if (response && response.props && response.props.role) {
                roles.value.push(response.props.role as Role)
            } else {
                fetch('/admin/roles').then(res => res.json()).then(data => { roles.value = data })
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message))
        }
    })
}
const addPermission = () => {
    newPermissionForm.post('/admin/permissions', {
        preserveScroll: true,
        onSuccess: (response) => {
            toast.success('Permission added!')
            showAddPermissionModal.value = false
            newPermissionForm.reset()
            if (response && response.props && response.props.permission) {
                permissions.value.push(response.props.permission as Permission)
            } else {
                fetch('/admin/permissions').then(res => res.json()).then(data => { permissions.value = data })
            }
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(message => toast.error(message))
        }
    })
}
const confirmDeleteRole = (id: number) => { deletingRoleId.value = id }
const confirmDeletePermission = (id: number) => { deletingPermissionId.value = id }
const deleteRole = () => {
    if (!deletingRoleId.value) return
    useForm<{ id: number; name: string }>({ id: deletingRoleId.value, name: '' }).delete(`/admin/roles/${deletingRoleId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Role deleted!')
            roles.value = roles.value.filter(r => r.id !== deletingRoleId.value)
            deletingRoleId.value = null
        },
        onError: () => toast.error('Failed to delete role!')
    })
}
const deletePermission = () => {
    if (!deletingPermissionId.value) return
    useForm<{ id: number; name: string }>({ id: deletingPermissionId.value, name: '' }).delete(`/admin/permissions/${deletingPermissionId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Permission deleted!')
            permissions.value = permissions.value.filter(p => p.id !== deletingPermissionId.value)
            deletingPermissionId.value = null
        },
        onError: () => toast.error('Failed to delete permission!')
    })
}


const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Roles and Permissions settings',
        href: '/settings/roles-permissions',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Roles & Permissions" />
        <SettingsLayout>
            <div class="max-w-2xl mx-auto w-full px-2 sm:px-4 md:px-0 py-8">
                <h1 class="text-2xl font-bold mb-6">Roles & Permissions</h1>
                <div class="mb-6 flex gap-4 border-b border-gray-200 dark:border-neutral-800">
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'roles' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'roles'">Roles</button>
                    <button
                        :class="['px-4 py-2 font-semibold', activeTab === 'permissions' ? 'border-b-2 border-primary text-primary' : 'text-gray-500']"
                        @click="activeTab = 'permissions'">Permissions</button>
                </div>
                <div v-if="activeTab === 'roles'">
                    <p class="mb-4 text-sm text-muted-foreground">Roles let you group permissions and assign them to
                        users. Expand a role to manage its permissions.</p>
                    <div class="flex justify-end mb-4">
                        <button @click="showAddRole = true"
                            class="flex items-center gap-2 px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition">
                            <Plus class="w-4 h-4" /> Add Role
                        </button>
                    </div>
                    <div v-if="showAddRole" class="mb-4">
                        <form @submit.prevent="addRole" class="flex gap-2 items-center">
                            <input v-model="newRoleForm.name" type="text" placeholder="Role name"
                                class="rounded border px-3 py-1 text-sm" />
                            <button type="submit" class="px-3 py-1 rounded bg-primary text-white">Add</button>
                            <button type="button" @click="showAddRole = false"
                                class="px-3 py-1 rounded bg-gray-200 dark:bg-neutral-700">Cancel</button>
                        </form>
                        <div v-if="newRoleForm.errors.name" class="text-xs text-red-600 mt-1">{{ newRoleForm.errors.name
                        }}</div>
                    </div>
                    <div v-if="loading" class="text-center py-8 text-muted-foreground">Loading roles...</div>
                    <div v-else class="space-y-4">
                        <Collapsible v-for="role in roles" :key="role.id">
                            <CollapsibleTrigger
                                class="w-full flex items-center justify-between px-4 py-3 rounded cursor-pointer bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition">
                                <span class="font-medium text-base">{{ capitalize(role.name) }}</span>
                                <div class="flex flex-row gap-3 items-center">
                                    <span class="text-xs text-muted-foreground">
                                        {{ Array.isArray(role.permissions) ? role.permissions.length : 0 }} permission{{
                                            Array.isArray(role.permissions) && role.permissions.length === 1 ? '' : 's' }}
                                    </span>
                                    <button v-if="!['admin', 'teacher', 'principal'].includes(role.name.toLowerCase())"
                                        @click.stop="confirmDeleteRole(role.id)"
                                        class="ml-2 p-1 rounded hover:bg-red-100 dark:hover:bg-red-900 transition">
                                        <Trash2 class="w-4 h-4 text-red-500" />
                                    </button>
                                </div>

                            </CollapsibleTrigger>
                            <CollapsibleContent
                                class="px-4 py-3 border-t border-gray-200 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900">
                                <div class="flex flex-col gap-2">
                                    <div v-for="perm in permissions" :key="perm.id" class="flex items-center gap-3">
                                        <Switch :checked="hasPermission(role, perm.id)"
                                            :disabled="updating[`${role.id}-${perm.id}`]"
                                            @update:checked="() => togglePermission(role, perm)" class="size-5" />
                                        <span class="text-sm">{{ capitalize(perm.name) }}</span>
                                        <span v-if="updating[`${role.id}-${perm.id}`]"
                                            class="text-xs text-muted-foreground ml-2">Updating...</span>
                                        <span v-else-if="updated[`${role.id}-${perm.id}`]"
                                            class="text-xs text-green-600 dark:text-green-400 ml-2">Updated!</span>
                                    </div>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    </div>
                </div>
                <div v-else-if="activeTab === 'permissions'">
                    <p class="mb-4 text-sm text-muted-foreground">Permissions control what users can do. Add, view, or
                        delete permissions here.</p>
                    <div class="flex justify-end mb-4">
                        <button @click="showAddPermissionModal = true"
                            class="flex items-center gap-2 px-3 py-1.5 rounded shadow bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 transition">
                            <Plus class="w-4 h-4" /> Add Permission
                        </button>
                    </div>
                    <Dialog v-model:open="showAddPermissionModal">
                        <DialogContent>
                            <DialogTitle>Add Permission</DialogTitle>
                            <form @submit.prevent="addPermission" class="flex flex-col gap-3">
                                <input v-model="newPermissionForm.name" type="text" placeholder="Permission name"
                                    class="rounded border px-3 py-2 text-sm" />
                                <div v-if="newPermissionForm.errors.name" class="text-xs text-red-600 mt-1">{{
                                    newPermissionForm.errors.name }}</div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showAddPermissionModal = false"
                                        class="px-3 py-1 rounded bg-gray-200 dark:bg-neutral-700">Cancel</button>
                                    <button type="submit" class="px-3 py-1 rounded bg-primary text-white">Add</button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                    <div v-if="loading" class="text-center py-8 text-muted-foreground">Loading permissions...</div>
                    <div v-else class="space-y-2">
                        <div v-for="perm in permissions" :key="perm.id"
                            class="flex items-center gap-3 bg-gray-50 dark:bg-neutral-900 rounded px-4 py-2">
                            <span class="text-sm flex-1">{{ capitalize(perm.name) }}</span>
                            <button @click.stop="confirmDeletePermission(perm.id)"
                                class="ml-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-900 transition">
                                <Trash2 class="w-4 h-4 text-red-500" />
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Delete Role Modal -->
                <div v-if="deletingRoleId !== null"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto">
                        <h3 class="text-lg font-semibold mb-4">Delete Role?</h3>
                        <p class="mb-4">Are you sure you want to delete this role? This action cannot be undone.</p>
                        <div class="flex gap-2 justify-end">
                            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-800"
                                @click="deletingRoleId = null">Cancel</button>
                            <button class="px-4 py-2 rounded bg-red-600 text-white" @click="deleteRole">Delete</button>
                        </div>
                    </div>
                </div>
                <!-- Delete Permission Modal -->
                <div v-if="deletingPermissionId !== null"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto">
                        <h3 class="text-lg font-semibold mb-4">Delete Permission?</h3>
                        <p class="mb-4">Are you sure you want to delete this permission? This action cannot be undone.
                        </p>
                        <div class="flex gap-2 justify-end">
                            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-800"
                                @click="deletingPermissionId = null">Cancel</button>
                            <button class="px-4 py-2 rounded bg-red-600 text-white"
                                @click="deletePermission">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>