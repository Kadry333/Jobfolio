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
    router.flushAll();
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
                variant="destructive"
                size="sm"
                as-child
                class="h-9 gap-1.5 px-3 text-xs font-semibold shadow-xs"
            >
                <Link
                    :href="logout()"
                    as="button"
                    @click="handleLogout"
                    data-test="header-logout-button"
                    title="Log out of jobfolio"
                >
                    <LogOut class="size-3.5" />
                    <span class="hidden sm:inline">Log out</span>
                </Link>
            </Button>
        </div>
    </header>
</template>
