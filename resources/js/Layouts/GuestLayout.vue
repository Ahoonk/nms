<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-gray-100 py-6 dark:bg-gray-900">
        <img
            src="/images/background-login.png"
            alt=""
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 h-full w-full object-cover object-center opacity-20"
        />
        <div class="absolute inset-0 bg-white/86 dark:bg-slate-950/80"></div>

        <div class="relative mx-auto flex w-full max-w-xl flex-col px-4 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center gap-2 sm:mb-5">
                <Link href="/" class="shrink-0">
                    <ApplicationLogo class="block h-12 w-auto object-contain" />
                </Link>

                <div class="min-w-0 border-l border-slate-300 pl-3 dark:border-slate-700 sm:pl-4">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.35em] text-sky-700/80 dark:text-sky-300/80">
                        ASKA Portal
                    </p>
                    <h1
                        class="mt-1 text-xl font-semibold leading-none tracking-tight text-slate-900 dark:text-white sm:text-3xl"
                    >
                        <span class="block">
                            Network <span class="text-sky-600 dark:text-sky-400">Monitoring</span>
                        </span>
                        <span class="block text-slate-700 dark:text-slate-300">
                            System
                        </span>
                    </h1>
                </div>
            </div>

            <div
                class="w-full overflow-hidden bg-white px-4 py-5 shadow-md sm:rounded-lg dark:bg-gray-800 sm:px-5"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
