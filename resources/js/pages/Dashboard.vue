<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    Bell,
    Briefcase,
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    ExternalLink,
    Eye,
    FileText,
    FileUp,
    LogOut,
    Pencil,
    Plus,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard, logout } from '@/routes';
import type {
    Cv,
    DashboardStats,
    JobReminder,
    TrackedJob,
    TrackedJobStatus,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    stats: DashboardStats;
    recentJobs: TrackedJob[];
    cvs: Cv[];
    upcomingReminders: (JobReminder & { trackedJob?: { id: number; jobTitle: string; companyName: string } })[];
    statuses: TrackedJobStatus[];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const showOnboardingBanner = ref(true);
const activeFilter = ref<string>('all');

const handleLogout = () => {
    router.flushAll();
};

function updateJobStatus(job: TrackedJob, newStatus: TrackedJobStatus) {
    router.patch(
        `/jobs/${job.id}`,
        { status: newStatus },
        {
            preserveScroll: true,
        }
    );
}

const filteredJobs = computed(() => {
    if (activeFilter.value === 'all') {
        return props.recentJobs;
    }
    return props.recentJobs.filter((job) => job.status === activeFilter.value);
});

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
    <Head title="Dashboard - Trackly" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Optional Onboarding Banner when no default CV exists -->
        <div
            v-if="!user.default_cv_id && cvs.length === 0 && showOnboardingBanner"
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-primary/20 bg-primary/5 text-foreground shadow-xs"
        >
            <div class="flex items-start gap-3">
                <div class="rounded-full bg-primary/10 p-2 text-primary shrink-0">
                    <FileUp class="size-5" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold">Optional: Upload your default resume</h3>
                    <p class="text-xs text-muted-foreground">
                        Set a default CV once, and it will automatically be attached to any new job application with one click.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                <Link
                    href="/onboarding/cv"
                    class="inline-flex items-center justify-center rounded-md text-xs font-medium bg-primary text-primary-foreground h-8 px-3 hover:bg-primary/90 transition-colors shadow-xs"
                >
                    Upload Resume
                </Link>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 text-xs text-muted-foreground"
                    @click="showOnboardingBanner = false"
                >
                    Dismiss
                </Button>
            </div>
        </div>

        <!-- Top Greeting Bar & Quick Link to Add Application -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                    Welcome back, {{ user.firstName || user.name }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    Here is an overview of your job applications and upcoming milestones.
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <Link
                    href="/jobs/create"
                    class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground h-9 px-4 py-2 hover:bg-primary/90 transition-colors shadow-xs"
                >
                    <Plus class="size-4" />
                    <span>Add Application</span>
                </Link>
                <Link
                    :href="logout()"
                    as="button"
                    @click="handleLogout"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md text-sm font-medium border border-input bg-background hover:bg-destructive/10 hover:text-destructive hover:border-destructive/30 text-muted-foreground h-9 px-3.5 py-2 transition-colors shadow-xs"
                    title="Log out"
                    data-test="dashboard-logout-button"
                >
                    <LogOut class="size-4" />
                    <span>Log out</span>
                </Link>
            </div>
        </div>

        <!-- 4 Key Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Total Tracked
                    </CardTitle>
                    <Briefcase class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total }}</div>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ stats.applied }} currently applied
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Interviews
                    </CardTitle>
                    <Calendar class="size-4 text-purple-600 dark:text-purple-400" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ stats.interview }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                        Active conversation stages
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Job Offers
                    </CardTitle>
                    <CheckCircle2 class="size-4 text-emerald-600 dark:text-emerald-400" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ stats.offer }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                        Offers extended
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Reminders Due
                    </CardTitle>
                    <Clock class="size-4 text-amber-600 dark:text-amber-400" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                        {{ stats.pendingReminders }}
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                        Follow-ups scheduled
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Filter Pills Bar -->
        <div class="flex flex-wrap items-center gap-2 border-b border-border pb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mr-1">
                Filter:
            </span>
            <Button
                size="sm"
                :variant="activeFilter === 'all' ? 'default' : 'outline'"
                class="h-7 text-xs"
                @click="activeFilter = 'all'"
            >
                All ({{ stats.total }})
            </Button>
            <Button
                v-for="s in statuses"
                :key="s"
                size="sm"
                :variant="activeFilter === s ? 'default' : 'outline'"
                class="h-7 text-xs capitalize"
                @click="activeFilter = s"
            >
                {{ s }}
            </Button>
        </div>

        <!-- Main Content Area: Applications List & Sidebar Widgets -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left 2 Cols: Tracked Applications -->
            <div class="lg:col-span-2 space-y-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg">Recent Applications</CardTitle>
                            <CardDescription>
                                Monitor statuses and progress across your opportunities.
                            </CardDescription>
                        </div>
                        <Link
                            href="/jobs"
                            class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1"
                        >
                            <span>View All ({{ stats.total }})</span>
                            <ArrowRight class="size-3" />
                        </Link>
                    </CardHeader>
                    <CardContent class="p-0">
                        <!-- Empty State -->
                        <div
                            v-if="filteredJobs.length === 0"
                            class="flex flex-col items-center justify-center p-12 text-center"
                        >
                            <div class="rounded-full bg-muted p-3 mb-3">
                                <Briefcase class="size-6 text-muted-foreground" />
                            </div>
                            <h3 class="text-base font-semibold">No applications found</h3>
                            <p class="text-sm text-muted-foreground max-w-sm mt-1 mb-4">
                                {{
                                    activeFilter === 'all'
                                        ? 'Start tracking your journey by logging your first job application.'
                                        : `No jobs currently marked as "${activeFilter}".`
                                }}
                            </p>
                            <Link
                                v-if="activeFilter === 'all'"
                                href="/jobs/create"
                                class="inline-flex items-center justify-center gap-1.5 rounded-md text-xs font-medium bg-primary text-primary-foreground h-8 px-3 shadow-xs"
                            >
                                <Plus class="size-3.5" />
                                Add First Job
                            </Link>
                        </div>

                        <!-- Table / Cards of Jobs -->
                        <div v-else class="divide-y divide-border">
                            <div
                                v-for="job in filteredJobs"
                                :key="job.id"
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 hover:bg-muted/40 transition-colors"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/jobs/${job.id}`"
                                            class="font-semibold text-foreground hover:text-primary transition-colors hover:underline"
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
                                        class="text-xs text-muted-foreground/90 line-clamp-1 italic pt-0.5"
                                    >
                                        "{{ job.notes }}"
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                                    <!-- Status selector dropdown -->
                                    <select
                                        :value="job.status"
                                        @change="updateJobStatus(job, ($event.target as HTMLSelectElement).value as TrackedJobStatus)"
                                        class="h-8 rounded-md border text-xs px-2.5 py-0.5 font-medium transition-colors cursor-pointer"
                                        :class="getStatusBadgeClass(job.status)"
                                    >
                                        <option v-for="s in statuses" :key="s" :value="s">
                                            {{ s }}
                                        </option>
                                    </select>

                                    <Link
                                        :href="`/jobs/${job.id}`"
                                        class="inline-flex items-center justify-center size-8 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                                        title="View Details"
                                    >
                                        <Eye class="size-4" />
                                    </Link>

                                    <Link
                                        :href="`/jobs/${job.id}/edit`"
                                        class="inline-flex items-center justify-center size-8 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                                        title="Edit Application"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Right 1 Col: Reminders & CV Quick Info -->
            <div class="space-y-6">
                <!-- Upcoming Reminders -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base flex items-center gap-2">
                                <Bell class="size-4 text-amber-500" />
                                Upcoming Reminders
                            </CardTitle>
                        </div>
                        <CardDescription>
                            Scheduled follow-ups and action items.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="upcomingReminders.length === 0"
                            class="text-xs text-muted-foreground py-4 text-center"
                        >
                            No pending reminders right now.
                        </div>
                        <div
                            v-else
                            v-for="rem in upcomingReminders"
                            :key="rem.id"
                            class="rounded-lg border border-border p-3 text-xs space-y-1 bg-card/60"
                        >
                            <Link
                                v-if="rem.trackedJob?.id"
                                :href="`/jobs/${rem.trackedJob.id}`"
                                class="font-medium text-foreground hover:text-primary transition-colors block"
                            >
                                {{ rem.trackedJob?.jobTitle || 'Job Follow-up' }}
                            </Link>
                            <div v-else class="font-medium text-foreground">
                                {{ rem.trackedJob?.jobTitle || 'Job Follow-up' }}
                            </div>
                            <div class="text-muted-foreground">
                                {{ rem.trackedJob?.companyName || 'Company' }}
                            </div>
                            <div class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium pt-1">
                                <Clock class="size-3" />
                                <span>Due: {{ formatDate(rem.scheduled_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- CVs & Quick Tips -->
                <Card>
                    <CardHeader class="pb-3 flex flex-row items-center justify-between">
                        <CardTitle class="text-base flex items-center gap-2">
                            <FileText class="size-4 text-blue-500" />
                            Your Resumes / CVs
                        </CardTitle>
                        <Link href="/cvs" class="text-xs text-primary hover:underline font-medium">
                            Manage
                        </Link>
                    </CardHeader>
                    <CardContent class="space-y-2 text-xs">
                        <div v-if="cvs.length === 0" class="text-muted-foreground py-2 text-center">
                            No CVs attached yet.
                        </div>
                        <div
                            v-else
                            v-for="cv in cvs"
                            :key="cv.id"
                            class="flex items-center justify-between rounded-md border border-border p-2"
                        >
                            <div class="truncate font-medium">
                                {{ cv.name || 'Resume #' + cv.id }}
                            </div>
                            <Badge
                                v-if="user.default_cv_id === cv.id"
                                variant="secondary"
                                class="text-[10px]"
                            >
                                Default
                            </Badge>
                        </div>

                        <div class="pt-2 text-muted-foreground border-t border-border mt-3">
                            <span class="font-semibold text-foreground">Tip:</span> Set a default CV to automatically attach it when creating new job applications.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
