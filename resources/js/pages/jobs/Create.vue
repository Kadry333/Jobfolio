<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    FileText,
    Globe,
    Mail,
    Plus,
    Trash2,
    UploadCloud,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import type {
    ContactDraft,
    Cv,
    JobContactStatus,
    TrackedJobStatus,
} from '@/types';

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
                title: 'New Application',
                href: '/jobs/create',
            },
        ],
    },
});

const props = defineProps<{
    cvs: Cv[];
    defaultCvId: number | null;
    statuses: TrackedJobStatus[];
    contactStatuses?: JobContactStatus[];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const defaultCv = computed(() => {
    return props.cvs.find((c) => c.id === props.defaultCvId) || null;
});

const contactStatusesList = computed<JobContactStatus[]>(() => {
    return props.contactStatuses && props.contactStatuses.length > 0
        ? props.contactStatuses
        : ['will contact', 'message sent', 'replied', 'no response'];
});

const initialCvSelection = computed<'default' | 'existing' | 'new' | 'none'>(
    () => {
        if (props.defaultCvId) return 'default';
        if (props.cvs.length > 0) return 'existing';
        return 'none';
    },
);

const form = ref({
    jobTitle: '',
    companyName: '',
    jobURL: '',
    status: 'will apply' as TrackedJobStatus,
    cv_selection: initialCvSelection.value,
    cv_id: props.defaultCvId
        ? String(props.defaultCvId)
        : props.cvs[0]
          ? String(props.cvs[0].id)
          : '',
    new_cv_name: '',
    make_default: false,
    notes: '',
    appliedAt: new Date().toISOString().split('T')[0],
});

const contacts = ref<ContactDraft[]>([]);

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

const newCvFile = ref<File | null>(null);
const processing = ref(false);
const errors = ref<Record<string, string>>({});

function onNewCvFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        newCvFile.value = target.files[0];
        if (!form.value.new_cv_name) {
            form.value.new_cv_name = target.files[0].name.replace(
                /\.[^/.]+$/,
                '',
            );
        }
    }
}

