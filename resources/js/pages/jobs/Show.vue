<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Bell,
    Briefcase,
    Building2,
    Calendar,
    Clock,
    ExternalLink,
    FileText,
    Globe,
    Mail,
    Pencil,
    Plus,
    Trash2,
    UserCircle2,
    Users,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import type { JobContact, JobContactStatus, TrackedJob, TrackedJobStatus } from '@/types';

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
                title: 'Job Details',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    job: TrackedJob;
    statuses: TrackedJobStatus[];
    contactStatuses?: JobContactStatus[];
}>();

const availableContactStatuses = computed<JobContactStatus[]>(() => {
    return props.contactStatuses && props.contactStatuses.length > 0
        ? props.contactStatuses
        : ['will contact', 'message sent', 'replied', 'no response'];
});

const isDeleteJobModalOpen = ref(false);
const isDeletingJob = ref(false);

function deleteJob() {
    isDeleteJobModalOpen.value = true;
}

function confirmDeleteJob() {
    isDeletingJob.value = true;
    router.delete(`/jobs/${props.job.id}`, {
        onSuccess: () => {
            isDeleteJobModalOpen.value = false;
            toast.success('Application deleted successfully.');
        },
        onError: () => {
            toast.error('Failed to delete application.');
        },
        onFinish: () => {
            isDeletingJob.value = false;
        },
    });
}

function updateStatus(newStatus: TrackedJobStatus) {
    router.patch(
        `/jobs/${props.job.id}`,
        { status: newStatus },
        { preserveScroll: true }
    );
}

// Add Contact state
const showAddContactModal = ref(false);
const newContactForm = ref({
    name: '',
    email: '',
    position: '',
    status: 'will contact' as JobContactStatus,
    notes: '',
    linkedin_url: '',
});
const addingContact = ref(false);
const contactErrors = ref<Record<string, string>>({});

function openAddContact() {
    newContactForm.value = {
        name: '',
        email: '',
        position: '',
        status: 'will contact',
        notes: '',
        linkedin_url: '',
    };
    contactErrors.value = {};
    showAddContactModal.value = true;
}

function submitNewContact() {
    addingContact.value = true;
    contactErrors.value = {};

    router.post(`/jobs/${props.job.id}/contacts`, newContactForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showAddContactModal.value = false;
        },
        onError: (errs) => {
            contactErrors.value = errs;
        },
        onFinish: () => {
            addingContact.value = false;
        },
    });
}

// Edit Contact state
const editingContactId = ref<number | null>(null);
const editContactForm = ref({
    name: '',
    email: '',
    position: '',
    status: 'will contact' as JobContactStatus,
    notes: '',
    linkedin_url: '',
});
const updatingContact = ref(false);
const editContactErrors = ref<Record<string, string>>({});

function startEditContact(contact: JobContact) {
    editingContactId.value = contact.id;
    editContactForm.value = {
        name: contact.name,
        email: contact.email || '',
        position: contact.position || '',
        status: contact.status,
        notes: contact.notes || '',
        linkedin_url: contact.linkedin_url || '',
    };
    editContactErrors.value = {};
}

function cancelEditContact() {
    editingContactId.value = null;
    editContactErrors.value = {};
}

function submitEditContact(contactId: number) {
    updatingContact.value = true;
    editContactErrors.value = {};

    router.put(`/contacts/${contactId}`, editContactForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            editingContactId.value = null;
        },
        onError: (errs) => {
            editContactErrors.value = errs;
        },
        onFinish: () => {
            updatingContact.value = false;
        },
    });
}

function updateContactStatus(contact: JobContact, newStatus: JobContactStatus) {
    router.put(
        `/contacts/${contact.id}`,
        {
            name: contact.name,
            email: contact.email,
            position: contact.position,
            status: newStatus,
            notes: contact.notes,
            linkedin_url: contact.linkedin_url,
        },
        { preserveScroll: true }
    );
}

const contactToDelete = ref<JobContact | null>(null);
const isDeleteContactModalOpen = ref(false);
const isDeletingContact = ref(false);

