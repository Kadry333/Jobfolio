<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10"
    >
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
            <ThemeToggle />
        </div>

        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2.5 self-center text-xl font-bold tracking-tight text-foreground"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 p-1.5 shadow-xs ring-1 ring-sky-500/20"
                >
                    <AppLogoIcon class="size-7" />
                </div>
                <span>jobfolio</span>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-xl">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
