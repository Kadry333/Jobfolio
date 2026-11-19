<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { dashboard, logout } from '@/routes';

defineOptions({
    layout: {
        title: 'Email verification',
        description: 'Email verification is not required.',
    },
});
</script>

<template>
    <Head title="Email verification" />

    <div class="space-y-6 text-center">
        <p class="text-sm text-muted-foreground">
            Email verification is not required for your account. You have full
            access to your dashboard.
        </p>

        <div class="flex flex-col gap-3">
            <Button as-child>
                <Link :href="dashboard()"> Go to Dashboard </Link>
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Log out
            </TextLink>
        </div>
    </div>
</template>
