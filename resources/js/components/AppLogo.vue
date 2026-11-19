<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 p-1"
    >
        <AppLogoIcon class="size-6" />
    </div>
    <div
        class="ml-2 grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden"
    >
        <span
            class="truncate text-base font-bold tracking-tight text-foreground"
            >Trackly</span
        >
        <span
            class="truncate text-[10px] font-medium tracking-wider text-muted-foreground uppercase"
            >Job Tracker</span
        >
    </div>
</template>