function submit() {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('jobTitle', form.value.jobTitle);
    formData.append('companyName', form.value.companyName);
    if (form.value.jobURL) formData.append('jobURL', form.value.jobURL);
    formData.append('status', form.value.status);
    formData.append('cv_selection', form.value.cv_selection);

    if (form.value.cv_selection === 'existing' && form.value.cv_id) {
        formData.append('cv_id', form.value.cv_id);
    }

    if (form.value.cv_selection === 'new' && newCvFile.value) {
        formData.append('new_cv_file', newCvFile.value);
        if (form.value.new_cv_name)
            formData.append('new_cv_name', form.value.new_cv_name);
        if (form.value.make_default) formData.append('make_default', '1');
    }

    if (form.value.notes) formData.append('notes', form.value.notes);
    if (form.value.appliedAt)
        formData.append('appliedAt', form.value.appliedAt);

    contacts.value.forEach((c, idx) => {
        if (!c.name.trim()) return;
        formData.append(`contacts[${idx}][name]`, c.name.trim());
        if (c.email?.trim())
            formData.append(`contacts[${idx}][email]`, c.email.trim());
        if (c.position?.trim())
            formData.append(`contacts[${idx}][position]`, c.position.trim());
        formData.append(`contacts[${idx}][status]`, c.status || 'will contact');
        if (c.notes?.trim())
            formData.append(`contacts[${idx}][notes]`, c.notes.trim());
        if (c.linkedin_url?.trim())
            formData.append(
                `contacts[${idx}][linkedin_url]`,
                c.linkedin_url.trim(),
            );
    });

    router.post('/jobs', formData, {
        onSuccess: () => {
            toast.success('Job application added successfully!');
        },
        onError: (errs) => {
            errors.value = errs;
            processing.value = false;
            toast.error('Please fix the errors in the form before submitting.');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Add Job Application - Trackly" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Back Link & Header -->
        <div class="space-y-2">
            <Link
                href="/jobs"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-3.5" />
                <span>Back to all applications</span>
            </Link>

            <h1
                class="text-2xl font-bold tracking-tight text-foreground md:text-3xl"
            >
                Add Job Application
            </h1>
            <p class="text-sm text-muted-foreground">
                Record a new opportunity you are targeting or have applied for.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Job Details -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base"
                        >Role & Company Information</CardTitle
                    >
                    <CardDescription
                        >Primary job details for this opening.</CardDescription
                    >
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="jobTitle">Job Title *</Label>
                            <Input
                                id="jobTitle"
                                v-model="form.jobTitle"
                                placeholder="e.g. Senior Backend Engineer"
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
                            <Label for="companyName">Company *</Label>
                            <Input
                                id="companyName"
                                v-model="form.companyName"
                                placeholder="e.g. Stripe, OpenAI"
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
                            <!-- <select
                                id="status"
                                v-model="form.status"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring capitalize"
                            >
                                <option v-for="s in statuses" :key="s" :value="s">
                                    {{ s }}
                                </option>
                            </select> -->
                        </div>

                        <div class="space-y-1.5">
                            <Label for="appliedAt">Date of Application</Label>
                            <Input
                                id="appliedAt"
                                type="date"
                                v-model="form.appliedAt"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="jobURL">Job Posting URL (Optional)</Label>
                        <Input
                            id="jobURL"
                            type="url"
                            v-model="form.jobURL"
                            placeholder="https://company.com/careers/role"
                        />
                        <span
                            v-if="errors.jobURL"
                            class="text-xs text-destructive"
                        >
                            {{ errors.jobURL }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <!-- Resume / CV Attachment -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base"
                        >Resume / CV Attachment</CardTitle
                    >
                    <CardDescription>
                        Attach the resume version you used or plan to use for
                        this specific role.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-2 text-xs sm:grid-cols-4">
                        <label
                            v-if="defaultCvId"
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                            :class="
                                form.cv_selection === 'default'
                                    ? 'border-primary bg-primary/10 font-semibold text-primary'
                                    : 'border-border hover:bg-muted/50'
                            "
                        >
                            <input
                                type="radio"
                                value="default"
                                v-model="form.cv_selection"
                                class="sr-only"
                            />
                            <span>Default CV</span>
                        </label>

                        <label
                            v-if="cvs.length > 0"
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                            :class="
                                form.cv_selection === 'existing'
                                    ? 'border-primary bg-primary/10 font-semibold text-primary'
                                    : 'border-border hover:bg-muted/50'
                            "
                        >
                            <input
                                type="radio"
                                value="existing"
                                v-model="form.cv_selection"
                                class="sr-only"
                            />
                            <span>Select Existing CV</span>
                        </label>

                        <label
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                            :class="
                                form.cv_selection === 'new'
                                    ? 'border-primary bg-primary/10 font-semibold text-primary'
                                    : 'border-border hover:bg-muted/50'
                            "
                        >
                            <input
                                type="radio"
                                value="new"
                                v-model="form.cv_selection"
                                class="sr-only"
                            />
                            <span>+ Upload New CV</span>
                        </label>

                        <label
                            class="flex cursor-pointer items-center justify-center rounded-lg border p-2.5 text-center transition-colors"
                            :class="
                                form.cv_selection === 'none'
                                    ? 'border-primary bg-primary/10 font-semibold text-primary'
                                    : 'border-border hover:bg-muted/50'
                            "
                        >
                            <input
                                type="radio"
                                value="none"
                                v-model="form.cv_selection"
                                class="sr-only"
                            />
                            <span>No CV</span>
                        </label>
                    </div>

                    <!-- Option 1: Default CV Info -->
                    <div
                        v-if="form.cv_selection === 'default'"
                        class="flex items-center gap-2 rounded-lg border bg-muted/20 p-3 text-xs"
                    >
                        <CheckCircle2
                            class="size-4 shrink-0 text-emerald-500"
                        />
                        <div>
                            <span>Your default resume will be attached: </span>
                            <strong>{{
                                defaultCv?.name || 'Default Resume'
                            }}</strong>
                        </div>
                    </div>

                    <!-- Option 2: Select from Existing CVs -->
                    <div
                        v-if="form.cv_selection === 'existing'"
                        class="space-y-1.5"
                    >
                        <Label for="selectCv"
                            >Select from your uploaded resumes</Label
                        >
                        <!-- <select
                            id="selectCv"
                            v-model="form.cv_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                        >
                            <option v-for="cv in cvs" :key="cv.id" :value="String(cv.id)">
                                {{ cv.name || 'Resume #' + cv.id }} {{ cv.id === defaultCvId ? '(Default)' : '' }}
                            </option>
                        </select> -->
                    </div>

                    <!-- Option 3: Upload New CV inline -->
                    <div
                        v-if="form.cv_selection === 'new'"
                        class="space-y-3 rounded-lg border bg-muted/20 p-4"
                    >
                        <div class="space-y-1.5">
                            <Label for="newCvFile"
                                >Upload Resume File (PDF, DOC, DOCX) *</Label
                            >
                            <Input
                                id="newCvFile"
                                type="file"
                                accept=".pdf,.doc,.docx"
                                required
                                @change="onNewCvFileChange"
                                class="cursor-pointer"
                            />
                            <span
                                v-if="errors.new_cv_file"
                                class="text-xs text-destructive"
                            >
                                {{ errors.new_cv_file }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <Label for="newCvName" class="text-xs"
                                    >Resume Title / Label</Label
                                >
                                <Input
                                    id="newCvName"
                                    v-model="form.new_cv_name"
                                    placeholder="e.g. Tailored Frontend CV"
                                    class="h-8 text-xs"
                                />
                            </div>

                            <div class="flex items-center gap-2 pt-5">
                                <input
                                    id="makeDefault"
                                    type="checkbox"
                                    v-model="form.make_default"
                                    class="rounded border-border"
                                />
                                <Label
                                    for="makeDefault"
                                    class="cursor-pointer text-xs"
                                >
                                    Set this file as my new default CV
                                </Label>
                            </div>
                        </div>
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

            <!-- Notes & Additional Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Notes & Follow-ups</CardTitle>
                    <CardDescription>
                        Key requirements, recruiter info, salary expectations,
                        interview notes.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <textarea
                        v-model="form.notes"
                        rows="4"
                        placeholder="e.g. Applied via referral, hiring manager is Sarah, next step is technical screen..."
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    />
                </CardContent>
                <CardFooter class="flex items-center justify-end gap-3 pt-2">
                    <Link
                        href="/jobs"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-input px-4 text-sm font-medium transition-colors hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <Button type="submit" :disabled="processing">
                        Save Job Application
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