function deleteContact(contact: JobContact) {
    contactToDelete.value = contact;
    isDeleteContactModalOpen.value = true;
}

function confirmDeleteContact() {
    if (!contactToDelete.value) return;
    isDeletingContact.value = true;
    router.delete(`/contacts/${contactToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteContactModalOpen.value = false;
            toast.success('Contact removed successfully.');
        },
        onError: () => {
            toast.error('Failed to remove contact.');
        },
        onFinish: () => {
            isDeletingContact.value = false;
            contactToDelete.value = null;
        },
    });
}

function getStatusBadgeClass(status: string) {
    switch (status) {
        case 'offer':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800';
        case 'interview':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 border-purple-300 dark:border-purple-800';
        case 'applied':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800';
        case 'will apply':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 border-red-300 dark:border-red-800';
        case 'no response':
        default:
            return 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700';
    }
}

function formatDate(dateStr?: string | null): string {
    if (!dateStr) return '—';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString(undefined, {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Head :title="`${job.jobTitle} at ${job.companyName} - Trackly`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
        <!-- Back Navigation & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <Link
                href="/jobs"
                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors font-medium"
            >
                <ArrowLeft class="size-3.5" />
                <span>Back to all applications</span>
            </Link>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <Link
                    :href="`/jobs/${job.id}/edit`"
                    class="inline-flex items-center gap-1.5 rounded-md text-xs font-medium border border-input h-8 px-3 hover:bg-muted transition-colors shadow-2xs"
                >
                    <Pencil class="size-3.5" />
                    <span>Edit Application</span>
                </Link>

                <Button
                    variant="outline"
                    size="sm"
                    class="h-8 text-xs text-destructive hover:bg-destructive/10 hover:text-destructive"
                    @click="deleteJob"
                >
                    <Trash2 class="size-3.5" />
                    <span>Delete</span>
                </Button>
            </div>
        </div>

        <!-- Main Job Summary Card -->
        <Card class="shadow-xs border-border/80">
            <CardHeader class="pb-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                                {{ job.jobTitle }}
                            </h1>
                            <a
                                v-if="job.jobURL"
                                :href="job.jobURL"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-muted-foreground hover:text-primary transition-colors"
                                title="Open original job posting"
                            >
                                <ExternalLink class="size-5" />
                            </a>
                        </div>

                        <div class="flex items-center gap-2 text-base font-medium text-muted-foreground">
                            <Building2 class="size-4" />
                            <span>{{ job.companyName }}</span>
                        </div>
                    </div>

                    <!-- Quick Status Dropdown -->
                    <div class="flex items-center gap-2 self-start md:self-auto">
                        <span class="text-xs text-muted-foreground font-medium">Status:</span>
                        <select
                            :value="job.status"
                            @change="updateStatus(($event.target as HTMLSelectElement).value as TrackedJobStatus)"
                            class="h-9 rounded-md border text-xs px-3 py-1 font-semibold transition-colors cursor-pointer capitalize shadow-2xs"
                            :class="getStatusBadgeClass(job.status)"
                        >
                            <option v-for="s in statuses" :key="s" :value="s">
                                {{ s }}
                            </option>
                        </select>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2 border-t border-border/50">
                <!-- Applied Date -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Applied Date
                    </span>
                    <p class="text-sm font-medium text-foreground flex items-center gap-1.5 pt-0.5">
                        <Calendar class="size-4 text-muted-foreground" />
                        <span>{{ formatDate(job.appliedAt) }}</span>
                    </p>
                </div>

                <!-- Attached CV -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Attached Resume
                    </span>
                    <div v-if="job.cv" class="flex items-center gap-1.5 pt-0.5 text-sm font-medium text-foreground">
                        <FileText class="size-4 text-primary" />
                        <span class="truncate">{{ job.cv.name || 'Resume #' + job.cv.id }}</span>
                    </div>
                    <p v-else class="text-sm text-muted-foreground pt-0.5">
                        No CV attached
                    </p>
                </div>

                <!-- Original Link -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Job Posting
                    </span>
                    <div v-if="job.jobURL" class="pt-0.5">
                        <a
                            :href="job.jobURL"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm text-primary hover:underline font-medium inline-flex items-center gap-1"
                        >
                            <span>Open Link</span>
                            <ExternalLink class="size-3.5" />
                        </a>
                    </div>
                    <p v-else class="text-sm text-muted-foreground pt-0.5">
                        Not provided
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Notes Section -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Notes & Key Details</CardTitle>
            </CardHeader>
            <CardContent>
                <div
                    v-if="job.notes"
                    class="rounded-lg bg-muted/30 border p-4 text-sm whitespace-pre-wrap text-foreground/90 leading-relaxed font-sans"
                >
                    {{ job.notes }}
                </div>
                <p v-else class="text-xs text-muted-foreground italic">
                    No notes recorded yet for this role.
                </p>
            </CardContent>
        </Card>

        <!-- Side-by-side: Contacts & Reminders -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Job Contacts -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-base flex items-center gap-2">
                                <Users class="size-4 text-primary" />
                                Recruiter & Company Contacts
                            </CardTitle>
                            <span class="text-xs text-muted-foreground">
                                {{ job.contacts?.length || 0 }} total
                            </span>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="gap-1.5 text-xs h-8"
                            @click="openAddContact"
                        >
                            <Plus class="size-3.5" />
                            <span>Add Contact</span>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-3">
                    <!-- Inline Add Contact Form -->
                    <div
                        v-if="showAddContactModal"
                        class="p-4 rounded-lg border-2 border-primary/20 bg-muted/40 space-y-3"
                    >
                        <div class="flex items-center justify-between pb-1 border-b">
                            <span class="text-xs font-semibold text-foreground">Add New Contact</span>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="showAddContactModal = false"
                                class="h-6 w-6 p-0 text-muted-foreground"
                            >
                                <X class="size-3.5" />
                            </Button>
                        </div>

                        <form @submit.prevent="submitNewContact" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="space-y-1">
                                    <Label class="text-[11px]">Full Name *</Label>
                                    <Input
                                        v-model="newContactForm.name"
                                        placeholder="e.g. Alex Rivera"
                                        class="h-8 text-xs"
                                        required
                                    />
                                    <span v-if="contactErrors.name" class="text-[10px] text-destructive">
                                        {{ contactErrors.name }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <Label class="text-[11px]">Role / Position</Label>
                                    <Input
                                        v-model="newContactForm.position"
                                        placeholder="e.g. Technical Recruiter"
                                        class="h-8 text-xs"
                                    />
                                    <span v-if="contactErrors.position" class="text-[10px] text-destructive">
                                        {{ contactErrors.position }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="space-y-1">
                                    <Label class="text-[11px]">Email</Label>
                                    <Input
                                        type="email"
                                        v-model="newContactForm.email"
                                        placeholder="alex@company.com"
                                        class="h-8 text-xs"
                                    />
                                    <span v-if="contactErrors.email" class="text-[10px] text-destructive">
                                        {{ contactErrors.email }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <Label class="text-[11px]">Outreach Status *</Label>
                                    <select
                                        v-model="newContactForm.status"
                                        class="flex h-8 w-full rounded-md border border-input bg-transparent px-2.5 py-1 text-xs shadow-2xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring capitalize"
                                    >
                                        <option
                                            v-for="st in availableContactStatuses"
                                            :key="st"
                                            :value="st"
                                            class="capitalize bg-background text-foreground"
                                        >
                                            {{ st }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <Label class="text-[11px]">LinkedIn URL</Label>
                                <Input
                                    v-model="newContactForm.linkedin_url"
                                    placeholder="https://linkedin.com/in/alex-rivera"
                                    class="h-8 text-xs"
                                />
                                <span v-if="contactErrors.linkedin_url" class="text-[10px] text-destructive">
                                    {{ contactErrors.linkedin_url }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <Label class="text-[11px]">Notes / Context</Label>
                                <Input
                                    v-model="newContactForm.notes"
                                    placeholder="e.g. Sent connection request with note"
                                    class="h-8 text-xs"
                                />
                                <span v-if="contactErrors.notes" class="text-[10px] text-destructive">
                                    {{ contactErrors.notes }}
                                </span>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs"
                                    @click="showAddContactModal = false"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    size="sm"
                                    class="h-7 text-xs"
                                    :disabled="addingContact"
                                >
                                    {{ addingContact ? 'Saving...' : 'Save Contact' }}
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="(!job.contacts || job.contacts.length === 0) && !showAddContactModal"
                        class="text-xs text-muted-foreground py-6 text-center border rounded-lg border-dashed space-y-2"
                    >
                        <Users class="size-6 mx-auto text-muted-foreground/60" />
                        <div>No contacts listed for this company yet.</div>
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            class="text-xs h-7 gap-1 mt-1"
                            @click="openAddContact"
                        >
                            <Plus class="size-3" />
                            <span>Add Contact</span>
                        </Button>
                    </div>

                    <!-- Contact items -->
                    <div
                        v-for="contact in job.contacts"
                        :key="contact.id"
                        class="p-3 rounded-lg border space-y-2 text-xs bg-muted/20 hover:bg-muted/30 transition-colors"
                    >
                        <!-- Inline Edit Form -->
                        <div v-if="editingContactId === contact.id" class="space-y-2.5">
                            <div class="flex items-center justify-between pb-1 border-b">
                                <span class="text-xs font-semibold text-foreground">Edit Contact</span>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="cancelEditContact"
                                    class="h-6 w-6 p-0 text-muted-foreground"
                                >
                                    <X class="size-3.5" />
                                </Button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="space-y-1">
                                    <Label class="text-[11px]">Full Name *</Label>
                                    <Input v-model="editContactForm.name" class="h-7 text-xs" required />
                                    <span v-if="editContactErrors.name" class="text-[10px] text-destructive">
                                        {{ editContactErrors.name }}
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-[11px]">Position / Role</Label>
                                    <Input v-model="editContactForm.position" class="h-7 text-xs" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="space-y-1">
                                    <Label class="text-[11px]">Email</Label>
                                    <Input v-model="editContactForm.email" type="email" class="h-7 text-xs" />
                                    <span v-if="editContactErrors.email" class="text-[10px] text-destructive">
                                        {{ editContactErrors.email }}
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-[11px]">Status</Label>
                                    <select
                                        v-model="editContactForm.status"
                                        class="flex h-7 w-full rounded-md border border-input bg-transparent px-2 text-xs capitalize"
                                    >
                                        <option
                                            v-for="st in availableContactStatuses"
                                            :key="st"
                                            :value="st"
                                            class="capitalize bg-background text-foreground"
                                        >
                                            {{ st }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <Label class="text-[11px]">LinkedIn URL</Label>
                                <Input v-model="editContactForm.linkedin_url" class="h-7 text-xs" />
                                <span v-if="editContactErrors.linkedin_url" class="text-[10px] text-destructive">
                                    {{ editContactErrors.linkedin_url }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <Label class="text-[11px]">Notes</Label>
                                <Input v-model="editContactForm.notes" class="h-7 text-xs" />
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs"
                                    @click="cancelEditContact"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="h-7 text-xs"
                                    :disabled="updatingContact"
                                    @click="submitEditContact(contact.id)"
                                >
                                    {{ updatingContact ? 'Saving...' : 'Update' }}
                                </Button>
                            </div>
                        </div>

                        <!-- Read view -->
                        <div v-else class="space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold text-foreground text-sm flex items-center gap-1.5">
                                    <span>{{ contact.name }}</span>
                                    <span v-if="contact.position" class="font-normal text-xs text-muted-foreground">
                                        ({{ contact.position }})
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <!-- Quick status select -->
                                    <select
                                        :value="contact.status"
                                        @change="updateContactStatus(contact, ($event.target as HTMLSelectElement).value as JobContactStatus)"
                                        class="h-6 rounded-full border border-input bg-background px-2 text-[10px] font-medium capitalize shadow-2xs hover:border-primary/40 focus:outline-none"
                                        title="Change outreach status"
                                    >
                                        <option
                                            v-for="st in availableContactStatuses"
                                            :key="st"
                                            :value="st"
                                            class="capitalize bg-background text-foreground text-xs"
                                        >
                                            {{ st }}
                                        </option>
                                    </select>

                                    <!-- Action buttons -->
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0 text-muted-foreground hover:text-foreground"
                                        title="Edit contact"
                                        @click="startEditContact(contact)"
                                    >
                                        <Pencil class="size-3" />
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                        title="Delete contact"
                                        @click="deleteContact(contact)"
                                    >
                                        <Trash2 class="size-3" />
                                    </Button>
                                </div>
                            </div>

                            <p v-if="contact.notes" class="text-xs text-muted-foreground italic">
                                "{{ contact.notes }}"
                            </p>

                            <div class="flex flex-wrap items-center gap-3 pt-0.5 text-muted-foreground">
                                <a
                                    v-if="contact.email"
                                    :href="`mailto:${contact.email}`"
                                    class="inline-flex items-center gap-1 hover:text-foreground transition-colors"
                                >
                                    <Mail class="size-3" />
                                    <span>{{ contact.email }}</span>
                                </a>
                                <a
                                    v-if="contact.linkedin_url"
                                    :href="contact.linkedin_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-primary hover:underline transition-colors"
                                >
                                    <Globe class="size-3" />
                                    <span>LinkedIn</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Reminders -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base flex items-center gap-2">
                        <Bell class="size-4 text-amber-500" />
                        Reminders & Follow-ups
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-if="!job.reminder"
                        class="text-xs text-muted-foreground py-4 text-center border rounded-lg border-dashed"
                    >
                        No active reminder scheduled.
                    </div>
                    <div
                        v-else
                        class="p-3 rounded-lg border space-y-1.5 text-xs bg-muted/20"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-foreground">Scheduled Follow-up</span>
                            <span class="px-2 py-0.5 rounded-full border text-[10px] font-medium capitalize bg-background">
                                {{ job.reminder.status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-medium pt-1">
                            <Clock class="size-3.5" />
                            <span>Due on: {{ formatDate(job.reminder.scheduled_at) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
    <!-- In-Page Delete Job Modal -->
    <Dialog v-model:open="isDeleteJobModalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-destructive">Delete Application</DialogTitle>
                <DialogDescription class="pt-2 text-sm text-foreground/80">
                    Are you sure you want to delete
                    <strong class="font-semibold text-foreground">"{{ job.jobTitle }}"</strong>
                    at
                    <strong class="font-semibold text-foreground">{{ job.companyName }}</strong>?
                    <br /><br />
                    This will permanently delete this job application and any contacts associated with it. This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end pt-4">
                <Button
                    type="button"
                    variant="outline"
                    @click="isDeleteJobModalOpen = false"
                    :disabled="isDeletingJob"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="isDeletingJob"
                    @click="confirmDeleteJob"
                >
                    {{ isDeletingJob ? 'Deleting...' : 'Delete Application' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- In-Page Delete Contact Modal -->
    <Dialog v-model:open="isDeleteContactModalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-destructive">Remove Contact</DialogTitle>
                <DialogDescription class="pt-2 text-sm text-foreground/80">
                    Are you sure you want to remove
                    <strong class="font-semibold text-foreground">"{{ contactToDelete?.name }}"</strong>
                    from this job application?
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end pt-4">
                <Button
                    type="button"
                    variant="outline"
                    @click="isDeleteContactModalOpen = false"
                    :disabled="isDeletingContact"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="isDeletingContact"
                    @click="confirmDeleteContact"
                >
                    {{ isDeletingContact ? 'Removing...' : 'Remove Contact' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
