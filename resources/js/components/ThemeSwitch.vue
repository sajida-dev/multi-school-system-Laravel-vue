<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{ compact?: boolean }>();

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div :class="['lg:inline-flex gap-1 hidden rounded-lg p-1 bg-gray-100 dark:bg-neutral-900']">
        <button v-for="{ value, Icon, label } in tabs" :key="value" @click="updateAppearance(value)" :class="[
            'flex items-center rounded-md transition-colors',
            compact
                ? 'px-2 py-1'
                : 'px-3.5 py-1.5',
            appearance === value
                ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
        ]" :aria-label="label" type="button">
            <component :is="Icon" class="h-4 w-4" />
            <span v-if="!compact" class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>