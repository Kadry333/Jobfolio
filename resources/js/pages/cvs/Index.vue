<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    FileText,
    Plus,
    Star,
    Trash2,
    UploadCloud,
} from '@lucide/vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'CVs & Resumes',
                href: '/cvs',
            },
        ],
    },
});

interface CvItem {
    id: number;
    name: string | null;
    file_path: string | null;
    file_url: string | null;
    is_default: boolean;
    created_at?: string;
}

const props = defineProps<{
    cvs: CvItem[];
    defaultCvId: number | null;
}>();

const isUploadModalOpen = ref(false);
const selectedFile = ref<File | null>(null);
const cvName = ref('');
const isDefault = ref(false);
const processing = ref(false);
const errorMessage = ref('');

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedFile.value = target.files[0];
        if (!cvName.value) {
            cvName.value = target.files[0].name.replace(/\.[^/.]+$/, '');
        }
    }
}

function uploadCv() {
    if (!selectedFile.value) {
        errorMessage.value = 'Please select a resume file.';
        return;
    }

    processing.value = true;
    errorMessage.value = '';

    const formData = new FormData();
    formData.append('cv_file', selectedFile.value);
    if (cvName.value) {
        formData.append('name', cvName.value);
    }
    if (isDefault.value) {
        formData.append('is_default', '1');
    }

    router.post('/cvs', formData, {
        onSuccess: () => {
            selectedFile.value = null;
            cvName.value = '';
            isDefault.value = false;
            isUploadModalOpen.value = false;
        },
        onError: (errs) => {
            errorMessage.value = Object.values(errs)[0] as string;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}

function makeDefault(cv: CvItem) {
    router.patch(`/cvs/${cv.id}/default`, {}, { preserveScroll: true });
}

const cvToDelete = ref<CvItem | null>(null);
const isDeleteCvModalOpen = ref(false);
const isDeletingCv = ref(false);

function deleteCv(cv: CvItem) {
    cvToDelete.value = cv;
    isDeleteCvModalOpen.value = true;
}

function confirmDeleteCv() {
    if (!cvToDelete.value) return;
    isDeletingCv.value = true;
    router.delete(`/cvs/${cvToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteCvModalOpen.value = false;
            toast.success('Resume deleted successfully.');
        },
        onError: () => {
            toast.error('Failed to delete resume.');
        },
        onFinish: () => {
            isDeletingCv.value = false;
            cvToDelete.value = null;
        },
    });
}
</script>

<template>
    <Head title="CVs & Resumes - Trackly" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-foreground md:text-3xl"
                >
                    Resumes & CVs
                </h1>
                <p class="text-sm text-muted-foreground">
                    Upload tailored CV versions to link to your job
                    applications.
                </p>
            </div>

            <Dialog v-model:open="isUploadModalOpen">
                <DialogTrigger as-child>
                    <Button class="gap-2 self-start sm:self-auto">
                        <Plus class="size-4" />
                        <span>Upload New CV</span>
                    </Button>
                </DialogTrigger>

                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Upload Resume / CV</DialogTitle>
                        <DialogDescription>
                            Supported formats: PDF, DOC, DOCX up to 10MB.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="uploadCv" class="space-y-4 py-2">
                        <div class="space-y-1.5">
                            <Label for="file">Resume File *</Label>
                            <Input
                                id="file"
                                type="file"
                                accept=".pdf,.doc,.docx"
                                required
                                @change="onFileChange"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="modalCvName">CV Label / Title</Label>
                            <Input
                                id="modalCvName"
                                v-model="cvName"
                                placeholder="e.g. Backend Engineer Resume"
                            />
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input
                                id="modalIsDefault"
                                type="checkbox"
                                v-model="isDefault"
                                class="rounded border-border"
                            />
                            <Label
                                for="modalIsDefault"
                                class="cursor-pointer text-xs"
                            >
                                Set as my default CV for future applications
                            </Label>
                        </div>

                        <p v-if="errorMessage" class="text-xs text-destructive">
                            {{ errorMessage }}
                        </p>

                        <DialogFooter class="pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="isUploadModalOpen = false"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="processing">
                                Upload CV
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div
            v-if="cvs.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border bg-card p-12 text-center"
        >
            <div class="mb-3 rounded-full bg-muted p-3">
                <FileText class="size-6 text-muted-foreground" />
            </div>
            <h3 class="text-base font-semibold">No resumes uploaded yet</h3>
            <p class="mt-1 mb-4 max-w-sm text-sm text-muted-foreground">
                Upload your main resume to quickly attach it when applying to
                jobs.
            </p>
            <Button size="sm" @click="isUploadModalOpen = true">
                Upload First Resume
            </Button>
        </div>

        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
        >
            <Card
                v-for="cv in cvs"
                :key="cv.id"
                class="relative transition-all"
                :class="cv.is_default ? 'border-primary/60 shadow-sm' : ''"
            >
                <CardHeader class="pb-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="rounded-md bg-primary/10 p-2 text-primary"
                            >
                                <FileText class="size-4" />
                            </div>
                            <div>
                                <CardTitle class="text-base leading-tight">
                                    {{ cv.name || 'Resume #' + cv.id }}
                                </CardTitle>
                                <span class="text-xs text-muted-foreground">
                                    Uploaded {{ cv.created_at }}
                                </span>
                            </div>
                        </div>

                        <Badge
                            v-if="cv.is_default"
                            variant="default"
                            class="gap-1 text-[11px]"
                        >
                            <Star class="size-3 fill-current" />
                            <span>Default</span>
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent
                    class="flex items-center justify-between border-t border-border/50 pt-2 text-xs"
                >
                    <div>
                        <a
                            v-if="cv.file_url"
                            :href="cv.file_url"
                            target="_blank"
                            class="font-medium text-primary hover:underline"
                        >
                            View File
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            v-if="!cv.is_default"
                            variant="outline"
                            size="sm"
                            class="h-7 text-xs"
                            @click="makeDefault(cv)"
                        >
                            Set Default
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="size-7 text-muted-foreground hover:text-destructive"
                            @click="deleteCv(cv)"
                            title="Delete CV"
                        >
                            <Trash2 class="size-3.5" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>

    <!-- In-Page Delete CV Modal -->
    <Dialog v-model:open="isDeleteCvModalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-destructive"
                    >Delete Resume</DialogTitle
                >
                <DialogDescription class="pt-2 text-sm text-foreground/80">
                    Are you sure you want to delete
                    <strong class="font-semibold text-foreground"
                        >"{{ cvToDelete?.name || 'this CV' }}"</strong
                    >? <br /><br />
                    This file will be permanently removed from storage.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter
                class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-end"
            >
                <Button
                    type="button"
                    variant="outline"
                    @click="isDeleteCvModalOpen = false"
                    :disabled="isDeletingCv"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="isDeletingCv"
                    @click="confirmDeleteCv"
                >
                    {{ isDeletingCv ? 'Deleting...' : 'Delete Resume' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
