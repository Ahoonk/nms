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
    type: props.filters.type ?? '',
});

const params = (page = 1) => ({
    search: form.search || undefined,
    severity: form.severity || undefined,
    status: form.status || undefined,
    type: form.type || undefined,
    per_page: props.pagination.per_page || 25,
    page,
});

const applyFilters = () => {
    router.get(route('monitoring.events'), params(1), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const goToPage = (page) => {
    router.get(route('monitoring.events'), params(page), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Monitoring Events" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Events</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Latest problem and recovery activity from Zabbix with filters and paging.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="meta.message" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ meta.message }}
                </div>

                <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid gap-4 lg:grid-cols-5">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                            <input v-model="form.search" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" placeholder="Message, host, tag" @keyup.enter="applyFilters">
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
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type</label>
                            <select v-model="form.type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <option value="">All</option>
                                <option value="Problem">Problem</option>
                                <option value="Recovery">Recovery</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500" @click="applyFilters">Apply Filters</button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Severity</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="event in items" :key="event.event_id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ event.clock_label }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="event.type_class">{{ event.type }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="event.severity_class">{{ event.severity }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="event.status_class">{{ event.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ event.message }}</td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No recent event found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <MonitoringPagination :pagination="pagination" @change="goToPage" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
