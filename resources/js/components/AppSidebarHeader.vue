<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from '@lucide/vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { logout } from '@/routes';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const handleLogout = () => {
    router.post(
        logout(),
        {},
        {
            onFinish: () => router.flushAll(),
        },
    );
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex items-center gap-2">
            <ThemeToggle />
            <Button
                type="button"
                size="sm"
                @click="handleLogout"
                class="h-9 cursor-pointer gap-1.5 border border-red-600 bg-red-600 px-3 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-red-700 active:bg-red-800 dark:border-rose-900/60 dark:bg-rose-950/80 dark:text-rose-200 dark:hover:bg-rose-900/90 dark:hover:text-white"
                data-test="header-logout-button"
                title="Log out of jobfolio"
            >
                <LogOut class="size-3.5" />
                <span class="hidden sm:inline">Log out</span>
            </Button>
        </div>
    </header>
</template>
