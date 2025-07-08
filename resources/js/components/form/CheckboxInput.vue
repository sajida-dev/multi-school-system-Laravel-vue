<template>
    <div class="flex items-center">
        <input :id="id" type="checkbox" :checked="modelValue"
            @change="$emit('update:modelValue', $event.target.checked)" class="form-checkbox mr-2" v-bind="$attrs" />
        <label :for="id" class="text-gray-700 dark:text-gray-200">
            {{ label }}<span v-if="required" class="text-red-500">*</span>
        </label>
    </div>
</template>
<script setup>
defineProps({
    label: String,
    modelValue: Boolean,
    required: Boolean,
    id: { type: String, default: () => Math.random().toString(36).substr(2, 9) }
});
defineEmits(['update:modelValue']);
</script>