<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MonitoringPagination from '@/Components/MonitoringPagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    connection: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    summary: {
        type: Array,
        required: true,
    },
    pagination: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});

const form = reactive({
    search: props.filters.search ?? '',
    severity: props.filters.severity ?? '',
    status: props.filters.status ?? '',
});

const params = (page = 1) => ({
    search: form.search || undefined,
    severity: form.severity || undefined,
    status: form.status || undefined,
    per_page: props.pagination.per_page || 25,
    page,
});

const applyFilters = () => {
    router.get(route('monitoring.problems'), params(1), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const goToPage = (page) => {
    router.get(route('monitoring.problems'), params(page), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const acknowledge = (problem) => {
    if (!confirm(`Acknowledge this problem on ${problem.host}?`)) {
        return;
    }

    const message = window.prompt('Ack message (optional)', 'Investigating');

    if (message === null) {
        return;
    }

    router.post(route('monitoring.problems.acknowledge'), {
        event_id: problem.event_id,
        message,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Monitoring Problems" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Problems</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Problem feed from Zabbix with search, status, and severity filters.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash.error" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                    {{ $page.props.flash.error }}
                </div>

                <div v-if="meta.message" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ meta.message }}
                </div>

                <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                            <input v-model="form.search" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" placeholder="Host, message, tag" @keyup.enter="applyFilters">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Severity</label>
                            <select v-model="form.severity" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <option value="">All</option>
                                <option value="Disaster">Disaster</option>
                                <option value="High">High</option>
                                <option value="Average">Average</option>
                                <option value="Warning">Warning</option>
                                <option value="Information">Information</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                            <select v-model="form.status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <option value="">All</option>
                                <option value="Open">Open</option>
                                <option value="Acknowledged">Acknowledged</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="w-full rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500" @click="applyFilters">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div v-for="card in summary" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ card.value }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-3 md:hidden">
                    <article v-for="problem in items" :key="problem.event_id" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ problem.host }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ problem.clock_label }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-medium" :class="problem.severity_class">{{ problem.severity }}</span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-medium" :class="problem.status_class">{{ problem.status }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ problem.tag || 'No tag' }}</span>
                        </div>

                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                            {{ problem.message }}
                        </p>

                        <div class="mt-4">
                            <button
                                v-if="!problem.acknowledged"
                                type="button"
                                class="w-full rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-500"
                                @click="acknowledge(problem)"
                            >
                                Acknowledge
                            </button>
                            <span v-else class="text-xs font-semibold text-emerald-600 dark:text-emerald-300">Acknowledged</span>
                        </div>
                    </article>
                    <div v-if="!items.length" class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                        No active problem found.
                    </div>
                </div>

                <div class="mt-6 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 md:block">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Severity</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Host</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tag</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Message</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="problem in items" :key="problem.event_id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="problem.severity_class">{{ problem.severity }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ problem.host }}</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ problem.clock_label }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ problem.duration }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="problem.status_class">{{ problem.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ problem.tag || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ problem.message }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        v-if="!problem.acknowledged"
                                        type="button"
                                        class="rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-500"
                                        @click="acknowledge(problem)"
                                    >
                                        Acknowledge
                                    </button>
                                    <span v-else class="text-xs font-semibold text-emerald-600 dark:text-emerald-300">Acknowledged</span>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">No active problem found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <MonitoringPagination :pagination="pagination" @change="goToPage" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
