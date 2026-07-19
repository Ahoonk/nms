<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    connections: { type: Object, required: true },
    companies: { type: Array, required: true },
});

const destroyConnection = (connection) => {
    if (confirm(`Delete ${connection.name}?`)) {
        router.delete(route('zabbix-connections.destroy', connection.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Zabbix Connections" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Zabbix Connections</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage API endpoints and credentials for Zabbix.</p>
                </div>
                <Link :href="route('zabbix-connections.create')" class="inline-flex items-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500">
                    New Connection
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ $page.props.flash.success }}
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Endpoint</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Default</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="connection in connections.data" :key="connection.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ connection.name }}</div>
                                    <div class="text-sm text-slate-500">Timeout {{ connection.timeout_seconds }}s</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ connection.company?.name ?? 'Global' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ connection.base_url }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ connection.is_default ? 'Yes' : 'No' }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium capitalize" :class="connection.status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-500'">
                                        {{ connection.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <Link :href="route('zabbix-connections.edit', connection.id)" class="text-sm font-medium text-sky-600 hover:text-sky-500">Edit</Link>
                                        <button type="button" class="text-sm font-medium text-rose-600 hover:text-rose-500" @click="destroyConnection(connection)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!connections.data.length">
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">No connection found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
