<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Globe, Mail, Plus, Trash2, Users } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import type {
    Cv,
    JobContactStatus,
    TrackedJob,
    TrackedJobStatus,
} from '@/types';

interface EditableContact {
    id?: number;
    name: string;
    email: string;
    position: string;
    status: JobContactStatus;
    notes: string;
    linkedin_url: string;
}

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
    contactStatuses?: JobContactStatus[];
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

const contacts = ref<EditableContact[]>(
    props.job.contacts && props.job.contacts.length > 0
        ? props.job.contacts.map((c) => ({
              id: c.id,
              name: c.name || '',
              email: c.email || '',
              position: c.position || '',
              status: c.status || 'will contact',
              notes: c.notes || '',
              linkedin_url: c.linkedin_url || '',
          }))
        : [],
);

function addContact() {
    contacts.value.push({
        name: '',
        email: '',
        position: '',
        status: 'will contact',
        notes: '',
        linkedin_url: '',
    });
}

function removeContact(index: number) {
    contacts.value.splice(index, 1);
}

function getContactError(index: number, field: string): string | undefined {
    return errors.value[`contacts.${index}.${field}`];
}

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
            contacts: contacts.value,
        },
        {
            onError: (errs) => {
                errors.value = errs;
                processing.value = false;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`Edit ${job.jobTitle} - jobfolio`" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="space-y-2">
            <Link
                href="/jobs"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-3.5" />
                <span>Back to applications</span>
            </Link>

            <h1
                class="text-2xl font-bold tracking-tight text-foreground md:text-3xl"
            >
                Edit Application
            </h1>
            <p class="text-sm text-muted-foreground">
                Update the details for {{ job.jobTitle }} at
                {{ job.companyName }}.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base font-semibold">
                        Application Details
                    </CardTitle>
                    <CardDescription>
                        Edit role information, status, and company profile.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="jobTitle">Job Title *</Label>
                            <Input
                                id="jobTitle"
                                v-model="form.jobTitle"
                                placeholder="e.g. Senior Frontend Engineer"
                                required
                            />
                            <span
                                v-if="errors.jobTitle"
                                class="text-xs text-destructive"
                            >
                                {{ errors.jobTitle }}
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="companyName">Company Name *</Label>
                            <Input
                                id="companyName"
                                v-model="form.companyName"
                                placeholder="e.g. Acme Inc."
                                required
                            />
                            <span
                                v-if="errors.companyName"
                                class="text-xs text-destructive"
                            >
                                {{ errors.companyName }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="status">Current Status *</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                required
                            >
                                <option
                                    v-for="status in statuses"
                                    :key="status"
                                    :value="status"
                                >
                                    {{
                                        status.charAt(0).toUpperCase() +
                                        status.slice(1)
                                    }}
                                </option>
                            </select>
                            <span
                                v-if="errors.status"
                                class="text-xs text-destructive"
                            >
                                {{ errors.status }}
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="appliedAt">Date Applied</Label>
                            <Input
                                id="appliedAt"
                                type="date"
                                v-model="form.appliedAt"
                            />
                            <span
                                v-if="errors.appliedAt"
                                class="text-xs text-destructive"
                            >
                                {{ errors.appliedAt }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="jobURL">Posting URL / Contact Link</Label>
                        <Input
                            id="jobURL"
                            type="text"
                            v-model="form.jobURL"
                            placeholder="https://jobs.example.com/posting/123 or +201234567890"
                        />
                        <span
                            v-if="errors.jobURL"
                            class="text-xs text-destructive"
                        >
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
                            <option
                                v-for="cv in cvs"
                                :key="cv.id"
                                :value="String(cv.id)"
                            >
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
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- People to Contact / Contacts -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-3"
                >
                    <div class="space-y-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Users class="size-4 text-primary" />
                            People to Contact (Optional)
                        </CardTitle>
                        <CardDescription>
                            Add recruiters, referrals, or hiring managers you
                            want to reach out to about this role.
                        </CardDescription>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="addContact"
                        class="h-8 gap-1.5 text-xs"
                    >
                        <Plus class="size-3.5" />
                        <span>Add Person</span>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-if="contacts.length === 0"
                        class="flex flex-col items-center justify-center space-y-2 rounded-lg border border-dashed bg-muted/10 p-6 text-center"
                    >
                        <Users class="size-8 text-muted-foreground/60" />
                        <div class="max-w-sm text-xs text-muted-foreground">
                            No contacts added yet. Click
                            <span class="font-medium text-foreground"
                                >Add Person</span
                            >
                            to track recruiters, engineering managers, or
                            interviewers for this job.
                        </div>
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="addContact"
                            class="mt-1 h-8 gap-1 text-xs"
                        >
                            <Plus class="size-3" />
                            <span>Add First Contact</span>
                        </Button>
                    </div>

                    <div
                        v-for="(contact, index) in contacts"
                        :key="index"
                        class="group relative space-y-3 rounded-lg border bg-card/60 p-4"
                    >
                        <div
                            class="flex items-center justify-between border-b pb-1"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-semibold text-foreground"
                                >
                                    Contact #{{ index + 1 }}
                                </span>
                                <span
                                    v-if="contact.name"
                                    class="text-xs text-muted-foreground"
                                >
                                    — {{ contact.name }}
                                </span>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="removeContact(index)"
                                class="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
                                title="Remove contact"
                            >
                                <Trash2 class="size-3.5" />
                            </Button>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <Label
                                    :for="`contact-name-${index}`"
                                    class="text-xs"
                                >
                                    Full Name *
                                </Label>
                                <input
                                    :id="`contact-name-${index}`"
                                    v-model="contact.name"
                                    placeholder="e.g. Sarah Miller"
                                    class="flex h-8 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs shadow-2xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    required
                                />
                                <span
                                    v-if="getContactError(index, 'name')"
                                    class="text-[10px] text-destructive"
                                >
                                    {{ getContactError(index, 'name') }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <Label
                                    :for="`contact-position-${index}`"
                                    class="text-xs"
                                >
                                    Role / Title
                                </Label>
                                <input
                                    :id="`contact-position-${index}`"
                                    v-model="contact.position"
                                    placeholder="e.g. Senior Tech Recruiter"
                                    class="flex h-8 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs shadow-2xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <span
                                    v-if="getContactError(index, 'position')"
                                    class="text-[10px] text-destructive"
                                >
                                    {{ getContactError(index, 'position') }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <Label
                                    :for="`contact-email-${index}`"
                                    class="text-xs"
                                >
                                    Email Address
                                </Label>
                                <div class="relative">
                                    <input
                                        :id="`contact-email-${index}`"
                                        type="email"
                                        v-model="contact.email"
                                        placeholder="sarah@company.com"
                                        class="flex h-8 w-full rounded-md border border-input bg-transparent py-1 pr-3 pl-7 text-xs shadow-2xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    />
                                    <Mail
                                        class="absolute top-2.5 left-2 size-3.5 text-muted-foreground"
                                    />
                                </div>
                                <span
                                    v-if="getContactError(index, 'email')"
                                    class="text-[10px] text-destructive"
                                >
                                    {{ getContactError(index, 'email') }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <Label
                                    :for="`contact-status-${index}`"
                                    class="text-xs"
                                >
                                    Outreach Status *
                                </Label>
                                <select
                                    :id="`contact-status-${index}`"
                                    v-model="contact.status"
                                    class="flex h-8 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs capitalize shadow-2xs focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option
                                        value="will contact"
                                        class="bg-background text-foreground capitalize"
                                    >
                                        will contact
                                    </option>
                                    <option
                                        value="message sent"
                                        class="bg-background text-foreground capitalize"
                                    >
                                        message sent
                                    </option>
                                    <option
                                        value="replied"
                                        class="bg-background text-foreground capitalize"
                                    >
                                        replied
                                    </option>
                                    <option
                                        value="no response"
                                        class="bg-background text-foreground capitalize"
                                    >
                                        no response
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <Label
                                    :for="`contact-linkedin-${index}`"
                                    class="text-xs"
                                >
                                    LinkedIn Profile URL
                                </Label>
                                <div class="relative">
                                    <input
                                        :id="`contact-linkedin-${index}`"
                                        v-model="contact.linkedin_url"
                                        placeholder="https://linkedin.com/in/username"
                                        class="flex h-8 w-full rounded-md border border-input bg-transparent py-1 pr-3 pl-7 text-xs shadow-2xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    />
                                    <Globe
                                        class="absolute top-2.5 left-2 size-3.5 text-muted-foreground"
                                    />
                                </div>
                                <span
                                    v-if="
                                        getContactError(index, 'linkedin_url')
                                    "
                                    class="text-[10px] text-destructive"
                                >
                                    {{ getContactError(index, 'linkedin_url') }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <Label
                                    :for="`contact-notes-${index}`"
                                    class="text-xs"
                                >
                                    Notes / Talking Points
                                </Label>
                                <input
                                    :id="`contact-notes-${index}`"
                                    v-model="contact.notes"
                                    placeholder="e.g. Connected via alumni network"
                                    class="flex h-8 w-full rounded-md border border-input bg-transparent px-3 py-1 text-xs shadow-2xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <span
                                    v-if="getContactError(index, 'notes')"
                                    class="text-[10px] text-destructive"
                                >
                                    {{ getContactError(index, 'notes') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-3">
                <Link
                    href="/jobs"
                    class="inline-flex h-9 items-center justify-center rounded-md border border-input px-4 text-sm font-medium transition-colors hover:bg-muted"
                >
                    Cancel
                </Link>
                <Button type="submit" :disabled="processing">
                    Save Changes
                </Button>
            </div>
        </form>
    </div>
</template>
