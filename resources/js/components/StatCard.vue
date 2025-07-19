<script setup lang="ts">
import { ArrowUpRight } from 'lucide-vue-next';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: [Object, Function], default: () => ArrowUpRight },
});
</script>

<template>
  <div class="bg-gray-50 rounded-xl shadow-sm p-5 flex items-center justify-between min-w-[150px]">
    <div>
      <div class="text-xs text-gray-400 font-medium mb-1">{{ label }}</div>
      <div class="text-2xl font-bold text-gray-700">
        <span v-if="label === 'Growth'">
          <span class="font-bold">{{ value }}</span>
        </span>
        <span v-else>{{ value }}</span>
      </div>
    </div>
    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600">
      <component :is="icon" class="w-5 h-5 text-white" />
    </div>
  </div>
</template> 