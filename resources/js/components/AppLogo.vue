<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-br from-blue-700 to-purple-800 text-white">
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">EDU Champ</span>
        <span class="truncate text-xs text-muted-foreground">Multi-School System</span>
    </div>
</template>
