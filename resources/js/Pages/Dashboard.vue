<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    summary: {
        type: Object,
        required: true,
    },
    summaryCards: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="NMS Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-sky-500 dark:text-sky-400">
                    Network Monitoring System
                </p>
                <h2 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-100">
                    Dashboard
                </h2>
                <p class="max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                    Fondasi portal monitoring sudah siap. Tahap berikutnya akan menghubungkan data live dari Zabbix API, company scope, site inventory, dan alert stream.
                </p>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
                    >
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            {{ card.label }}
                        </p>
                        <div class="mt-4 flex items-end justify-between gap-4">
                            <div class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">
                                {{ card.value }}
                            </div>
                            <span class="rounded-full bg-sky-500/10 px-3 py-1 text-xs font-medium text-sky-600 dark:text-sky-400">
                                Stage 1
                            </span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                            {{ card.hint }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm dark:border-slate-800">
                        <p class="text-sm text-slate-400">CPU</p>
                        <p class="mt-2 text-2xl font-semibold">Ready for Zabbix</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm dark:border-slate-800">
                        <p class="text-sm text-slate-400">RAM</p>
                        <p class="mt-2 text-2xl font-semibold">Ready for Zabbix</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm dark:border-slate-800">
                        <p class="text-sm text-slate-400">Bandwidth</p>
                        <p class="mt-2 text-2xl font-semibold">Ready for Zabbix</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm dark:border-slate-800">
                        <p class="text-sm text-slate-400">Availability</p>
                        <p class="mt-2 text-2xl font-semibold">Ready for Zabbix</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Monitoring Pipeline</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    User -> Laravel Dashboard -> Service Layer -> Repository -> Zabbix API -> Zabbix Server
                                </p>
                            </div>
                            <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                Ready
                            </span>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Auth</p>
                                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">Laravel Breeze + Inertia + Vue 3</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">RBAC</p>
                                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">Spatie Permission with 4 roles</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Monitoring Engine</p>
                                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">Zabbix API only, no direct DB access</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Scope</p>
                                <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white">100 company, 5.000 site, 50.000 device</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Next Stage</h3>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                            <li>Service layer untuk Zabbix API</li>
                            <li>Repository dan DTO untuk domain company/site/device</li>
                            <li>Company scoped dashboard & navigation</li>
                            <li>Problem, event, graph, dan availability widgets</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
