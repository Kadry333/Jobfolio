<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Briefcase,
    Building2,
    Calendar,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ExternalLink,
    Eye,
    FileText,
    Pencil,
    Plus,
    Search,
    Trash2,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
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
import { dashboard } from '@/routes';
import type { PaginatedData, TrackedJob, TrackedJobStatus } from '@/types';

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
        ],
    },
});

const props = defineProps<{
    jobs: PaginatedData<TrackedJob>;
    filters: {
        status?: string | null;
        search?: string | null;
    };
    statuses: TrackedJobStatus[];
}>();

const search = ref(props.filters.search || '');
const activeStatus = ref(props.filters.status || 'all');

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        '/jobs',
        {
            search: search.value || undefined,
            status: activeStatus.value !== 'all' ? activeStatus.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

function setStatus(status: string) {
    activeStatus.value = status;
    applyFilters();
}

// In-line Quick Status Change
const updatingStatusJobId = ref<number | null>(null);

function handleStatusChange(job: TrackedJob, newStatus: TrackedJobStatus) {
    if (job.status === newStatus) return;

    updatingStatusJobId.value = job.id;
    router.patch(
        `/jobs/${job.id}`,
        { status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`Status updated to "${newStatus}"`);
            },
            onError: () => {
                toast.error('Failed to update status.');
            },
            onFinish: () => {
                updatingStatusJobId.value = null;
            },
        }
    );
}

// In-Page Delete Modal State
const isDeleteDialogOpen = ref(false);
const jobToDelete = ref<TrackedJob | null>(null);
const isDeleting = ref(false);

function openDeleteModal(job: TrackedJob) {
    jobToDelete.value = job;
    isDeleteDialogOpen.value = true;
}

