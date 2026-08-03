<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref, toRefs, watch } from 'vue';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
    summaryCards: {
        type: Array,
        required: true,
    },
    telemetryCards: {
        type: Array,
        required: true,
    },
    availabilityPreview: {
        type: Object,
        required: true,
    },
});

const { summary, summaryCards, telemetryCards, availabilityPreview } = toRefs(props);

const selectedCompany = ref('');

const availabilityCompanies = computed(() => {
    const groups = new Map();

    for (const host of availabilityPreview.value?.items ?? []) {
        const companyName = host.site?.company_name?.trim() || 'Global';

        if (!groups.has(companyName)) {
            groups.set(companyName, {
                name: companyName,
                hosts: [],
                activeHosts: [],
            });
        }

        const group = groups.get(companyName);
        group.hosts.push(host);

        if ((host.availability ?? '').toLowerCase() === 'online') {
            group.activeHosts.push(host);
        }
    }

    return Array.from(groups.values()).sort((a, b) => a.name.localeCompare(b.name));
});

const selectedCompanyData = computed(() => {
    return availabilityCompanies.value.find((group) => group.name === selectedCompany.value) ?? availabilityCompanies.value[0] ?? null;
});

const selectCompany = (name) => {
    selectedCompany.value = name;
};

watch(
    availabilityCompanies,
    (groups) => {
        if (!groups.length) {
            selectedCompany.value = '';
            return;
        }

        if (!groups.some((group) => group.name === selectedCompany.value)) {
            selectedCompany.value = groups.find((group) => group.activeHosts.length > 0)?.name ?? groups[0].name;
        }
    },
    { immediate: true },
);
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
                    <div
                        v-for="card in telemetryCards"
                        :key="card.label"
                        class="rounded-2xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm dark:border-slate-800"
                    >
                        <p class="text-sm text-slate-400">{{ card.label }}</p>
                        <p class="mt-2 text-2xl font-semibold">{{ card.value }}</p>
                        <p class="mt-3 text-sm text-slate-400">{{ card.hint }}</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Availability by Company</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Pilih company untuk melihat host aktif yang sudah dikelompokkan berdasarkan badge availability.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-5 lg:grid-cols-[300px_minmax(0,1fr)]">
                            <div class="space-y-3">
                                <button
                                    v-for="company in availabilityCompanies"
                                    :key="company.name"
                                    type="button"
                                    class="w-full rounded-2xl border p-4 text-left transition hover:-translate-y-0.5 hover:shadow-md"
                                    :class="selectedCompanyData?.name === company.name ? 'border-sky-400 bg-sky-50 shadow-sm dark:border-sky-500 dark:bg-sky-950/40' : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/50'"
                                    @click="selectCompany(company.name)"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ company.name }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {{ company.activeHosts.length }} active host{{ company.activeHosts.length === 1 ? '' : 's' }} of {{ company.hosts.length }}
                                            </p>
                                        </div>
                                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-medium text-emerald-700 dark:text-emerald-300">
                                            {{ company.activeHosts.length }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span
                                            v-for="host in company.activeHosts.slice(0, 3)"
                                            :key="host.hostid"
                                            class="rounded-full px-2.5 py-1 text-[11px] font-medium"
                                            :class="host.availability_class"
                                        >
                                            {{ host.name }}
                                        </span>
                                        <span
                                            v-if="company.activeHosts.length > 3"
                                            class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            +{{ company.activeHosts.length - 3 }}
                                        </span>
                                    </div>
                                </button>

                                <div
                                    v-if="!availabilityCompanies.length"
                                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400"
                                >
                                    No company host data returned from Zabbix.
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950/50">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h4 class="text-base font-semibold text-slate-900 dark:text-white">
                                            {{ selectedCompanyData?.name ?? 'Select a company' }}
                                        </h4>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ selectedCompanyData ? `${selectedCompanyData.activeHosts.length} active host(s) ready to monitor` : 'Klik company di sebelah kiri untuk melihat host aktif.' }}
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ selectedCompanyData?.hosts.length ?? 0 }} total
                                    </span>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    <button
                                        v-for="host in selectedCompanyData?.activeHosts ?? []"
                                        :key="host.hostid"
                                        type="button"
                                        class="group inline-flex items-center gap-2 rounded-full border px-3 py-2 text-left text-sm transition hover:-translate-y-0.5 hover:shadow-sm"
                                        :class="host.availability === 'Online' ? 'border-emerald-200 bg-emerald-500/10 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300' : 'border-slate-200 bg-white text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200'"
                                    >
                                        <span
                                            class="h-2.5 w-2.5 rounded-full"
                                            :class="host.availability === 'Online' ? 'bg-emerald-500' : 'bg-slate-400'"
                                        />
                                        <span class="max-w-[14rem] truncate font-medium">
                                            {{ host.name }}
                                        </span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" :class="host.status_class">
                                            {{ host.status }}
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-if="selectedCompanyData && !selectedCompanyData.activeHosts.length"
                                    class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400"
                                >
                                    Tidak ada host aktif untuk company ini.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Availability Preview</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Snapshot host, status availability, dan device dari menu Monitoring.
                                </p>
                            </div>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-medium"
                                :class="availabilityPreview.meta?.message ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'"
                            >
                                {{ availabilityPreview.meta?.message ? 'No Connection' : 'Live' }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Hosts</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">
                                    {{ availabilityPreview.summary?.total ?? 0 }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Online</p>
                                <p class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-300">
                                    {{ availabilityPreview.summary?.online ?? 0 }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/80">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Offline</p>
                                <p class="mt-2 text-2xl font-semibold text-rose-600 dark:text-rose-300">
                                    {{ availabilityPreview.summary?.offline ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
