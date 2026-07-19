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
    summary: {
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
    status: props.filters.status ?? '',
    availability: props.filters.availability ?? '',
});

const params = (page = 1) => ({
    search: form.search || undefined,
    status: form.status || undefined,
    availability: form.availability || undefined,
    per_page: props.pagination.per_page || 25,
    page,
});

const applyFilters = () => {
    router.get(route('monitoring.availability'), params(1), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const goToPage = (page) => {
    router.get(route('monitoring.availability'), params(page), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Monitoring Availability" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Availability</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Availability snapshot for the current monitoring scope.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Total Hosts</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ summary.total }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Uptime</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ summary.uptime }}%</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Online</p>
                        <p class="mt-3 text-3xl font-semibold text-emerald-600 dark:text-emerald-300">{{ summary.online }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Offline</p>
                        <p class="mt-3 text-3xl font-semibold text-rose-600 dark:text-rose-300">{{ summary.offline }}</p>
                    </div>
                </div>

                <div v-if="meta.message" class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ meta.message }}
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                            <input v-model="form.search" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" placeholder="Host, site, device" @keyup.enter="applyFilters">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Availability</label>
                            <select v-model="form.availability" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <option value="">All</option>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                                <option value="Unknown">Unknown</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                            <select v-model="form.status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                <option value="">All</option>
                                <option value="Enabled">Enabled</option>
                                <option value="Disabled">Disabled</option>
                                <option value="Unknown">Unknown</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-500" @click="applyFilters">Apply Filters</button>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Host</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Availability</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Site</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Device</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="host in items" :key="host.hostid">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ host.name }}</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ host.host }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="host.status_class">{{ host.status }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="host.availability_class">{{ host.availability }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    <div v-if="host.site">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ host.site.name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ host.site.company_name ?? 'Global' }}</div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    <div v-if="host.device">
                                        <div class="font-medium text-slate-900 dark:text-white">{{ host.device.hostname }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ host.device.device_type }}</div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No host data returned from Zabbix.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <MonitoringPagination :pagination="pagination" @change="goToPage" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
