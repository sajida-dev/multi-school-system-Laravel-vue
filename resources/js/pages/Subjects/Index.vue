<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Subjects" />
        <div class="max-w-3xl mx-auto py-8">
            <h1 class="text-2xl font-bold mb-4">Subjects</h1>
            <form @submit.prevent="submit" class="mb-6 flex flex-col gap-2">
                <input v-model="form.name" placeholder="Subject Name" required class="input" />
                <input v-model="form.code" placeholder="Code" class="input" />
                <textarea v-model="form.description" placeholder="Description" class="input"></textarea>
                <div class="flex gap-2">
                    <Button type="submit">{{ form.id ? 'Update' : 'Add' }} Subject</Button>
                    <Button v-if="form.id" type="button" @click="resetForm">Cancel</Button>
                </div>
            </form>
            <ul class="mt-6">
                <li v-for="subject in subjects" :key="(subject as any).id"
                    class="flex justify-between items-center py-2 border-b">
                    <span>{{ (subject as any).name }} <span v-if="(subject as any).code">({{ (subject as any).code
                            }})</span></span>
                    <div>
                        <Button @click="edit(subject as any)">Edit</Button>
                        <Button variant="destructive" @click="remove((subject as any).id)">Delete</Button>
                    </div>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { BreadcrumbItem } from '@/types'

const props = defineProps({ subjects: Array })
const subjects = ref(props.subjects)
const form = useForm({ id: null, name: '', code: '', description: '' })
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Subjects', href: '/subjects' }
]

function submit() {
    if (form.id) {
        form.put(`/subjects/${form.id}`, {
            onSuccess: () => resetForm()
        })
    } else {
        form.post('/subjects', {
            onSuccess: () => resetForm()
        })
    }
}
function edit(subject: any) {
    form.id = subject.id
    form.name = subject.name
    form.code = subject.code
    form.description = subject.description
}
function remove(id: number) {
    if (confirm('Delete this subject?')) {
        router.delete(`/subjects/${id}`)
    }
}
function resetForm() {
    form.id = null
    form.name = ''
    form.code = ''
    form.description = ''
}
</script>