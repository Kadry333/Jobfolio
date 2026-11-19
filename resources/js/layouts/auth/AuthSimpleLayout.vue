<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
            <ThemeToggle />
        </div>

        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 group"
                    >
                        <div
                            class="mb-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/10 p-2 shadow-xs ring-1 ring-sky-500/20 transition-transform group-hover:scale-105"
                        >
                            <AppLogoIcon class="size-8" />
                        </div>
                        <span class="text-2xl font-bold tracking-tight text-foreground">Trackly</span>
                    </Link>
                    <div class="space-y-1.5 text-center">
                        <h1 class="text-xl font-semibold tracking-tight text-foreground">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
