<script setup lang="ts">
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import { computed } from 'vue'

const props = defineProps<{
    modelValue: boolean
    title?: string
    message: string
    confirmText?: string
    cancelText?: string
}>()
const emit = defineEmits(['update:modelValue', 'confirm'])

const open = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
})

const close = () => emit('update:modelValue', false)
const confirm = () => {
    emit('confirm')
    close()
}
</script>

<template>
    <Dialog :open="open" @update:open="open = $event">
        <DialogContent>
            <DialogTitle>{{ props.title || 'Notice' }}</DialogTitle>
            <div class="my-4 whitespace-pre-line">{{ props.message }}</div>
            <DialogFooter>
                <template v-if="props.confirmText && props.cancelText">
                    <button class="btn btn-danger w-full sm:w-auto" @click="confirm">{{ props.confirmText }}</button>
                    <button class="btn btn-secondary w-full sm:w-auto" @click="close">{{ props.cancelText }}</button>
                </template>
                <template v-else>
                    <button class="btn btn-primary w-full" @click="close">OK</button>
                </template>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>