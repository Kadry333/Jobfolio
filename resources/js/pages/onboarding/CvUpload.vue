<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    FileUp,
    Sparkles,
    UploadCloud,
} from '@lucide/vue';
import { ref } from 'vue';
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

const props = defineProps<{
    user: {
        firstName?: string;
        name: string;
    };
}>();

const selectedFile = ref<File | null>(null);
const cvName = ref('');
const isDragging = ref(false);
const processing = ref(false);
const errorMessage = ref('');

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        setFile(target.files[0]);
    }
}

function onDrop(e: DragEvent) {
    isDragging.value = false;
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        setFile(e.dataTransfer.files[0]);
    }
}

function setFile(file: File) {
    selectedFile.value = file;
    if (!cvName.value) {
        cvName.value = file.name.replace(/\.[^/.]+$/, '');
    }
    errorMessage.value = '';
}

function submit() {
    if (!selectedFile.value) {
        errorMessage.value =
            'Please choose a file to upload or click "Skip for now".';
        return;
    }

    processing.value = true;
    errorMessage.value = '';

    const formData = new FormData();
    formData.append('cv_file', selectedFile.value);
    if (cvName.value) {
        formData.append('name', cvName.value);
    }

    router.post('/onboarding/cv', formData, {
        onError: (errs) => {
            errorMessage.value = Object.values(errs)[0] as string;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Upload Your Default CV - jobfolio" />

    <div
        class="flex min-h-screen flex-col items-center justify-center bg-muted/30 p-4 sm:p-6"
    >
        <div class="w-full max-w-lg space-y-6">
            <!-- Brand Badge -->
            <div class="space-y-2 text-center">
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                >
                    <Sparkles class="size-3.5" />
                    <span>Welcome to jobfolio</span>
                </div>
                <h1
                    class="text-3xl font-extrabold tracking-tight text-foreground"
                >
                    Set up your default CV
                </h1>
                <p class="mx-auto max-w-sm text-sm text-muted-foreground">
                    Hi {{ user.firstName || user.name }}! Uploading a default CV
                    makes applying to jobs fast. You can always change or add
                    more later.
                </p>
            </div>

            <!-- Upload Card -->
            <Card class="border-border/80 shadow-md">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg">Upload Resume</CardTitle>
                        <span
                            class="rounded bg-muted px-2 py-0.5 text-xs font-medium tracking-wider text-muted-foreground uppercase"
                        >
                            Optional Step
                        </span>
                    </div>
                    <CardDescription>
                        Accepted formats: PDF, DOC, or DOCX (max 10MB).
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Dropzone -->
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="onDrop"
                        class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                        :class="
                            isDragging
                                ? 'border-primary bg-primary/5'
                                : 'border-muted-foreground/25 hover:border-muted-foreground/50 hover:bg-muted/40'
                        "
                        @click="($refs.fileInput as HTMLInputElement).click()"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            class="hidden"
                            @change="onFileChange"
                        />

                        <div
                            class="rounded-full bg-primary/10 p-3 text-primary"
                        >
                            <UploadCloud class="size-8" />
                        </div>

                        <div v-if="!selectedFile" class="space-y-1">
                            <p class="text-sm font-semibold text-foreground">
                                Click to browse or drag and drop your resume
                            </p>
                            <p class="text-xs text-muted-foreground">
                                PDF, DOC, DOCX up to 10MB
                            </p>
                        </div>

                        <div v-else class="space-y-1">
                            <div
                                class="flex items-center gap-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                <CheckCircle2 class="size-4" />
                                <span>{{ selectedFile.name }}</span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                ({{
                                    (selectedFile.size / 1024 / 1024).toFixed(2)
                                }}
                                MB) • Click to replace
                            </p>
                        </div>
                    </div>

                    <!-- CV Title input -->
                    <div v-if="selectedFile" class="space-y-1.5 pt-1">
                        <Label for="cvName">CV Label / Title</Label>
                        <Input
                            id="cvName"
                            v-model="cvName"
                            placeholder="e.g. Fullstack Developer CV"
                        />
                    </div>

                    <p v-if="errorMessage" class="text-xs text-destructive">
                        {{ errorMessage }}
                    </p>
                </CardContent>

                <CardFooter
                    class="flex flex-col items-center justify-between gap-3 border-t border-border/50 pt-2 sm:flex-row"
                >
                    <Link
                        href="/dashboard"
                        class="order-2 text-xs font-medium text-muted-foreground underline-offset-4 hover:text-foreground hover:underline sm:order-1"
                    >
                        Skip for now, I'll do this later
                    </Link>

                    <Button
                        type="button"
                        @click="submit"
                        :disabled="processing || !selectedFile"
                        class="order-1 w-full gap-2 sm:order-2 sm:w-auto"
                    >
                        <span>Save & Go to Dashboard</span>
                        <ArrowRight class="size-4" />
                    </Button>
                </CardFooter>
            </Card>

            <div class="text-center text-xs text-muted-foreground">
                You can manage all your CVs anytime from your jobfolio
                dashboard.
            </div>
        </div>
    </div>
</template>