function confirmDelete() {
    if (!jobToDelete.value) return;

    isDeleting.value = true;
    router.delete(`/jobs/${jobToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            toast.success('Application deleted successfully.');
        },
        onError: () => {
            toast.error('Failed to delete application.');
        },
        onFinish: () => {
            isDeleting.value = false;
            jobToDelete.value = null;
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
    <Head title="Job Applications - Trackly" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                    Job Applications
                </h1>
                <p class="text-sm text-muted-foreground">
                    Manage, track, and update all your job opportunities in one place.
                </p>
            </div>

            <Link
                href="/jobs/create"
                class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground h-9 px-4 py-2 hover:bg-primary/90 transition-colors shadow-xs self-start sm:self-auto"
            >
                <Plus class="size-4" />
                <span>Add Application</span>
            </Link>
        </div>

        <!-- Filter & Search Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-card border rounded-xl p-3 shadow-2xs">
            <div class="relative w-full md:w-80">
                <Search class="absolute left-3 top-2.5 size-4 text-muted-foreground" />
                <Input
                    v-model="search"
                    placeholder="Search by role or company..."
                    class="pl-9 h-9 text-xs"
                />
            </div>

            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto">
                <Button
                    size="sm"
                    :variant="activeStatus === 'all' ? 'default' : 'outline'"
                    class="h-7 text-xs"
                    @click="setStatus('all')"
                >
                    All ({{ jobs.total }})
                </Button>
                <Button
                    v-for="s in statuses"
                    :key="s"
                    size="sm"
                    :variant="activeStatus === s ? 'default' : 'outline'"
                    class="h-7 text-xs capitalize"
                    @click="setStatus(s)"
                >
                    {{ s }}
                </Button>
            </div>
        </div>

        <!-- Applications List / Table -->
        <Card>
            <CardContent class="p-0">
                <div v-if="jobs.data.length === 0" class="flex flex-col items-center justify-center p-12 text-center">
                    <div class="rounded-full bg-muted p-3 mb-3">
                        <Briefcase class="size-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-base font-semibold">No applications found</h3>
                    <p class="text-sm text-muted-foreground max-w-sm mt-1 mb-4">
                        {{
                            search || activeStatus !== 'all'
                                ? 'No jobs match your current filters. Try resetting your search.'
                                : 'You have not added any job applications yet.'
                        }}
                    </p>
                    <Link
                        v-if="!search && activeStatus === 'all'"
                        href="/jobs/create"
                        class="inline-flex items-center justify-center gap-1.5 rounded-md text-xs font-medium bg-primary text-primary-foreground h-8 px-3 shadow-xs"
                    >
                        <Plus class="size-3.5" />
                        Add First Application
                    </Link>
                </div>

                <div v-else class="divide-y divide-border">
                    <div
                        v-for="job in jobs.data"
                        :key="job.id"
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 hover:bg-muted/40 transition-colors"
                    >
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/jobs/${job.id}`"
                                    class="font-semibold text-base text-foreground hover:text-primary transition-colors hover:underline"
                                >
                                    {{ job.jobTitle }}
                                </Link>
                                <a
                                    v-if="job.jobURL"
                                    :href="job.jobURL"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-muted-foreground hover:text-foreground"
                                    title="Open job URL"
                                >
                                    <ExternalLink class="size-3.5" />
                                </a>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                <span class="flex items-center gap-1 font-medium text-foreground/80">
                                    <Building2 class="size-3.5" />
                                    {{ job.companyName }}
                                </span>
                                <span>•</span>
                                <span>Applied: {{ formatDate(job.appliedAt) }}</span>
                                <span v-if="job.cv">
                                    • CV: <span class="text-foreground/80">{{ job.cv.name || 'Resume' }}</span>
                                </span>
                                <span v-if="job.contacts && job.contacts.length > 0">
                                    • {{ job.contacts.length }} contact{{ job.contacts.length > 1 ? 's' : '' }}
                                </span>
                            </div>

                            <p
                                v-if="job.notes"
                                class="text-xs text-muted-foreground line-clamp-1 italic pt-0.5"
                            >
                                "{{ job.notes }}"
                            </p>
                        </div>

                        <!-- Direct Quick Status Changer & Actions -->
                        <div class="flex items-center gap-3 self-start sm:self-auto shrink-0">
                            <!-- Direct Quick Status Changer -->
                            <div class="relative inline-flex items-center" title="Click to quickly change status directly">
                                <select
                                    :value="job.status"
                                    :disabled="updatingStatusJobId === job.id"
                                    @change="(e) => handleStatusChange(job, (e.target as HTMLSelectElement).value as TrackedJobStatus)"
                                    class="appearance-none cursor-pointer text-xs font-semibold rounded-full pl-3 pr-7 py-1 border transition-all focus:outline-none focus:ring-2 focus:ring-primary/40 capitalize disabled:opacity-50"
                                    :class="getStatusBadgeClass(job.status)"
                                >
                                    <option
                                        v-for="s in statuses"
                                        :key="s"
                                        :value="s"
                                        class="bg-popover text-popover-foreground capitalize"
                                    >
                                        {{ s }}
                                    </option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-2 size-3 opacity-60" />
                            </div>

                            <div class="flex items-center gap-1">
                                <Link
                                    :href="`/jobs/${job.id}`"
                                    class="inline-flex items-center justify-center size-8 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                                    title="View Details"
                                >
                                    <Eye class="size-4" />
                                </Link>
                                <Link
                                    :href="`/jobs/${job.id}/edit`"
                                    class="inline-flex items-center justify-center size-8 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                                    title="Edit"
                                >
                                    <Pencil class="size-4" />
                                </Link>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                                    @click="openDeleteModal(job)"
                                    title="Delete"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="jobs.total > 0"
                    class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t px-4 py-3 bg-card rounded-b-xl"
                >
                    <div class="text-xs text-muted-foreground">
                        Showing
                        <span class="font-medium text-foreground">{{ jobs.from ?? 0 }}</span>
                        to
                        <span class="font-medium text-foreground">{{ jobs.to ?? 0 }}</span>
                        of
                        <span class="font-medium text-foreground">{{ jobs.total }}</span>
                        applications
                    </div>

                    <!-- Page Navigation -->
                    <div v-if="jobs.last_page > 1" class="flex items-center gap-1">
                        <Link
                            v-if="jobs.prev_page_url"
                            :href="jobs.prev_page_url"
                            preserve-scroll
                            preserve-state
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                        >
                            <ChevronLeft class="size-3.5" />
                            <span>Previous</span>
                        </Link>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium border border-input/40 bg-muted/30 text-muted-foreground/50 cursor-not-allowed"
                        >
                            <ChevronLeft class="size-3.5" />
                            <span>Previous</span>
                        </span>

                        <!-- Page number links -->
                        <div class="hidden sm:flex items-center gap-1 mx-1">
                            <template v-for="(link, i) in jobs.links.slice(1, -1)" :key="i">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-scroll
                                    preserve-state
                                    class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-xs font-medium rounded-md border transition-colors"
                                    :class="
                                        link.active
                                            ? 'bg-primary text-primary-foreground border-primary font-semibold shadow-2xs'
                                            : 'bg-background hover:bg-accent border-input text-foreground'
                                    "
                                >
                                    {{ link.label }}
                                </Link>
                                <span
                                    v-else
                                    class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-xs text-muted-foreground"
                                >
                                    {{ link.label }}
                                </span>
                            </template>
                        </div>

                        <Link
                            v-if="jobs.next_page_url"
                            :href="jobs.next_page_url"
                            preserve-scroll
                            preserve-state
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                        >
                            <span>Next</span>
                            <ChevronRight class="size-3.5" />
                        </Link>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium border border-input/40 bg-muted/30 text-muted-foreground/50 cursor-not-allowed"
                        >
                            <span>Next</span>
                            <ChevronRight class="size-3.5" />
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- In-Page Delete Confirmation Dialog (Not Browser Confirm) -->
    <Dialog v-model:open="isDeleteDialogOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-destructive/10 text-destructive mb-2 sm:mx-0">
                    <AlertTriangle class="size-6" />
                </div>
                <DialogTitle class="text-lg font-bold">Delete Application</DialogTitle>
                <DialogDescription class="pt-2 text-sm text-foreground/80">
                    Are you sure you want to delete
                    <strong class="font-semibold text-foreground">"{{ jobToDelete?.jobTitle }}"</strong>
                    at
                    <strong class="font-semibold text-foreground">{{ jobToDelete?.companyName }}</strong>?
                    <br /><br />
                    This will permanently delete this job application and any contacts associated with it. This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end pt-4">
                <Button
                    type="button"
                    variant="outline"
                    @click="isDeleteDialogOpen = false"
                    :disabled="isDeleting"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="isDeleting"
                    @click="confirmDelete"
                >
                    {{ isDeleting ? 'Deleting...' : 'Delete Application' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
