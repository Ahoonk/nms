<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MonitoringPagination from '@/Components/MonitoringPagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
    connection: {
        type: Object,
        required: true,
    },
    hosts: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    selectedHostId: {
        type: Number,
        default: null,
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

const selectedHost = ref(props.selectedHostId);
const form = reactive({
    search: props.filters.search ?? '',
});

const params = (page = 1) => ({
    host_id: selectedHost.value || undefined,
    search: form.search || undefined,
    per_page: props.pagination.per_page || 20,
    page,
});

const applyFilter = () => {
    router.get(route('monitoring.graphs', params(1)), {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const goToPage = (page) => {
    router.get(route('monitoring.graphs', params(page)), {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Monitoring Graphs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Graphs</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Graphs are pulled from Zabbix, not re-created inside Laravel.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="min-w-0">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Host Filter</label>
                        <select
                            v-model="selectedHost"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            @change="goToPage(1)"
                        >
                            <option :value="null">All hosts</option>
                            <option v-for="host in hosts" :key="host.hostid" :value="Number(host.hostid)">
                                {{ host.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                        <input
                            v-model="form.search"
                            type="text"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            placeholder="Graph name or host"
                            @keyup.enter="applyFilter"
                        >
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="meta.message" class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ meta.message }}
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Total Graphs</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ pagination.total }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Selected Host</p>
                        <p class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ hosts.find((host) => Number(host.hostid) === Number(selectedHost))?.name || 'All hosts' }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:hidden">
                    <div v-for="graph in items" :key="graph.graph_id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-4 py-4 dark:border-slate-800">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ graph.name }}</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ graph.host }} - {{ graph.size }}</p>
                        </div>
                        <div class="flex justify-center bg-white p-3 dark:bg-slate-900">
                            <img
                                v-if="graph.image"
                                :src="graph.image"
                                :alt="graph.name"
                                class="w-full rounded-lg border border-slate-200 object-contain dark:border-slate-800"
                            >
                            <div
                                v-else
                                class="flex h-44 items-center justify-center text-sm text-slate-500"
                            >
                                Graph belum tersedia
                            </div>
                        </div>
                    </div>
                    <div v-if="!items.length" class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                        No graph returned from Zabbix.
                    </div>
                </div>

                <div class="mt-6 hidden gap-4 md:grid md:grid-cols-1 lg:grid-cols-2">
                    <div v-for="graph in items" :key="graph.graph_id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ graph.name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ graph.host }} - {{ graph.size }}</p>
                        </div>

                        <div class="flex justify-center bg-white p-3 dark:bg-slate-900">
                            <img
                                v-if="graph.image"
                                :src="graph.image"
                                :alt="graph.name"
                                class="w-full rounded-lg border border-slate-200 object-contain dark:border-slate-800"
                            >
                            <div
                                v-else
                                class="flex h-56 items-center justify-center text-slate-500"
                            >
                                Graph belum tersedia
                            </div>
                        </div>
                    </div>
                    <div v-if="!items.length" class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 lg:col-span-2">
                        No graph returned from Zabbix.
                    </div>
                </div>

                <MonitoringPagination :pagination="pagination" @change="goToPage" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
