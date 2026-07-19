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
    connection: { type: Object, default: null },
    companies: { type: Array, required: true },
});

const form = useForm({
    company_id: props.connection?.company_id ?? '',
    name: props.connection?.name ?? '',
    base_url: props.connection?.base_url ?? '',
    username: props.connection?.username ?? '',
    password: '',
    api_token: '',
    timeout_seconds: props.connection?.timeout_seconds ?? 30,
    is_default: props.connection?.is_default ?? false,
    status: props.connection?.status ?? 'active',
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
    <Head :title="mode === 'edit' ? 'Edit Zabbix Connection' : 'Create Zabbix Connection'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ mode === 'edit' ? 'Edit Zabbix Connection' : 'Create Zabbix Connection' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Store Zabbix API endpoints without touching the Zabbix database.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="company_id" value="Company" />
                            <select id="company_id" v-model="form.company_id" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="">Global</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.company_id" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Connection Name" />
                            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="base_url" value="Base URL" />
                            <TextInput id="base_url" v-model="form.base_url" class="mt-1 block w-full" placeholder="https://zabbix.example.com" />
                            <InputError class="mt-2" :message="form.errors.base_url" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="username" value="Username" />
                                <TextInput id="username" v-model="form.username" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.username" />
                            </div>
                            <div>
                                <InputLabel for="timeout_seconds" value="Timeout Seconds" />
                                <TextInput id="timeout_seconds" v-model="form.timeout_seconds" type="number" min="5" max="300" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.timeout_seconds" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel for="api_token" value="API Token" />
                                <TextInput id="api_token" v-model="form.api_token" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.api_token" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select id="status" v-model="form.status" class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <input v-model="form.is_default" type="checkbox" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                                    Default connection
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('zabbix-connections.index')" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Cancel
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ mode === 'edit' ? 'Update Connection' : 'Save Connection' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
