<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import MonitoringPagination from '@/Components/MonitoringPagination.vue';

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const form = reactive({
    search: props.filters.search ?? '',
    action: props.filters.action ?? '',
});

const applyFilters = () => {
    router.get(route('activity-trail.index'), {
        search: form.search || undefined,
        action: form.action || undefined,
        per_page: props.filters.per_page || 15,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const goToPage = (page) => {
    router.get(route('activity-trail.index'), {
        search: form.search || undefined,
        action: form.action || undefined,
        per_page: props.filters.per_page || 15,
        page,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Activity Trail" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Activity Trail</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Audit log of user actions, subject changes, and request context.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    placeholder="Action, description, route..."
                                    @keyup.enter="applyFilters"
                                >
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Action</label>
                                <input
                                    v-model="form.action"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    placeholder="company.created"
                                    @keyup.enter="applyFilters"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm dark:border-sky-900 dark:bg-sky-950/30">
                        <p class="text-sm font-medium text-sky-700 dark:text-sky-300">Scope</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">Company-scoped where applicable</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Super admins can see all logs. Company admins see their own tenant trail.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Actor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Route</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ log.created_at }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ log.user?.name ?? 'System' }}</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ log.user?.company?.name ?? 'Global' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ log.action }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ log.subject_type?.split('\\').pop() ?? '-' }}<span v-if="log.subject_id"> #{{ log.subject_id }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ log.description ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ log.route ?? '-' }}</td>
                            </tr>
                            <tr v-if="!logs.data.length">
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No activity trail found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <MonitoringPagination :pagination="logs" @change="goToPage" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
