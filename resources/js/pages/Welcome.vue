<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { dashboard, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-3">
                <ThemeToggle />
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <!-- @chisel-registration -->
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                    <!-- @end-chisel-registration -->
                </template>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
            >
                <div
                    class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h1 class="mb-1 text-2xl font-bold">Trackly</h1>
                    <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                        Your personal job application & interview tracker. Never
                        lose track of an opportunity, recruiter contact, or
                        follow-up deadline again.
                    </p>
                    <ul class="mb-6 flex flex-col gap-2.5">
                        <li
                            class="flex items-center gap-2 text-sm text-foreground"
                        >
                            <span class="size-2 rounded-full bg-emerald-500" />
                            <span
                                ><strong>Full Pipeline:</strong> Track from
                                'Will Apply' to 'Offer'.</span
                            >
                        </li>
                        <li
                            class="flex items-center gap-2 text-sm text-foreground"
                        >
                            <span class="size-2 rounded-full bg-purple-500" />
                            <span
                                ><strong>Interviews & Reminders:</strong> Never
                                miss a follow-up email.</span
                            >
                        </li>
                        <li
                            class="flex items-center gap-2 text-sm text-foreground"
                        >
                            <span class="size-2 rounded-full bg-blue-500" />
                            <span
                                ><strong>Resume Management:</strong> Link the
                                exact CV used for each role.</span
                            >
                        </li>
                    </ul>
                    <div class="flex gap-3 text-sm leading-normal">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm font-medium text-white hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="register()"
                                class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm font-medium text-white hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                            >
                                Get Started Free
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-block rounded-sm border border-[#19140035] px-5 py-2 text-sm font-medium text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Log in
                            </Link>
                        </template>
                    </div>
                </div>
                <div
                    class="relative -mb-px flex aspect-[335/364] w-full shrink-0 items-center justify-center overflow-hidden rounded-t-lg border-l border-border/40 bg-gradient-to-br from-sky-50 via-sky-100/40 to-indigo-50 p-8 lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:from-slate-900 dark:via-sky-950/40 dark:to-slate-900"
                >
                    <div
                        class="flex flex-col items-center justify-center space-y-4 p-6 text-center"
                    >
                        <img
                            src="/images/logo.png"
                            alt="Trackly Logo"
                            class="w-full max-w-[280px] object-contain drop-shadow-xs dark:hidden"
                        />
                        <img
                            src="/images/logo-dark.png"
                            alt="Trackly Logo"
                            class="hidden w-full max-w-[280px] object-contain drop-shadow-md dark:block"
                        />
                        <div
                            class="inline-flex items-center gap-1.5 rounded-full border border-sky-500/20 bg-sky-500/10 px-3 py-1 text-xs font-semibold text-sky-700 dark:text-sky-300"
                        >
                            <span>Job Application Tracker</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
