<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    summaryCards: {
        type: Array,
        required: true,
    },
    notifications: {
        type: Array,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Notification Center" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Notification Center</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Skeleton for unread, system, and audit-driven notifications.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="meta.message" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ meta.message }}
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div v-for="card in summaryCards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ card.value }}</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ card.hint }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recent items</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Backed by activity trail until live notification routing is ready.</p>
                            </div>
                            <span class="rounded-full bg-sky-500/10 px-3 py-1 text-xs font-medium text-sky-600 dark:text-sky-300">Skeleton</span>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div v-for="notification in notifications" :key="notification.id" class="rounded-2xl border border-slate-200 px-4 py-4 dark:border-slate-800">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ notification.title }}</p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ notification.message }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-medium"
                                        :class="{
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300': notification.tone === 'success',
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-300': notification.tone === 'danger',
                                            'bg-sky-500/10 text-sky-600 dark:text-sky-300': notification.tone === 'info',
                                        }"
                                    >
                                        {{ notification.source }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs text-slate-400">{{ notification.time ?? 'Just now' }}</p>
                            </div>

                            <div v-if="!notifications.length" class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No notifications yet. This center will surface audit, monitoring, and delivery-state events.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Planned channels</h3>
                        <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                            <li>Problem acknowledgements</li>
                            <li>Host state transitions</li>
                            <li>Configuration changes</li>
                            <li>Tenant-level announcements</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
