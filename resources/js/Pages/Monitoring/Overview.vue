<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    connection: {
        type: Object,
        required: true,
    },
    summaryCards: {
        type: Array,
        required: true,
    },
    severityCards: {
        type: Array,
        required: true,
    },
    hostRows: {
        type: Array,
        required: true,
    },
    problemRows: {
        type: Array,
        required: true,
    },
    eventRows: {
        type: Array,
        required: true,
    },
    graphRows: {
        type: Array,
        required: true,
    },
    availability: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Monitoring Overview" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Monitoring Overview</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Live data from Zabbix API without touching the Zabbix database.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full border border-sky-500/20 bg-sky-500/10 px-3 py-1 text-xs font-medium text-sky-700 dark:text-sky-300">
                        {{ connection.name }}
                    </span>
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        {{ connection.status }}
                    </span>
                </div>
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

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ card.value }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="card in severityCards"
                        :key="card.label"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950/60"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">{{ card.value }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 xl:grid-cols-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Top Problems</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Recent unresolved issues from Zabbix.</p>
                            </div>
                            <Link :href="route('monitoring.problems')" class="text-sm font-medium text-sky-600 hover:text-sky-500">View all</Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="problem in problemRows" :key="problem.event_id" class="rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-800">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">{{ problem.host }}</p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ problem.message }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-medium" :class="problem.severity_class">{{ problem.severity }}</span>
                                        <span class="rounded-full px-3 py-1 text-xs font-medium" :class="problem.status_class">{{ problem.status }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!problemRows.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No active problem returned by Zabbix.
                            </p>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Availability</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Current host reachability snapshot.</p>
                        </div>
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/60">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Uptime</p>
                                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ availability.uptime }}%</p>
                            </div>
                            <div class="grid grid-cols-3 gap-3 text-center text-sm">
                                <div class="rounded-xl bg-emerald-500/10 px-3 py-4 text-emerald-700 dark:text-emerald-300">
                                    <div class="text-lg font-semibold">{{ availability.online }}</div>
                                    <div>Online</div>
                                </div>
                                <div class="rounded-xl bg-rose-500/10 px-3 py-4 text-rose-700 dark:text-rose-300">
                                    <div class="text-lg font-semibold">{{ availability.offline }}</div>
                                    <div>Offline</div>
                                </div>
                                <div class="rounded-xl bg-slate-500/10 px-3 py-4 text-slate-700 dark:text-slate-300">
                                    <div class="text-lg font-semibold">{{ availability.unknown }}</div>
                                    <div>Unknown</div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid gap-6 xl:grid-cols-2">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Latest Events</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Newest event activity from Zabbix.</p>
                            </div>
                            <Link :href="route('monitoring.events')" class="text-sm font-medium text-sky-600 hover:text-sky-500">View all</Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="event in eventRows" :key="event.event_id" class="rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-800">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">{{ event.name }}</p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ event.clock_label }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-medium" :class="event.type_class">{{ event.type }}</span>
                                        <span class="rounded-full px-3 py-1 text-xs font-medium" :class="event.severity_class">{{ event.severity }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!eventRows.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No recent event returned by Zabbix.
                            </p>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Graph Samples</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Rendered directly from Zabbix graph metadata.</p>
                            </div>
                            <Link :href="route('monitoring.graphs')" class="text-sm font-medium text-sky-600 hover:text-sky-500">View all</Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="graph in graphRows" :key="graph.graph_id" class="rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-800">
                                <p class="font-medium text-slate-900 dark:text-white">{{ graph.name }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ graph.host }}</p>
                            </div>
                            <p v-if="!graphRows.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No graph returned by Zabbix.
                            </p>
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid gap-6 xl:grid-cols-2">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Host Snapshot</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Recent hosts, their availability, and latest values.</p>
                        </div>
                        <div class="space-y-3">
                            <div v-for="host in hostRows" :key="host.hostid" class="rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-800">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">{{ host.name }}</p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ host.ip || 'No IP' }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-medium" :class="host.availability_class">{{ host.availability }}</span>
                                </div>
                            </div>
                            <p v-if="!hostRows.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No host returned by Zabbix.
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
