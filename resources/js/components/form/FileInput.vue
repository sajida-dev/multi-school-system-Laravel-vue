<template>
    <div>
        <label :for="id" class="block text-gray-700 dark:text-gray-200">
            {{ label }}<span v-if="required" class="text-red-500">*</span>
        </label>
        <input :id="id" type="file" @change="onFileChange" :required="required"
            class="input input-bordered w-full dark:bg-gray-800 dark:text-gray-100" v-bind="$attrs" />
    </div>
</template>
<script setup>
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
    label: String,
    modelValue: [File, null],
    required: Boolean,
    id: { type: String, default: () => Math.random().toString(36).substr(2, 9) }
});
function onFileChange(e) {
    emit('update:modelValue', e.target.files[0] || null);
}
</script>