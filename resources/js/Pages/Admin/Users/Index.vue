<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MonitoringPagination from '@/Components/MonitoringPagination.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    users: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth.user?.id);

const destroyUser = (user) => {
    if (confirm(`Delete ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
        });
    }
};

const goToPage = (pageNumber) => {
    router.get(route('users.index', { page: pageNumber }), {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Users</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage portal accounts and assign company scope.</p>
                </div>
                <Link
                    :href="route('users.create')"
                    class="inline-flex items-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500"
                >
                    New User
                </Link>
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

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ user.name }}</div>
                                    <div class="text-sm text-slate-500">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ user.company?.name ?? 'Global' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium bg-sky-500/10 text-sky-700 dark:text-sky-300">
                                        {{ user.role ?? 'No role' }}
                                    </span>
                                    <span
                                        v-if="user.is_super_admin"
                                        class="ms-2 rounded-full px-3 py-1 text-xs font-medium bg-amber-500/10 text-amber-700 dark:text-amber-300"
                                    >
                                        Super Admin
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="user.status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-500'"
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div v-if="user.id !== currentUserId" class="inline-flex items-center gap-3">
                                        <Link :href="route('users.edit', user.id)" class="text-sm font-medium text-sky-600 hover:text-sky-500">Edit</Link>
                                        <button type="button" class="text-sm font-medium text-rose-600 hover:text-rose-500" @click="destroyUser(user)">Delete</button>
                                    </div>
                                    <span v-else class="text-xs font-semibold text-slate-500 dark:text-slate-400">Current account</span>
                                </td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No user found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <MonitoringPagination :pagination="users" @change="goToPage" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
