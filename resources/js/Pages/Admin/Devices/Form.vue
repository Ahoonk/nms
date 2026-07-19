<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    mode: { type: String, required: true },
    action: { type: String, required: true },
    method: { type: String, required: true },
    device: { type: Object, default: null },
    sites: { type: Array, required: true },
    deviceTypeOptions: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});

const form = useForm({
    site_id: props.device?.site_id ?? '',
    device_type: props.device?.device_type ?? 'router',
    hostname: props.device?.hostname ?? '',
    ip: props.device?.ip ?? '',
    vendor: props.device?.vendor ?? '',
    model: props.device?.model ?? '',
    serial_number: props.device?.serial_number ?? '',
    mac: props.device?.mac ?? '',
    os: props.device?.os ?? '',
    status: props.device?.status ?? 'unknown',
    zabbix_host_id: props.device?.zabbix_host_id ?? '',
});

const submit = () => {
    if (props.mode === 'edit') {
        form.put(props.action);
        return;
    }

    form.post(props.action);
};
</script>

<template>
    <Head :title="mode === 'edit' ? 'Edit Device' : 'Create Device'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ mode === 'edit' ? 'Edit Device' : 'Create Device' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Register monitored hardware and its Zabbix host reference.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="site_id" value="Site" />
                            <select id="site_id" v-model="form.site_id" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="">Select site</option>
                                <option v-for="site in sites" :key="site.id" :value="site.id">
                                    {{ site.company_name ? `${site.company_name} - ${site.name}` : site.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.site_id" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="device_type" value="Device Type" />
                                <select id="device_type" v-model="form.device_type" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    <option v-for="option in deviceTypeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.device_type" />
                            </div>
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="hostname" value="Hostname" />
                            <TextInput id="hostname" v-model="form.hostname" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.hostname" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="ip" value="IP Address" />
                                <TextInput id="ip" v-model="form.ip" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.ip" />
                            </div>
                            <div>
                                <InputLabel for="mac" value="MAC Address" />
                                <TextInput id="mac" v-model="form.mac" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.mac" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="vendor" value="Vendor" />
                                <TextInput id="vendor" v-model="form.vendor" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.vendor" />
                            </div>
                            <div>
                                <InputLabel for="model" value="Model" />
                                <TextInput id="model" v-model="form.model" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.model" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="serial_number" value="Serial Number" />
                                <TextInput id="serial_number" v-model="form.serial_number" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.serial_number" />
                            </div>
                            <div>
                                <InputLabel for="os" value="OS" />
                                <TextInput id="os" v-model="form.os" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.os" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="zabbix_host_id" value="Zabbix Host ID" />
                            <TextInput id="zabbix_host_id" v-model="form.zabbix_host_id" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.zabbix_host_id" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('devices.index')" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancel
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ mode === 'edit' ? 'Update Device' : 'Save Device' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
