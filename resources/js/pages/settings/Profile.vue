<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Update your profile name"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="firstName">First name</Label>
                    <Input
                        id="firstName"
                        class="mt-1 block w-full"
                        name="firstName"
                        :default-value="user.firstName"
                        required
                        autocomplete="given-name"
                        placeholder="First name"
                    />
                    <InputError class="mt-2" :message="errors.firstName" />
                </div>

                <div class="grid gap-2">
                    <Label for="lastName">Last name</Label>
                    <Input
                        id="lastName"
                        class="mt-1 block w-full"
                        name="lastName"
                        :default-value="user.lastName"
                        required
                        autocomplete="family-name"
                        placeholder="Last name"
                    />
                    <InputError class="mt-2" :message="errors.lastName" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-muted/60 cursor-not-allowed opacity-80"
                    :value="user.email"
                    disabled
                    readonly
                />
                <p class="text-xs text-muted-foreground">Email address cannot be changed.</p>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button">
                    Save
                </Button>
            </div>
        </Form>
    </div>
</template>
