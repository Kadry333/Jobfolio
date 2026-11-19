<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import type { Cv, TrackedJob, TrackedJobStatus } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Applications',
                href: '/jobs',
            },
            {
                title: 'Edit Application',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    job: TrackedJob;
    cvs: Cv[];
    statuses: TrackedJobStatus[];
}>();

const form = ref({
    jobTitle: props.job.jobTitle,
    companyName: props.job.companyName,
    jobURL: props.job.jobURL || '',
    status: props.job.status,
    cv_id: props.job.cv_id ? String(props.job.cv_id) : '',
    notes: props.job.notes || '',
    appliedAt: props.job.appliedAt ? props.job.appliedAt.split('T')[0] : '',
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});

function submit() {
    processing.value = true;
    errors.value = {};

    router.patch(
        `/jobs/${props.job.id}`,
        {
            jobTitle: form.value.jobTitle,
            companyName: form.value.companyName,
            jobURL: form.value.jobURL || null,
            status: form.value.status,
            cv_id: form.value.cv_id ? Number(form.value.cv_id) : null,
            notes: form.value.notes || null,
            appliedAt: form.value.appliedAt || null,
        },
        {
            onError: (errs) => {
                errors.value = errs;
                processing.value = false;
            },
            onFinish: () => {
                processing.value = false;
            },
        }
    );
}
</script>

<template>
    <Head :title="`Edit ${job.jobTitle} - Trackly`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
        <div class="space-y-2">
            <Link
                :href="`/jobs/${job.id}`"
                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors font-medium"
            >
                <ArrowLeft class="size-3.5" />
                <span>Back to application details</span>
            </Link>

            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                Edit Application
            </h1>
            <p class="text-sm text-muted-foreground">
                Update details for {{ job.jobTitle }} at {{ job.companyName }}.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Role Information</CardTitle>
                    <CardDescription>Primary application fields.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="jobTitle">Job Title *</Label>
                            <Input
                                id="jobTitle"
                                v-model="form.jobTitle"
                                required
                            />
                            <span v-if="errors.jobTitle" class="text-xs text-destructive">
                                {{ errors.jobTitle }}
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="companyName">Company *</Label>
                            <Input
                                id="companyName"
                                v-model="form.companyName"
                                required
                            />
                            <span v-if="errors.companyName" class="text-xs text-destructive">
                                {{ errors.companyName }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="status">Current Status *</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring capitalize"
                            >
                                <option v-for="s in statuses" :key="s" :value="s">
                                    {{ s }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="appliedAt">Date Applied</Label>
                            <Input
                                id="appliedAt"
                                type="date"
                                v-model="form.appliedAt"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="jobURL">Job Posting URL</Label>
                        <Input
                            id="jobURL"
                            type="url"
                            v-model="form.jobURL"
                            placeholder="https://..."
                        />
                        <span v-if="errors.jobURL" class="text-xs text-destructive">
                            {{ errors.jobURL }}
                        </span>
                    </div>

                    <div class="space-y-1.5" v-if="cvs.length > 0">
                        <Label for="cv_id">Attached CV / Resume</Label>
                        <select
                            id="cv_id"
                            v-model="form.cv_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                        >
                            <option value="">None</option>
                            <option v-for="cv in cvs" :key="cv.id" :value="String(cv.id)">
                                {{ cv.name || 'Resume #' + cv.id }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="notes">Notes & Observations</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="Interview rounds, recruiter feedback, salary info..."
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                    </div>
                </CardContent>
                <CardFooter class="flex items-center justify-end gap-3 pt-2">
                    <Link
                        :href="`/jobs/${job.id}`"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input h-9 px-4 hover:bg-muted transition-colors"
                    >
                        Cancel
                    </Link>
                    <Button type="submit" :disabled="processing">
                        Save Changes
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
