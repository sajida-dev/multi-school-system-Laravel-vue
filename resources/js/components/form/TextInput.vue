<template>
    <div>
        <label :for="id" class="block text-gray-700 dark:text-gray-200">
            {{ label }}<span v-if="required" class="text-red-500">*</span>
        </label>
        <input :id="id" :type="type" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
            :required="required" class="input input-bordered w-full dark:bg-gray-800 dark:text-gray-100"
            v-bind="$attrs" />
    </div>
</template>
<script setup>
defineProps({
    label: String,
    modelValue: [String, Number],
    required: Boolean,
    type: { type: String, default: 'text' },
    id: { type: String, default: () => Math.random().toString(36).substr(2, 9) }
});
defineEmits(['update:modelValue']);
</script>