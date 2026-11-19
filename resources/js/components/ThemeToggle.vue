<script setup lang="ts">
import { Check, Monitor, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

const currentIcon = computed(() => {
    if (appearance.value === 'system') return Monitor;
    return resolvedAppearance.value === 'dark' ? Moon : Sun;
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="size-8 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent/60 transition-colors"
                title="Change theme (Light / Dark / System)"
                aria-label="Change theme"
            >
                <component :is="currentIcon" class="size-4 transition-transform duration-200" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-36">
            <DropdownMenuItem
                class="flex items-center justify-between cursor-pointer text-xs"
                @click="updateAppearance('light')"
            >
                <div class="flex items-center gap-2">
                    <Sun class="size-3.5 text-amber-500" />
                    <span>Light</span>
                </div>
                <Check v-if="appearance === 'light'" class="size-3.5 text-primary" />
            </DropdownMenuItem>

            <DropdownMenuItem
                class="flex items-center justify-between cursor-pointer text-xs"
                @click="updateAppearance('dark')"
            >
                <div class="flex items-center gap-2">
                    <Moon class="size-3.5 text-indigo-400" />
                    <span>Dark</span>
                </div>
                <Check v-if="appearance === 'dark'" class="size-3.5 text-primary" />
            </DropdownMenuItem>

            <DropdownMenuItem
                class="flex items-center justify-between cursor-pointer text-xs"
                @click="updateAppearance('system')"
            >
                <div class="flex items-center gap-2">
                    <Monitor class="size-3.5 text-muted-foreground" />
                    <span>System</span>
                </div>
                <Check v-if="appearance === 'system'" class="size-3.5 text-primary" />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
